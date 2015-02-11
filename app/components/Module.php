<?php
namespace app\components;

use Yii;

/**
 * Базовый класс для всех модулей в проекте
 */
abstract class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();

        // в зависимости от типа приложения подключаем тот или иной неймспейс для контроллеров
        if ($this->id != 'backend' && $this->controllerNamespace !== null && !empty(Yii::$app->id)) {
            $this->controllerNamespace .= '\\' . Yii::$app->id;
        }
    }
}