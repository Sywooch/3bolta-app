<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\widgets\DetailView;
use advert\models\Category;
use yii\bootstrap\Tabs;

/* @var $this yii\web\View */
/* @var $model advert\models\Advert */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="page-form">

    <?php
    $form = ActiveForm::begin([
        'enableAjaxValidation' => true
    ]);
        print Tabs::widget([
            'items' => [
                [
                    'label' => Yii::t('backend/advert', 'Common settings'),
                    'active' => true,
                    'content' => $this->render('_form_common', [
                        'form' => $form,
                        'model' => $model,
                    ])
                ],
                [
                    'label' => Yii::t('backend/advert', 'Auto'),
                    'active' => false,
                    'content' => $this->render('_form_auto', [
                        'form' => $form,
                        'model' => $model,
                    ])
                ],
            ]
        ]);
    ?>
        <div class="form-group">
            <?=Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-success', 'name' => 'save'])?>
            <?=Html::submitButton(Yii::t('backend', 'Apply'), ['class' => 'btn btn-primary', 'name' => 'apply'])?>
        </div>
    <?php ActiveForm::end(); ?>
</div>
