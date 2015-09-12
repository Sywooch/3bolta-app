<?php
namespace advert\models;

use app\helpers\Date as DateHelper;
use partner\models\Partner;
use partner\models\TradePoint;
use storage\models\File;
use user\models\User;
use Yii;
use Exception;
use advert\exception\AdvertException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\Html;
use yii\web\UploadedFile;

/**
 * Модель объявления
 */
class Advert extends ActiveRecord
{
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
            [['advert_name', 'price'], 'required'],
            ['advert_name', 'string', 'max' => self::NAME_MAX_LENGTH, 'skipOnEmpty' => false],
            ['description', 'string', 'max' => self::DESCRIPTION_MAX_LENGTH],
            ['price', 'filter', 'filter' => function($value) {
                return str_replace(',', '.', $value);
            }],
            ['price', 'number', 'min' => 1, 'max' => 9999999,
                'numberPattern' => '#^[0-9]{1,7}[\.|\,]?[0-9]{0,2}$#',
                'enableClientValidation' => false,
            ],
            [['description', 'published', 'published_to'], 'safe'],
            ['user_id', 'integer'],
            ['active', 'boolean'],
            ['uploadImage', 'file',
                'skipOnEmpty' => true,
                'extensions' => self::$_imageFileExtensions,
                'maxFiles' => self::UPLOAD_MAX_FILES,
                'maxSize' => self::UPLOAD_MAX_FILE_SIZE,
            ],
            ['confirmation', 'safe'],
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
            'advert_name' => Yii::t('advert', 'Part name'),
            'price' => Yii::t('advert', 'Part price'),
            'user_id' => Yii::t('advert', 'User id'),
            'active' => Yii::t('advert', 'Part active'),
            'description' => Yii::t('advert', 'Advert description'),
            'uploadImage' => Yii::t('advert', 'Upload image'),
            'allow_questions' => Yii::t('advert', 'Allow questions by email'),
        ];
    }

    /**
     * Перед сохранением установить дату создания
     *
     * @param mixed $insert
     * @return boolean
     */
    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->created = date('Y-m-d H:i:s');
        }
        $this->edited = date('Y-m-d H:i:s');

        return parent::beforeSave($insert);
    }

    /**
     * Подгрузить изображения после сохранения
     *
     * @param mixed $insert
     * @param array $changedAttributes
     * @return boolean
     */
    public function afterSave($insert, $changedAttributes)
    {
        unset ($this->contact);
        unset ($this->images);

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
     * @return boolean true в случае успеха
     * @throws AdvertException
     */
    public function updateDefaultImage()
    {
        $firstImage = null;
        foreach ($this->getImages()->all() as $image) {
            /* @var $image Image */
            if ($image->is_preview) {
                return true;
            }
            if (!$firstImage) {
                /* @var $firstImage Image */
                $firstImage = $image;
            }
        }

        if (!($firstImage instanceof Image)) {
            // нет изображений
            return true;
        }

        try {
            $firstImage->is_preview = true;
            if (!$firstImage->save(false, ['is_preview'])) {
                throw new AdvertException('', AdvertException::VALIDATION_ERROR);
            }
        } catch (Exception $ex) {
            AdvertException::throwUp($ex);
        }

        return true;
    }

    /**
     * прикрепить изображения к объявлению
     * @param array $uploadedFiles
     * @return boolean true в случае успеха
     * @throws AdvertException
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
                Image::attachToAdvert($this, $file, $isFirst && !$hasPreview);
                $isFirst = false;
            }

            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollback();
            AdvertException::throwUp($ex);
        }

        return true;
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
        else if ($this->contact instanceof Contact) {
            return $this->contact->user_email;
        }
        return '';
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
        else if ($this->contact instanceof Contact) {
            return $this->contact->user_phone;
        }
        return '';
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
        else if ($this->contact instanceof Contact) {
            return $this->contact->user_name;
        }
        return '';
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
     * Получить превью
     * @return File
     */
    public function getPreview()
    {
        $ret = null;
        foreach ($this->images as $image) {
            if ($image->is_preview) {
                /* @var $image Image */
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
        return $image instanceof File ? $image->getUrl() : null;
    }

    /**
     * Получить изображения
     * @return ActiveQuery
     */
    public function getImages()
    {
        return $this->hasMany(Image::className(), ['advert_id' => 'id'])
            ->groupBy(Image::tableName() . '.id');
    }

    /**
     * Поиск объявлений авторизованного пользователя
     *
     * @return ActiveQuery
     * @throws AdvertException в случае, если пользователь не авторизован
     */
    public static function findUserList()
    {
        if (Yii::$app->user->isGuest) {
            // если пользователь неавторизован - выполнять метод невозможно
            throw new AdvertException('', AdvertException::UNKNOWN_ERROR);
        }

        return self::find()->andWhere([
            self::tableName() . '.user_id' => Yii::$app->user->getId()
        ])->orderBy(self::tableName() . '.published DESC');
    }

    /**
     * Поиск активных и опубликованных объявлений
     * @return ActiveQuery
     */
    public static function findActiveAndPublished()
    {
        return self::find()->andWhere([
                self::tableName() . '.active' => true
            ])
            ->andWhere(['not', self::tableName() . '.published IS NULL'])
            ->andWhere(['or',
                ['>=', self::tableName() . '.published_to', new Expression('NOW()')],
                self::tableName() . '.published_to IS NULL'
            ])
            ->andWhere(['<=', self::tableName() . '.published', new Expression('NOW()')]);
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
     * @param boolean $hidePrivatePerson
     * @return string
     */
    public function getSeller($hidePrivatePerson = true)
    {
        /* @var $contact Contact */
        $contact = $this->contact;

        if (!($contact instanceof Contact)) {
            return Yii::t('frontend/advert', 'private person');
        }

        if ($tradePoint = $contact->tradePoint) {
            /* @var $tradePoint TradePoint */
            /* @var $partner Partner */
            $partner = $tradePoint->partner;
            if ($partner instanceof Partner) {
                return Html::encode($partner->name);
            }
        }

        if (!$hidePrivatePerson && $contact->user_name) {
            return Html::encode($contact->user_name);
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

    /**
     * Получить привязку к контакту
     *
     * @return ActiveQuery
     */
    public function getContact()
    {
        return $this->hasOne(Contact::className(), ['advert_id' => 'id']);
    }
}