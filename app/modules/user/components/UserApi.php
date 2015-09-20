<?php
namespace user\components;

use advert\components\PartsApi;
use Exception;
use partner\components\PartnersApi;
use user\exception\UserApiException;
use user\forms\ChangePassword;
use user\forms\LostPassword;
use user\forms\Profile;
use user\forms\Register;
use user\models\SocialAccount;
use user\models\User;
use user\models\UserConfirmation;
use Yii;
use yii\authclient\clients\Facebook;
use yii\authclient\clients\GoogleOAuth;
use yii\authclient\clients\VKontakte;
use yii\authclient\clients\YandexOAuth;
use yii\base\Component;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

/**
 * API для работы с пользователями
 */
class UserApi extends Component
{
    /**
     * Активировать пользователя, если его аккаунт требует подтверждения.
     * Используется как доверительная активация через соц. сеть и как подметод метода confirmUserRegistration.
     *
     * @param User $user
     * @param mixed $confirmation модель подтверждения, по умолчанию - null, будет взята из модели пользователя
     * @return User|null
     * @throws UserApiException
     */
    public function trustUserConfirmation(User $user, $confirmation = null)
    {
        $ret = null;

        if (!($confirmation instanceof UserConfirmation) || $confirmation->user_id != $user->id) {
            $confirmation = $user->getConfirmation();
        }

        $transaction = User::getDb()->beginTransaction();
        try {
            // очистить код подтверждения
            $confirmation->email_confirmation = null;
            $confirmation->email = null;
            if (!$confirmation->save(true, ['email', 'email_confirmation'])) {
                // не удалось сохранить модель подтверждения
                throw new UserApiException('', UserApiException::VALIDATION_ERROR);
            }

            // сохранить пользователя
            $user->status = User::STATUS_ACTIVE;
            if (!$user->save(true, ['status'])) {
                // не удалось сохранить пользователя
                throw new UserApiException('', UserApiException::VALIDATION_ERROR);
            }

            /* @var $advertApi PartsApi */
            $advertApi = Yii::$app->getModule('advert')->parts;
            // прикрепить к пользователю все его неавторизованные объявления
            $advertApi->attachNotAuthAdvertsToUser($user);

            $transaction->commit();

            $ret = $user->id;
        } catch (Exception $ex) {
            $transaction->rollBack();
            $ret = null;
            throw new UserApiException('', UserApiException::CONFIRMATION_ERROR, $ex);
        }

        return $ret;
    }

    /**
     * Подтвердить регистрацию пользователя.
     * На вход передается код подтверждения.
     * В случае, если код переданный неверный, либо у пользователя статус отличен от
     * User::STATUS_WAIT_CONFIRMATION - генерируется ошибка.
     * В случае успеха пользователь активируется и передается его идентификатор.
     * Иначе, передается null.
     *
     * @param string $code
     * @return int|null идентификатор пользователя в случае успеха
     * @throws NotFoundHttpException
     * @throws UserApiException
     */
    public function confirmUserRegistration($code)
    {
        $code = (string) $code;
        if (!$code) {
            // без кода не работаем
            throw new NotFoundHttpException();
        }

        /* @var $confirmation UserConfirmation */
        $confirmation = UserConfirmation::find()->where(['email_confirmation' => $code])->one();
        if (!$confirmation) {
            // неверный код подтверждения
            throw new NotFoundHttpException();
        }

        /* @var $user User */
        $user = $confirmation->user;
        if (!$user || !$user->needConfirmation()) {
            // пользователь не найден
            throw new NotFoundHttpException();
        }

        // подтверждение
        return $this->trustUserConfirmation($user, $confirmation);
    }

    /**
     * Выслать письмо пользователю с подтверждением e-mail.
     * Возможно только в том случае, если статус = Ожидает подтверждения.
     *
     * @param User $user
     * @return boolean
     */
    public function sendEmailConfirmation(User $user)
    {
        if ($user->status == User::STATUS_WAIT_CONFIRMATION && $confirmation = $user->getConfirmation()) {
            $confirmationLink = Url::toRoute(['/user/user/confirmation', 'code' => $confirmation->email_confirmation], true);
            return Yii::$app->mailer->compose('userEmailConfirmation', [
                'user' => $user,
                'confirmationLink' => $confirmationLink,
            ])
            ->setTo($confirmation->email)
            ->setSubject(Yii::t('mail_subjects', 'E-mail confirmation'))
            ->send();
        }
        return false;
    }

