<?php
namespace partner\forms;

use Yii;

use app\components\PhoneValidator;
use partner\models\TradePoint as TradePointModel;

/**
 * Форма создания/редактирования торговой точки
 */
class TradePoint extends \yii\base\Model
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
            ['phone_from_profile', 'boolean'],
            ['phone', 'required', 'when' => function($model) {
                /* @var $model \partner\models\TradePoint */
                return !$model->phone_from_profile;
            }],
            ['phone', PhoneValidator::className(),
                'canonicalAttribute' => 'phone_canonical',
                'targetClass' => TradePointModel::className(),
                'targetAttribute' => 'phone_canonical',
                'filter' => function($query) {
                    /* @var $query \yii\db\ActiveQuery */
                    if ($userIds = TradePoint::getUserIds()) {
                        $query->andWhere(['not', 'id', $userIds]);
                    }
                },
                'message' => Yii::t('frontend/user', 'This phone already exists'),
                'when' => function($model) {
                    /* @var $model \partner\models\TradePoint */
                    return !$model->phone_from_profile;
                }
            ],
        ];
    }

    /**
     * Подписи атрибутов
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'address' => Yii::t('frontend/partner', 'Address'),
            'latitude' => Yii::t('frontend/partner', 'Latitude'),
            'longitude' => Yii::t('frontend/partner', 'Longitude'),
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
        ]);

        $form->_existsId = (int) $model->id;

        return $form;
    }
}