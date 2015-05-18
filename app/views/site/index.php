<?php
use yii\helpers\Html;
use yii\helpers\Url;
use advert\forms\Search;
use app\assets\MainPageAssets;

MainPageAssets::register($this);

/* @var $this yii\web\View */
/* @var $marks \auto\models\Mark[] */
/* @var $lastAdverts \advert\models\Advert[] */
$this->title = '3bolta.com';

// параметр для установки марки в запрос
$markParam = Html::getInputName(new Search(), Search::getAutoParam('mark'));
?>
<div class="site-index">
    <div class="index-automobiles">
        <div class="col-xs-12"><h3>Запчасти для автомобилей</h3></div>
        <?php foreach ($marks as $mark):?>
            <div class="col-lg-2 main-mark">
                <a href="<?=Url::toRoute(['/advert/catalog/search', $markParam => $mark->id])?>"><?=Html::encode($mark->name)?></a>
            </div>
        <?php endforeach;?>
        <?php foreach ($marks as $mark):?>
            <div class="col-lg-2 main-mark">
                <a href="<?=Url::toRoute(['/advert/catalog/search', $markParam => $mark->id])?>"><?=Html::encode($mark->name)?></a>
            </div>
        <?php endforeach;?>
        <?php foreach ($marks as $mark):?>
            <div class="col-lg-2 main-mark">
                <a href="<?=Url::toRoute(['/advert/catalog/search', $markParam => $mark->id])?>"><?=Html::encode($mark->name)?></a>
            </div>
        <?php endforeach;?>
        <?php foreach ($marks as $mark):?>
            <div class="col-lg-2 main-mark">
                <a href="<?=Url::toRoute(['/advert/catalog/search', $markParam => $mark->id])?>"><?=Html::encode($mark->name)?></a>
            </div>
        <?php endforeach;?>
    </div>
    <?php if (!empty($lastAdverts)):?>
        <div class="index-last-adverts">
            <div class="col-lg-12"><h3>Последние объявления</h3></div>
            <?php foreach ($lastAdverts as $model):?>
                <?php
                // получить превью
                /* @var $preivew storage\models\File */
                $preview = $model->getPreview();
                ?>
                <div class="col-lg-6 col-sm-12 col-md-6 list-item">
                    <div class="panel panel-default list-item-internal">
                        <div class="panel-body">
                            <div class="<?php if ($preview):?>col-lg-8<?php endif;?> col-sm-12">
                                <div class="list-item-title list-item-title-internal">
                                    <h3><?=Html::a(
                                        Html::encode($model->advert_name),
                                        Url::toRoute(['details', 'id' => $model->id])
                                    )?></h3>
                                </div>
                                <div class="list-item-date">
                                    <i class="publish-date">
                                        <?=Yii::t('frontend/advert', 'Published at')?>
                                        <?=$model->getPublishedFormatted()?>
                                    </i>
                                </div>
                                <div class="list-item-row list-item-price">
                                    <span class="label label-primary">
                                        <span class="glyphicon glyphicon-ruble"></span>
                                        <?=$model->getPriceFormated()?>
                                    </span>
                                    <small class="list-item-condition"><strong><?=$model->getConditionName()?></strong></small>
                                </div>
                                <div class="list-item-row list-item-seller-type">
                                    <strong><?=Yii::t('frontend/advert', 'Seller type')?>:</strong>
                                    <?=Yii::t('frontend/advert', 'private person')?>
                                </div>
                            </div>
                            <?php if ($preview):?>
                                <div class="list-item-internal-preview col-lg-4 col-sm-12">
                                    <?=Html::img($preview->getUrl())?>
                                </div>
                            <?php endif;?>
                        </div>
                    </div>
                </div>
            <?php endforeach;?>
        </div>
    <?php endif;?>
</div>
