<?php
namespace user\components;

use Yii;

use yii\helpers\Url;
use yii\base\Exception;
use user\models\User;
use user\forms\Register;
use user\models\UserConfirmation;
use user\forms\LostPassword;
use user\forms\ChangePassword;
use user\forms\Profile;

use yii\web\NotFoundHttpException;

/**
 * API для работы с пользователями
 */
class UserApi extends \yii\base\Component
{
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
        if (!$user || $user->status != User::STATUS_WAIT_CONFIRMATION) {
            // пользователь не найден
            throw new NotFoundHttpException();
        }

        $ret = null;

        // подтверждение
        $transaction = User::getDb()->beginTransaction();
        try {
            // очистить код подтверждения
            $confirmation->email_confirmation = null;
            $confirmation->email = null;
            if (!$confirmation->save(true, ['email', 'email_confirmation'])) {
                // не удалось сохранить модель подтверждения
                throw new Exception();
            }

            // сохранить пользователя
            $user->status = User::STATUS_ACTIVE;
            if (!$user->save(true, ['status'])) {
                // не удалось сохранить пользователя
                throw new Exception();
            }

            /* @var $advertApi \advert\components\AdvertApi */
            $advertApi = Yii::$app->getModule('advert')->advert;

            // прикрепить к пользователю все его неавторизованные объявления
            $advertApi->attachNotAuthAdvertsToUser($user);

            $transaction->commit();

            $ret = $user->id;
        } catch (Exception $ex) {
            $transaction->rollBack();
            $ret = null;
        }

        return $ret;
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
     * Регистрация пользователя. На вход передается заполненная форма регистрации.
     *
     * @param Register $form
     * @return User|null модель зарегистрированного пользователя в случае успеха
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
                    'phone' => $form->phone,
                    'type' => $form->type,
                ]);
                if (!$user->save()) {
                    throw new Exception();
                }

                // создать модель партнера, если регистрируемся как партнер
                if ($form->type == User::TYPE_LEGAL_PERSON) {
                    /* @var $partnersApi \partner\components\PartnersApi */
                    $partnersApi = Yii::$app->getModule('partner')->api;
                    $partner = $partnersApi->registerPartner($form, $user);
                    if (!$partner) {
                        throw new Exception();
                    }
                }

                // сгенерировать подтверждение
                $confirmation = $user->getConfirmation();
                if (!$confirmation) {
                    throw new Exception();
                }

                $confirmation->setAttributes([
                    'email' => $user->email,
                    'email_confirmation' => md5(uniqid() . $user->id . time()),
                ]);
                if (!$confirmation->save()) {
                    throw new Exception();
                }

                $this->sendEmailConfirmation($user);

                $transaction->commit();

                $ret = $user;
            }
            catch (Exception $ex) {
                $transaction->rollBack();
                $ret = null;
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
                throw new Exception();
            }

            $confirmationLink = Url::toRoute(['/user/user/change-password', 'code' => $confirmation->restore_confirmation], true);
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
     */
    public function changePassword(User $user, ChangePassword $form)
    {
        $ret = false;

        if ($form->validate()) {
            $transaction = $user->getDb()->beginTransaction();

            try {
                // очистить строку подтверждения
                $confirmation = $user->getConfirmation();
                $confirmation->restore_confirmation = null;
                if (!$confirmation->save(true, ['restore_confirmation'])) {
                    throw new Exception();
                }

                // изменить пароль
                $user->new_password = $form->password;
                if (!$user->save()) {
                    throw new Exception();
                }

                $transaction->commit();

                $ret = true;
            }
            catch (Exception $ex) {
                $transaction->rollBack();
                $ret = false;
            }
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
     */
    public function setNewUserEmail(User $user, Profile $profile)
    {
        $ret = false;

        if (!$profile->validate()) {
            return $ret;
        }

        $transaction = User::getDb()->beginTransaction();

        try {
            // сохранить подтверждение
            $confirmation = $user->getConfirmation();
            $confirmation->email = $profile->email;
            $confirmation->email_confirmation = md5($user->id . $user->email . $profile->email . uniqid());
            if (!$confirmation->save()) {
                throw new Exception();
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
     * @throws Exception
     */
    public function changeUserEmail(User $user, $code)
    {
        $ret = false;

        $confirmation = $user->getConfirmation();

        if ($confirmation->email_confirmation != $code) {
            throw new Exception(Yii::t('frontend/user', 'Wrong confirmation code'));
        }

        $transaction = User::getDb()->beginTransaction();

        try {
            // установить новый e-mail
            $user->email = $confirmation->email;
            if (!$user->save(true, ['email'])) {
                throw new Exception();
            }

            // очистить код подтверждения
            $confirmation->email_confirmation = null;
            $confirmation->email = null;
            if (!$confirmation->save(true, ['email', 'email_confirmation'])) {
                throw new Exception();
            }

            $transaction->commit();

            $ret = true;

        } catch (Exception $ex) {
            $transaction->rollBack();
            $ret = false;
            throw new Exception(Yii::t('frontend/user', 'Error change e-mail'));
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

        if (!$profile->validate()) {
            return $ret;
        }

        try {
            $user->setAttributes([
                'name' => $profile->name,
                'phone' => $profile->phone,
            ]);
            $ret = $user->save(true, ['name', 'phone', 'phone_canonical']);
        }
        catch (Exception $ex) {
            $ret = false;
        }

        return $ret;
    }
}