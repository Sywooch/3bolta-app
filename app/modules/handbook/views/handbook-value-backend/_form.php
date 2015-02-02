<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\handbook\models\HandbookValue */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="page-form">

    <?php
    $form = ActiveForm::begin([
        'enableAjaxValidation' => true
    ]);
        print DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                'handbook_code' => [
                    'attribute' => 'handbook_code',
                    'value' => $model->getHandbook()->name,
                ]
            ]
        ]);
        print $form->field($model, 'sort')->textInput();
        print $form->field($model, 'name')->textInput();
    ?>
        <div class="form-group">
            <?=Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-success', 'name' => 'save'])?>
            <?=Html::submitButton(Yii::t('backend', 'Apply'), ['class' => 'btn btn-primary', 'name' => 'apply'])?>
        </div>
    <?php ActiveForm::end(); ?>
</div>
