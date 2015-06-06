<?php
/**
 * Профиль пользователя
 */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\widgets\MaskedInput;
use app\components\PhoneValidator;
use user\forms\Register;
use partner\models\Partner;

/* @var $this \yii\base\View */
/* @var $changePassword \user\forms\ChangePassword */
/* @var $form \yii\bootstrap\ActiveForm */
/* @var $profile \user\forms\Profile */
/* @var $partner \partner\forms\Partner */
/* @var $user \user\models\User */

// тип пользователя - патнер
$isPartner = $user->type == user\models\User::TYPE_LEGAL_PERSON;

// ширина колонок в зависимости от типа партнера
$colLength = $isPartner ? 'col-lg-6' : 'col-lg-4';
?>
<div class="col-sm-10 col-lg-12 profile">
    <div class="profile-row">
        <h1><?=Yii::t('frontend/user', 'Profile')?></h1>
    </div>

    <?php if (Yii::$app->session->getFlash('partner_success_update')):?>
        <div class="alert alert-success">
            <?=Yii::t('frontend/user', 'Company data success updated')?>
        </div>
    <?php elseif (Yii::$app->session->getFlash('partner_error_update')):?>
        <div class="alert alert-warning">
            <?=Yii::t('frontend/user', 'Company data update error')?>
        </div>
    <?php endif;?>
    <?php if (Yii::$app->session->getFlash('profile_success_update')):?>
        <div class="alert alert-success">
            <?=Yii::t('frontend/user', 'Profile success updated')?>
        </div>
    <?php elseif (Yii::$app->session->getFlash('profile_error_update')):?>
        <div class="alert alert-warning">
            <?=Yii::t('frontend/user', 'Profile update error')?>
        </div>
    <?php endif;?>
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
    <?php if (Yii::$app->session->getFlash('password_success_changed')):?>
        <div class="alert alert-success">
            <?=Yii::t('frontend/user', 'Password success changed')?>
        </div>
    <?php elseif (Yii::$app->session->getFlash('password_error_changed')):?>
        <div class="alert alert-warning">
            <?=Yii::t('frontend/user', 'Password changes error')?>
        </div>
    <?php endif;?>

    <?php if ($isPartner):?>
        <a name="partner"></a>
        <div class="profile-row col-sm-12 <?=$colLength?>">
            <h2><?=Yii::t('frontend/user', 'Company data')?></h2>
            <?php
            $form = ActiveForm::begin([
                'id' => 'partner',
                'action' => ['update-company-data'],
                'enableAjaxValidation' => true,
                'enableClientValidation' => true,
            ]);
            print $form->field($partner, 'name')->textInput(['maxlength' => Register::MAX_PARTNER_NAME_LENGTH]);
            print $form->field($partner, 'type')->dropDownList(Partner::getCompanyTypes());
            print Html::submitButton(Yii::t('frontend/user', 'Update data'), [
                'class' => 'btn btn-primary',
            ]);
            ActiveForm::end();
            ?>
        </div>
    <?php endif;?>

    <a name="profile"></a>
    <div class="profile-row col-sm-12 <?=$colLength?>">
        <h2><?=Yii::t('frontend/user', 'Contact data')?></h2>
        <?php
        $form = ActiveForm::begin([
            'id' => 'profile',
            'action' => ['update-contact-data'],
            'enableAjaxValidation' => true,
            'enableClientValidation' => true,
        ]);
        print $form->field($profile, 'name')->textInput(['maxlength' => Register::MAX_NAME_LENGTH]);
        print $form->field($profile, 'phone', [
            'errorOptions' => [
                'encode' => false,
            ]
        ])->widget(MaskedInput::className(), [
            'mask' => PhoneValidator::PHONE_MASK,
        ]);
        print Html::submitButton(Yii::t('frontend/user', 'Update data'), [
            'class' => 'btn btn-primary',
        ]);
        ActiveForm::end();
        ?>
    </div>

    <a name="change-email"></a>
    <div class="profile-row col-sm-12 <?=$colLength?>">
        <h2><?=Yii::t('frontend/user', 'Change e-mail')?></h2>
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
    <div class="profile-row col-sm-12 <?=$colLength?>">
        <h2><?=Yii::t('frontend/user', 'Change password')?></h2>
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