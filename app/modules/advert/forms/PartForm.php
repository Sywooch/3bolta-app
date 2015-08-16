<?php
namespace advert\forms;

use advert\models\PartAdvert;
use advert\models\PartCategory;
use app\components\AdvertEmailValidator;
use app\components\AdvertPhoneValidator;
use app\components\PhoneValidator;
use auto\models\Mark;
use auto\models\Model;
use auto\models\Modification;
use auto\models\Serie;
use geo\models\Region;
use handbook\models\HandbookValue;
use partner\models\Partner;
use partner\models\TradePoint;
use user\models\User;
use Yii;
use yii\base\Model as BaseModel;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * Форма добавления/редактирования запчасти.
 * Контакты обязательны только в том случае, если user_id пустое.
 *
 * Доступные сценарии:
 * - default - сценарий по умолчанию;
 * - submit - будет происходить валидация на правильность ввода реальных ID
 *  марок, моделей, серий и модификаций, а также категорий и состояний;
 */
class PartForm extends BaseModel
{
    // Привязки к автомобилям
    protected $_mark = [];
    protected $_model = [];
    protected $_serie = [];
    protected $_modification = [];

    /**
     * @var array загрузка изображений
     */
    protected $_uploadImage;

    /**
     * @var PartAdvert
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
     * @var integer идентификатор региона
     */
    protected $_region_id;

    public $name;
    public $catalogue_number;
    public $category_id;
    public $condition_id;
    public $description;
    public $price;
    public $user_name;
    public $user_phone;
    public $user_phone_canonical;
    public $user_email;
    public $allow_questions = true;

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
                if (!$value || !(PartCategory::find()->where(['id' => $value])->exists())) {
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
            ['catalogue_number', 'string', 'max' => PartAdvert::CATALOGUE_NUMBER_MAX_LENGTH],
            ['name', 'string', 'max' => PartAdvert::NAME_MAX_LENGTH],
            ['description', 'string', 'max' => PartAdvert::DESCRIPTION_MAX_LENGTH],
            [['user_name', 'user_phone', 'user_email'], 'required', 'when' => function($model) {
                return !$model->getUserId();
            }],
            ['trade_point_id', 'required', 'when' => function($data) {
                /* @var $data PartForm */
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
                'targetClass' => PartAdvert::className(), 'targetAttribute' => 'user_phone_canonical', 'when' => function($model) {
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
            ['region_id', 'in', 'range' => array_keys(self::getRegionsDropDownList())],

            ['uploadImage', 'validateImagesCount', 'skipOnEmpty' => false],
            ['uploadImage', 'file',
                'skipOnEmpty' => true,
                'extensions' => PartAdvert::$_imageFileExtensions,
                'maxFiles' => PartAdvert::UPLOAD_MAX_FILES,
                'maxSize' => PartAdvert::UPLOAD_MAX_FILE_SIZE,
            ],
            ['allow_questions', 'boolean'],
        ];
    }

    /**
     * Валидация максимального количества загружаемых файлов.
     * Суммируется количество уже подгруженных файлов и количество вновь подгружаемых файлов.
     *
     * @param string $attribute
     * @param array $params
     */
    public function validateImagesCount($attribute, $params)
    {
        $count = 0;
        if ($this->getExists()) {
            $count += $this->getExists()->getImages()->count();
        }
        if (!empty($this->_uploadImage)) {
            $count += count($this->_uploadImage);
        }
        if ($count > PartAdvert::UPLOAD_MAX_FILES) {
            $this->addError($attribute, \Yii::t('frontend/advert', 'Max files is: {n}', [
                'n' => PartAdvert::UPLOAD_MAX_FILES,
            ]));
        }
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
            'region_id' => Yii::t('frontend/advert', 'Region'),
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
            'allow_questions' => Yii::t('advert', 'Allow questions by email'),
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
     *
     * @return array
     */
    public function getUploadImage()
    {
        return $this->_uploadImage;
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

    /**
     * Сбросить установленные значения переменных перед выводом
     */
    public function resetOutputValues()
    {
        $this->_uploadImage = null;
    }

    public function getImages()
    {
        return $this->_uploadImage;
    }

    /**
     * Получить модель существующего объявления (по умолчанию-null)
     * @return PartAdvert|null
     */
    public function getExists()
    {
        return $this->_exists;
    }

    /**
     * Создать форму на основе существующего объявления.
     * @param PartAdvert $advert
     * @return self
     */
    public static function createFromExists(PartAdvert $advert)
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
            'catalogue_number' => $advert->catalogue_number,
            'price' => $advert->price,
            'description' => $advert->description,
            'category_id' => $advert->category_id,
            'condition_id' => $advert->condition_id,
            'region_id' => $advert->region_id,
            'allow_questions' => (boolean) $advert->allow_questions,
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
     * Метод подгружает файлы из массива $images.
     * Должен вызываться сразу после метода load.
     * Должен всегда возвращать true.
     *
     * @param array $images
     * @return boolean true, всегда
     */
    public function loadImages($images)
    {
        if (is_array($images)) {
            $this->_uploadImage = [];
            foreach ($images as $image) {
                if ($image instanceof UploadedFile) {
                    $this->_uploadImage[] = $image;
                }
            }
        }

        return true;
    }

    /**
     * Установить фотографии для удаления
     * @param array $val
     */
    public function setRemoveImages($val)
    {
        $this->_removeImages = array();

        foreach ($val as $imageId) {
            $imageId = (int) $imageId;
            if ($imageId) {
                $this->_removeImages[] = $imageId;
            }
        }
    }

    /**
     * Получить изображения для удаления
     * @return array
     */
    public function getRemoveImages()
    {
        return $this->_removeImages;
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

    /**
     * Получить идентификаторы регионов
     * @return array
     */
    public static function getRegionsDropDownList()
    {
        return ArrayHelper::map(Region::find()->all(), 'id', 'site_name');
    }

    /**
     * Получить идентификатор региона
     * @return integer
     */
    public function getRegion_id()
    {
        if (is_null($this->_region_id)) {
            // по умолчанию текущий регион пользователя
            /* @var $userRegion GeoApi */
            $geoApi = \Yii::$app->getModule('geo')->api;
            /* @var $userRegion Region */
            $userRegion = $geoApi->getUserRegion(true);
            $this->_region_id = $userRegion instanceof Region ? $userRegion->id : 0;
        }

        return $this->_region_id;
    }

    /**
     * Установить идентификатор региона
     * @param integer $value
     */
    public function setRegion_id($value)
    {
        $this->_region_id = (int) $value;
    }
}