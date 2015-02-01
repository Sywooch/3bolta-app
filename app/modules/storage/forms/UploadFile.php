<?php
namespace app\modules\storage\forms;

use Yii;
use app\modules\storage\components\Storage;
use yii\web\UploadedFile;

/**
 * Форма загрузки файла.
 * Необходимо указывать репозиторий, в который загружаем и сам файл.
 */
class UploadFile extends \yii\base\Model
{
    protected $_storage;
    protected $_file;

    /**
     * Правила валидации
     * @return []
     */
    public function rules()
    {
        return [
            [['storage', 'file'], 'required'],
            [['file'], 'file'],
        ];
    }

    /**
     * Подписи
     * @return []
     */
    public function attributeLabels()
    {
        return [
            'storage' => Yii::t('storage', 'Repository'),
            'file' => Yii::t('storage', 'Upload file'),
        ];
    }

    /**
     * Установка репозитория. Необходимо указать символьный код репозитория.
     * @param string $value
     */
    public function setStorage($value)
    {
        if (is_string($value)) {
            $module = Yii::$app->getModule('storage');
            if ($module->{$value} instanceof Storage) {
                $this->_storage = $module->{$value};
            }
        }
    }

    /**
     * Возвращает репозиторий или null
     * @return app\modules\storage\components\Storage|null
     */
    public function getStorage()
    {
        return $this->_storage;
    }

    /**
     * Получить файл
     * @return UploadedFile|null
     */
    public function getFile()
    {
        return $this->_file;
    }

    /**
     * Установить файл
     * @param UploadedFile $value
     */
    public function setFile($value)
    {
        if ($value instanceof UploadedFile) {
            $this->_file = $value;
        }
    }
}