<?php
/**
 * Вывод результов поиска
 */

use advert\assets\AdvertList;
use advert\models\PartIndex;
use advert\widgets\PartSearch;
use sammaye\solr\SolrDataProvider;
use yii\base\View;
use yii\helpers\Html;
use yii\widgets\LinkPager;

AdvertList::register($this);

print PartSearch::widget();

/* @var $this View */
/* @var $dataProvider SolrDataProvider */
$models = $dataProvider->getModels();
foreach ($models as $model) {
    /* @var $model PartIndex */
    print Html::beginTag('div', [
        'class' => 'col-lg-6 col-sm-12 col-md-6 list-item'
    ]);
        print $this->render('_list_item', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    print Html::endTag('div');
}

if ($dataProvider->pagination->getPageCount() > 1) {
    print Html::beginTag('div', ['class' => 'list-pager']);
    print LinkPager::widget(['pagination' => $dataProvider->pagination]);
    print Html::endTag('div');
}
