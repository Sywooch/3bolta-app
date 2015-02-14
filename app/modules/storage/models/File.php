<?php
namespace storage\models;

use Yii;
use yii\base\Exception;
use storage\components\Storage;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

class File extends \app\components\ActiveRecord
{
    /**
     * Название таблицы
     * @return string
     */
    public static function tableName()
    {
        return '{{%storage}}';
    }

    /**
     * Правила валидации
     * @return []
     */
    public function rules()
    {
        return [
            [['repository', 'real_name', 'file_path', 'mime_type', 'uploader_addr', 'size'], 'required'],
            [['uploader_addr'], 'match', 'pattern' => '#^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$#'],
            [['is_image'], 'boolean'],
            [['width', 'height'], 'integer', 'skipOnEmpty' => true],
            [['size'], 'integer'],
        ];
    }

    /**
     * Подписи
     * @return []
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'repository' => Yii::t('storage', 'Repository'),
            'size' => Yii::t('storage', 'Size'),
            'real_name' => Yii::t('storage', 'Real file name'),
            'file_path' => Yii::t('storage', 'File path'),
            'mime_type' => Yii::t('storage', 'Mime type'),
            'uploader_addr' => Yii::t('storage', 'Uploader IP address'),
            'is_image' => Yii::t('storage', 'The file is image'),
            'width' => Yii::t('storage', 'Image width'),
            'height' => Yii::t('storage', 'Image height'),
        ];
    }

    /**
     * Определить и установить IP-адрес с которого загружаем
     * @param \storage\models\File $file
     */
    protected static function setUploadedAddr(File $file)
    {
        $userAddress = \Yii::$app->request->getUserIP();
        if (empty($userAddress)) {
            $userAddress = '127.0.0.1';
        }
        $file->setAttribute('uploader_addr', $userAddress);
    }

    /**
     * Получить размеры изображения, если файл является изображением.
     * Иначе - устанавливает только mime.
     *
     * @param string $filePath абсолютный путь к файл
     * @param \storage\models\File $file файл, в который сохраняем изображение
     */
    protected static function setImageSize($existsFile, File $file)
    {
        try {
            // определить тип
            $info = getimagesize($existsFile);
            if (!empty($info[0]) && !empty($info[1])) {
                // в случае изображения сохраняем размеры
                $file->setAttributes([
                    'is_image' => true,
                    'width' => $info[0],
                    'height' => $info[1],
                ]);
            }
        } catch (Exception $ex) { }
    }

    /**
     * Скопировать файл $existsFile в $realPath и запомнить его в $file.
     * В случае ошибки генерирует Exception.
     *
     * @param \storage\models\File $file
     * @param string $existsFile путь к файлу, который необходимо скопировать
     * @param string $realPath путь к вновь создаваемому файлу
     * @throws yii\base\Exception
     */
    protected static function createFile(File $file, $existsFile, $realPath)
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            if (!$file->save()) {
                throw new Exception();
            }

            if (!@copy($existsFile, $realPath)) {
                throw new Exception();
            }

            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollBack();

            // удаляем файл
            if (is_file($realPath)) {
                @unlink($realPath);
            }

            throw $ex;
        }
    }

    public function beforeSave($insert)
    {
        if (is_null($this->created)) {
            $this->created = date('Y-m-d H:i:s');
        }
        return parent::beforeSave($insert);
    }

    /**
     * Загрузить файл.
     *
     * @param Storage $repository
     * @param UploadedFile $uploadedFile
     * @return File|null
     */
    public static function uploadFile(Storage $repository, UploadedFile $uploadedFile)
    {
        $ret = null;

        $file = new self();
        $file->setAttributes([
            'repository' => $repository->code,
            'real_name' => $uploadedFile->name,
            'file_path' => $repository->getHashFileName($uploadedFile->name),
            'size' => $uploadedFile->size,
        ]);

        // абсолютный путь к новому файлу
        $realPath = $repository->getPath($file->file_path);

        // установить размер изображения
        self::setImageSize($uploadedFile->tempName, $file);

        // установить тип
        $file->setAttribute('mime_type', FileHelper::getMimeType($uploadedFile->tempName));

        // установить IP-адрес
        self::setUploadedAddr($file);

        try {
            self::createFile($file, $uploadedFile->tempName, $realPath);

            if ($file->id) {
                $ret = $file;
            }
        } catch (Exception $ex) {
            $ret = null;
        }

        return $ret;
    }

    /**
     * Создать файл из существующего.
     *
     * @param \storage\components\Storage $repository
     * @param string $existsFile
     * @param string $realName
     * @param string $path
     */
    public static function createFromExists(Storage $repository, $existsFile, $realName, $path)
    {
        $ret = null;

        $file = new self();
        $file->setAttributes([
            'repository' => $repository->code,
            'real_name' => $realName,
            'file_path' => $path,
            'size' => @filesize($existsFile),
        ]);

        // получить размеры, если это изображение
        self::setImageSize($existsFile, $file);

        // установить тип
        $file->setAttribute('mime_type', FileHelper::getMimeType($existsFile));

        // определить адрес загрузки
        self::setUploadedAddr($file);

        // реальный путь к файлу
        $realPath = $repository->getPath($path);

        try {
            self::createFile($file, $existsFile, $realPath);

            if ($file->id) {
                $ret = $file;
            }
        } catch (Exception $ex) {
            $ret = null;
        }

        return $ret;
    }

    /**
     * Возвращает репозиторий
     * @return \storage\components\Storage
     */
    public function getStorage()
    {
        $ret = null;

        if (\Yii::$app->getModule('storage')->{$this->repository} instanceof Storage) {
            $ret = \Yii::$app->getModule('storage')->{$this->repository};
        }

        return $ret;
    }

    /**
     * Возвращает url файла
     * @return string
     */
    public function getUrl()
    {
        $ret = '';

        if ($repository = $this->getStorage()) {
            $ret = $repository->getUrl($this->file_path);
        }

        return $ret;
    }

    /**
     * Возвращает разрешение файла
     * @return string
     */
    public function getExtension()
    {
        $info = pathinfo($this->getPath());
        return !empty($info['extension']) ? $info['extension'] : null;
    }

    /**
     * Возвращает полный путь к файлу
     * @return string
     */
    public function getPath()
    {
        $ret = '';

        if ($repository = $this->getStorage()) {
            $ret = $repository->getPath($this->file_path);
        }

        return $ret;
    }

    /**
     * Удаление файла
     */
    public function delete()
    {
        $storage = $this->getStorage();

        if (parent::delete()) {
            $storage->delete($this->file_path);
        }
    }
}