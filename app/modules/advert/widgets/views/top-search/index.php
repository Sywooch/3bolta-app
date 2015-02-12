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

<div class="panel panel-default">
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'id' => 'topSearch',
            'method' => 'get',
            'action' => Url::toRoute(['/advert/advert/search']),
        ]);
        ?>
        <div class="row">
            <div class="col-xs-3 col-sm-3">
                <?=$this->render('_choose_auto', [
                    'form' => $form,
                    'model' => $model,
                ])?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-2 col-sm-2">
                <?=$form->field($model, 'con')->dropDownList(Advert::getConditionDropDownList())?>
            </div>
            <div class="col-xs-3 col-sm-3">
                <?=$form->field($model, 'cat')->dropDownList(Advert::getCategoryDropDownList(true))?>
            </div>
            <div class="col-xs-3 col-sm-5">
                <?=$form->field($model, 'q')->textInput(['maxlength' => Search::MAX_QUERY_LENGTH])?>
            </div>
            <div class="col-xs-2 col-sm-2">
                <label class="control-label">&nbsp;</label>
                <?=Html::submitButton(Yii::t('frontend/advert', 'Search'), ['class' => 'form-control btn btn-primary'])?>
            </div>
        </div>
        <?php $form->end(); ?>
    </div>
</div>
