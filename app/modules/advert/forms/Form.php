<?php
namespace advert\forms;

use Yii;

use advert\models\Category;

use advert\models\Advert;

use auto\models\Mark;
use auto\models\Model;
use auto\models\Serie;
use auto\models\Modification;

use handbook\models\HandbookValue;

/**
 * Форма добавления/редактирования объявления.
 * Контакты обязательны только в том случае, если user_id пустое.
 *
 * Доступные сценарии:
 * - default - сценарий по умолчанию;
 * - submit - будет происходить валидация на правильность ввода реальных ID
 *  марок, моделей, серий и модификаций, а также категорий и состояний;
 */
class Form extends \yii\base\Model
{
    protected $_mark = [];
    protected $_model = [];
    protected $_serie = [];
    protected $_modification = [];

    protected $_uploadImage;

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
    public $user_id;
    public $user_phone;
    public $user_email;

    /**
     * Правила валидации
     * @return []
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
                return empty($model->user_id);
            }],
            [['price'], 'filter', 'filter' => function($price) {
                return trim(str_replace(',', '.', $price));
            }],
            [['price'], 'number'],
            [['user_email'], 'email', 'when' => function($model) {
                return empty($model->user_id);
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
     * @return []
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
     * @return []
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
            'price' => Yii::t('frontend/advert', 'Part price'),
            'mark' => Yii::t('frontend/advert', 'Choose mark'),
            'model' => Yii::t('frontend/advert', 'Choose model'),
            'serie' => Yii::t('frontend/advert', 'Choose serie'),
            'modification' => Yii::t('frontend/advert', 'Choose modification'),
        ];
    }

    /**
     * Получить атрибуты
     * @return []
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
     * @param [] $files
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
}