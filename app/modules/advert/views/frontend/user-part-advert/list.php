<?php
/**
 * Список объявлений пользователя
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;
use advert\assets\AdvertList;

AdvertList::register($this);

/* @var $this \yii\base\View */
/* @var $dataProvider \yii\db\DataProvider */

print Html::beginTag('div', ['class' => 'col-lg-12']);
    print Html::tag('h1', Yii::t('frontend/advert', 'My adverts'));
print Html::endTag('div');

print Html::beginTag('div', ['class' => 'col-xs-12 list-item']);
    print Html::a(Yii::t('frontend/advert', 'Append advert'), ['append'], ['class' => 'btn btn-success btn-sm']);
print Html::endTag('div');

$models = $dataProvider->getModels();
foreach ($models as $model) {
    print Html::beginTag('div', [
        'class' => 'col-lg-8 col-xs-12 col-md-6 col-sm-12 list-item'
    ]);
        print $this->render('_list_item', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    print Html::endTag('div');
}

print Html::beginTag('div', ['class' => 'list-pager']);
print LinkPager::widget(['pagination' => $dataProvider->pagination]);
print Html::endTag('div');