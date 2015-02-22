<?php
/**
 * Форма регистрации
 */

use app\components\PhoneValidator;
use user\forms\Register;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\widgets\MaskedInput;

/* @var $this \yii\base\View */
/* @var $model \user\forms\Register */
?>
<div class="col-sm-1 col-lg-3"></div>
<div class="col-sm-10 col-lg-6">
    <h1><?=Yii::t('frontend/user', 'Registration')?></h1>

    <?php
    $form = ActiveForm::begin([
        'enableAjaxValidation' => true,
    ]);
    print $form->field($model, 'name')->textInput([
            'maxlength' => Register::MAX_NAME_LENGTH,
        ]);
    print $form->field($model, 'email')->textInput([
            'maxlength' => Register::MAX_NAME_LENGTH,
        ]);
    print $form->field($model, 'phone')->widget(MaskedInput::className(), [
        'mask' => '+7 (999) 999-99-99'
    ]);
    print $form->field($model, 'password')->passwordInput();
    print $form->field($model, 'password_confirmation')->passwordInput();
    print Html::submitButton(Yii::t('frontend/user', 'Register'), [
        'class' => 'btn btn-primary',
    ]);
    ActiveForm::end();
    ?>
</div>
<div class="col-sm-1 col-lg-3"></div>
