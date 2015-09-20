<?php
namespace advert\models;

use yii\db\ActiveRecord;
use app\components\PhoneValidator;
use geo\models\Region;
use partner\models\TradePoint;
use user\models\User;
use Yii;
use yii\db\ActiveQuery;

/**
 * Модель контактов объявления
 */
class Contact extends ActiveRecord
{
    /**
     * Максимальная длина строки с именем пользователя
     */
    const MAX_USER_NAME_LENGTH = 50;

    /**
     * Максимальная длина строки с e-mail пользователя
     */
    const MAX_EMAIL_LENGTH = 100;

    /**
     * Максимальная длина строки с каноническим номером телефона
     */
    const MAX_PHONE_CANONICAL_LENGTH = 11;

    /**
     * Максимальная длина строки с номером телефона
     */
    const MAX_PHONE_LENGTH = 19;

    /**
     * Таблица
     * @return string
     */
    public static function tableName()
    {
        return '{{%advert_contact}}';
    }

    /**
     * Правила валидации
     * @return array
     */
    public function rules()
    {
        return [
            ['advert_id', 'required'],
            ['advert_id', 'integer'],
            [['user_name', 'user_phone', 'user_email'], 'required', 'when' => function($model) {
                // обязательна либо привязка к пользователю, либо координаты пользователя
                /* @var $model Contact */
                /* @var $advert Advert */
                $advert = $model->advert;
                if ($advert instanceof Advert) {
                    return empty($advert->user_id);
                }
                return true;
            }],
            ['trade_point_id', 'integer', 'skipOnEmpty' => false, 'when' => function($model) {
                // обязательна привязка к ТТ, если пользователь - партнер
                /* @var $model Contact */
                /* @var $advert Advert */
                $advert = $model->advert;
                if ($advert instanceof Advert) {
                    $userId = (int) $advert->user_id;
                    if ($userId) {
                        /* @var $user User */
                        $user = User::find()->andWhere(['id' => $userId])->one();
                        return $user instanceof User && $user->type == User::TYPE_LEGAL_PERSON;
                    }
                }
            }],
            ['user_phone', PhoneValidator::className(), 'canonicalAttribute' => 'user_phone_canonical'],
            ['user_name', 'string', 'max' => self::MAX_USER_NAME_LENGTH],
            ['user_email', 'string', 'max' => self::MAX_EMAIL_LENGTH],
            ['user_phone_canonical', 'string', 'max' => self::MAX_PHONE_CANONICAL_LENGTH],
            ['user_phone', 'string', 'max' => self::MAX_PHONE_LENGTH],
            [['user_email'], 'filter', 'filter' => 'strtolower'],
            [['user_email'], 'email', 'when' => function($model) {
                // e-mail обязателен, если у родителя нет привязки к пользователю
                /* @var $model Contact */
                /* @var $advert Advert */
                $advert = $model->advert;
                return !($advert instanceof Advert) || empty($advert->user_id);
            }],
            [['region_id'], 'integer', 'skipOnEmpty' => false],
        ];
    }

    /**
     * Подписи атрибутов
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'user_name' => Yii::t('advert', 'Contact name'),
            'user_phone' => Yii::t('advert', 'Contact phone'),
            'user_email' => Yii::t('advert', 'Contact email'),
            'trade_point_id' => Yii::t('advert', 'Trade point'),
            'region_id' => Yii::t('advert', 'Region'),
        ];
    }

    /**
     * Получить привязку к объявлению
     * @return ActiveQuery
     */
    public function getAdvert()
    {
        return $this->hasOne(Advert::className(), ['id' => 'advert_id']);
    }

    /**
     * Получить привязку к ТТ
     * @return ActiveQuery
     */
    public function getTradePoint()
    {
        return $this->hasOne(TradePoint::className(), ['id' => 'trade_point_id']);
    }

    /**
     * Получить привязку к региону
     * @return ActiveQuery
     */
    public function getRegion()
    {
        return $this->hasOne(Region::className(), ['id' => 'region_id']);
    }
}