<?php
namespace user;

use Yii;

/**
 * Модуль пользователей
 */
class Module extends \app\components\Module
{
    public function init()
    {
        Yii::configure($this, include __DIR__ . '/config.php');
        parent::init();
    }

    /**
     * Вернуть захешированный пароль
     * @param string $password
     * @return string
     */
    public function getPasswordHash($password)
    {
        return \Yii::$app->security->generatePasswordHash($password);
    }
}