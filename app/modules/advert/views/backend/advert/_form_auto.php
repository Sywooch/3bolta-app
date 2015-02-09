<?php
use yii\helpers\Html;
use auto\widgets\ChooseAutomobile;

/* @var $this yii\web\View */
/* @var $model advert\models\Category */
/* @var $form yii\bootstrap\ActiveForm */

print ChooseAutomobile::widget([
    'markName' => Html::getInputName($model, 'marks'),
    'modelName' => Html::getInputName($model, 'models'),
    'serieName' => Html::getInputName($model, 'series'),
    'modificationName' => Html::getInputName($model, 'modifications'),
    'markIds' => $model->getMarks(),
    'modelIds' => $model->getModels(),
    'serieIds' => $model->getSeries(),
    'modificationIds' => $model->getModifications(),
]);
