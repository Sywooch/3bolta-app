<?php
/**
 * Форма регистрации
 */

use app\widgets\JS;
use yii\bootstrap\Modal;
use user\models\User;
use app\components\PhoneValidator;
use user\forms\Register;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\widgets\MaskedInput;

/* @var $this \yii\base\View */
/* @var $model \user\forms\Register */
/* @var $registeredUser \user\models\User */
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
        'mask' => PhoneValidator::PHONE_MASK,
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

<?php if ($registeredUser instanceof User) {
    Modal::begin([
        'id' => 'userConfirmationSend',
        'header' => '<h2 class="primary-title"><span class="glyphicon glyphicon-info-sign"></span> ' . Yii::t('frontend/user', 'Success registered') . '</h2>',
        'toggleButton' => false,
    ]);
    ?>
    Вы успешно зарегистрировались!
    <br /><br />
    Для того, чтобы пользоваться полными возможностями сайта требуется активировать ваш e-mail.<br />
    На ваш почтовый ящик <strong><?=Html::encode($registeredUser->email)?></strong>
    выслана ссылка для подтверждения.<br /><br />
    Пожалуйста, пройдите по этой ссылке и вы сможете авторизоваться на сайте.
    <?php
    Modal::end();
    JS::begin();
    ?>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#userConfirmationSend').modal('toggle');
        });
    </script>
    <?php
    JS::end();
}
