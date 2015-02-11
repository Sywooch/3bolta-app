<?php
namespace app\components;

use Yii;

/**
 * Базовый контроллер для всех приложений
 */
abstract class Controller extends \yii\web\Controller
{
    public function getViewPath()
    {
        if ($this->module->id == 'backend' || $this->module->id == Yii::$app->id) {
            // для модуля backend все остается по старому
            return parent::getViewPath();
        }
        return $this->module->getViewPath() . DIRECTORY_SEPARATOR . Yii::$app->id . DIRECTORY_SEPARATOR . $this->id;
    }
}