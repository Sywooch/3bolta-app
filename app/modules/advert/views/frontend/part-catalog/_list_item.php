<?php
/**
 * Вывод объявления в результате поиска
 */

use advert\components\PartsSearchApi;
use advert\models\Contact;
use advert\models\PartIndex;
use geo\models\Region;
use sammaye\solr\SolrDataProvider;
use storage\models\File;
use yii\base\View;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $searchApi PartsSearchApi */
$searchApi = Yii::$app->getModule('advert')->partsSearch;

// ссылки на автомобили
$automobiles = $searchApi->getAutomobilesLink(['search'], $model->getAutomobilesTree());

/* @var $this View */
/* @var $dataProvider SolrDataProvider */
/* @var $model PartIndex */
?>
<div class="list-item-internal-desc <?php if (!empty($model->preview_url)):?>col-xs-8<?php else:?>col-xs-12<?php endif;?>">
    <div class="col-xs-12 list-item-title list-item-title-internal">
        <h3><?=Html::a(
            Html::encode($model->name),
            Url::toRoute(['details', 'id' => $model->id])
        )?></h3>
    </div>
    <div class="col-xs-12 list-item-date">
        <i class="publish-date">
            <?=Yii::t('frontend/advert', 'Published at')?>
            <?=$model->getPublishedFormatted()?>
        </i>
    </div>
    <div class="col-xs-12 list-item-row list-item-seller-type">
        <i class="icon icon-user"></i>
        <?=$model->getSeller()?>
    </div>
    <div class="col-xs-12 list-item-row list-item-region">
        <i class="icon icon-location"></i>
        <?=Html::encode($model->region_name)?>
    </div>
    <div class="col-xs-12 list-item-row list-item-price">
        <span class="label label-price">
            <span class="icon icon-rouble"></span>
            <?=$model->getPriceFormated()?>
        </span>
    </div>
</div>
<?php if (!empty($model->preview_url)):?>
    <div class="list-item-internal-preview col-xs-4">
        <?=Html::img($model->preview_url)?>
    </div>
<?php endif;?>