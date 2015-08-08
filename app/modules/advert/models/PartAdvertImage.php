<?php
namespace advert\models;

use Imagine\Image\Box;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\Point;
use storage\components\Storage;
use storage\models\File;
use Yii;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\imagine\Image;
use yii\web\UploadedFile;

/**
 * Модель изображения объявления
 */
class PartAdvertImage extends ActiveRecord
{
    const PREVIEW_WIDTH = 123;
    const PREVIEW_HEIGHT = 123;

    const THUMB_WIDTH = 100;
    const THUMB_HEIGHT = 100;

    const IMAGE_WIDTH = 1000;
    const IMAGE_HEIGHT = 750;

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
     * @return array
     */
    public function rules()
    {
        return [
            [['advert_id', 'file_id'], 'required'],
            [['image_id', 'advert_id', 'thumb_id', 'preview_id', 'file_id'], 'integer'],
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
     * @param int $width
     * @param int $height
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

            if ($file->width <= $width && $file->height <= $height) {
                // создать рамку вокруг
                $origImage = Image::getImagine()->open($file->getPath());
                $newImage = Image::getImagine()->create(new Box($width, $height))
                    //->fill(new \Imagine\Image\Color('#FFF'))
                    ->paste($origImage, new Point(
                        ($width - $file->width) / 2,
                        ($height - $file->height) / 2
                    ))
                    ->save($path, ['quality' => 100]);
            }
            else {
                Image::getImagine()->open($file->getPath())
                    ->thumbnail(new Box($width, $height), ManipulatorInterface::THUMBNAIL_OUTBOUND)
                    ->save($path, ['quality' => 80]);
            }

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
     * Создать тултип, если его еще нет
     *
     * @return File
     * @throws Exception
     */
    public function createThumb()
    {
        if ($this->thumb_id) {
            // если картинка уже есть - возвращаем ее
            return $this->thumbnail;
        }

        $fileMain = $this->file;

        // вновь создаваемый файл
        $file = null;

        $transaction = $this->getDb()->beginTransaction();

        try {
            // создать тултип для изображения
            $file = self::createThumbCopy($fileMain, self::THUMB_WIDTH, self::THUMB_HEIGHT);

            $this->thumb_id = $file->id;
            if (!$this->save(false, ['thumb_id'])) {
                throw new Exception();
            }

            $transaction->commit();
        }
        catch (Exception $ex) {
            $transaction->rollBack();
            if ($file instanceof File) {
                $file->getStorage()->delete($file->file_path);
            }
            throw new Exception();
        }

        return $file;
    }

    /**
     * Создать превью, если его еще нет
     *
     * @return File
     * @throws Exception
     */
    public function createPreview()
    {
        if ($this->preview_id) {
            // если картинка уже есть - возвращаем ее
            return $this->preview;
        }

        $fileMain = $this->file;

        // вновь создаваемый файл
        $file = null;

        $transaction = $this->getDb()->beginTransaction();

        try {
            // создать превью
            $file = self::createThumbCopy($fileMain, self::PREVIEW_WIDTH, self::PREVIEW_HEIGHT);

            $this->preview_id = $file->id;
            if (!$this->save(false, ['preview_id'])) {
                throw new Exception();
            }

            $transaction->commit();
        }
        catch (Exception $ex) {
            $transaction->rollBack();
            if ($file instanceof File) {
                $file->getStorage()->delete($file->file_path);
            }
            throw new Exception();
        }

        return $file;
    }

    /**
     * Создать сжатую копию, если его еще нет
     *
     * @return File
     * @throws Exception
     */
    public function createImage()
    {
        if ($this->image_id) {
            // если картинка уже есть - возвращаем ее
            return $this->image;
        }

        $fileMain = $this->file;

        // вновь создаваемый файл
        $file = null;

        $transaction = $this->getDb()->beginTransaction();

        try {
            // создать сжатое изображение
            $file = self::createThumbCopy($fileMain, self::IMAGE_WIDTH, self::IMAGE_HEIGHT);

            $this->image_id = $file->id;
            if (!$this->save(false, ['image_id'])) {
                throw new Exception();
            }

            $transaction->commit();
        }
        catch (Exception $ex) {
            $transaction->rollBack();
            if ($file instanceof File) {
                $file->getStorage()->delete($file->file_path);
            }
            throw $ex;
        }

        return $file;
    }

    /**
     * Загрузить изображение к объявлению
     *
     * @param PartAdvert $advert
     * @param UploadedFile $uploadedFile
     * @param boolean $isPreview
     * @return self|null модель загруженной фотографии
     * @throws Exception
     */
    public static function attachToAdvert(PartAdvert $advert, UploadedFile $uploadedFile, $isPreview = false)
    {
        $ret = null;

        $file = null;

        $transaction = self::getDb()->beginTransaction();

        try {
            // загрузить основной файл
            $file = File::uploadFile(self::getStorage(), $uploadedFile);
            if (!($file instanceof File) || !$file->width || !$file->height) {
                // без файла дальше не работаем,
                // либо это не изображение
                throw new Exception();
            }

            // создать привязку к объявлению
            $ret = new self();
            $ret->setAttributes([
                'advert_id' => $advert->id,
                'file_id' => $file->id,
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
            if ($file instanceof File) {
                $file->getStorage()->delete($file->file_path);
            }

            throw $ex;
        }

        return $ret;
    }

    /**
     * Получить файл
     * @return ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne(File::className(), ['id' => 'file_id']);
    }

    /**
     * Получить превью
     * @return ActiveQuery
     */
    public function getPreview()
    {
        return $this->hasOne(File::className(), ['id' => 'preview_id']);
    }

    /**
     * Получить иконку
     * @return ActiveQuery
     */
    public function getThumbnail()
    {
        return $this->hasOne(File::className(), ['id' => 'thumb_id']);
    }

    /**
     * Получить сжатую картинку
     *
     * @return ActiveQuery
     */
    public function getImage()
    {
        return $this->hasOne(File::className(), ['id' => 'image_id']);
    }

    /**
     * Удаление
     */
    public function delete()
    {
        $ret = 0;

        $transaction = $this->getDb()->beginTransaction();

        try {
            $ret = parent::delete();

            if ($ret) {
                $image = $this->image;
                if ($image instanceof File) {
                    $image->delete();
                }
                $thumb = $this->thumbnail;
                if ($thumb instanceof File) {
                    $thumb->delete();
                }
                $preview = $this->preview;
                if ($preview instanceof File) {
                    $preview->delete();
                }
            }

            $transaction->commit();
        }
        catch (\Exception $ex) {
            $transaction->rollBack();
            throw $ex;
        }

        return $ret;
    }

    /**
     * Получить URL по полю
     *
     * @param string $field название поля: thumbnail, preview, image, file
     * @return string|null
     */
    public function getUrl($field)
    {
        $file = $this->{$field};

        if ($file instanceof File) {
            return $file->getUrl();
        }

        return null;
    }
}