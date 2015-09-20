<?php
/**
 * Форма регистрации
 */

use app\widgets\JS;
use app\widgets\MagicSuggestDefaults;
use auto\models\Mark;
use partner\models\Partner;
use user\forms\Register;
use user\models\User;
use yii\base\View;
use yii\bootstrap\ActiveForm;
use app\widgets\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $this View */
/* @var $model Register */
/* @var $registeredUser User */
/* @var $form ActiveForm */
?>
<div class="col-sm-1 col-lg-3"></div>
<div class="col-sm-10 col-lg-6">
    <h1><?=Yii::t('frontend/user', 'Registration')?></h1>

    <?php
    $form = ActiveForm::begin([
        'enableAjaxValidation' => true,
        'enableClientValidation' => false,
    ]);
    print $form->field($model, 'type')->dropDownList($model->getRegistrationTypes());
    ?>
    <div class="js-legal-person-fields" style="display:none;">
        <?=$form->field($model, 'partnerName')->textInput([
            'maxlength' => Register::MAX_PARTNER_NAME_LENGTH,
        ])?>
        <?=$form->field($model, 'partnerType')->dropDownList(Partner::getCompanyTypes())?>
        <?=$form->field($model, 'partnerSpecialization')->widget(MagicSuggestDefaults::className(), [
            'items' => ArrayHelper::map(Mark::find()->all(), 'id', function($data) {
                return ['id' => $data->id, 'name' => $data->full_name];
            }),
            'clientOptions' => [
                'editable' => true,
                'expandOnFocus' => true,
                'maxSelection' => Register::MAX_PARTNER_SPECIALIZATION,
                'maxSelectionRenderer' => '',
                'maxEntryRenderer' => '',
                'minCharsRenderer' => '',
                'value' => $model->getPartnerSpecializationArray(),
            ]
        ])?>
    </div>
    <?php
    print $form->field($model, 'name')->textInput([
        'maxlength' => Register::MAX_NAME_LENGTH,
    ]);
    print $form->field($model, 'email')->textInput([
        'maxlength' => Register::MAX_EMAIL_LENGTH,
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

<?php JS::begin(); ?>
<script type="text/javascript">
    $(document).ready(function() {
        var legalPersonType = <?=User::TYPE_LEGAL_PERSON?>;
        var privatePersonType = <?=User::TYPE_PRIVATE_PERSON?>;
        var typeSelect = '#<?=Html::getInputId($model, 'type')?>';
        $(typeSelect).on('change', function(e) {
            if ($(this).val() == legalPersonType) {
                $('.js-legal-person-fields').show();
            }
            else {
                $('.js-legal-person-fields').hide();
            }
        });
    });
</script>
<?php JS::end(); ?>

<?php if ($registeredUser instanceof User) {
    Modal::begin([
        'id' => 'userConfirmationSend',
        'title' => Yii::t('frontend/user', 'Success registered'),
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
