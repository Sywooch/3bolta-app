<?php
/**
 * Редактирование информации о компании-партнере, список ТТ
 */

use app\widgets\JS;
use app\widgets\MagicSuggestDefaults;
use kalyabin\maplocation\SelectMapLocationWidget;
use auto\models\Mark;
use partner\assets\TradePointListAssets;
use partner\forms\MyTradePointSearch;
use partner\forms\Partner as Partner2;
use partner\models\Partner;
use partner\models\TradePoint;
use partner\widgets\TradePointModal;
use user\forms\Register;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm as ActiveForm2;

TradePointListAssets::register($this);

/* @var $this View */
/* @var $list TradePoint[] */
/* @var $partnerForm Partner2 */

// модель для поиска торговых точек по карте
$formModel = new MyTradePointSearch();
?>
<a name="partner"></a>
<div class="col-xs-12"><h2><?=Yii::t('frontend/user', 'Company data')?></h2></div>
<?php if (Yii::$app->session->getFlash('partner_success_update')):?>
    <div class="alert alert-success">
        <?=Yii::t('frontend/user', 'Company data success updated')?>
    </div>
<?php elseif (Yii::$app->session->getFlash('partner_error_update')):?>
    <div class="alert alert-warning">
        <?=Yii::t('frontend/user', 'Company data update error')?>
    </div>
<?php endif;?>
<?php
$form = ActiveForm::begin([
    'id' => 'partner',
    'enableAjaxValidation' => true,
    'enableClientValidation' => true,
]);
?>
<div class="col-xs-12 col-md-6">
    <?php
    print $form->field($partnerForm, 'name')->textInput(['maxlength' => Register::MAX_PARTNER_NAME_LENGTH]);
    print $form->field($partnerForm, 'type')->dropDownList(Partner::getCompanyTypes());
    ?>
</div>
<div class="col-xs-12 col-md-6">
    <?=$form->field($partnerForm, 'specialization')->widget(MagicSuggestDefaults::className(), [
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
            'value' => $partnerForm->getSpecializationArray(),
        ]
    ])?>
</div>
<div class="col-md-12">
    <?=Html::submitButton(Yii::t('frontend/user', 'Update data'), [
        'class' => 'btn btn-primary',
    ])?>
</div>
<?php ActiveForm::end(); ?>

<div class="col-md-12">
    <h2><?=Yii::t('frontend/partner', 'Trade points')?></h2>
</div>

<div class="my-trade-points-map col-sm-12">
    <?php
    /* @var $form ActiveForm2 */
    $form = ActiveForm::begin([
        'action' => '#',
        'enableAjaxValidation' => false,
        'enableClientValidation' => false,
    ]);
    print $form->field($formModel, 'address')->widget(SelectMapLocationWidget::className(), [
        'attributeLatitude' => 'latitude',
        'attributeLongitude' => 'longitude',
        'wrapperOptions' => [
            'class' => 'js-my-trade-point-map',
        ],
        'jsOptions' => [
            'onLoadMap' => new JsExpression('document.onLoadTradePointMap'),
            'hideMarker' => true,
        ]
    ]);
    ActiveForm::end();
    ?>
</div>
<?php foreach ($list as $tradePoint):?>
    <?=TradePointModal::widget(['tradePoint' => $tradePoint])?>
<?php endforeach;?>

<div class="col-md-12">
    <?=TradePointModal::widget();?>
    <?=Html::button(Yii::t('frontend/partner', 'Create new trade point'), [
        'class' => 'btn btn-primary',
        'data-toggle' => 'modal',
        'data-target' => '#newTradePointModal',
    ])?>
</div>

<?php JS::begin(); ?>
    <script type="text/javascript">
        <?php
        $tradepoints = [];
        foreach ($list as $tradepoint) {
            $tradepoints[] = [
                'id' => $tradepoint->id,
                'address' => $tradepoint->address,
                'phone' => $tradepoint->getTradePointPhone(),
                'latitude' => $tradepoint->latitude,
                'longitude' => $tradepoint->longitude,
                'removeUrl' => Url::toRoute(['/partner/partner/delete-trade-point', 'id' => $tradepoint->id]),
            ];
        }
        ?>
        document.tradePoints = <?=Json::encode($tradepoints)?>;
    </script>
<?php JS::end(); ?>