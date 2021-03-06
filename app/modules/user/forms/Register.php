<?php
namespace user\forms;

use Yii;
use user\models\User;
use partner\models\Partner;

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
     * Максимальное количество специализаций для выбора их селекта
     */
    const MAX_PARTNER_SPECIALIZATION = 5;

    /**
     * @var string название компании-партнера, если пользователь - юр. лицо
     */
    public $partnerName;

    /**
     * @var string тип компании-партнера, если пользователь - юр. лицо
     */
    public $partnerType;

    /**
     * @var string специализации партнера в текстовом виде для саггеста
     */
    protected $_partnerSpecialization;

    /**
     * @var array массив идентификаторов специализаций
     */
    protected $_partnerSpecializationArray = [];

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
            [['name', 'email', 'password', 'password_confirmation'], 'required'],
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
            ['partnerSpecialization', 'safe'],
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
            'password' => Yii::t('frontend/user', 'Password'),
            'password_confirmation' => Yii::t('frontend/user', 'Password confirmation'),
            'partnerSpecialization' => Yii::t('frontend/user', 'Specialization'),
        ];
    }

    /**
     * Получение специализаций в текстовом виде
     * @return string
     */
    public function getPartnerSpecialization()
    {
        return $this->_partnerSpecialization;
    }

    /**
     * Установка специализаций. Если пришел массив то его запоминаем в _partnerSpecializationArray
     * @param array $value
     */
    public function setPartnerSpecialization($value)
    {
        if (is_array($value)) {
            $this->_partnerSpecializationArray = [];
            foreach ($value as $v) {
                $v = (int) $v;
                if ($v) {
                    $this->_partnerSpecializationArray[] = $v;
                }
            }
        }
    }

    /**
     * Получить массив специализаций
     * @return array
     */
    public function getPartnerSpecializationArray()
    {
        return $this->_partnerSpecializationArray;
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