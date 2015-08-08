<?php
/**
 * Вывод объявления в результате поиска
 * Если передать переменную $hideDropDown, то будет скрыта выпадашка при наведении
 */

use yii\helpers\Html;
use yii\helpers\Url;

$hideDropDown = isset($hideDropDown) && $hideDropDown;

/* @var $searchApi \advert\components\PartsSearchApi */
$searchApi = Yii::$app->getModule('advert')->partsSearch;

// ссылки на автомобили
$automobiles = $searchApi->getAutomobilesLink(['search'], $model);

// получить превью
/* @var $preivew storage\models\File */
$preview = $model->getPreview();

/* @var $this \yii\base\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $model \advert\models\PartAdvert */
?>
<?php if (!$hideDropDown):?>
    <div class="panel panel-default list-item-hover">
        <div class="panel-body">
            <div class="list-item-internal-desc<?php if ($preview):?> col-lg-8<?php endif;?>">
                <div class="list-item-title">
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
                </div>
                <div class="list-item-row list-item-seller-type">
                    <strong><?=Yii::t('frontend/advert', 'Seller type')?>:</strong>
                    <?=Yii::t('frontend/advert', 'private person')?>
                </div>
            </div>
            <?php if ($preview):?>
                <div class="list-item-internal-preview col-lg-4">
                    <?=Html::img($preview->getUrl())?>
                </div>
            <?php endif;?>

            <?php if (!empty($automobiles)):?>
                <div class="col-lg-12 list-item-row list-item-automobiles">
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
<?php endif;?>
<div class="panel panel-default list-item-internal">
    <div class="panel-body">
        <div class="list-item-internal-desc <?php if ($preview):?>col-xs-8<?php else:?>col-xs-12<?php endif;?>">
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