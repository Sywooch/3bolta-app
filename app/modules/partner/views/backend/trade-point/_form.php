<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\widgets\DetailView;
use yii\widgets\MaskedInput;
use app\components\PhoneValidator;

/* @var $this yii\web\View */
/* @var $model partner\models\TradePoint */
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
                    [
                        'attribute' => 'partner_id',
                        'value' => $model->partner instanceof \partner\models\Partner ? $model->partner->name : '',
                    ]
                ]
            ]);
        }
        print $form->field($model, 'partner_id')->textInput();
        print $form->field($model, 'latitude')->textInput();
        print $form->field($model, 'longitude')->textInput();
        print $form->field($model, 'address')->textInput();
        print $form->field($model, 'phone', [
            'errorOptions' => [
                'encode' => false,
            ]
        ])->widget(MaskedInput::className(), [
            'mask' => PhoneValidator::PHONE_MASK,
        ])
        ?>
        <div class="form-group">
            <?=Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-success', 'name' => 'save'])?>
            <?=Html::submitButton(Yii::t('backend', 'Apply'), ['class' => 'btn btn-primary', 'name' => 'apply'])?>
        </div>
    <?php ActiveForm::end(); ?>
</div>