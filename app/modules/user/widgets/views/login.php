<?php
/**
 * Вывод модального окна авторизации
 */

use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use app\widgets\JS;
use yii\helpers\Html;
use app\widgets\Modal;

/* @var $this \user\widgets\LoginModal */
/* @var $modal \user\forms\Login */
Modal::begin([
    'id' => 'loginModal',
    'title' => Yii::t('frontend/user', 'Authorize'),
]);
?>
<div class="login-row">
<?php
$form = ActiveForm::begin([
    'id' => 'loginForm',
    'action' => Url::toRoute(['/user/user/login']),
    'enableClientValidation' => true,
    'enableAjaxValidation' => true,
    'validateOnChange' => false,
    'validateOnSubmit' => true,
]);
?>
    <div class="account-wait-confirmation block-info block-info-error" style="display:none;">
        Вы не подтвердили ваш e-mail.<br />
        На ваш e-mail <strong class="email"></strong> повторно выслана ссылка для подтверждения.<br />
        Пожалуйста, пройдите по этой ссылке чтобы активировать ваш аккаунт.
    </div>
    <div class="account-locked block-info block-info-error" style="display:none;">
        Ваш аккаунт заблокирован.<br />
        Пожалуйста, обратитесь в <a href="mailto:support@3bolta.com">службу поддержки</a> для выяснения причин блокировки аккаунта.<br />
    </div>
<?php
print $form->field($model, 'username')->textInput([
    'class' => 'form-control username',
]);
print $form->field($model, 'password')->passwordInput([
    'class' => 'form-control password',
]);
print Html::submitButton(Yii::t('frontend/user', 'Enter'), [
    'class' => 'btn btn-primary'
]);
print Html::beginTag('div', ['class' => 'pull-right']);
    print Html::a(Yii::t('frontend/user', 'Lost password?'), '#', [
        'class' => 'lost-password-link',
        'data-toggle' => 'modal',
        'data-target' => '#lostPasswordModal',
        'data-dismiss' => 'modal',
    ]);
print Html::endTag('div');
ActiveForm::end();
?>
</div>
<div class="login-row">
    <h3><?=Yii::t('frontend/user', 'Use social accounts')?>:</h3>
    <a class="btn btn-social-login btn-vk" href="<?=Url::to(['/user/external-auth/redirect', 'key' => 'vkontakte'])?>"><i class="icon icon-vkontakte"></i>&nbsp;&nbsp;&nbsp;<?=Yii::t('frontend/user', 'VKontakte')?></a>
    <a class="btn btn-social-login btn-facebook" href="<?=Url::to(['/user/external-auth/redirect', 'key' => 'facebook'])?>"><i class="icon icon-facebook"></i>&nbsp;&nbsp;&nbsp;<?=Yii::t('frontend/user', 'Facebook')?></a>
    <a class="btn btn-social-login btn-google" href="<?=Url::to(['/user/external-auth/redirect', 'key' => 'google'])?>"><i class="icon icon-google"></i>&nbsp;&nbsp;&nbsp;<?=Yii::t('frontend/user', 'Google+')?></a>
    <a class="btn btn-social-login btn-yandex" href="<?=Url::to(['/user/external-auth/redirect', 'key' => 'yandex'])?>"><i class="icon icon-yandex"></i>&nbsp;&nbsp;&nbsp;<?=Yii::t('frontend/user', 'Yandex')?></a>
</div>
<div class="login-row">
    <h3 class="inline"><?=Yii::t('frontend/user', 'Have not account?')?></h3>
    <strong><a href="<?=Url::to(['/user/user/register'])?>"><?=Yii::t('frontend/user', 'Register')?></a></strong>
</div>
<?php
Modal::end();

JS::begin();
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('#loginForm').on('afterValidate', function(e, messages) {
            var showNeedActivation = false;
            var showAccountLocked = false;

            var msgs = messages['<?=Html::getInputId($model, 'password')?>'];
            for (var i in msgs) {
                if (msgs[i] == 'Требуется активация') {
                    showNeedActivation = true;
                }
                else if (msgs[i] == 'Аккаунт заблокирован') {
                    showAccountLocked = true;
                }
            }

            if (showNeedActivation) {
                $('#loginForm .account-wait-confirmation .email').text($('#loginForm .username').val());
                $('#loginForm .account-wait-confirmation').show();
            }
            else {
                $('#loginForm .account-wait-confirmation').hide();
            }
            if (showAccountLocked) {
                $('#loginForm .account-locked').show();
            }
            else {
                $('#loginForm .account-locked').hide();
            }
        });
    });
</script>
<?php
JS::end();
