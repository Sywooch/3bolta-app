<?php
/**
 * Вывод модального окна авторизации
 */

use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use app\widgets\JS;
use yii\helpers\Html;
use yii\bootstrap\Modal;

/* @var $this \user\widgets\LoginModal */
/* @var $modal \user\forms\Login */
Modal::begin([
    'id' => 'loginModal',
    'header' => '<h2 class="primary-title"><span class="glyphicon glyphicon-hand-right"></span> ' . Yii::t('frontend/user', 'Authorize') . '</h2>',
    'toggleButton' => false,
]);
$form = ActiveForm::begin([
    'id' => 'loginForm',
    'action' => Url::toRoute(['/user/user/login']),
    'enableClientValidation' => true,
    'enableAjaxValidation' => true,
    'validateOnChange' => false,
    'validateOnSubmit' => true,
]);
    print Html::beginTag('div', [
        'class' => ''
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
    print Html::endTag('div');
print $form->field($model, 'username')->textInput([
    'class' => 'form-control username',
]);
print $form->field($model, 'password')->passwordInput([
    'class' => 'form-control password',
]);
print Html::submitButton(Yii::t('frontend/user', 'Enter'), [
    'class' => 'btn btn-primary'
]);
ActiveForm::end();
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
