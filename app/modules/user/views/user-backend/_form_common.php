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

print $form->field($model, 'email')->textInput();
print $form->field($model, 'first_name')->textInput();
print $form->field($model, 'last_name')->textInput();
print $form->field($model, 'second_name')->textInput();

print $form->field($model, 'new_password')->textInput();