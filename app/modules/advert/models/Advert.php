<?php
namespace advert\models;

use Yii;

use yii\helpers\ArrayHelper;
use user\models\User;
use handbook\models\HandbookValue;

use auto\models\Mark;
use auto\models\Model;
use auto\models\Serie;
use auto\models\Modification;

use yii\base\Exception;

use storage\models\File;
use yii\web\UploadedFile;

/**
 * Модель объявления
 */
class Advert extends \yii\db\ActiveRecord
{
    /**
     * Максимальное количество файлов для загрузки
     */
    const UPLOAD_MAX_FILES = 10;

    /**
     * @var [] доступные расширения изображений
     */
    public static $_imageFileExtensions = ['jpeg', 'jpg', 'gif', 'png', 'bmp', 'tiff'];

    /**
     * Привязка к автомобилям, массивы
     */
    protected $_marks;
    protected $_models;
    protected $_series;
    protected $_modifications;

    /**
     * @var [] поле для загрузки новых изображений
     */
    protected $_uploadImage;

    /**
     * Таблица
     * @return string
     */
    public static function tableName()
    {
        return '{{%advert}}';
    }

    /**
     * Правила валидации
     * @return []
     */
    public function rules()
    {
        return [
            [['advert_name', 'price', 'condition_id', 'category_id'], 'required'],
            [['price'], 'filter', 'filter' => function($value) {
                return str_replace(',', '.', $value);
            }],
            [['price'], 'number', 'min' => 1, 'max' => 9999999],
            [['description'], 'safe'],
            [['user_id', 'condition_id', 'category_id'], 'integer'],
            [['user_name', 'user_phone', 'user_email'], 'required', 'when' => function($model) {
                // обязательна либо привязка к пользователю, либо координаты пользователя
                return empty($model->user_id);
            }],
            [['user_email'], 'email', 'when' => function($model) {
                return empty($model->user_id);
            }],
            [['active'], 'boolean'],
            [['marks', 'models', 'series', 'modifications'], 'safe'],
            [['uploadImage'], 'file',
                'skipOnEmpty' => true,
                'extensions' => self::$_imageFileExtensions,
                'maxFiles' => self::UPLOAD_MAX_FILES
            ],
        ];
    }

    /**
     * Подписи атрибутов
     * @return []
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'advert_name' => Yii::t('advert', 'Part name'),
            'price' => Yii::t('advert', 'Part price'),
            'condition_id' => Yii::t('advert', 'Part condition'),
            'category_id' => Yii::t('advert', 'Part category'),
            'user_name' => Yii::t('advert', 'Contact name'),
            'user_phone' => Yii::t('advert', 'Contact phone'),
            'user_email' => Yii::t('advert', 'Contact email'),
            'user_id' => Yii::t('advert', 'User id'),
            'active' => Yii::t('advert', 'Part active'),
            'description' => Yii::t('advert', 'Advert description'),
            'marks' => Yii::t('advert', 'Choose mark'),
            'models' => Yii::t('advert', 'Choose model'),
            'series' => Yii::t('advert', 'Choose serie'),
            'modifications' => Yii::t('advert', 'Choose modificaion'),
            'uploadImage' => Yii::t('advert', 'Upload image'),
        ];
    }

    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->created = date('Y-m-d H:i:s');
        }
        $this->edited = date('Y-m-d H:i:s');

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        // после сохранения необходимо прицепить файлы к объявлению
        if (!empty($this->_uploadImage)) {
            $this->attachImages($this->_uploadImage);
        }

        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * прикрепить изображения к объявлению
     * @param [] $uploadedFiles
     */
    protected function attachImages($uploadedFiles)
    {
        /* @var $storage \storage\components\Storage */
        $storage = Yii::$app->getModule('storage')->advert;
        /* @var $db yii\db\Connection */
        $db = $this->getDb();

        $transaction = $db->beginTransaction();

        try {
            foreach ($uploadedFiles as $image) {
                /* @var $image UploadedFile */
                $file = File::uploadFile($storage, $image);
                if ($file instanceof File) {
                    $db->createCommand()
                        ->insert('{{%advert_image}}', [
                            'advert_id' => $this->id,
                            'file_id' => $file->id,
                        ])
                        ->execute();
                }
            }

            $transaction->commit();
        }
        catch (Exception $ex) {
            $transaction->rollback();
        }
    }

