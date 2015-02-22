<?php
namespace user\forms;

use Yii;
use user\models\User;
use app\components\PhoneValidator;

/**
 * Форма регистрации пользователя
 */
class Register extends \yii\base\Model
{
    /**
     * Максимальное количество символов для имени
     */
    const MAX_NAME_LENGTH = 50;

    public $name;
    public $email;
    public $phone;
    public $phone_canonical;
    public $password;
    public $password_confirmation;

    /**
     * Правила валидации
     * @return []
     */
    public function rules()
    {
        return [
            [['name', 'email', 'phone', 'password', 'password_confirmation'], 'required'],
            [['name', 'email'], 'string', 'max' => self::MAX_NAME_LENGTH],
            [['phone'], PhoneValidator::className(),
                'canonicalAttribute' => 'phone_canonical',
                'targetClass' => User::className(), 'targetAttribute' => 'phone_canonical'
            ],
            [['email'], 'email'],
            [['password'], 'string', 'min' => 6],
            [['password_confirmation'], 'compare', 'compareAttribute' => 'password', 'message' => Yii::t('frontend/user', 'Password not equal')],
            [['email'], 'unique', 'targetClass' => User::className(), 'targetAttribute' => 'email'],
        ];
    }

    /**
     * Подписи атрибутов
     * @return []
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('frontend/user', 'Your name'),
            'email' => Yii::t('frontend/user', 'Your email'),
            'phone' => Yii::t('frontend/user', 'Your phone'),
            'phone_canonical' => Yii::t('frontend/user', 'Your phone'),
            'password' => Yii::t('frontend/user', 'Password'),
            'password_confirmation' => Yii::t('frontend/user', 'Password confirmation'),
        ];
    }
}