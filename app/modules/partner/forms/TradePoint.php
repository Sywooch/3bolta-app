<?php
namespace partner\forms;

use app\components\PhoneValidator;
use geo\components\GeoApi;
use geo\models\Region;
use partner\models\TradePoint as TradePointModel;
use Yii;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * Форма создания/редактирования торговой точки
 */
class TradePoint extends Model
{
    /**
     * @var int идентификатор существующей модели
     */
    protected $_existsId;

    public $address;
    public $latitude;
    public $longitude;
    public $phone_from_profile = true;
    public $phone;
    public $phone_canonical;
    public $region_id;

    /**
     * @var array сюда собираются идентификаторы пользовательских ТТ
     * @fixme это костыль, т.к. он используется только в валидаторе PhoneValidator
     */
    protected static $_userIds = [];

    /**
     * Правила валидации
     * @return array
     */
    public function rules()
    {
        return [
            [['address', 'latitude', 'longitude'], 'required'],
            [['latitude', 'longitude'], 'number', 'min' => -180, 'max' => 180],
            ['phone_from_profile', 'boolean'],
            ['phone', 'required', 'when' => function($model) {
                /* @var $model TradePointModel */
                return !$model->phone_from_profile;
            }],
            ['phone', PhoneValidator::className(),
                'canonicalAttribute' => 'phone_canonical',
                'targetClass' => TradePointModel::className(),
                'targetAttribute' => 'phone_canonical',
                'filter' => function($query) {
                    /* @var $query ActiveQuery */
                    if ($userIds = TradePoint::getUserIds()) {
                        $query->andWhere(['not', 'id', $userIds]);
                    }
                },
                'message' => Yii::t('frontend/user', 'This phone already exists'),
                'when' => function($model) {
                    /* @var $model TradePointModel */
                    return !$model->phone_from_profile;
                }
            ],
            ['region_id', 'in', 'range' => array_keys(self::getRegionsDropDownList())],
            ['region_id', 'default', 'value' => function($model, $attribute) {
                // по умолчанию текущий регион пользователя
                /* @var $userRegion GeoApi */
                $geoApi = \Yii::$app->getModule('geo')->api;
                /* @var $userRegion Region */
                $userRegion = $geoApi->getUserRegion(true);
                return $userRegion instanceof Region ? $userRegion->id : null;
            }],
        ];
    }

    /**
     * Подписи атрибутов
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'region_id' => Yii::t('frontend/partner', 'Region'),
            'address' => Yii::t('frontend/partner', 'Address'),
            'latitude' => Yii::t('frontend/partner', 'Latitude'),
            'longitude' => Yii::t('frontend/partner', 'Longitude'),
            'phone' => Yii::t('frontend/partner', 'Phone'),
            'phone_from_profile' => Yii::t('frontend/partner', 'Phone from profile'),
        ];
    }

    /**
     * @fixme костыль для валидатора
     */
    public static function getUserIds()
    {
        return self::$_userIds;
    }

    /**
     * Получить идентификатор существующей модели
     * @return int
     */
    public function getExistsId()
    {
        self::$_userIds[] = $this->_existsId;
        return $this->_existsId;
    }

    /**
     * Создать форму на основе существующей модели
     *
     * @param TradePointModel $model
     * @return \self
     */
    public static function createFromExists(TradePointModel $model)
    {
        $form = new self();

        $form->setAttributes([
            'address' => $model->address,
            'latitude' => $model->latitude,
            'longitude' => $model->longitude,
            'phone' => $model->phone,
            'phone_from_profile' => $model->phone_from_profile,
            'region_id' => $model->region_id,
        ]);

        $form->_existsId = (int) $model->id;

        return $form;
    }

    /**
     * Получить идентификаторы регионов
     * @return array
     */
    public static function getRegionsDropDownList()
    {
        return ArrayHelper::map(Region::find()->all(), 'id', 'site_name');
    }
}