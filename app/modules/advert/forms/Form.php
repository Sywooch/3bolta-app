<?php
namespace advert\forms;

use advert\models\Advert;
use advert\models\Category;
use app\components\AdvertEmailValidator;
use app\components\AdvertPhoneValidator;
use app\components\PhoneValidator;
use auto\models\Mark;
use auto\models\Model;
use auto\models\Modification;
use auto\models\Serie;
use handbook\models\HandbookValue;
use partner\models\Partner;
use partner\models\TradePoint;
use user\models\User;
use Yii;
use yii\base\Model as BaseModel;

/**
 * Форма добавления/редактирования объявления.
 * Контакты обязательны только в том случае, если user_id пустое.
 *
 * Доступные сценарии:
 * - default - сценарий по умолчанию;
 * - submit - будет происходить валидация на правильность ввода реальных ID
 *  марок, моделей, серий и модификаций, а также категорий и состояний;
 */
class Form extends BaseModel
{
    // Привязки к автомобилям
    protected $_mark = [];
    protected $_model = [];
    protected $_serie = [];
    protected $_modification = [];

    // загрузка изображений
    protected $_uploadImage;

    /**
     * @var Advert
     */
    protected $_exists;

    /**
     * @var int идентификатор пользователя
     */
    protected $_user_id;

    /**
     * @var int установить торговую точку
     */
    protected $_trade_point_id;

    /**
     * Максимальная длина описания
     */
    const DESCRIPTION_MAX_LENGTH = 255;

    public $name;
    public $category_id;
    public $condition_id;
    public $description;
    public $price;
    public $user_name;
    public $user_phone;
    public $user_phone_canonical;
    public $user_email;

    /**
     * Правила валидации
     * @return array
     */
    public function rules()
    {
        return [
            // !!! ВНИМАНИЕ !!! Этот валидатор оставить, он необходим для отсечения несуществующих значений !!!
            [['model', 'serie', 'modification'], 'safe'],

            [['category_id'], 'filter', 'filter' => function($value) {
                // проверить существование категории
                if (!$value || !(Category::find()->where(['id' => $value])->exists())) {
                    return null;
                }
                else {
                    return $value;
                }
            }, 'on' => 'submit'],

            [['condition_id'], 'filter', 'filter' => function($value) {
                // проверить существование состояния
                if (!$value || !(HandbookValue::find()->where([
                    'handbook_code' => 'part_condition',
                    'id' => $value
                ])->exists())) {
                    return null;
                }
                else {
                    return $value;
                }
            }, 'on' => 'submit'],

            [['name', 'category_id', 'condition_id', 'price'], 'required', 'message' => Yii::t('frontend/advert', 'Required field')],
            [['mark'], 'required', 'message' => Yii::t('frontend/advert', 'Choose one or more automobiles')],
            [['category_id', 'condition_id'], 'integer'],
            [['description'], 'string', 'max' => self::DESCRIPTION_MAX_LENGTH],
            [['user_name', 'user_phone', 'user_email'], 'required', 'when' => function($model) {
                return !$model->getUserId();
            }],
            ['trade_point_id', 'required', 'when' => function($data) {
                /* @var $data Form */
                $user = $data->getUser();
                return $user instanceof User && $user->type == User::TYPE_LEGAL_PERSON;
            }],
            ['price', 'filter', 'filter' => function($value) {
                return str_replace(',', '.', $value);
            }],
            [['price'], 'number', 'min' => 1, 'max' => 9999999,
                'numberPattern' => '#^[-]?[0-9]{1,7}[\.|\,]?[0-9]{0,2}$#',
            ],
            [['user_name', 'name'], 'string', 'max' => 50],
            ['user_email', 'string', 'max' => 100],
            ['user_phone_canonical', 'string', 'max' => 11],
            ['user_phone', 'string', 'max' => 19],
            [['user_phone'], AdvertPhoneValidator::className()],
            [['user_phone'], PhoneValidator::className(),
                'canonicalAttribute' => 'user_phone_canonical',
                'targetClass' => Advert::className(), 'targetAttribute' => 'user_phone_canonical', 'when' => function($model) {
                    return !$model->getUserId();
                }
            ],
            [['user_email'], 'filter', 'filter' => 'strtolower'],
            [['user_email'], AdvertEmailValidator::className(), 'when' => function($model) {
                return !$model->getUserId();
            }],
            [['user_email'], 'email', 'when' => function($model) {
                return !$model->getUserId();
            }],

            [['uploadImage'], 'file',
                'skipOnEmpty' => true,
                'extensions' => Advert::$_imageFileExtensions,
                'maxFiles' => Advert::UPLOAD_MAX_FILES
            ],
        ];
    }

