<?php
namespace advert\models;

use app\components\ActiveRecord;
use app\components\PhoneValidator;
use app\helpers\Date as DateHelper;
use auto\models\Mark;
use auto\models\Model;
use auto\models\Modification;
use auto\models\Serie;
use geo\models\Region;
use handbook\models\HandbookValue;
use partner\models\Partner;
use partner\models\TradePoint;
use storage\models\File;
use user\models\User;
use Yii;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\UploadedFile;

/**
 * Модель объявления
 */
class PartAdvert extends ActiveRecord
{
    const TABLE_MARK = '{{%advert_mark}}';
    const TABLE_MODEL = '{{%advert_model}}';
    const TABLE_MODIFICATION = '{{%advert_modification}}';
    const TABLE_SERIE = '{{%advert_serie}}';

    /**
     * Максимальное количество символов в каталожном номере
     */
    const CATALOGUE_NUMBER_MAX_LENGTH = 100;

    /**
     * Максимальная длина названия запчасти
     */
    const NAME_MAX_LENGTH = 100;

    /**
     * Максимальная длина описания
     */
    const DESCRIPTION_MAX_LENGTH = 255;

    /**
     * Количество дней по умолчанию для публикации объявлений
     */
    const DEFAULT_PUBLISH_DAYS = 30;

    /**
     * Максимальное количество файлов для загрузки
     */
    const UPLOAD_MAX_FILES = 5;

    /**
     * Максимальный размер загружаемых файлов
     */
    const UPLOAD_MAX_FILE_SIZE = 2097152;

