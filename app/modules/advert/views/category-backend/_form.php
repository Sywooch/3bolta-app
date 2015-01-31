<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\widgets\DetailView;
use app\modules\advert\models\Category;

/* @var $this yii\web\View */
/* @var $model app\modules\advert\models\Category */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="page-form">

    <?php
    $form = ActiveForm::begin([
        'enableAjaxValidation' => true
    ]);

    if (!$model->isNewRecord) {
        print DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id'
            ]
        ]);
    }

    print $form->field($model, 'name')->textInput();
    print $form->field($model, 'sort')->textInput();
    print $form->field($model, 'parent_id')->dropDownList(Category::getParentsList($model->id));
    ?>
        <div class="form-group">
            <?=Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-success', 'name' => 'save'])?>
            <?=Html::submitButton(Yii::t('backend', 'Apply'), ['class' => 'btn btn-primary', 'name' => 'apply'])?>
        </div>
    <?php ActiveForm::end(); ?>
</div>
