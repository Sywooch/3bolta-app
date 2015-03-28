<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model partner\models\Partner */
/* @var $form yii\bootstrap\ActiveForm */
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
        ?>
        <div class="form-group">
            <?=Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-success', 'name' => 'save'])?>
            <?=Html::submitButton(Yii::t('backend', 'Apply'), ['class' => 'btn btn-primary', 'name' => 'apply'])?>
        </div>
    <?php ActiveForm::end(); ?>
</div>