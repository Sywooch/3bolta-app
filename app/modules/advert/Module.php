<?php

namespace advert;

use Yii;

/**
 * Модуль объявлений
 */
class Module extends \app\components\BaseModule
{
    public function init()
    {
        parent::init();

        Yii::configure($this, include __DIR__ . '/config.php');
    }
}