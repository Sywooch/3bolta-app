<?php
/**
 * Список торговых точек партнера - вывод на карте
 */

use yii\helpers\Json;
use app\widgets\JS;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use partner\forms\MyTradePointSearch;
use app\widgets\SelectMapLocation;
use partner\widgets\TradePointModal;
use partner\assets\TradePointListAssets;

TradePointListAssets::register($this);

/* @var $this \yii\web\View */
/* @var $list \partner\models\TradePoint[] */

// модель для поиска торговых точек по карте
$formModel = new MyTradePointSearch();
?>
<div class="col-lg-12">
    <div class="col-lg-12">
        <h1><?=Yii::t('frontend/partner', 'My trade points')?></h1>
    </div>

    <div class="my-trade-points-map col-sm-12">
        <?php
        /* @var $form \yii\widgets\ActiveForm */
        $form = ActiveForm::begin([
            'action' => '#',
            'enableAjaxValidation' => false,
            'enableClientValidation' => false,
        ]);
        print $form->field($formModel, 'address')->widget(SelectMapLocation::className(), [
            'attributeLatitude' => 'latitude',
            'attributeLongitude' => 'longitude',
            'wrapperOptions' => [
                'class' => 'js-my-trade-point-map',
            ],
            'jsOptions' => [
                'onLoadMap' => new \yii\web\JsExpression('document.onLoadTradePointMap'),
                'hideMarker' => true,
            ]
        ]);
        ActiveForm::end();
        ?>
    </div>
    <?php foreach ($list as $tradePoint):?>
        <?=TradePointModal::widget(['tradePoint' => $tradePoint])?>
    <?php endforeach;?>

    <div class="col-lg-12">
        <?=TradePointModal::widget();?>
        <?=Html::button(Yii::t('frontend/partner', 'Create new trade point'), [
            'class' => 'btn btn-primary',
            'data-toggle' => 'modal',
            'data-target' => '#newTradePointModal',
        ])?>
    </div>
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
                'removeUrl' => Url::toRoute(['/partner/user-trade-point/delete', 'id' => $tradepoint->id]),
            ];
        }
        ?>
        document.tradePoints = <?=Json::encode($tradepoints)?>;
    </script>
<?php JS::end(); ?>