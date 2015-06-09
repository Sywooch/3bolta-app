<?php

use app\widgets\MagicSuggestDefaults;
use auto\models\Mark;
use partner\models\Partner;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Partner */
/* @var $form ActiveForm */
?>

<div class="page-form">

    <?php
    $form = ActiveForm::begin([
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
    ]);
        if (!$model->isNewRecord) {
            print DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id', 'created', 'edited',
                ]
            ]);
        }
        print $form->field($model, 'company_type')->dropDownList($model->getCompanyTypes());
        print $form->field($model, 'name')->textInput();
        print $form->field($model, 'user_id')->textInput();
        print $form->field($model, 'mark')->widget(MagicSuggestDefaults::className(), [
            'items' => ArrayHelper::map(Mark::find()->all(), 'id', function($data) {
                return ['id' => $data->id, 'name' => $data->full_name];
            }),
            'clientOptions' => [
                'editable' => true,
                'expandOnFocus' => true,
                'maxSelection' => 5,
                'maxSelectionRenderer' => '',
                'maxEntryRenderer' => '',
                'minCharsRenderer' => '',
                'value' => $model->getMarkArray(),
            ]
        ]);
        ?>
        <div class="form-group">
            <?=Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-success', 'name' => 'save'])?>
            <?=Html::submitButton(Yii::t('backend', 'Apply'), ['class' => 'btn btn-primary', 'name' => 'apply'])?>
        </div>
    <?php ActiveForm::end(); ?>
</div>