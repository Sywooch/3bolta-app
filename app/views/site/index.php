<?php
use yii\helpers\Html;
use yii\helpers\Url;
use advert\forms\PartSearch;
use app\assets\MainPageAssets;

use advert\widgets\LastPartAdverts;
MainPageAssets::register($this);

/* @var $this yii\web\View */
/* @var $marks \auto\models\Mark[] */
$this->title = '3bolta.com';

// параметр для установки марки в запрос
$markParam = Html::getInputName(new PartSearch(), PartSearch::getAutoParam('mark'));
?>
<div class="site-index no-content-margin">
    <div class="index-automobiles">
        <div class="col-xs-12"><h3><?=Yii::t('main', 'Parts for automobiles')?></h3></div>
        <?php foreach ($marks as $mark):?>
            <div class="col-lg-2 main-mark">
                <a href="<?=Url::toRoute(['/advert/part-catalog/search', $markParam => $mark->id])?>"><?=Html::encode($mark->name)?></a>
            </div>
        <?php endforeach;?>
        <?php foreach ($marks as $mark):?>
            <div class="col-lg-2 main-mark">
                <a href="<?=Url::toRoute(['/advert/part-catalog/search', $markParam => $mark->id])?>"><?=Html::encode($mark->name)?></a>
            </div>
        <?php endforeach;?>
        <?php foreach ($marks as $mark):?>
            <div class="col-lg-2 main-mark">
                <a href="<?=Url::toRoute(['/advert/part-catalog/search', $markParam => $mark->id])?>"><?=Html::encode($mark->name)?></a>
            </div>
        <?php endforeach;?>
        <?php foreach ($marks as $mark):?>
            <div class="col-lg-2 main-mark">
                <a href="<?=Url::toRoute(['/advert/part-catalog/search', $markParam => $mark->id])?>"><?=Html::encode($mark->name)?></a>
            </div>
        <?php endforeach;?>
    </div>
    <?=LastPartAdverts::widget();?>
</div>
