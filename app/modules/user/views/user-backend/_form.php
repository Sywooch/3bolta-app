<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Tabs;

/* @var $this yii\web\View */
/* @var $model user\models\User */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="page-form">

    <?php $form = ActiveForm::begin([
        'enableAjaxValidation' => true
    ]); ?>
        <?=Tabs::widget([
            'items' => [
                [
                    'label' => Yii::t('backend/user', 'Common settings'),
                    'active' => true,
                    'content' => $this->render('_form_common', [
                        'form' => $form,
                        'model' => $model,
                    ])
                ],
                [
                    'label' => Yii::t('backend/user', 'User roles'),
                    'active' => false,
                    'content' => $this->render('_form_roles', [
                        'form' => $form,
                        'model' => $model,
                    ])
                ],
            ]
        ])?>

        <div class="form-group">
            <?=Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-success', 'name' => 'save'])?>
            <?=Html::submitButton(Yii::t('backend', 'Apply'), ['class' => 'btn btn-primary', 'name' => 'apply'])?>
        </div>
    <?php ActiveForm::end(); ?>
</div>