    /**
     * Прикрпепить к пользователю соц. сеть, если ее еще нет
     *
     * @param User $user
     * @param ExternalUser $externalUser
     */
    public function attachUserServiceAccount(User $user, ExternalUser $externalUser)
    {
        foreach ($user->socialAccounts as $account) {
            /* @var $account SocialAccount */
            if ($account->code == $externalUser->code) {
                // у пользователя уже есть такая соц. сеть
                // выходим из метода
                return;
            }
        }

        // создать привязку к соц. сети
        $socialAccount = new SocialAccount();
        $socialAccount->setAttributes([
            'user_id' => $user->id,
            'code' => (string) $externalUser->code,
            'external_uid' => (string) $externalUser->id,
            'external_name' => (string) $externalUser->external_name,
            'external_page' => (string) $externalUser->external_page,
        ]);

        try {
            $socialAccount->save();
        } catch (Exception $ex) { }

        unset ($user->socialAccounts);
    }

    /**
     * Зарегистрировать пользователя из соц. сети.
     * В случае успеха - возвращает модель пользователя, иначе - генерирует исключение.
     *
     * @param ExternalUser $externalUser
     * @return User|null
     * @throws UserApiException
     */
    public function registerSocialUser(ExternalUser $externalUser)
    {
        $ret = null;

        $transaction = User::getDb()->beginTransaction();

        try {
            $user = new User();
            $user->setAttributes([
                'status' => User::STATUS_ACTIVE,
                'email' => $externalUser->email,
                'name' => $externalUser->name,
                'type' => User::TYPE_PRIVATE_PERSON,
            ]);
            if (!$user->save()) {
                throw new UserApiException('', UserApiException::VALIDATION_ERROR, $ex);
            }

            // прикрепить к пользователю все неавторизованные его объявления
            /* @var $advertApi PartsApi */
            $advertApi = Yii::$app->getModule('advert')->parts;
            // прикрепить к пользователю все его неавторизованные объявления
            $advertApi->attachNotAuthAdvertsToUser($user);
            $transaction->commit();

            $ret = $user;
        }
        catch (Exception $ex) {
            $transaction->rollBack();
            $ret = null;
            throw new UserApiException('', UserApiException::REGISTRATION_ERROR, $ex);
        }

        return $ret;
    }

    /**
     * Регистрация пользователя. На вход передается заполненная форма регистрации.
     *
     * @param Register $form
     * @return User|null модель зарегистрированного пользователя в случае успеха
     * @throws UserApiException
     */
    public function registerUser(Register $form)
    {
        $ret = null;

        if ($form->validate()) {
            $transaction = User::getDb()->beginTransaction();

            try {
                // создать модель пользователя
                $user = new User();
                $user->setAttributes([
                    'status' => User::STATUS_WAIT_CONFIRMATION,
                    'email' => $form->email,
                    'name' => $form->name,
                    'new_password' => $form->password,
                    'type' => $form->type,
                ]);
                if (!$user->save()) {
                    throw new UserApiException('', UserApiException::VALIDATION_ERROR);
                }

                // создать модель партнера, если регистрируемся как партнер
                if ($form->type == User::TYPE_LEGAL_PERSON) {
                    /* @var $partnersApi PartnersApi */
                    $partnersApi = Yii::$app->getModule('partner')->api;
                    $partnersApi->registerPartner($form, $user);
                }

                // сгенерировать подтверждение
                $confirmation = $user->getConfirmation();
                if (!$confirmation) {
                    throw new UserApiException('', UserApiException::DATABASE_ERROR);
                }

                $confirmation->setAttributes([
                    'email' => $user->email,
                    'email_confirmation' => md5(uniqid() . $user->id . time()),
                ]);
                if (!$confirmation->save()) {
                    throw new UserApiException('', UserApiException::VALIDATION_ERROR);
                }
                // отправить e-mail уведомление
                $this->sendEmailConfirmation($user);

                $transaction->commit();

                $ret = $user;
            }
            catch (Exception $ex) {
                $transaction->rollBack();
                $ret = null;
                throw new UserApiException('', UserApiException::REGISTRATION_ERROR, $ex);
            }
        }

        return $ret;
    }

