<?php
/**
 * Форма изменения пароля
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this \yii\base\View */
/* @var $model \user\forms\ChangePassword */
?>
<div class="col-sm-1 col-lg-3"></div>
<div class="col-sm-10 col-lg-6">
    <h1><?=Yii::t('frontend/user', 'Change password')?></h1>

    <div class="block-info block-info-primary">
        Введите новый пароль и его подтверждение.<br />
        После этого вы сможете успешно войти в личный кабинет с новым паролем.
    </div>

    <?php
    $form = ActiveForm::begin([
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
    ]);
    print $form->field($model, 'password')->passwordInput();
    print $form->field($model, 'password_confirmation')->passwordInput();
    print Html::submitButton(Yii::t('frontend/user', 'Change password'), ['class' => 'btn btn-primary']);
    ActiveForm::end();
    ?>
</div>
<div class="col-sm-1 col-lg-3"></div>