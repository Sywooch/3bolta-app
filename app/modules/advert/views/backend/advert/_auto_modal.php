<?php
/**
 * Список моделей автомобиля (выбор)
 */
use auto\widgets\ChooseAutomobile;
use yii\helpers\Html;

/* @var $form yii\bootstrap\ActiveForm */
/* @var $model advert\models\Advert */

print ChooseAutomobile::widget([
    'markName' => Html::getInputName($model, 'marks') . '[]',
    'modelName' => Html::getInputName($model, 'models') . '[]',
    'serieName' => Html::getInputName($model, 'series') . '[]',
    'modificationName' => Html::getInputName($model, 'modifications') . '[]',
    'markIds' => $model->getMarks(),
    'modelIds' => $model->getModels(),
    'serieIds' => $model->getSeries(),
    'modificationIds' => $model->getModifications(),
]);