    /**
     * Удалить несуществующие и недоступные модификации
     */
    protected function clearModification()
    {
        $availValues = [];
        if (!empty($this->_serie)) {
            foreach (Modification::find()->andWhere(['serie_id' => $this->_serie])->each() as $row) {
                $availValues[] = $row->id;
            }
        }

        $this->_modification = $this->clearIntArray($this->_modification, $availValues);
    }

    /**
     * Удалить несуществующие и недоступные серии
     */
    protected function clearSerie()
    {
        $availValues = [];
        if (!empty($this->_model)) {
            foreach (Serie::find()->andWhere(['model_id' => $this->_model])->each() as $row) {
                $availValues[] = $row->id;
            }
        }

        $this->_serie = $this->clearIntArray($this->_serie, $availValues);
    }

    /**
     * Удалить несуществующие и недоступные модели
     */
    protected function clearModel()
    {
        $availValues = [];
        if (!empty($this->_mark)) {
            foreach (Model::find()->andWhere(['mark_id' => $this->_mark])->each() as $row) {
                $availValues[] = $row->id;
            }
        }

        $this->_model = $this->clearIntArray($this->_model, $availValues);
    }

    /**
     * Удалить несуществующие марки
     */
    protected function clearMark()
    {
        $availValues = [];
        foreach (Mark::find()->each() as $row) {
            $availValues[] = $row->id;
        }

        $this->_mark = $this->clearIntArray($this->_mark, $availValues);
    }

    public function beforeValidate()
    {
        // если субмитим форму, необходимо запомнить список доступных параметров для множественных списков
        // и отфильтровать эти значения
        if ($this->getScenario() == 'submit') {
            $this->clearMark();
            $this->clearModel();
            $this->clearSerie();
            $this->clearModification();
        }

        return parent::beforeValidate();
    }

    /**
     * Очистить массив от значений, отличных от integer.
     * @params [] $availValues доступные значения для массива
     * @return array
     */
    protected function clearIntArray($arr, $availValues = [])
    {
        $arr = is_array($arr) ? $arr : [];
        foreach ($arr as $k => $i) {
            if (!intval($i) || (!empty($availValues) && !in_array($i, $availValues))) {
                unset ($arr[$k]);
            }
            else {
                $arr[$k] = (int) $i;
            }
        }
        return array_values($arr);
    }

    // гетеры и сетеры для автомобилей
    public function getMark() { return $this->_mark; }
    public function setMark($values) { $this->_mark = $this->clearIntArray($values); }
    public function getModel() { return $this->_model; }
    public function setModel($values) { $this->_model = $this->clearIntArray($values); }
    public function getSerie() { return $this->_serie; }
    public function setSerie($values) { $this->_serie = $this->clearIntArray($values); }
    public function getModification() { return $this->_modification; }
    public function setModification($values) { $this->_modification = $this->clearIntArray($values); }

    /**
     * Подписи полей
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('frontend/advert', 'Part name'),
            'category_id' => Yii::t('frontend/advert', 'Part category'),
            'condition_id' => Yii::t('frontend/advert', 'Part condition'),
            'description' => Yii::t('frontend/advert', 'Advert description'),
            'user_name' => Yii::t('frontend/advert', 'Contact name'),
            'user_phone' => Yii::t('frontend/advert', 'Contact phone'),
            'user_email' => Yii::t('frontend/advert', 'Contact email'),
            'user_id' => Yii::t('frontend/advert', 'User id'),
            'trade_point_id' => Yii::t('frontend/advert', 'Trade point'),
            'price' => Yii::t('frontend/advert', 'Part price'),
            'mark' => Yii::t('frontend/advert', 'Choose mark'),
            'model' => Yii::t('frontend/advert', 'Choose model'),
            'serie' => Yii::t('frontend/advert', 'Choose serie'),
            'modification' => Yii::t('frontend/advert', 'Choose modification'),
        ];
    }

    /**
     * Получить атрибуты
     * @return array
     */
    public function attributes()
    {
        $ret = parent::attributes();
        if (is_array($ret)) {
            $ret = array_merge($ret, [
                'mark', 'model', 'serie', 'modification', 'uploadImage',
            ]);
        }
        return $ret;
    }


