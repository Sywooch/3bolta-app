<?php
namespace user\forms;

use Yii;
use user\models\User;
use partner\models\Partner;
use app\components\PhoneValidator;

/**
 * Форма регистрации пользователя.
 * Пользователь может зарегистрироваться как частное лицо, так и как партнер.
 * Партнер помимо всего прочего заполняет еще:
 * - название компании;
 * - тип компании.
 */
class Register extends \yii\base\Model
{
    /**
     * Максимальное количество символов для имени
     */
    const MAX_NAME_LENGTH = 50;

    /**
     * Максимальное количество символов для названия партнера
     */
    const MAX_PARTNER_NAME_LENGTH = 100;

    /**
     * Максимальное количество символов для e-mail
     */
    const MAX_EMAIL_LENGTH = 100;

    /**
     * Минимальная длина пароля
     */
    const MIN_PASSWORD_LENGTH = 6;

    /**
     * @var string название компании-партнера, если пользователь - юр. лицо
     */
    public $partnerName;

    /**
     * @var string тип компании-партнера, если пользователь - юр. лицо
     */
    public $partnerType;

    /**
     * @var int тип регистрации: частное лицо, юр. лицо
     */
    public $type;

    /**
     * @var string имя пользователя
     */
    public $name;

    /**
     * @var string e-mail пользователя
     */
    public $email;

    /**
     * @var string телефон пользователя
     */
    public $phone;

    /**
     * @var string телефон в каноническом виде (производное от поля phone)
     */
    public $phone_canonical;

    /**
     * @var string пароль
     */
    public $password;

    /**
     * @var string подтверждение пароля
     */
    public $password_confirmation;

    /**
     * Правила валидации
     * @return []
     */
    public function rules()
    {
        return [
            [['name', 'email', 'phone', 'password', 'password_confirmation'], 'required'],
            [['phone'], PhoneValidator::className(),
                'canonicalAttribute' => 'phone_canonical',
                'targetClass' => User::className(), 'targetAttribute' => 'phone_canonical'
            ],
            [['email'], 'email'],
            [['password', 'password_confirmation'], 'string', 'min' => self::MIN_PASSWORD_LENGTH],
            [['password_confirmation'], 'compare', 'compareAttribute' => 'password', 'message' => Yii::t('frontend/user', 'Password not equal')],
            [['email'], 'unique', 'targetClass' => User::className(), 'targetAttribute' => 'email'],

            ['name', 'string', 'max' => self::MAX_NAME_LENGTH],
            ['email', 'string', 'max' => self::MAX_EMAIL_LENGTH],
            ['type', 'in', 'range' => array_keys(User::getTypesList())],

            ['partnerName', 'string', 'max' => self::MAX_PARTNER_NAME_LENGTH],
            ['partnerType', 'in', 'range' => array_keys(Partner::getCompanyTypes())],
            [['partnerName', 'partnerType'], 'required', 'when' => function($model) {
                /* @var $model \user\forms\Register */
                return $model->type == User::TYPE_LEGAL_PERSON;
            }],
        ];
    }

    /**
     * Подписи атрибутов
     * @return []
     */
    public function attributeLabels()
    {
        return [
            'type' => Yii::t('frontend/user', 'Register type'),
            'partnerName' => Yii::t('frontend/user', 'Company name'),
            'partnerType' => Yii::t('frontend/user', 'Company type'),
            'name' => Yii::t('frontend/user', 'Your name'),
            'email' => Yii::t('frontend/user', 'Your email'),
            'phone' => Yii::t('frontend/user', 'Your phone'),
            'phone_canonical' => Yii::t('frontend/user', 'Your phone'),
            'password' => Yii::t('frontend/user', 'Password'),
            'password_confirmation' => Yii::t('frontend/user', 'Password confirmation'),
        ];
    }

    /**
     * Получить выпадающий список типов регистраций
     */
    public static function getRegistrationTypes()
    {
        return [
            User::TYPE_PRIVATE_PERSON => Yii::t('frontend/user', 'I am a private person'),
            User::TYPE_LEGAL_PERSON => Yii::t('frontend/user', 'I am a representative a company'),
        ];
    }
}