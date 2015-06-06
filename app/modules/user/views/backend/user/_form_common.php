<?php
/* @var $this yii\web\View */
/* @var $model user\models\User */
/* @var $form yii\bootstrap\ActiveForm */

use yii\widgets\DetailView;
use yii\helpers\Html;

if (!$model->isNewRecord) {
    print DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id', 'last_login',
        ]
    ]);
}

print $form->field($model, 'type')->dropDownList($model->getTypesList());
print $form->field($model, 'email')->textInput();
print $form->field($model, 'name')->textInput();

print $form->field($model, 'new_password')->textInput();