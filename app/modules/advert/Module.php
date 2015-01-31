<?php

namespace app\modules\advert;

use Yii;

/**
 * Модуль объявлений
 */
class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();

        Yii::configure($this, include __DIR__ . '/config.php');
    }
}