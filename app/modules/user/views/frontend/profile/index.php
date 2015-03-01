<?php
/**
 * Профиль пользователя
 */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

use yii\widgets\MaskedInput;
use app\components\PhoneValidator;

/* @var $this \yii\base\View */
/* @var $changePassword \user\forms\ChangePassword */
/* @var $form \yii\bootstrap\ActiveForm */
/* @var $profile \user\forms\Profile */
?>
<div class="col-sm-1 col-lg-3"></div>
<div class="col-sm-10 col-lg-6 profile">
    <div class="profile-row">
        <h1><?=Yii::t('frontend/user', 'Profile')?></h1>
    </div>

    <a name="profile"></a>
    <div class="profile-row">
        <?php if (Yii::$app->session->getFlash('profile_success_update')):?>
            <div class="alert alert-success">
                <?=Yii::t('frontend/user', 'Profile success updated')?>
            </div>
        <?php endif;?>
        <?php
        $form = ActiveForm::begin([
            'id' => 'profile',
            'enableAjaxValidation' => true,
            'enableClientValidation' => true,
        ]);
        print $form->field($profile, 'name')->textInput();
        print $form->field($profile, 'phone', [
            'errorOptions' => [
                'encode' => false,
            ]
        ])->widget(MaskedInput::className(), [
            'mask' => PhoneValidator::PHONE_MASK,
        ]);
        print Html::submitButton(Yii::t('frontend/user', 'Update profile'), [
            'class' => 'btn btn-primary',
        ]);
        ActiveForm::end();
        ?>
    </div>

    <a name="change-email"></a>
    <div class="profile-row">
        <?php if ($email = Yii::$app->session->getFlash('email_change_message')):?>
            <div class="alert alert-info">
                На ваш e-mail <strong><?=$email?></strong> отправлена ссылка для подтверждения. Пожалуйста, пройдите по этой ссылке и ваш e-mail профиля будет изменен.
            </div>
        <?php elseif ($error = Yii::$app->session->getFlash('email_change_error')):?>
            <div class="alert alert-warning">
                <?=Html::encode($error)?>
            </div>
        <?php elseif (Yii::$app->session->getFlash('email_change_success')):?>
            <div class="alert alert-success">
                <?=Yii::t('frontend/user', 'E-mail success changed')?>
            </div>
        <?php endif;?>
        <?php
        $form = ActiveForm::begin([
            'id' => 'change-email',
            'enableAjaxValidation' => true,
            'enableClientValidation' => true,
        ]);
        ?>
        <div class="form-group">
            <label class="control-label" for="profile-email"><?=Yii::t('frontend/user', 'Current e-mail')?></label>
            <input disabled="disabled" readonly="readonly" type="text" class="form-control" value="<?=Html::encode($profile->getMaskedEmail())?>">
        </div>
        <?php
        print $form->field($profile, 'email')->textInput([
            'value' => '',
        ]);
        print Html::submitButton(Yii::t('frontend/user', 'Change e-mail'), [
            'class' => 'btn btn-primary',
        ]);
        ActiveForm::end();
        ?>
    </div>

    <a name="change-password"></a>
    <div class="profile-row">
        <?php if (Yii::$app->session->getFlash('password_success_changed')):?>
            <div class="alert alert-success">
                <?=Yii::t('frontend/user', 'Password success changed')?>
            </div>
        <?php endif;?>

        <?php
        $form = ActiveForm::begin([
            'id' => 'change-password',
            'enableAjaxValidation' => true,
            'enableClientValidation' => true,
        ]);
        print $form->field($changePassword, 'password')->passwordInput();
        print $form->field($changePassword, 'password_confirmation')->passwordInput();
        print Html::submitButton(Yii::t('frontend/user', 'Change password'), [
            'class' => 'btn btn-primary',
        ]);
        ActiveForm::end();
        ?>
    </div>
</div>
<div class="col-sm-1 col-lg-3"></div>