    /**
     * Восстановление пароля: отправить e-mail уведомление.
     * На вход передается форма восстановления.
     * Метод генерирует строку подтверждения и отправляет e-mail уведомление.
     *
     * @param LostPassword $form
     * @return boolean true в случае успеха
     * @throws UserApiException
     */
    public function lostPassword(LostPassword $form)
    {
        $ret = false;

        /* @var $user User */
        $user = $form->getUser();

        /* @var $confirmation UserConfirmation */
        $confirmation = $user->getConfirmation();

        $transaction = $user->getDb()->beginTransaction();

        try {
            $confirmation->restore_confirmation = md5(uniqid() . $user->email . $user->id . time());
            if (!$confirmation->save(true, ['restore_confirmation'])) {
                throw new UserApiException('', UserApiException::VALIDATION_ERROR);
            }

            $confirmationLink = Url::toRoute(['/user/user/change-password', 'code' => $confirmation->restore_confirmation], true);
            // отправить e-mail уведомление
            $ret = Yii::$app->mailer->compose('userLostPassword', [
                'user' => $user,
                'confirmationLink' => $confirmationLink,
            ])
            ->setTo($user->email)
            ->setSubject(Yii::t('mail_subjects', 'Restore password'))
            ->send();

            $transaction->commit();
        }
        catch (Exception $ex) {
            $transaction->rollBack();
            $ret = false;
            UserApiException::throwUp($ex);
        }

        return $ret;
    }

    /**
     * Получить пользователя по коду подтверждения для восстановления пароля.
     * Возвращает модель пользователя или null.
     *
     * @param string $code код подтверждения
     * @return User|null
     */
    public function getUserByRestoreConfirmation($code)
    {
        $ret = null;
        $code = (string) $code;
        if (!$code) {
            return $ret;
        }

        /* @var $res UserConfirmation */
        $res = UserConfirmation::find()->where(['restore_confirmation' => $code])->one();
        if ($res) {
            $ret = $res->user;
        }
        return $ret;
    }

    /**
     * Изменение пароля пользователя.
     * Метод устанавливает новый пароль у пользователя и очищает строку
     * подтверждения для восстановления пароля.
     * В случае успеха возвращает true.
     *
     * @param User $user
     * @param ChangePassword $form
     * @return boolean
     * @throws UserApiException
     */
    public function changePassword(User $user, ChangePassword $form)
    {
        $ret = false;

        $transaction = $user->getDb()->beginTransaction();

        try {
            // очистить строку подтверждения
            $confirmation = $user->getConfirmation();
            $confirmation->restore_confirmation = null;
            if (!$confirmation->save(true, ['restore_confirmation'])) {
                throw new UserApiException('', UserApiException::VALIDATION_ERROR);
            }

            // изменить пароль
            $user->new_password = $form->password;
            if (!$user->save()) {
                throw new UserApiException('', UserApiException::VALIDATION_ERROR);
            }

            $transaction->commit();

            $ret = true;
        }
        catch (Exception $ex) {
            $transaction->rollBack();
            $ret = false;
            UserApiException::throwUp($ex);
        }

        return $ret;
    }

    /**
     * Отправить уведомление пользователю на изменение e-mail.
     * В случае успеха возвращает true.
     *
     * @param User $user
     * @param Profile $profile
     * @return boolean
     * @throws UserApiException
     */
    public function setNewUserEmail(User $user, Profile $profile)
    {
        $ret = false;

        $transaction = User::getDb()->beginTransaction();

        try {
            // сохранить подтверждение
            $confirmation = $user->getConfirmation();
            $confirmation->email = $profile->email;
            $confirmation->email_confirmation = md5($user->id . $user->email . $profile->email . uniqid());
            if (!$confirmation->save()) {
                throw new UserApiException('', UserApiException::VALIDATION_ERROR);
            }

            // отправить уведомление
            $confirmationLink = Url::toRoute(['/user/profile/change-email',
                'code' => $confirmation->email_confirmation
            ], true);
            Yii::$app->mailer->compose('userChangeEmailConfirmation', [
                'user' => $user,
                'confirmationLink' => $confirmationLink,
            ])
            ->setTo($confirmation->email)
            ->setSubject(Yii::t('mail_subjects', 'E-mail confirmation'))
            ->send();

            $transaction->commit();

            $ret = true;
        }
        catch (Exception $ex) {
            $transaction->rollBack();
            $ret = false;
            UserApiException::throwUp($ex);
        }

        return $ret;
    }

    /**
     * Изменить e-mail пользователя по коду подтверждения.
     * В случае ошибки генерирует Exception с описанием ошибки.
     * В случае успеха возвращает true.
     *
     * @param User $user модель пользователя
     * @param string $code код подтверждения
     * @throws UserApiException
     */
    public function changeUserEmail(User $user, $code)
    {
        $ret = false;

        $confirmation = $user->getConfirmation();

        if ($confirmation->email_confirmation != $code) {
            throw new UserApiException(Yii::t('frontend/user', 'Wrong confirmation code'), UserApiException::DATA_ERROR);
        }

        $transaction = User::getDb()->beginTransaction();

        try {
            // установить новый e-mail
            $user->email = $confirmation->email;
            if (!$user->save(true, ['email'])) {
                throw new UserApiException('', UserApiException::VALIDATION_ERROR);
            }

            // очистить код подтверждения
            $confirmation->email_confirmation = null;
            $confirmation->email = null;
            if (!$confirmation->save(true, ['email', 'email_confirmation'])) {
                throw new UserApiException('', UserApiException::VALIDATION_ERROR);
            }

            $transaction->commit();

            $ret = true;

        } catch (Exception $ex) {
            $transaction->rollBack();
            $ret = false;
            throw new UserApiException(Yii::t('frontend/user', 'Error change e-mail'), UserApiException::DATABASE_ERROR, $ex);
        }
    }

