<?php
/**
 * Вывод виджета последних объявлений
 */

/* @var $this \yii\web\View */
/* @var $list \advert\models\Advert[] */

use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="index-last-adverts">
    <div class="col-lg-12"><h3><?=Yii::t('main', 'Last adverts')?></h3></div>
    <?php foreach ($list as $model):?>
        <?php
        // получить превью
        /* @var $preivew storage\models\File */
        $preview = $model->getPreview();
        ?>
        <div class="col-lg-6 col-sm-12 col-md-6 list-item">
            <div class="panel panel-default list-item-internal">
                <div class="panel-body">
                    <div class="list-item-internal-desc <?php if ($preview):?>col-xs-8<?php else:?>col-xs-12<?php endif;?>">
                        <div class="list-item-title list-item-title-internal">
                            <h3><?=Html::a(
                                Html::encode($model->advert_name),
                                Url::toRoute(['/advert/catalog/details', 'id' => $model->id])
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
                        </div>
                        <div class="list-item-row list-item-seller-type">
                            <strong><?=Yii::t('frontend/advert', 'Seller type')?>:</strong>
                            <?=Yii::t('frontend/advert', 'private person')?>
                        </div>
                    </div>
                    <?php if ($preview):?>
                        <div class="list-item-internal-preview col-xs-4">
                            <?=Html::img($preview->getUrl())?>
                        </div>
                    <?php endif;?>
                </div>
            </div>
        </div>
    <?php endforeach;?>
</div>