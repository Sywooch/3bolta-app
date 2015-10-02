<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\widgets\DetailView;
use yii\widgets\MaskedInput;
use app\components\PhoneValidator;
use kalyabin\maplocation\SelectMapLocationWidget;

/* @var $this yii\web\View */
/* @var $model partner\models\TradePoint */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="page-form">
    <?php
    $form = ActiveForm::begin([
        'enableAjaxValidation' => true,
        'enableClientValidation' => false,
    ]);
        if (!$model->isNewRecord) {
            print DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id', 'created', 'edited',
                    [
                        'attribute' => 'partner_id',
                        'value' => $model->partner instanceof \partner\models\Partner ? $model->partner->name : '',
                    ]
                ]
            ]);
        }
        print $form->field($model, 'partner_id')->textInput();
        print $form->field($model, 'address')->widget(SelectMapLocationWidget::className(), [
            'wrapperOptions'        => [
                'class' => 'form-control',
            ],
            'attributeLatitude' => 'latitude',
            'attributeLongitude' => 'longitude'
        ]);
        print $form->field($model, 'phone', [
            'errorOptions' => [
                'encode' => false,
            ]
        ])->widget(MaskedInput::className(), [
            'mask' => PhoneValidator::PHONE_MASK,
        ]);
        print $form->field($model, 'phone_from_profile')->checkbox();
        ?>
        <div class="form-group">
            <?=Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-success', 'name' => 'save'])?>
            <?=Html::submitButton(Yii::t('backend', 'Apply'), ['class' => 'btn btn-primary', 'name' => 'apply'])?>
        </div>
    <?php ActiveForm::end(); ?>
</div>
