<?php
/**
 * Вывод результов поиска
 */

use advert\assets\AdvertList;
use advert\models\PartIndex;
use advert\forms\PartSearch;
use sammaye\solr\SolrDataProvider;
use app\widgets\View;
use yii\helpers\Html;
use yii\widgets\LinkPager;

/* @var $this View */
/* @var $dataProvider SolrDataProvider */
/* @var $searchForm PartSearch */
/* @var $emptySearchForm boolean */

AdvertList::register($this);

$this->pageH1 = Yii::t('frontend/advert', 'Parts search');

// ссылка переключающая параметры поиска
$this->pageH1Extend = Html::tag('a',
    ($emptySearchForm ? '<i class="glyphicon glyphicon-chevron-up"></i>' : '<i class="glyphicon glyphicon-chevron-down"></i>') .
    '&nbsp;&nbsp;' . Html::tag('span', Yii::t('frontend/advert', 'Search params')),
    [
        'href' => '#',
        'class' => 'top-toggle-search-link js-top-search-toggle' . ($emptySearchForm ? ' js-expand' : '')
    ]
);
?>
<div class="top-search js-top-search"<?php if (!$emptySearchForm):?> style="display:none;"<?php endif;?>>
<?php print $this->render('_search_form', [
    'model' => $searchForm,
]);
?>
</div>
<?php

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