    /**
     * @var array доступные расширения изображений
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
     * @var array поле для загрузки новых изображений
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
     * @return array
     */
    public function rules()
    {
        return [
            [['advert_name', 'price', 'condition_id', 'category_id'], 'required'],
            ['catalogue_number', 'string', 'max' => self::CATALOGUE_NUMBER_MAX_LENGTH, 'skipOnEmpty' => true],
            ['advert_name', 'string', 'max' => self::NAME_MAX_LENGTH, 'skipOnEmpty' => false],
            ['description', 'string', 'max' => self::DESCRIPTION_MAX_LENGTH],
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
            ['trade_point_id', 'integer', 'skipOnEmpty' => false, 'when' => function($data) {
                /* @var $data PartAdvert */
                $userId = (int) $data->user_id;
                if ($userId) {
                    /* @var $user User */
                    $user = User::find()->andWhere(['id' => $userId])->one();
                    return $user instanceof User && $user->type == User::TYPE_LEGAL_PERSON;
                }
                return false;
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
                'maxFiles' => self::UPLOAD_MAX_FILES,
                'maxSize' => self::UPLOAD_MAX_FILE_SIZE,
            ],
            [['confirmation'], 'safe'],
            [['region_id'], 'integer', 'skipOnEmpty' => false],
            ['allow_questions', 'boolean'],
        ];
    }

    /**
     * Подписи атрибутов
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'region_id' => Yii::t('advert', 'Region'),
            'advert_name' => Yii::t('advert', 'Part name'),
            'catalogue_number' => Yii::t('advert', 'Catalogue number'),
            'price' => Yii::t('advert', 'Part price'),
            'condition_id' => Yii::t('advert', 'Part condition'),
            'category_id' => Yii::t('advert', 'Part category'),
            'user_name' => Yii::t('advert', 'Contact name'),
            'user_phone' => Yii::t('advert', 'Contact phone'),
            'user_email' => Yii::t('advert', 'Contact email'),
            'user_id' => Yii::t('advert', 'User id'),
            'trade_point_id' => Yii::t('advert', 'Trade point'),
            'active' => Yii::t('advert', 'Part active'),
            'description' => Yii::t('advert', 'Advert description'),
            'marks' => Yii::t('advert', 'Choose mark'),
            'models' => Yii::t('advert', 'Choose model'),
            'series' => Yii::t('advert', 'Choose serie'),
            'modifications' => Yii::t('advert', 'Choose modificaion'),
            'uploadImage' => Yii::t('advert', 'Upload image'),
            'allow_questions' => Yii::t('advert', 'Allow questions by email'),
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

        // обновить изображение по умолчанию
        $this->updateDefaultImage();

        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Если не установлено изображение с признаком "по умолчанию (is_preview)" - устанавливает его
     */
    public function updateDefaultImage()
    {
        if (empty($this->images)) {
            return true;
        }

        $firstImage = null;
        foreach ($this->images as $image) {
            /* @var $image PartAdvertImage */
            if ($image->is_preview) {
                return true;
            }
            if (!$firstImage) {
                /* @var $firstImage PartAdvertImage */
                $firstImage = $image;
            }
        }

        try {
            $firstImage->is_preview = true;
            return $firstImage->save(false, ['is_preview']);
        } catch (Exception $ex) { }

        return false;
    }

    /**
     * прикрепить изображения к объявлению
     * @param array $uploadedFiles
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
                PartAdvertImage::attachToAdvert($this, $file, $isFirst && !$hasPreview);
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
     * Получить торговую точку
     * @return ActiveQuery
     */
    public function getTradePoint()
    {
        return $this->hasOne(TradePoint::className(), ['id' => 'trade_point_id']);
    }

    /**
     * Получить регион
     * @return ActiveQuery
     */
    public function getRegion()
    {
        return $this->hasOne(Region::className(), ['id' => 'region_id']);
    }

    /**
     * Получить пользователя
     * @return ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id'])->one();
    }

    /**
     * Получить категорию
     * @return ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(PartCategory::className(), ['id' => 'category_id']);
    }

    /**
     * Получить состояние запчасти
     * @return ActiveQuery
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
     * @return File
     */
    public function getPreview()
    {
        $ret = null;
        foreach ($this->images as $image) {
            if ($image->is_preview) {
                /* @var $image PartAdvertImage */
                $ret = $image->preview;
            }
        }
        return $ret;
    }

    /**
     * Получить URL на превью
     *
     * @return string
     */
    public function getPreviewUrl()
    {
        $image = $this->getPreview();
        return $image instanceof PartAdvertImage ? $image->getUrl() : null;
    }

    /**
     * Получить изображения
     * @return ActiveQuery
     */
    public function getImages()
    {
        return $this->hasMany(PartAdvertImage::className(), ['advert_id' => 'id']);
    }

    /**
     * Получить марки автомобилей
     * @return ActiveQuery
     */
    public function getMark()
    {
        return $this->hasMany(Mark::className(), ['id' => 'mark_id'])
            ->viaTable(self::TABLE_MARK, ['advert_id' => 'id']);
    }

    /**
     * Получить модели автомобилей
     * @return ActiveQuery
     */
    public function getModel()
    {
        return $this->hasMany(Model::className(), ['id' => 'model_id'])
            ->viaTable(self::TABLE_MODEL, ['advert_id' => 'id']);
    }

    /**
     * Получить серии автомобилей
     * @return ActiveQuery
     */
    public function getSerie()
    {
        return $this->hasMany(Serie::className(), ['id' => 'serie_id'])
            ->viaTable(self::TABLE_SERIE, ['advert_id' => 'id']);
    }

    /**
     * Получить модификации автомобилей
     * @return ActiveQuery
     */
    public function getModification()
    {
        return $this->hasMany(Modification::className(), ['id' => 'modification_id'])
            ->viaTable(self::TABLE_MODIFICATION, ['advert_id' => 'id']);
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
            case self::TABLE_MARK:
                $xrefColumn = 'mark_id';
                break;
            case self::TABLE_MODEL:
                $xrefColumn = 'model_id';
                break;
            case self::TABLE_SERIE:
                $xrefColumn = 'serie_id';
                break;
            case self::TABLE_MODIFICATION:
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
     * @param array $ids массив идентификаторов автомобилей
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
     * @param array $markIds
     */
    public function attachMark($markIds)
    {
        $this->attachAutomobile(self::TABLE_MARK, $markIds);
    }

    /**
     * Прикрепить к объявлению модели.
     * В случае, если это новая запись - генерирует Exception.
     * @param array $modelIds
     */
    public function attachModel($modelIds)
    {
        $this->attachAutomobile(self::TABLE_MODEL, $modelIds);
    }

    /**
     * Прикрепить к объявлению серии.
     * В случае, если это новая запись - генерирует Exception.
     * @param array $serieIds
     */
    public function attachSerie($serieIds)
    {
        $this->attachAutomobile(self::TABLE_SERIE, $serieIds);
    }

    /**
     * Прикрепить к объявлению модификации.
     * В случае, если это новая запись - генерирует Exception.
     * @param array $modificationIds
     */
    public function attachModification($modificationIds)
    {
        $this->attachAutomobile(self::TABLE_MODIFICATION, $modificationIds);
    }

    /**
     * Выпадающий список категорий
     * @param boolean $getFirstEmpty получать первый пустой элемент
     * @return array
     */
    public static function getCategoryDropDownList($getFirstEmpty = false)
    {
        $ret = [];

        if ($getFirstEmpty) {
            $ret[''] = '';
        }

        $categories = PartCategory::find()->all();
        foreach ($categories as $category) {
            $ret[$category->id] = $category->getFormatName();
        }

        return $ret;
    }

    /**
     * Выпадающий список состояния запчасти
     * @param boolean $getFirstEmpty получать первый пустой элемент
     * @return array
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
     * @return array
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
     * @return array
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
     * @return array
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
     * @return array
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
     * @param array $ids
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
     * @param array $ids
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
     * @param array $ids
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
     * @param array $ids
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
     * @return ActiveQuery
     * @throws Exception в случае, если пользователь не авторизован
     */
    public static function findUserList()
    {
        if (Yii::$app->user->isGuest) {
            // если пользователь неавторизован - выполнять метод невозможно
            throw new Exception();
        }

        return self::find()->andWhere([
            'partadvert.user_id' => Yii::$app->user->getId()
        ])->orderBy('partadvert.published DESC');
    }

    /**
     * Поиск активных и опубликованных объявлений
     * @return ActiveQuery
     */
    public static function findActiveAndPublished()
    {
        return self::find()->andWhere([
                'partadvert.active' => true
            ])
            ->andWhere(['not', 'partadvert.published IS NULL'])
            ->andWhere(['or',
                ['>=', 'partadvert.published_to', new Expression('NOW()')],
                'partadvert.published_to IS NULL'
            ])
            ->andWhere(['<=', 'partadvert.published', new Expression('NOW()')]);
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
     * @return array
     */
    public function getCategoriesTree()
    {
        $ret = [];

        if ($this->category_id && $category = $this->category) {
            $ret[$category->id] = $category->name;
            $previewDepth = $category->depth;
            if ($previewDepth > 1) {
                $list = PartCategory::find()
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

    /**
     * Получить продавца:
     * 1) если компания - возвращает название партнера;
     * 2) если частное лицо и нет признака $hidePrivatePerson - возаращет имя контактного лица;
     * 3) иначе - "Частное лицо".
     *
     * @return type
     */
    public function getSeller($hidePrivatePerson = true)
    {
        if ($tradePoint = $this->tradePoint) {
            /* @var $tradePoint TradePoint */
            /* @var $partner Partner */
            $partner = $tradePoint->partner;
            if ($partner instanceof Partner) {
                return Html::encode($partner->name);
            }
        }

        if (!$hidePrivatePerson && $this->user_name) {
            return Html::encode($this->user_name);
        }
        else if (!$hidePrivatePerson && $user = $this->user) {
            return Html::encode($user->name);
        }

        return Yii::t('frontend/advert', 'private person');
    }

    /**
     * Возвращает true, если это не объявление текущего пользвоателя
     * и в объявлении проставлена галка "Разрешить вопросы по e-mail".
     *
     * @return boolean
     */
    public function allowQuestions()
    {
        $ret = $this->allow_questions;

        if ($ret) {
            $ret = !$this->user_id || $this->user_id != \Yii::$app->user->getId();
        }

        return $ret;
    }
}