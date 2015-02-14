<?php
/**
 * Вывод объявления в результате поиска
 */

use yii\helpers\Html;
use advert\forms\Search;
use yii\helpers\ArrayHelper;

// необходима модель для формирования названий параметров по поиску автомобилей
$searchModel = new Search();
$markParam = Html::getInputName($searchModel, 'mark');
$modelParam = Html::getInputName($searchModel, 'model');
$serieParam = Html::getInputName($searchModel, 'serie');
$modificationParam = Html::getInputName($searchModel, 'modification');

// получить массив ссылок на автомобили
$automobiles = $model->getAutomobilesLinks('search', $markParam, $modelParam, $serieParam, $modificationParam);
$automobiles = ArrayHelper::map($automobiles, 'name', function($data, $default) {
    return Html::a(Html::encode($data['name']), $data['url']);
});
$automobiles = array_values($automobiles);

// получить превью
/* @var $preivew storage\models\File */
$preview = $model->getPreview();

/* @var $this \yii\base\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $model \advert\models\Advert */
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-lg-6 col-sm-12">
                <?=Html::encode($model->advert_name)?>
            </div>
            <div class="col-lg-6 col-sm-12">
                <div class="pull-right">
                    <span class="glyphicon glyphicon-ruble"></span>
                    <?=$model->getPriceFormated()?>
                </div>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php if (!$model->user_id):?>
            <div class="row list-item-contacts">
                <div class="col-xs-12 col-sm-12 col-lg-6 pull-left">
                    <span class="glyphicon glyphicon-user"></span>
                    <?=Html::encode($model->user_name)?>
                </div>
                <div class="col-xs-12 col-sm-12 col-lg-6">
                    <div class="pull-right">
                        <span class="glyphicon glyphicon-earphone"></span>
                        <?=Html::encode($model->user_phone)?>
                    </div>
                </div>
            </div>
        <?php endif;?>
        <?php if ($preview):?>
            <div class="col-xs-12 col-sm-12 col-lg-6 list-item-preview">
                <?=Html::img($preview->getUrl())?>
            </div>
        <?php endif;?>
        <?php if (!empty($automobiles)):?>
            <div class="list-item-automobiles col-xs-12 col-sm-12 <?=$preview ? 'col-lg-6' : 'col-lg-12'?>">
                <strong><?=Yii::t('frontend/advert', 'Apply to')?>:</strong>
                <?=implode(', ', $automobiles)?>
            </div>
        <?php endif;?>
    </div>
</div>