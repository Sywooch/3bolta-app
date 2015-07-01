<?php
namespace app\components;

use Yii;

/**
 * Контроллер для демонов на php.
 * В папку runtime демон складывает pid-файл с идентификатором текущего процесса.
 * При следующей загрузке, если процесс с текущим pid уже существует - прекращает свою работу, иначе - заново сохраняет pid и продолжает свою работу.
 */
abstract class DaemonController extends \yii\console\Controller
{
    /**
     * @var boolean режим дебаггинга
     */
    protected $_debug = false;

    /**
     * Дебаггинг
     *
     * @param boolean $string
     * @return null
     */
    public function stdout($string)
    {
        if ($this->_debug) {
            return parent::stdout($string);
        }
        return;
    }

    /**
     * Проверка предыдущего процесса, запущенного этой командой.
     * Если процесс уже запущен - возвращает false, если процесс еще не запущен, запоминает
     * идентификатор текущего процесса и возвращает true.
     *
     * @param mixed $action
     * @return boolean
     */
    public function beforeAction($action)
    {
        $class = preg_replace('/\\\\/', "_", get_called_class());
        $pidFile = Yii::getAlias('@runtime/' . $class . '.pid');

        if (is_file($pidFile)) {
            // получить pid существующего процесса
            $pid = (int) file_get_contents($pidFile);
            if ($pid > 0) {
                // по pidу узнаем, не умер ли еще процесс
                $results = [];
                $command = 'ps --pid ' . $pid;
                exec($command, $results);
                if (!empty($results[1])) {
                    // если предыдущий процесс не умер возвращаем false
                    parent::stdout('Process already exists' . "\n");
                    return false;
                }
            }
        }

        // предыдущий процесс либо умер, либо не был еще создан
        // записываем в файл $pidFile идентификатор текущего процесса и возвращаем true
        return file_put_contents($pidFile, getmypid()) && parent::beforeAction($action);
    }
}