<?php
namespace storage\components;

use storage\exception\FileException;
use storage\models\File;
use yii\base\Component;

/**
 * Компонент файлого хранилища.
 * Сохраняет файлы на диск и в БД.
 */
class Storage extends Component
{
    /**
     * @var string символьный код файлового хранилища
     */
    public $code;

    /**
     * @var string путь к папке файлого хранилища
     */
    public $basePath;

    /**
     * @var string url файлого хранилища
     */
    public $baseUrl;

    /**
     * Сохранить файл $file с именем $name на диск.
     * Если не указана переменная $name - берет имя файла из $file.
     * Возвращает модель файла или null.
     *
     * @param string $file абсолютный путь к файлу, который необходимо сохранить
     * @param string $name название сохраняемого файла
     * @param boolean $removeExists удалять существующий файл
     * @return File|null
     * @throws FileException
     */
    public function saveFile($file, $name = '', $removeExists = false)
    {
        if (empty($name)) {
            $name = $file;
        }

        $realName = basename($name);

        $name = $this->getHashFileName($name);
        $path = $this->getPath($name);

        $this->checkDirs($path);

        $ret = File::createFromExists($this, $file, $realName, $name);
        if ($ret instanceof File && $removeExists) {
            unlink($file);
        }
        return $ret;
    }

    /**
     * Удалить файл
     * @param string $name имя файла
     */
    public function delete($name)
    {
        @unlink($this->getPath($name));
    }

    /**
     * Получить url файла
     * @param string $name имя файла
     * @return string
     */
    public function getUrl($name)
    {
        return rtrim($this->baseUrl, '/') . '/' . ltrim($name, '/');
    }

    /**
     * Получить путь к файлу
     * @param string $name имя файла
     * @return string
     */
    public function getPath($name)
    {
        $path = rtrim($this->basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($name, DIRECTORY_SEPARATOR);
        $this->checkDirs($path);
        return $path;
    }

    /**
     * Получить захешированное имя файла
     * @param string $name имя файла
     * @return string
     */
    public function getHashFileName($name)
    {
        $ext = explode('.', $name);
        $ext = !empty($ext) ? array_pop($ext) : '';
        $name = md5($name . uniqid() . time() . rand(0, 1000));
        $name .= !empty($ext) ? '.' . $ext : '';

        $dir = substr($name, 0, 3);

        $name = $dir . DIRECTORY_SEPARATOR . $name;

        return $name;
    }

    /**
     * Проверяет существование директории $path; создает ее, если ее нет
     *
     * @param $path
     */
    protected function checkDirs($path)
    {
        $dir = dirname($path);

        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }

    public function __toString()
    {
        return $this->code;
    }
}