    /**
     * Обновить автомобили, если требуется
     */
    public function updateAutomobiles()
    {
        $this->attachMark(is_array($this->_marks) ? $this->_marks : []);
        $this->attachModel(is_array($this->_models) ? $this->_models : []);
        $this->attachSerie(is_array($this->_series) ? $this->_series : []);
        $this->attachModification(is_array($this->_modifications) ? $this->_modifications : []);
    }

    /**
     * Загрузить изображения в модель
     * @return true
     */
    public function loadUploadedImages()
    {
        if ($files = UploadedFile::getInstances($this, 'uploadImage')) {
            $this->setUploadImage($files);
        }
        return true;
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

    /**
     * Получить пользователя
     * @return yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Получить категорию
     * @return yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }

    /**
     * Получить состояние запчасти
     * @return yii\db\ActiveQuery
     */
    public function getCondition()
    {
        return $this->hasOne(HandbookValue::className(), ['id' => 'condition_id'])
                ->where(['handbook_code' => 'part_condition']);
    }

    /**
     * Получить изображения
     * @return yii\db\ActiveQuery
     */
    public function getImages()
    {
        return $this->hasMany(File::className(), ['id' => 'file_id'])
            ->viaTable('{{%advert_image}}', ['advert_id' => 'id']);
    }

    /**
     * Получить марки автомобилей
     * @return yii\db\ActiveQuery
     */
    public function getMark()
    {
        return $this->hasMany(Mark::className(), ['id' => 'mark_id'])
            ->viaTable('{{%advert_mark}}', ['advert_id' => 'id']);
    }

    /**
     * Получить модели автомобилей
     * @return yii\db\ActiveQuery
     */
    public function getModel()
    {
        return $this->hasMany(Model::className(), ['id' => 'model_id'])
            ->viaTable('{{%advert_model}}', ['advert_id' => 'id']);
    }

    /**
     * Получить серии автомобилей
     * @return yii\db\ActiveQuery
     */
    public function getSerie()
    {
        return $this->hasMany(Serie::className(), ['id' => 'serie_id'])
            ->viaTable('{{%advert_serie}}', ['advert_id' => 'id']);
    }

    /**
     * Получить модификации автомобилей
     * @return yii\db\ActiveQuery
     */
    public function getModification()
    {
        return $this->hasMany(Modification::className(), ['id' => 'modification_id'])
            ->viaTable('{{%advert_modification}}', ['advert_id' => 'id']);
    }

    /**
     * По названию таблицы возвращает колонку для связи с автомобилем.
     * Таблица должна быть любой из:
     * - mark;
     * - model;
     * - serie;
     * - modification;
     * В случае ошибки генерирует Exception
     *
     * @param string $tableName
     * @throws Exception
     */
    protected function getAutoXrefColumn($tableName)
    {
        $xrefColumn = '';

        switch ($tableName) {
            case '{{%advert_mark}}':
                $xrefColumn = 'mark_id';
                break;
            case '{{%advert_model}}':
                $xrefColumn = 'model_id';
                break;
            case '{{%advert_serie}}':
                $xrefColumn = 'serie_id';
                break;
            case '{{%advert_modification}}':
                $xrefColumn = 'modification_id';
                break;
            default:
                throw new Exception();
        }

        return $xrefColumn;
    }

    /**
     * Очистить привязку по автомобилям.
     * Передается название таблицы для привязки и массив идентификаторов автомобиля.
     * Таблица должна быть любой из:
     * - mark;
     * - model;
     * - serie;
     * - modification;
     *
     * В случае, если запись новая - генерирует Exception.
     *
     * @param string $tableName
     * @return string
     * @throws Exception
     */
    protected function clearAutomobiles($tableName)
    {
        if ($this->isNewRecord) {
            throw new Exception();
        }

        $this->getDb()->createCommand()
            ->delete($tableName, 'advert_id=:id', [
                ':id' => $this->id
            ])
            ->execute();
    }

    /**
     * Прикрепить к объявлению автомобиль.
     * Передается название таблицы для привязки и массив идентификаторов автомобиля.
     * Таблица должна быть любой из:
     * - mark;
     * - model;
     * - serie;
     * - modification;
     *
     * В случае, если запись новая - генерирует Exception.
     *
     * @param string $tableName название таблицы для привязки
     * @param [] $ids массив идентификаторов автомобилей
     * @throws Exception
     */
    protected function attachAutomobile($tableName, $ids)
    {
        $this->clearAutomobiles($tableName);

        $xrefColumn = $this->getAutoXrefColumn($tableName);

        // сгенерировать строки для записи
        $rows = [];
        foreach ($ids as $id) {
            $rows[] = [$id, $this->id];
        }

        if (!empty($ids)) {
            $this->getDb()->createCommand()
                ->batchInsert($tableName, [$xrefColumn, 'advert_id'], $rows)
                ->execute();
        }
    }

    /**
     * Прикрепить к объявлению марки.
     * В случае, если это новая запись - генерирует Exception.
     * @param [] $markIds
     */
    public function attachMark($markIds)
    {
        $this->attachAutomobile('{{%advert_mark}}', $markIds);
    }

    /**
     * Прикрепить к объявлению модели.
     * В случае, если это новая запись - генерирует Exception.
     * @param [] $modelIds
     */
    public function attachModel($modelIds)
    {
        $this->attachAutomobile('{{%advert_model}}', $modelIds);
    }

    /**
     * Прикрепить к объявлению серии.
     * В случае, если это новая запись - генерирует Exception.
     * @param [] $serieIds
     */
    public function attachSerie($serieIds)
    {
        $this->attachAutomobile('{{%advert_serie}}', $serieIds);
    }

    /**
     * Прикрепить к объявлению модификации.
     * В случае, если это новая запись - генерирует Exception.
     * @param [] $modificationIds
     */
    public function attachModification($modificationIds)
    {
        $this->attachAutomobile('{{%advert_modification}}', $modificationIds);
    }

    /**
     * Выпадающий список категорий
     * @return []
     */
    public static function getCategoryDropDownList()
    {
        $ret = [];

        $categories = Category::find()->all();
        foreach ($categories as $category) {
            $ret[$category->id] = $category->getFormatName();
        }

        return $ret;
    }

    /**
     * Выпадающий список состояния запчасти
     * @return []
     */
    public static function getConditionDropDownList()
    {
        $ret = [];

        $values = HandbookValue::find()->andWhere(['handbook_code' => 'part_condition'])->all();
        foreach ($values as $value) {
            $ret[$value->id] = $value->name;
        }

        return $ret;
    }

    /**
     * Возвращает массив идентификаторов привязанных марок
     * @return []
     */
    public function getMarks()
    {
        if ($this->_marks === null) {
            $this->_marks = [];
            $res = $this->getMark()->all();
            $this->_marks = array_values(ArrayHelper::map($res, 'id', 'id'));
        }
        return $this->_marks;
    }

    /**
     * Возвращает массив идентификаторов привязанных моделей
     * @return []
     */
    public function getModels()
    {
        if ($this->_models === null) {
            $this->_models = [];
            $res = $this->getModel()->all();
            $this->_models = array_values(ArrayHelper::map($res, 'id', 'id'));
        }
        return $this->_models;
    }

    /**
     * Возвращает массив идентификаторов привязанных серий
     * @return []
     */
    public function getSeries()
    {
        if ($this->_series === null) {
            $this->_series = [];
            $res = $this->getSerie()->all();
            $this->_series = array_values(ArrayHelper::map($res, 'id', 'id'));
        }
        return $this->_series;
    }

    /**
     * Возвращает массив идентификаторов привязанных модификаций
     * @return []
     */
    public function getModifications()
    {
        if ($this->_modifications === null) {
            $this->_modifications = [];
            $res = $this->getModification()->all();
            $this->_modifications = array_values(ArrayHelper::map($res, 'id', 'id'));
        }
        return $this->_modifications;
    }

    /**
     * Установить новые марки
     * @param [] $ids
     */
    public function setMarks($ids)
    {
        if (is_array($ids)) {
            $this->_marks = $ids;
        }
        else {
            $this->_marks = [];
        }
    }

    /**
     * Установить новые модели
     * @param [] $ids
     */
    public function setModels($ids)
    {
        if (is_array($ids)) {
            $this->_models = $ids;
        }
        else {
            $this->_models = [];
        }
    }

    /**
     * Установить новые серии
     * @param [] $ids
     */
    public function setSeries($ids)
    {
        if (is_array($ids)) {
            $this->_series = $ids;
        }
        else {
            $this->_series = [];
        }
    }

    /**
     * Установить новые модификации
     * @param [] $ids
     */
    public function setModifications($ids)
    {
        if (is_array($ids)) {
            $this->_modifications = $ids;
        }
        else {
            $this->_modifications = [];
        }
    }
}