<?php
/**
 * Вывод объявления в списке пользователя
 */

use advert\components\PartsSearchApi;
use advert\models\PartParam;
use advert\models\Part;
use app\helpers\Date;
use storage\models\File;
use yii\base\View;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $searchApi PartsSearchApi */
$searchApi = Yii::$app->getModule('advert')->partsSearch;

// ссылки на автомобили
$automobiles = $searchApi->getAutomobilesLink(['search'], $model);

// получить превью
/* @var $preivew File */
$preview = $model->getPreview();

/* @var $this View */
/* @var $dataProvider ActiveDataProvider */
/* @var $model Part */
?>
<div class="panel panel-default">
    <div class="panel-body">
        <div class="col-sm-2 col-xs-12 col-lg-12 list-item-title">
            <strong class="advert-title"><?=Html::encode($model->advert_name)?></strong>
            <?php if ($model->published_to && strtotime($model->published_to) > time()):?>
                <strong>
                    <a href="<?=Url::toRoute(['/advert/part-catalog/details', 'id' => $model->id])?>" target="_blank"><?=Yii::t('frontend/advert', 'View')?></a>
                </strong>
            <?php endif;?>
            <strong>
                <a href="<?=Url::toRoute(['edit', 'id' => $model->id])?>"><?=Yii::t('frontend/advert', 'Edit')?></a>
            </strong>
            <strong>
                <a href="<?=Url::toRoute(['stop-publication', 'id' => $model->id])?>"><?=Yii::t('frontend/advert', 'Stop advert publication')?></a>
            </strong>
        </div>
        <div class="col-sm-2 col-xs-12 col-lg-12 list-item-date">
            <?php if ($model->active && $model->published_to && strtotime($model->published_to) > time()):?>
                <strong class="publish-date">
                    <?=Yii::t('frontend/advert', 'Published to')?>
                    <?=$model->getPublishedToFormatted()?>
                </strong>
            <?php elseif (!$model->active || !$model->published_to):?>
                <strong class="unpublished">
                    <?=Yii::t('frontend/advert', 'Unpublished')?>
                </strong>
            <?php else:?>
                <strong class="unpublished">
                    <?=Yii::t('frontend/advert', 'Complete publication')?>
                    <?=$model->getPublishedToFormatted()?>
                    <br />
                    <?php
                    $date = new DateTime();
                    $date->add(new DateInterval('P' . Part::DEFAULT_PUBLISH_DAYS . 'D'));
                    $date = Date::formatDate($date);
                    ?>
                    <a href="<?=Url::toRoute(['update-publication', 'id' => $model->id])?>"><?=Yii::t('frontend/advert', 'Publish to {date}', ['date' => $date])?></a>
                </strong>
            <?php endif;?>
        </div>
        <?php if ($preview):?>
            <div class="col-lg-4 col-sm-12 col-xs-12 list-item-image">
                <?=Html::img($preview->getUrl())?>
            </div>
        <?php endif;?>
        <div class="col-lg-<?=$preview ? '8' : '12'?>">
            <div class="list-item-row list-item-price">
                <span class="label label-primary">
                    <span class="glyphicon glyphicon-ruble"></span>
                    <?=$model->getPriceFormated()?>
                </span>
            </div>
            <?php if ($model->published):?>
                <div class="list-item-row list-item-category">
                    <strong><?=Yii::t('frontend/advert', 'Begin publication')?>:</strong>
                    <?=$model->getPublishedFormatted()?>
                </div>
            <?php endif;?>
            <div class="list-item-row list-item-category">
                <strong><?=Yii::t('frontend/advert', 'Condition')?>:</strong>
                <?=$model->getConditionName()?>
            </div>
            <div class="list-item-row list-item-category">
                <strong><?=Yii::t('frontend/advert', 'Category')?>:</strong>
                <?=implode(', ', $model->getCategoriesTree())?>
            </div>
            <?php if (!empty($automobiles)):?>
                <div class="list-item-row list-item-automobiles">
                    <strong><?=Yii::t('frontend/advert', 'Apply to')?>:</strong>
                    <?php if (count($automobiles) > 10):?>
                        <?=implode(', ', array_slice($automobiles, 0, 8))?>,
                        <?=Html::a(
                            Yii::t('frontend/advert', 'and {n, plural, =0{automobiles} =1{automobile} one{# automobile} few{# few automobiles} many{# automobiles} other{# automobiles}}', [
                                'n'=> count($automobiles) - 2
                            ]) . '...',
                            Url::toRoute(['details', 'id' => $model->id])
                        );?>
                    <?php else:?>
                        <?=implode(', ', $automobiles)?>
                    <?php endif;?>
                </div>
            <?php endif;?>
        </div>
    </div>
</div>