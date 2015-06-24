<?php
/**
 * Главная страница для поиска торговых точек на карте
 */

use partner\assets\TradePointMapAssets;
use partner\forms\TradePointMap;
use yii\helpers\Html;
use yii\jui\AutoComplete;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $searchForm TradePointMap */

TradePointMapAssets::register($this);
?>
<div class="top-search">
    <div class="row">
        <?php $form = ActiveForm::begin([
            'action' => ['search'],
            'options' => [
                'class' => 'js-trade-point-map-form',
            ],
            'enableClientValidation' => false,
            'enableAjaxValidation' => false,
        ]); ?>
            <div class="col-md-4 col-xs-12">
                <?=$form->field($searchForm, 'name')->textInput()?>
            </div>
            <div class="col-md-4 col-xs-12">
                <?=$form->field($searchForm, 'specialization')->widget(AutoComplete::className(), [
                    'options' => [
                        'class' => 'form-control',
                    ],
                    'clientOptions' => [
                        'source' => $searchForm->getSpecializationAutocomplete(),
                    ]
                ])?>
            </div>
            <div class="col-md-4 col-xs-12">
                <?=$form->field($searchForm, 'address')->textInput([
                    'class' => 'form-control js-trade-point-address',
                ])?>
            </div>
            <?=Html::activeHiddenInput($searchForm, 'coordinates', [
                'class' => 'js-trade-point-map-coordinates'
            ])?>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<div class="trade-point-map no-content-margin js-trade-point-map"></div>
