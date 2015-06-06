<?php
namespace user\forms;

use Yii;

use user\forms\Register;

/**
 * Форма восстановления пароля - изменить пароль
 */
class ChangePassword extends \yii\base\Model
{
    public $password;
    public $password_confirmation;

    public function rules()
    {
        return [
            [['password', 'password_confirmation'], 'string', 'min' => Register::MIN_PASSWORD_LENGTH],
            [['password', 'password_confirmation'], 'required'],
            [['password_confirmation'], 'compare', 'compareAttribute' => 'password', 'message' => Yii::t('frontend/user', 'Password not equal')],
        ];
    }

    /**
     * Подписи атрибутов
     * @return []
     */
    public function attributeLabels()
    {
        return [
            'password' => Yii::t('frontend/user', 'New password'),
            'password_confirmation' => Yii::t('frontend/user', 'Password confirmation'),
        ];
    }
}