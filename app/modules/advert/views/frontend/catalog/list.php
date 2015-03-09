<?php
/**
 * Вывод результов поиска
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;
use advert\assets\AdvertList;

AdvertList::register($this);

/* @var $this \yii\base\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
$models = $dataProvider->getModels();
foreach ($models as $model) {
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
