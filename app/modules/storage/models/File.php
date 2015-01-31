<?php
namespace app\modules\storage\models;

use Yii;
use yii\base\Exception;

class File extends \yii\db\ActiveRecord
{
    /**
     * Название таблицы
     * @return string
     */
    public function tableName()
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
            [['uploader_addr'], 'match', 'pattern' => '^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$'],
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
     * Создать файл из существующего.
     *
     * @param \app\modules\storage\components\Storage $repository
     * @param string $existsFile
     * @param string $realName
     * @param string $path
     */
    public static function createFromExists($repository, $existsFile, $realName, $path)
    {
        $ret = null;

        $file = new self();
        $file->setAttributes([
            'repository' => $repository->code,
            'real_name' => $realName,
            'file_path' => $path,
            'size' => @filesize($existsFile),
        ]);

        try {
            // определить тип
            $info = getimagesize($existsFile);
            if (!empty($info['mime']) &&
                in_array($info['mime'], ['image/gif', 'image/jpeg', 'image/png', 'image/bmp']) &&
                !empty($info['width']) && !empty($info['height'])) {
                // в случае изображения сохраняем размеры
                $file->setAttributes([
                    'is_image' => true,
                    'width' => $info['width'],
                    'height' => $info['height'],
                ]);
            }
            if (!empty($info['mime'])) {
                $file->setAttribute('mime_type', $info['mime']);
            }
        } catch (Exception $ex) { }

        // определить адрес загрузки
        $userAddress = \Yii::$app->request->getUserIP();
        if (empty($userAddress)) {
            $userAddress = '127.0.0.1';
        }
        $file->setAttribute('uploader_addr', $userAddress);

        // реальный путь к файлу
        $realPath = $repository->getPath($path);

        $transaction = $this->getDb()->beginTransaction();

        try {
            if (!$file->save()) {
                throw new Exception();
            }

            if (!@copy($existsFile, $realPath)) {
                throw new Exception();
            }

            $transaction->commit();

            $ret = $file;
        } catch (Exception $ex) {
            $transaction->rollBack();

            // удаляем файл
            if (is_file($realPath)) {
                @unlink($realPath);
            }
        }

        return $ret;
    }

    /**
     * Возвращает репозиторий
     * @return \app\modules\storage\components\Storage
     */
    public function getStorage()
    {
        $ret = null;

        if (isset(\Yii::$app->getModule('storage')->{$repository})) {
            $ret = \Yii::$app->getModule('storage')->{$repository};
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
     * Возвращает полный путь к файлу
     * @return string
     */
    public function getPath()
    {
        $ret = '';

        if ($repository = $this->getStorage()) {
            $ret = $repository->getPath($this->file_name);
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