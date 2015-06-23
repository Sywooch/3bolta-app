<?php
/**
 * Главная страница для поиска торговых точек на карте
 */

use partner\forms\TradePointMap;
use yii\jui\AutoComplete;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $searchForm TradePointMap */

$form = ActiveForm::begin();
print $form->field($searchForm, 'name')->textInput();
print $form->field($searchForm, 'specialization')->widget(AutoComplete::className(), [
    'options' => [
        'class' => 'form-control',
    ],
    'clientOptions' => [
        'source' => $searchForm->getSpecializationAutocomplete(),
    ]
]);
ActiveForm::end();