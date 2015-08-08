<?php
/**
 * Форма создания/редактирования торговой точки
 */

use app\components\PhoneValidator;
use app\widgets\SelectMapLocation;
use partner\assets\TradePointFormAssets;
use partner\forms\TradePoint;
use user\models\User;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\MaskedInput;

TradePointFormAssets::register($this);

$user = Yii::$app->user->getIdentity();

$phoneFromProfileLabel = $user instanceof User ?
    Yii::t('frontend/partner', 'Use phone from <a href="{link}">profile</a>: {phone}', [
        'link' => Url::toRoute(['/user/profile/index']),
        'phone' => $user->phone,
    ]) :
    Yii::t('frontend/partner', 'Use phone from <a href="{link}">profile</a>', [
        'link' => Url::toRoute(['/user/profile/index']),
    ]);

/* @var $model TradePoint */
/* @var $this View */
print Html::tag('div', Yii::t('frontend/partner', 'Can\'t send request: system error'), [
    'class' => 'alert alert-danger',
    'style' => 'display: none;',
    'class' => 'trade-point-error',
]);
/* @var $form ActiveForm */
$form = ActiveForm::begin([
    'id' => 'trade-point-form',
    'enableClientValidation' => false,
    'enableAjaxValidation' => true,
    'validateOnChange' => false,
    'validateOnSubmit' => true,
]);
print $form->field($model, 'region_id')->dropDownList(TradePoint::getRegionsDropDownList(), [
    'class' => 'form-control js-select-region-dropdown',
    'data-live-search' => 'true',
]);
print $form->field($model, 'address')->widget(SelectMapLocation::className(), [
    'wrapperOptions' => [
        'id' => 'tradePointLocation' . $model->getExistsId() . uniqid(),
    ],
    'attributeLatitude' => 'latitude',
    'attributeLongitude' => 'longitude',
]);
print Html::beginTag('div', [
    'class' => 'js-trade-point-phone',
    'style' => $model->phone_from_profile ? 'display:none;' : null
]);
    print $form->field($model, 'phone')->widget(MaskedInput::className(), [
        'mask' => PhoneValidator::PHONE_MASK,
    ]);
print Html::endTag('div');
print $form->field($model, 'phone_from_profile', [
    'labelOptions' => [
        'label' => $phoneFromProfileLabel,
    ],
])->checkbox();
print Html::submitButton(
    $model->getExistsId() ?
        Yii::t('frontend/partner', 'Update') :
        Yii::t('frontend/partner', 'Create'),
    [
    'class' => 'btn btn-primary'
    ]
);
ActiveForm::end();