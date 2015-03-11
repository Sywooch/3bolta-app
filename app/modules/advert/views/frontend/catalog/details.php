<?php
/**
 * Вывод результов поиска
 */

/* @var $model \advert\models\Advert */
use yii\helpers\Html;
use advert\assets\AdvertDetail;

use app\helpers\Date;
use yii\bootstrap\Modal;
use app\widgets\JS;
use yii\helpers\Url;

/* @var $searchApi \advert\components\SearchApi */
$searchApi = Yii::$app->getModule('advert')->search;

/* @var $related \yii\data\ActiveDataProvider */
$related = $searchApi->getRelated($model);

// ссылки на автомобили
$automobiles = $searchApi->getAutomobilesLink(['search'], $model);

AdvertDetail::register($this);
?>

<div class="col-lg-10 col-xs-12 col-sm-12 item-details">
    <div class="item-details-title">
        <h2><?=Html::encode($model->advert_name)?></h2>
    </div>
    <div class="item-details-row item-details-date">
        <span class="publish-date">
            <?=Yii::t('frontend/advert', 'Published at')?>
            <?=$model->getPublishedFormatted()?>
        </span>
    </div>
    <div class="item-details-row item-details-price">
        <span class="label label-success">
            <span class="glyphicon glyphicon-ruble"></span>
            <?=$model->getPriceFormated()?>
        </span>
    </div>
    <div class="item-details-row item-details-contacts">
        <strong><?=Yii::t('frontend/advert', 'Contacts')?>:</strong>
        <?=Html::encode($model->getUserName())?>
        (<?=Yii::t('frontend/advert', 'private person')?>)
        <span class="item-details-phone label label-primary">
            <span class="glyphicon glyphicon-earphone"></span>
            <?=Html::encode($model->getUserPhone())?>
        </span>
    </div>
    <div class="item-details-row item-details-condition">
        <strong><?=Yii::t('frontend/advert', 'Condition')?>:</strong>
        <?=$model->getConditionName()?>
    </div>
    <div class="item-details-row item-details-condition">
        <strong><?=Yii::t('frontend/advert', 'Category')?>:</strong>
        <?=implode(', ', $model->getCategoriesTree())?>
    </div>
    <?php if (!empty($automobiles)):?>
        <div class="item-details-row item-details-automobiles">
            <strong><?=Yii::t('frontend/advert', 'Apply to')?>:</strong>
            <?=implode(', ', $automobiles)?>
        </div>
    <?php endif;?>

    <?php if ($images = $model->images):?>
        <div class="item-details-images item-details-row">
            <div class="item-details-images-full">
                <?=Html::img(reset($images)->file->getUrl(), [
                    'class' => 'full-image',
                ])?>
            </div>
            <?php if (count($images) > 1):?>
                <div class="item-details-images-list">
                    <?php foreach ($images as $image):?>
                        <?php
                        /* @var $image \advert\models\AdvertImage */
                        ?>
                        <div class="col-lg-2 col-xs-4">
                            <a href="<?=$image->file->getUrl()?>" class="thumbnail">
                                <?=Html::img($image->thumbnail->getUrl())?>
                            </a>
                        </div>
                    <?php endforeach;?>
                </div>
            <?php endif;?>
        </div>
    <?php endif;?>

    <?php if (!empty($model->description)):?>
        <div class="item-details-row item-details-description">
            <strong><?=Yii::t('frontend/advert', 'Description')?>:</strong><br />
            <?=nl2br(Html::encode($model->description))?>
        </div>
    <?php endif;?>
    <?php if ($related->getCount() > 0 && $relatedList = $related->getModels()):?>
        <?php advert\assets\AdvertList::register($this); ?>
        <div class="col-lg-12"><h2><?=Yii::t('frontend/advert', 'Related adverts')?></h2></div>
        <div class="item-details-related">
            <?php foreach ($relatedList as $advert):?>
                <div class="col-lg-6 col-sm-12 col-md-6 list-item">
                    <?=$this->render('_list_item', [
                        'model' => $advert,
                        'dataProvider' => $related,
                        'hideDropDown' => true,
                    ])?>
                </div>
            <?php endforeach;?>
        </div>
    <?php endif;?>
</div>

<?php if (($id = Yii::$app->session->getFlash('advert_published')) && $id == $model->id):?>
    <?php
    Modal::begin([
        'id' => 'advertWasPublishedModal',
        'header' => '<h2 class="primary-title"><span class="glyphicon glyphicon-info-sign"></span> ' . Yii::t('frontend/advert', 'Advert was published') . '</h2>',
        'toggleButton' => false,
    ]);
    ?>
    Поздравляем, ваше объявление успешно опубликовано!
    <br /><br />
    Постоянная ссылка для просмотра объявления:
    <a href="<?=Url::toRoute(['details', 'id' => $model->id])?>"><?=Url::toRoute(['details', 'id' => $model->id], true)?></a>.<br />
    <br />
    После окончания публикации вам придет уведомление с предложением зарегистрироваться на сайте и публиковать
    объявления без ограничений.<br />
    Дата окончания публикации: <strong><?=Date::formatDate($model->published_to)?></strong>.<br />
    <br /><br />
    <?php
    Modal::end();
    JS::begin();
    ?>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#advertWasPublishedModal').modal('toggle');
        });
    </script>
    <?php
    JS::end();
    ?>
<?php endif; ?>
