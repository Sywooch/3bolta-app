<?php
use yii\widgets\DetailView;
use advert\models\Advert;
use yii\widgets\MaskedInput;
use app\components\PhoneValidator;

/* @var $this yii\web\View */
/* @var $model advert\models\Advert */
/* @var $form yii\bootstrap\ActiveForm */
if (!$model->isNewRecord) {
    DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id', 'created', 'edited',
            'published'
        ]
    ]);
}
print $form->field($model, 'active')->checkbox();
print $form->field($model, 'advert_name')->textInput();
print $form->field($model, 'price')->textInput();
print $form->field($model, 'user_name')->textInput();
print $form->field($model, 'user_phone', [
    'errorOptions' => [
        'encode' => false,
    ]
])->widget(MaskedInput::className(), [
    'mask' => PhoneValidator::PHONE_MASK,
]);
print $form->field($model, 'user_email')->textInput();
print $form->field($model, 'condition_id')->dropDownList(Advert::getConditionDropDownList());
print $form->field($model, 'category_id')->dropDownList(Advert::getCategoryDropDownList());
print $form->field($model, 'description')->textarea();
