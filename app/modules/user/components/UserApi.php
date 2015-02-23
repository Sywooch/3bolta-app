<?php
namespace user\components;

use Yii;

use yii\helpers\Url;
use yii\base\Exception;
use user\models\User;
use user\forms\Register;
use user\models\UserConfirmation;

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
        $user = $confirmation->getUser()->one();
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
                ]);
                if (!$user->save()) {
                    throw new Exception();
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
}