    /**
     * Получить изображения для загрузки.
     * Возвращает всегда null, иначе начинаются глюки в виджете FileInput.
     * @return null
     */
    public function getUploadImage()
    {
        return null;
    }

    /**
     * Установить изображения для загрузки
     * @param array $files
     */
    public function setUploadImage($files)
    {
        if (is_array($files)) {
            $this->_uploadImage = $files;
        }
        else {
            $this->_uploadImage = [];
        }
    }

    public function getImages()
    {
        return $this->_uploadImage;
    }

    /**
     * Получить модель существующего объявления (по умолчанию-null)
     * @return Advert|null
     */
    public function getExists()
    {
        return $this->_exists;
    }

    /**
     * Создать форму на основе существующего объявления.
     * @param Advert $advert
     * @return self
     */
    public static function createFromExists(Advert $advert)
    {
        $ret = new self();

        $ret->_exists = $advert;

        $ret->_user_id = $advert->user_id;
        $ret->_trade_point_id = $advert->trade_point_id;

        $ret->setAttributes([
            'user_name' => $advert->user_name,
            'user_email' => $advert->user_email,
            'user_phone' => $advert->user_phone,
            'name' => $advert->advert_name,
            'price' => $advert->price,
            'description' => $advert->description,
            'category_id' => $advert->category_id,
            'condition_id' => $advert->condition_id,
        ]);

        $ret->setMark($advert->getMarks());
        $ret->setModel($advert->getModels());
        $ret->setSerie($advert->getSeries());
        $ret->setModification($advert->getModifications());

        return $ret;
    }

    /**
     * Установить идентификатор торговой точки
     * @param int $val
     */
    public function setTrade_point_id($val)
    {
        $val = (int) $val;
        $availValues = array_keys($this->getAvailTradePoints());
        $this->_trade_point_id = in_array($val, $availValues) ? $val : null;
    }

    /**
     * Получить идентификатор торговой точки
     * @return int
     */
    public function getTrade_point_id()
    {
        return $this->_trade_point_id;
    }

    /**
     * Получить идентификатор пользователя
     * @return int
     */
    public function getUserId()
    {
        return $this->_user_id;
    }

    /**
     * Получить модель пользователя
     *
     * @return User|null
     */
    public function getUser()
    {
        return $this->_user_id ? User::find()->andWhere(['id' => $this->_user_id])->one() : null;
    }

    /**
     * Получить массив доступных торговых точек для пользователя user_id.
     *
     * @return TradePoint[]
     */
    public function getAvailTradePoints()
    {
        $ret = [];
        $user = $this->getUser();

        if ($user instanceof User && $user->type == User::TYPE_LEGAL_PERSON) {
            /* @var $partner Partner */
            $partner = $user->partner;
            if ($partner) {
                $res = TradePoint::find()->andWhere(['partner_id' => $partner->id])->all();
                foreach ($res as $row) {
                    /* @var $row TradePoint */
                    $ret[$row->id] = $row;
                }
            }
        }

        return $ret;
    }

    public function getTradePointsDropDown()
    {
        $ret = [];

        $list = $this->getAvailTradePoints();
        foreach ($list as $row) {
            $label = '<strong>' . Yii::t('frontend/advert', 'Address') . ':</strong> ' . $row->address;
            $label .= '<br /><strong>' . Yii::t('frontend/advert', 'Phone') . ':</strong> ' . $row->phone;
            $ret[$row->id] = $label;
        }

        return $ret;
    }

    /**
     * Создать новое объявление для существующего пользователя
     *
     * @param User $user
     * @return self
     */
    public static function createNewForUser(User $user)
    {
        $ret = new self();
        $ret->_user_id = $user->id;
        return $ret;
    }
}