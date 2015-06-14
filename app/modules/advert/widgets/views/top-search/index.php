<?php
/**
 * Поиск в верхней части
 */

use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use advert\models\Advert;
use yii\helpers\Html;
use advert\forms\Search;

/* @var $model advert\forms\Search */
/* @var $this yii\base\View */
?>

<div class="panel panel-default top-search" id="topSearchWrap">
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'id' => 'topSearch',
            'method' => 'get',
            'action' => Url::toRoute(['/advert/catalog/search']),
            'enableAjaxValidation' => false,
            'enableClientValidation' => false,
            'fieldConfig' => [
                'template' => '{input}{icon}',
                'parts' => ['{icon}' => ''],
            ]
        ]);
        ?>
        <div class="row">
            <div class="col-xs-12">
                <?=$this->render('_choose_auto', [
                    'form' => $form,
                    'model' => $model,
                ])?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 col-xs-12">
                <?=$form->field($model, 'con', [
                    'parts' => ['{icon}' => '<span class="form-control-icon glyphicon glyphicon-wrench"></span>'],
                    'inputOptions' => [
                        'class' => 'form-control form-control-with-icon',
                        'placeholder' => Yii::t('frontend/advert', 'Condition'),
                    ],
                ])->dropDownList(Advert::getConditionDropDownList(true))?>
            </div>
            <div class="col-md-4 col-xs-12">
                <?=$form->field($model, 'cat', [
                    'parts' => ['{icon}' => '<span class="form-control-icon glyphicon glyphicon-tag"></span>'],
                    'inputOptions' => [
                        'class' => 'form-control form-control-with-icon',
                        'placeholder' => Yii::t('frontend/advert', 'Category'),
                    ],
                ])->dropDownList(Advert::getCategoryDropDownList(true))?>
            </div>
            <div class="col-md-4 col-xs-12">
                <?=$form->field($model, 'q', [
                    'parts' => ['{icon}' => '<span class="form-control-icon glyphicon glyphicon-search"></span>'],
                    'inputOptions' => [
                        'class' => 'form-control form-control-with-icon',
                        'placeholder' => Yii::t('frontend/advert', 'Name'),
                    ]
                ])->textInput(['maxlength' => Search::MAX_QUERY_LENGTH])?>
            </div>
            <div class="col-md-2 col-xs-12">
                <?=Html::submitButton(Yii::t('frontend/advert', 'Search'), ['class' => 'form-control btn btn-primary'])?>
            </div>
        </div>
        <?php $form->end(); ?>
    </div>
</div>
