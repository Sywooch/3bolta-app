<?php
/**
 * Вывод результов поиска
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;

/* @var $this \yii\base\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
$models = $dataProvider->getModels();
foreach ($models as $model) {
    print Html::beginTag('div', [
        'class' => 'col-xs-10 col-sm-10'
    ]);
        print $this->render('_list_item', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    print Html::endTag('div');
}

print Html::beginTag('div', ['class' => 'container']);
print LinkPager::widget(['pagination' => $dataProvider->pagination]);
print Html::endTag('div');
