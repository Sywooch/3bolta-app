<?php

namespace geo;

use Yii;

/**
 * Модуль для работы с геоданными, такими как регион и местоположения пользователя
 */
class Module extends \app\components\Module
{
    public function init()
    {
        parent::init();

        Yii::configure($this, include __DIR__ . '/config.php');
    }
}