<?php
/**
 * Главная страница для поиска торговых точек на карте
 */

use app\components\Controller;
use app\widgets\JS;
use partner\assets\TradePointMapAssets;
use partner\forms\TradePointMap;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\jui\AutoComplete;
use app\widgets\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $searchForm TradePointMap */

TradePointMapAssets::register($this);

$this->pageH1 = Yii::t('frontend/partner', 'Organization search');
?>

<?php JS::begin(); ?>
<script type="text/javascript">
    document.tradePointMapParams['mapMarkerSvg'] = '<?=Controller::getFrontendAssetsUrl()?>/img/map-marker.svg';
    document.tradePointMapParams['mapMarkerUnactiveSvg'] = '<?=Controller::getFrontendAssetsUrl()?>/img/map-marker-unactive.svg';
    document.tradePointMapParams['mapMarkerPng'] = '<?=Controller::getFrontendAssetsUrl()?>/img/map-marker.png';
    document.tradePointMapParams['mapMarkerUnactivePng'] = '<?=Controller::getFrontendAssetsUrl()?>/img/map-marker-unactive.png';
</script>
<?php JS::end(); ?>

<div class="top-search">
    <div class="row">
        <?php $form = ActiveForm::begin([
            'action' => ['search'],
            'options' => [
                'class' => 'js-trade-point-map-form',
            ],
            'enableClientValidation' => false,
            'enableAjaxValidation' => false,
            'fieldConfig' => [
                'template' => '{input}{icon}',
                'parts' => ['{icon}' => ''],
            ]
        ]); ?>
            <div class="col-md-4 col-xs-12">
                <?=$form->field($searchForm, 'name', [
                    'parts' => ['{icon}' => '<span class="form-control-icon icon-tag"></span>'],
                ])->widget(AutoComplete::className(), [
                    'options' => [
                        'placeholder' => $searchForm->getAttributeLabel('name'),
                        'class' => 'form-control form-control-with-icon js-trade-point-map-name',
                    ],
                    'clientOptions' => [
                        'source' => Url::toRoute(['name-autocomplete']),
                        'minLength' => 3,
                    ]
                ])?>
            </div>
            <div class="col-md-4 col-xs-12">
                <?=$form->field($searchForm, 'specialization', [
                    'parts' => ['{icon}' => '<span class="form-control-icon icon-cab"></span>'],
                ])->widget(AutoComplete::className(), [
                    'options' => [
                        'placeholder' => $searchForm->getAttributeLabel('specialization'),
                        'class' => 'form-control form-control-with-icon js-trade-point-map-mark',
                    ],
                    'clientOptions' => [
                        'source' => Url::toRoute(['mark-autocomplete']),
                        'minLength' => 3,
                    ]
                ])?>
            </div>
            <div class="col-md-4 col-xs-12">
                <?=$form->field($searchForm, 'address', [
                    'parts' => ['{icon}' => '<span class="form-control-icon icon-location"></span>'],
                    'inputOptions' => [
                        'class' => 'form-control form-control-with-icon js-trade-point-address',
                        'placeholder' => $searchForm->getAttributeLabel('address'),
                    ],
                ])->textInput()?>
            </div>
            <?=Html::activeHiddenInput($searchForm, 'coordinates', [
                'class' => 'js-trade-point-map-coordinates'
            ])?>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<div class="no-content-margin trade-point-map-wrapper">
    <div class="trade-point-map js-trade-point-map"></div>
    <div class="trade-point-list js-trade-point-list"></div>
</div>