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
            'fieldConfig' => [
                'template' => '{input}{icon}',
                'parts' => ['{icon}' => ''],
            ]
        ]); ?>
            <div class="col-md-4 col-xs-12">
                <?=$form->field($searchForm, 'name', [
                    'parts' => ['{icon}' => '<span class="form-control-icon icon-tag"></span>'],
                    'inputOptions' => [
                        'class' => 'form-control form-control-with-icon',
                        'placeholder' => $searchForm->getAttributeLabel('name'),
                    ],
                ])->textInput()?>
            </div>
            <div class="col-md-4 col-xs-12">
                <?=$form->field($searchForm, 'specialization', [
                    'parts' => ['{icon}' => '<span class="form-control-icon icon-cab"></span>'],
                    'inputOptions' => [
                        'class' => 'form-control form-control-with-icon',
                        'placeholder' => $searchForm->getAttributeLabel('specialization'),
                    ],
                ])->textInput()?>
            </div>
            <div class="col-md-4 col-xs-12">
                <?=$form->field($searchForm, 'address', [
                    'parts' => ['{icon}' => '<span class="form-control-icon icon-location"></span>'],
                    'inputOptions' => [
                        'class' => 'form-control form-control-with-icon js-trade-point-address',
                        'placeholder' => $searchForm->getAttributeLabel('address'),
                    ],
                ])->textInput()?>
            </div>
            <?=Html::activeHiddenInput($searchForm, 'coordinates', [
                'class' => 'js-trade-point-map-coordinates'
            ])?>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<div class="trade-point-map no-content-margin js-trade-point-map"></div>
