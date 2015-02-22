<?php
namespace user\components;

use yii\base\Exception;
use user\models\User;
use user\forms\Register;

/**
 * API для работы с пользователями
 */
class UserApi extends \yii\base\Component
{
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