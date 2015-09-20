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
use yii\helpers\Url;

/* @var $this View */
/* @var $changePassword ChangePassword */
/* @var $form ActiveForm */
/* @var $profile Profile */
/* @var $user User */

$socials = [
    'vkontakte' => [
        'name' => Yii::t('frontend/user', 'VKontakte'),
        'btn' => 'btn-vk',
        'icon' => 'icon-vkontakte',
        'link' => '',
        'user_name' => '',
        'attached' => false,
    ],
    'facebook' => [
        'name' => Yii::t('frontend/user', 'Facebook'),
        'btn' => 'btn-facebook',
        'icon' => 'icon-facebook',
        'link' => '',
        'user_name' => '',
        'attached' => false,
    ],
    'google' => [
        'name' => Yii::t('frontend/user', 'Google+'),
        'btn' => 'btn-google',
        'icon' => 'icon-google',
        'link' => '',
        'user_name' => '',
        'attached' => false,
    ],
    'yandex' => [
        'name' => Yii::t('frontend/user', 'Yandex'),
        'btn' => 'btn-yandex',
        'icon' => 'icon-yandex',
        'link' => '',
        'user_name' => '',
        'attached' => false,
    ],
];
$attachedServices = [];
foreach ($user->socialAccounts as $socialAccount) {
    /* @var $socialAccount \user\models\SocialAccount */
    $socials[$socialAccount->code]['link'] = $socialAccount->external_page;
    $socials[$socialAccount->code]['user_name'] = $socialAccount->external_name;
    $socials[$socialAccount->code]['attached'] = true;
}
?>
<div class="col-md-4 col-sm-12"></div>
<div class="col-md-5 col-sm-12 profile">
    <a name="socials"></a>
    <div class="profile-row col-md-12">
        <h2><?=Yii::t('frontend/user', 'Attached social services')?></h2>
        <?php foreach ($socials as $code => $params):?>
            <div class="col-md-12 social-button">
                <a class="btn <?=$params['btn']?>"><i class="icon <?=$params['icon']?>"></i>&nbsp;</a>
                <?=$params['name']?>
                <?php if ($params['attached'] && $params['link']):?>
                    (<a href="<?=$params['link']?>" target="_blank"><?=Html::encode($params['user_name'])?></a>)
                <?php elseif($params['attached']):?>
                    (<?=Html::encode($params['user_name'])?>)
                <?php endif;?>
                <small>
                    <?php if ($params['attached']):?>
                        <a href="<?=Url::to(['disconnect-social', 'key' => $code])?>"><?=Yii::t('frontend/user', 'Disconnect')?></a>
                    <?php else:?>
                        <a href="<?=Url::to(['/user/external-auth/redirect', 'key' => $code])?>"><?=Yii::t('frontend/user', 'Connect')?></a>
                    <?php endif;?>
                </small>
            </div>
        <?php endforeach;?>
    </div>
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