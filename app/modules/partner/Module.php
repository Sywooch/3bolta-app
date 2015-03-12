<?php
namespace partner;

use Yii;

/**
 * Модуль партнеров
 */
class Module extends \app\components\Module
{
    public function init()
    {
        Yii::configure($this, include __DIR__ . '/config.php');
        parent::init();
    }
}