<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model user\forms\Role */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $role yii\rbac\Role */
/* @var $permissions yii\rbac\Permission[] */
?>

<div class="page-form">

    <?php $form = ActiveForm::begin([
        'enableAjaxValidation' => true
    ]); ?>
        <?php if ($model->getIsNewRecord()):?>
            <?=$form->field($model, 'name')->textInput(['maxlength' => 255])?>
        <?php endif;?>

        <?=$form->field($model, 'description')->textInput(['maxlength' => 255])?>

        <?php $items = []; ?>

        <?php foreach ($permissions as $permission):?>
            <?php $items[$permission->name] = Yii::t('rbac', $permission->description); ?>
        <?php endforeach;?>

        <?=$form->field($model, 'permissions')->checkboxList($items);?>

        <div class="form-group">
            <?=Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-success'])?>
        </div>
    <?php ActiveForm::end(); ?>
</div>
