<?php
namespace advert\models;

use Yii;

use yii\base\Exception;
use yii\imagine\Image;
use yii\web\UploadedFile;
use storage\models\File;
use Imagine\Image\ManipulatorInterface;

/**
 * Модель изображения объявления
 */
class AdvertImage extends \yii\db\ActiveRecord
{
    const PREVIEW_WIDTH = 200;
    const PREVIEW_HEIGHT = 200;

    const THUMB_WIDTH = 100;
    const THUMB_HEIGHT = 100;

    /**
     * @var Storage
     */
    protected static $_storage;

    /**
     * Таблица
     * @return string
     */
    public static function tableName()
    {
        return '{{%advert_image}}';
    }

    /**
     * Правила валидации
     * @return []
     */
    public function rules()
    {
        return [
            [['advert_id', 'thumb_id', 'file_id'], 'required'],
            [['advert_id', 'thumb_id', 'preview_id', 'file_id'], 'integer'],
            [['is_preview'], 'boolean'],
        ];
    }

    /**
     * Возвращает файловое хранилище
     * @return Storage
     */
    public static function getStorage()
    {
        if (self::$_storage === null) {
            self::$_storage = Yii::$app->getModule('storage')->advert;
        }
        return self::$_storage;
    }

    /**
     * Создает кропнутую копию файла $file.
     * Возвращает объект File или null.
     *
     * @param File $file
     * @param type $width
     * @param type $height
     * @return File|null
     * @throws Exception
     */
    protected static function createThumbCopy(File $file, $width, $height)
    {
        $ret = null;

        $path = Yii::getAlias('@runtime/crop-' .
            md5($file->real_name . uniqid()) .
            '.' . $file->getExtension()
        );

        $transaction = File::getDb()->beginTransaction();
        try {
            Image::thumbnail($file->getPath(), $width, $height, ManipulatorInterface::THUMBNAIL_INSET)
                ->save($path, ['quality' => 80]);
            if (!is_file($path)) {
                throw new Exception();
            }
            $ret = self::getStorage()->saveFile($path, $file->real_name, true);
            if (!($ret instanceof File)) {
                throw Exception();
            }
            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollback();
            if (is_file($path)) {
                // удалить промежуточный файл
                unlink($path);
            }
            throw $ex;
        }

        return $ret;
    }

    /**
     * Загрузить изображение к объявлению
     *
     * @param \advert\models\Advert $advert
     * @param \yii\web\UploadedFile $file
     * @param boolean $isPreview
     * @return self|null модель загруженной фотографии
     * @throws \yii\base\Exception
     */
    public static function attachToAdvert(Advert $advert, UploadedFile $uploadedFile, $isPreview = false)
    {
        $ret = null;

        $fileMain = null;
        $fileThumb = null;
        $filePreview = null;

        $transaction = self::getDb()->beginTransaction();

        try {
            // загрузить основной файл
            $fileMain = File::uploadFile(self::getStorage(), $uploadedFile);
            if (!($fileMain instanceof File)) {
                throw new Exception();
            }

            // создать тултип
            $fileThumb = self::createThumbCopy($fileMain, self::THUMB_WIDTH, self::THUMB_HEIGHT);

            // создать превьюху, если надо
            if ($isPreview) {
                $filePreview = self::createThumbCopy($fileMain, self::PREVIEW_WIDTH, self::PREVIEW_HEIGHT);
            }

            // создать привязку к объявлению
            $ret = new self();
            $ret->setAttributes([
                'advert_id' => $advert->id,
                'file_id' => $fileMain->id,
                'thumb_id' => $fileThumb->id,
                'preview_id' => !empty($filePreview) ? $filePreview->id : null,
                'is_preview' => $isPreview && !empty($filePreview),
            ]);
            if (!$ret->save()) {
                throw new Exception();
            }

            $transaction->commit();
        }
        catch (Exception $ex) {
            $transaction->rollBack();

            // удалить файлы, если они были созданы
            if ($fileMain instanceof File) {
                $fileMain->getStorage()->delete($fileMain->file_path);
            }
            if ($fileThumb instanceof File) {
                $fileThumb->getStorage()->delete($fileThumb->file_path);
            }
            if ($filePreview instanceof File) {
                $filePreview->getStorage()->delete($filePreview->file_path);
            }

            throw $ex;
        }

        return $ret;
    }

    /**
     * Получить файл
     * @return File
     */
    public function getFile()
    {
        return $this->hasOne(File::className(), ['id' => 'file_id'])->one();
    }

    /**
     * Получить превью
     * @return File|null
     */
    public function getPreview()
    {
        return $this->preview_id ?
            $this->hasOne(File::className(), ['id' => 'preview_id'])->one() :
            null;
    }

    /**
     * Получить иконку
     * @return File
     */
    public function getThumbnail()
    {
        return $this->hasOne(File::className(), ['id' => 'thumb_id'])->one();
    }
}