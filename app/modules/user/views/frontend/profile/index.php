<?php
/**
 * Профиль пользователя
 */

use user\forms\ChangePassword;
use user\forms\Profile;
use user\forms\Register;
use user\models\User;
use yii\base\View;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this View */
/* @var $changePassword ChangePassword */
/* @var $form ActiveForm */
/* @var $profile Profile */
/* @var $user User */
?>
<div class="col-md-4 col-sm-12"></div>
<div class="col-md-5 col-sm-12 profile">
    <a name="profile"></a>
    <div class="profile-row col-md-12">
        <h2><?=Yii::t('frontend/user', 'Change contact name')?></h2>
        <?php if (Yii::$app->session->getFlash('profile_success_update')):?>
            <div class="col-md-12 alert alert-success">
                <?=Yii::t('frontend/user', 'Profile success updated')?>
            </div>
        <?php elseif (Yii::$app->session->getFlash('profile_error_update')):?>
            <div class="col-md-12 alert alert-warning">
                <?=Yii::t('frontend/user', 'Profile update error')?>
            </div>
        <?php endif;?>
        <?php
        $form = ActiveForm::begin([
            'id' => 'profile',
            'action' => ['update-contact-data'],
            'enableAjaxValidation' => true,
            'enableClientValidation' => true,
        ]);
        print $form->field($profile, 'name')->textInput(['maxlength' => Register::MAX_NAME_LENGTH]);
        print Html::submitButton(Yii::t('frontend/user', 'Update data'), [
            'class' => 'btn btn-primary',
        ]);
        ActiveForm::end();
        ?>
    </div>

    <a name="change-email"></a>
    <div class="profile-row col-md-12">
        <h2><?=Yii::t('frontend/user', 'Change e-mail')?></h2>
        <?php if ($email = Yii::$app->session->getFlash('email_change_message')):?>
            <div class="col-md-12 alert alert-info">
                На ваш e-mail <strong><?=$email?></strong> отправлена ссылка для подтверждения. Пожалуйста, пройдите по этой ссылке и ваш e-mail профиля будет изменен.
            </div>
        <?php elseif ($error = Yii::$app->session->getFlash('email_change_error')):?>
            <div class="col-md-12 alert alert-warning">
                <?=Html::encode($error)?>
            </div>
        <?php elseif (Yii::$app->session->getFlash('email_change_success')):?>
            <div class="col-md-12 alert alert-success">
                <?=Yii::t('frontend/user', 'E-mail success changed')?>
            </div>
        <?php endif;?>
        <?php
        $form = ActiveForm::begin([
            'id' => 'change-email',
            'action' => ['update-email'],
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
            'maxlength' => Register::MAX_EMAIL_LENGTH,
        ]);
        print Html::submitButton(Yii::t('frontend/user', 'Change e-mail'), [
            'class' => 'btn btn-primary',
        ]);
        ActiveForm::end();
        ?>
    </div>

    <a name="change-password"></a>
    <div class="profile-row col-md-12">
        <h2><?=Yii::t('frontend/user', 'Change password')?></h2>
        <?php if (Yii::$app->session->getFlash('password_success_changed')):?>
            <div class="col-md-12 alert alert-success">
                <?=Yii::t('frontend/user', 'Password success changed')?>
            </div>
        <?php elseif (Yii::$app->session->getFlash('password_error_changed')):?>
            <div class="col-md-12 alert alert-warning">
                <?=Yii::t('frontend/user', 'Password changes error')?>
            </div>
        <?php endif;?>
        <?php
        $form = ActiveForm::begin([
            'id' => 'change-password',
            'action' => ['change-password'],
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
<div class="col-md-4 col-sm-12"></div>