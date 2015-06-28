<?php
/**
 * Вывод модального окна восстановления пароля
 */

use app\widgets\Modal;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;
use app\widgets\JS;

/* @var $this \user\widgets\LostPasswordModal */
/* @var $modal \user\forms\LostPassword */
Modal::begin([
    'id' => 'lostPasswordModal',
    'title' => Yii::t('frontend/user', 'Restore password'),
]);
$form = ActiveForm::begin([
    'id' => 'lostPasswordForm',
    'action' => Url::toRoute(['/user/user/lost-password']),
    'enableClientValidation' => true,
    'enableAjaxValidation' => true,
    'validateOnChange' => true,
    'validateOnSubmit' => true,
]);
?>
    <div class="block-info block-info-primary">
        Для восстановления пароля введите ваш e-mail.<br />
        На него будут высланы инструкции для дальнейшего изменения пароля.
    </div>

    <div class="lostPasswordError alert alert-dange" style="display:none;" data-role="alert">
        При отправке e-mail произошла ошибка. Попробуйте еще раз.
    </div>
<?php
print $form->field($model, 'email')->textInput();
print Html::submitButton(Yii::t('main', 'Submit'), ['class' => 'btn btn-primary']);
ActiveForm::end();
?>
    <div class="lostPasswordSuccess alert alert-success" style="display:none;" data-role="alert">
        На ваш e-mail <strong class="lostUserEmail"></strong> выслана информация для дальнейшего восстановления пароля.
    </div>
<?php
Modal::end();

JS::begin();
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('#lostPasswordForm').on('beforeSubmit', function(e) {
            document.appendLoader(this);
            $.ajax({
                'url'           : $(this).attr('action'),
                'type'          : 'post',
                'data'          : $(this).serialize(),
                'dataType'      : 'json',
                'success'       : function(d) {
                    document.removeLoader();
                    if (d.success && d.email) {
                        $('.lostPasswordSuccess .lostUserEmail').text(d.email);
                        $('#lostPasswordForm').hide();
                        $('.lostPasswordSuccess').show();
                    }
                    else {
                        $('.lostPasswordError').show();
                    }
                }
            });
            return false;
        });
    });
</script>
<?php
JS::end();
