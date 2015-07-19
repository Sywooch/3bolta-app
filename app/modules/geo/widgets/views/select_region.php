<?php
/**
 * Модальное окно выбора региона
 */

/* @var $this View */
/* @var $selectRegion SelectRegion */

use app\widgets\Modal;
use geo\forms\SelectRegion;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

Modal::begin([
    'id' => 'selectRegionModal',
    'title' => Yii::t('frontend/geo', 'Select your region...'),
]);
    $form = ActiveForm::begin([
        'options' => ['class' => 'js-select-region-form'],
        'action' => Url::toRoute(['/geo/geo/select-region']),
        'enableClientValidation' => false,
        'enableAjaxValidation' => false,
    ]);
        print $form->field($selectRegion, 'regionId', [
            'template' => '{input}{hint}{error}',
        ])->dropDownList($selectRegion->getRegionDropDown(), [
            'class' => 'form-control js-select-region-dropdown',
            'data-live-search' => 'true',
        ]);
        print Html::submitButton(Yii::t('frontend/geo', 'Select'), [
            'class' => 'btn btn-primary',
        ]);
    ActiveForm::end();
Modal::end();