<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use advert\models\Advert;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $model advert\models\Category */
/* @var $form yii\bootstrap\ActiveForm */
print $this->render('_auto_modal', [
    'form' => $form,
    'model' => $model,
]);
