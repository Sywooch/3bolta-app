<?php
namespace advert\models;

use Yii;

use app\helpers\Date as DateHelper;
use yii\helpers\ArrayHelper;
use user\models\User;
use handbook\models\HandbookValue;

use auto\models\Mark;
use auto\models\Model;
use auto\models\Serie;
use auto\models\Modification;

use yii\db\Expression;

use yii\base\Exception;

use yii\web\UploadedFile;

use app\components\PhoneValidator;

/**
 * Модель объявления
 */
class Advert extends \app\components\ActiveRecord
{
    /**
     * Количество дней по умолчанию для публикации объявлений
     */
    const DEFAULT_PUBLISH_DAYS = 30;

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
     * @var User
     */
    protected $_user;

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
            ['price', 'filter', 'filter' => function($value) {
                return str_replace(',', '.', $value);
            }],
            [['price'], 'number', 'min' => 1, 'max' => 9999999,
                'numberPattern' => '#^[0-9]{1,7}[\.|\,]?[0-9]{0,2}$#',
                'enableClientValidation' => false,
            ],
            [['description', 'published', 'published_to'], 'safe'],
            [['user_id', 'condition_id', 'category_id'], 'integer'],
            [['user_name', 'user_phone', 'user_email'], 'required', 'when' => function($model) {
                // обязательна либо привязка к пользователю, либо координаты пользователя
                return empty($model->user_id);
            }],
            [['user_phone'], PhoneValidator::className(), 'canonicalAttribute' => 'user_phone_canonical'],
            [['user_name', 'advert_name'], 'string', 'max' => 50],
            ['user_email', 'string', 'max' => 100],
            ['user_phone_canonical', 'string', 'max' => 11],
            ['user_phone', 'string', 'max' => 19],
            [['user_email'], 'filter', 'filter' => 'strtolower'],
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
            [['confirmation'], 'safe'],
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
            $this->_uploadImage = [];
        }

        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * прикрепить изображения к объявлению
     * @param [] $uploadedFiles
     */
    protected function attachImages($uploadedFiles)
    {
        if ($this->isNewRecord) {
            return false;
        }

        // получить превью, если оно есть
        $preview = $this->getPreview();
        $hasPreview = !empty($preview);

        $transaction = self::getDb()->beginTransaction();

        try {
            $isFirst = true;
            foreach ($uploadedFiles as $file) {
                AdvertImage::attachToAdvert($this, $file, $isFirst && !$hasPreview);
                $isFirst = false;
            }

            $transaction->commit();
        } catch (Exception $ex) {
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
     * Получить e-mail пользователя
     * @return string
     */
    public function getUserEmail()
    {
        if ($this->user_id && $user = $this->getUser()) {
            return $user->email;
        }
        return $this->user_email;
    }

    /**
     * Получить контактный телефон
     * @return string
     */
    public function getUserPhone()
    {
        if ($this->user_id && $user = $this->getUser()) {
            return $user->phone;
        }
        return $this->user_phone;
    }

    /**
     * Получить контактное имя пользователя
     * @return string
     */
    public function getUserName()
    {
        if ($this->user_id && $user = $this->getUser()) {
            return $user->name;
        }
        return $this->user_name;
    }

    /**
     * Получить пользователя
     * @return yii\db\ActiveQuery
     */
    public function getUser()
    {
        if ($this->_user === null) {
            $this->_user = $this->hasOne(User::className(), ['id' => 'user_id'])->one();
        }
        return $this->_user;
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
     * Получить название состояния
     * @return string
     */
    public function getConditionName()
    {
        if ($this->condition_id && $this->condition instanceof HandbookValue) {
            return $this->condition->name;
        }

        return '';
    }

    /**
     * Получить превью
     * @return \storage\models\File
     */
    public function getPreview()
    {
        $ret = null;
        foreach ($this->images as $image) {
            if ($image->is_preview) {
                /* @var $image \advert\models\AdvertImage */
                $ret = $image->preview;
            }
        }
        return $ret;
    }

    /**
     * Получить изображения
     * @return \yii\db\ActiveQuery
     */
    public function getImages()
    {
        return $this->hasMany(AdvertImage::className(), ['advert_id' => 'id']);
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
            if ($id) {
                $rows[] = [$id, $this->id];
            }
        }

        if (!empty($rows)) {
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
     * @param boolean $getFirstEmpty получать первый пустой элемент
     * @return []
     */
    public static function getCategoryDropDownList($getFirstEmpty = false)
    {
        $ret = [];

        if ($getFirstEmpty) {
            $ret[''] = '';
        }

        $categories = Category::find()->all();
        foreach ($categories as $category) {
            $ret[$category->id] = $category->getFormatName();
        }

        return $ret;
    }

    /**
     * Выпадающий список состояния запчасти
     * @param boolean $getFirstEmpty получать первый пустой элемент
     * @return []
     */
    public static function getConditionDropDownList($getFirstEmpty = false)
    {
        $ret = [];

        if ($getFirstEmpty) {
            $ret[''] = '';
        }

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
            $this->_marks = array_values(ArrayHelper::map($this->mark, 'id', 'id'));
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
            $this->_models = array_values(ArrayHelper::map($this->model, 'id', 'id'));
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
            $this->_series = array_values(ArrayHelper::map($this->serie, 'id', 'id'));
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
            $this->_modifications = array_values(ArrayHelper::map($this->modification, 'id', 'id'));
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

    /**
     * Поиск объявлений авторизованного пользователя
     *
     * @return \yii\db\ActiveQuery
     * @throws Exception в случае, если пользователь не авторизован
     */
    public static function findUserList()
    {
        if (Yii::$app->user->isGuest) {
            // если пользователь неавторизован - выполнять метод невозможно
            throw new Exception();
        }

        return self::find()->andWhere([
            'advert.user_id' => Yii::$app->user->getId()
        ])->orderBy('advert.published DESC');
    }

    /**
     * Поиск активных и опубликованных объявлений
     * @return \yii\db\ActiveQuery
     */
    public static function findActiveAndPublished()
    {
        return self::find()->andWhere([
                'advert.active' => true
            ])
            ->andWhere(['not', 'advert.published IS NULL'])
            ->andWhere(['or',
                ['>=', 'advert.published_to', new Expression('NOW()')],
                'advert.published_to IS NULL'
            ])
            ->andWhere(['<=', 'advert.published', new Expression('NOW()')]);
    }

    /**
     * Возвращает отформатированную цену
     * @return string
     */
    public function getPriceFormated()
    {
        $price = (float) $this->price;
        $decimals = 2;
        if (round($price, 0) == $price) {
            $decimals = 0;
        }
        return number_format($price, $decimals, ',', ' ');
    }

    /**
     * Получить массив дерева категорий
     * @return []
     */
    public function getCategoriesTree()
    {
        $ret = [];

        if ($this->category_id && $category = $this->category) {
            $ret[$category->id] = $category->name;
            $previewDepth = $category->depth;
            if ($previewDepth > 1) {
                $list = Category::find()
                    ->andWhere(['<', 'lft', $category->lft])
                    ->orderBy('lft DESC')
                    ->all();
                foreach ($list as $i) {
                    if ($i->depth == $previewDepth) {
                        continue;
                    }
                    $previewDepth = $i->depth;
                    $ret[$i->id] = $i->name;
                    if ($previewDepth == 1) {
                        break;
                    }
                }
            }
        }

        return array_reverse($ret, true);
    }

    /**
     * Возвращает отформатированную дату публикации
     * @return string
     */
    public function getPublishedFormatted()
    {
        $ret = '';
        if ($this->published) {
            $ret = DateHelper::formatDate($this->published);
        }
        return $ret;
    }

    /**
     * Возвращает отформатированную дату публикации до
     * @return string
     */
    public function getPublishedToFormatted()
    {
        $ret = '';
        if ($this->published_to) {
            $ret = DateHelper::formatDate($this->published_to);
        }
        return $ret;
    }
}