    /**
     * Изменить профиль пользователя
     *
     * @param User $user
     * @param Profile $profile
     * @return boolean
     */
    public function updateUserProfile(User $user, Profile $profile)
    {
        $ret = false;

        try {
            $user->setAttributes([
                'name' => $profile->name,
            ]);
            $ret = $user->save(true, ['name']);
        }
        catch (Exception $ex) {
            $ret = false;
        }

        return $ret;
    }

    /**
     * Получить данные авторизованного пользователя из сети Google+.
     *
     * @param GoogleOAuth $client OAuth-клиент для связи с Google+
     * @param string $authCode код авторизации
     * @return ExternalUser
     */
    public function fetchGoogleUserData(GoogleOAuth $client, $authCode)
    {
        $client->fetchAccessToken($authCode);
        $userData = $client->getUserAttributes();

        return new ExternalUser([
            'code' => 'google',
            'id' => $userData['id'],
            'name' => $userData['name']['givenName'],
            'email' => !empty($userData['emails'][0]['value']) ? $userData['emails'][0]['value'] : null,
            'external_name' => $userData['displayName'],
            'external_page' => !empty($userData['url']) ? $userData['url'] : null,
        ]);
    }

    /**
     * Получить данные авторизованного пользователя из сети Facebook.
     *
     * @param Facebook $client OAuth-клиент для связи с Facebook
     * @param string $authCode код авторизации
     * @return ExternalUser
     */
    public function fetchFacebookUserData(\yii\authclient\clients\Facebook $client, $authCode)
    {
        $client->fetchAccessToken($authCode);// получить общую информацию пользователя
        $userData = $client->api('/me', 'GET', [
            'fields' => implode(',', [
                'id', 'first_name', 'name', 'email', 'link',
            ]),
        ]);

        return new ExternalUser([
            'code' => 'facebook',
            'id' => $userData['id'],
            'name' => $userData['first_name'],
            'email' => !empty($userData['email']) ? $userData['email'] : null,
            'external_name' => !empty($userData['name']) ? $userData['name'] : $userData['first_name'],
            'external_page' => !empty($userData['link']) ? $userData['link'] : null,
        ]);
    }

    /**
     * Получить данные авторизованного пользователя из сети Yandex.
     *
     * @param YandexOAuth $client OAuth-клиент для связи с Yandex
     * @param string $authCode код авторизации
     * @return ExternalUser
     */
    public function fetchYandexUserData(YandexOAuth $client, $authCode)
    {
        $client->fetchAccessToken($authCode);
        $userData = $client->getUserAttributes();

        return new ExternalUser([
            'code' => 'yandex',
            'id' => $userData['id'],
            'email' => !empty($userData['default_email']) ? $userData['default_email'] :
                (!empty($userData['emails'][0]) ? $userData['emails'][0] : null),
            'name' => $userData['first_name'],
            'external_name' => $userData['display_name'],
            // сети ya.ru больше нет
            'external_page' => '',
        ]);
    }

    /**
     * Получить данные авторизованного пользователя из сети VKontakte.
     *
     * @param VKontakte $client OAuth-клиент для связи с Vkontakte
     * @param string $authCode код авторизации
     * @return ExternalUser
     */
    public function fetchVkontakteUserData(VKontakte $client, $authCode)
    {
        $client->fetchAccessToken($authCode);
        $userData = $client->getUserAttributes();

        // внешнее имя
        $externalName = $userData['first_name'];
        if (!empty($userData['screen_name'])) {
            $externalName = $userData['screen_name'];
        }
        else if (!empty($userData['last_name'])) {
            $externalName .= ' ' . $userData['last_name'];
        }
        else if (!empty($userData['domain'])) {
            $externalName = $userData['domain'];
        }

        return new ExternalUser([
            'code' => 'vkontakte',
            'id' => $userData['id'],
            'email' => !empty($userData['email']) ? $userData['email'] : null,
            'name' => $userData['first_name'],
            'external_name' => $externalName,
            'external_page' => 'http://vk.com/' . $userData['domain'],
        ]);
    }
}