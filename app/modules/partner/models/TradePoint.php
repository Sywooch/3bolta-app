<?php
namespace partner\models;

use Yii;

use app\components\PhoneValidator;
use yii\db\Expression;

/**
 * Модель торговой точки партнера. Привязывается к модели Partner
 */
class TradePoint extends \yii\db\ActiveRecord
{
    /**
     * @var boolean активность на карте, используется только для поиска торговых точек по карте
     */
    public $active = true;

    /**
     * Максимальная длина поля адреса
     */
    const MAX_ADDRESS_LENGTH = 255;

    /**
     * Название таблицы
     * @return string
     */
    public static function tableName()
    {
        return '{{%partner_trade_point}}';
    }

    /**
     * Правила валидации
     * @return string
     */
    public function rules()
    {
        return [
            [['partner_id', 'latitude', 'longitude', 'address'], 'required'],
            ['phone_from_profile', 'default', 'value' => true],
            ['phone_from_profile', 'boolean'],
            ['phone', 'required', 'when' => function($model) {
                /* @var $model \partner\models\TradePoint */
                return !$model->phone_from_profile;
            }],
            ['phone', PhoneValidator::className(),
                'canonicalAttribute' => 'phone_canonical',
                'when' => function($model) {
                    /* @var $model \partner\models\TradePoint */
                    return !$model->phone_from_profile;
                }
            ],
            [['latitude', 'longitude'], 'number', 'min' => -180, 'max' => 180],
            ['address', 'string', 'max' => self::MAX_ADDRESS_LENGTH],
            ['region_id', 'number', 'integerOnly' => true, 'skipOnEmpty' => false],
        ];
    }

    /**
     * Подписи атрибутов
     * @return []
     */
    public function attributeLabels()
    {
        return [
            'created' => Yii::t('main', 'Created'),
            'edited' => Yii::t('main', 'Edited'),
            'partner_id' => Yii::t('partner', 'Partner'),
            'latitude' => Yii::t('partner', 'Latitude'),
            'longitude' => Yii::t('partner', 'Longitude'),
            'address' => Yii::t('partner', 'Address'),
            'phone' => Yii::t('partner', 'Contact phone'),
            'phone_from_profile' => Yii::t('partner', 'Use profile phone'),
            'region_id' => Yii::t('partner', 'Region'),
        ];
    }

    /**
     * Получить партнера
     * @return \yii\db\ActiveQuery
     */
    public function getPartner()
    {
        return $this->hasOne(Partner::className(), ['id' => 'partner_id']);
    }

    /**
     * Обертка для метода сохранения
     * @param boolean $runValidation
     * @param [] $attributeNames
     * @return boolean
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if (is_array($attributeNames) && in_array('phone', $attributeNames)) {
            // телефон без валидации сохранить невозможно,
            // т.к. необходимо записать его в канонической форме
            // через валидатор PhoneValidator
            $runValidation = true;
            if (!in_array('phone_canonical', $attributeNames)) {
                $attributeNames[] = 'phone_canonical';
            }
        }
        return parent::save($runValidation, $attributeNames);
    }

    /**
     * Действия перед сохранением
     */
    public function beforeSave($insert)
    {
        if ($this->latitude && $this->longitude) {
            $this->coordinates = new Expression('POINT(' . (float) $this->latitude . ', ' . (float) $this->longitude . ')');
        }
        if ($this->isNewRecord) {
            $this->created = date('Y-m-d H:i:s');
        }
        $this->edited = date('Y-m-d H:i:s');
        return parent::beforeSave($insert);
    }

    /**
     * Поиск торговых точек пользователя
     *
     * @return \yii\db\ActiveQuery
     * @throws Exception в случае, если пользователь не авторизован или к нему не привязан партнер
     */
    public static function findUserList()
    {
        if (Yii::$app->user->isGuest) {
            // если пользователь неавторизован - выполнять метод невозможно
            throw new Exception();
        }

        /* @var $user \user\models\User */
        $user = Yii::$app->user->getIdentity();
        /* @var $partner Partner */
        $partner = $user->partner;

        return self::find()->andWhere([
            'partner_id' => $partner instanceof Partner ? $partner->id : 0
        ])->orderBy('created DESC');
    }

    /**
     * Получить телефон ТТ в зависимости от настроек
     * @return string
     */
    public function getTradePointPhone()
    {
        $ret = null;

        if ($this->phone_from_profile) {
            $partner = $this->partner;
            if ($partner instanceof Partner && $user = $partner->user) {
                $ret = $user->phone;
            }
        }
        else {
            $ret = $this->phone;
        }

        return $ret;
    }
}