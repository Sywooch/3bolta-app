<?php
/**
 * Вывод результов поиска
 */

use advert\assets\AdvertDetail;
use advert\assets\AdvertList;
use advert\components\PartsSearchApi;
use advert\forms\AnswerForm;
use advert\forms\QuestionForm;
use advert\models\Contact;
use advert\models\Image;
use advert\models\Part;
use advert\models\PartParam;
use app\helpers\Date;
use app\widgets\JS;
use app\widgets\Modal;
use geo\models\Region;
use partner\models\Partner;
use partner\models\TradePoint;
use sammaye\solr\SolrDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $model Part */
/* @var $questionForm QuestionForm */
/* @var $answerForm AnswerForm */
/* @var $searchApi PartsSearchApi */
$searchApi = Yii::$app->getModule('advert')->partsSearch;

/* @var $related SolrDataProvider */
$related = $searchApi->getRelated($model);

// ссылки на автомобили
$automobiles = $searchApi->getAutomobilesLink(['search'], $searchApi->getAdvertAutomobileTree($model));

/* @var $partParam PartParam */
$partParam = $model->partParam;

/* @var $contact Contact */
$contact = $model->contact;

AdvertDetail::register($this, $questionForm instanceof QuestionForm, $answerForm instanceof AnswerForm);
?>

<div class="item-details-title col-md-9 col-xs-12">
    <h2><?=Html::encode($model->advert_name)?></h2>
    <div class="item-details-date">
        <i class="publish-date">
            <?=Yii::t('frontend/advert', 'Published at')?>
            <?=$model->getPublishedFormatted()?>
        </i>
    </div>
</div>
<div class="item-details-price col-md-2 col-xs-12">
    <span class="label label-primary">
        <i class="icon-rouble"></i>
        <?=$model->getPriceFormated()?>
    </span>
</div>
<?php if ($partParam instanceof PartParam && $partParam->catalogue_number):?>
    <div class="item-details-row item-details-catalogue-number col-xs-12">
        <i class="icon-barcode"></i>
        <?=Html::encode($partParam->catalogue_number)?>
    </div>
<?php endif;?>
<div class="item-details-row item-details-condition col-xs-12">
    <i class="icon-wrench"></i>
    <?=$model->getConditionName()?>, <?=implode(', ', $model->getCategoriesTree())?>
</div>
<?php if (!empty($automobiles)):?>
    <div class="item-details-row item-details-automobiles col-xs-12">
        <i class="icon-cab"></i>
        <?=implode(', ', $automobiles)?>
    </div>
<?php endif;?>

<?php if ($images = $model->images):?>
    <div class="item-details-images item-details-row no-content-margin">
        <div class="item-details-images-full js-item-image-full">
            <div class="prev js-item-image-prev"><span class="glyphicon glyphicon-chevron-left"></span></div>
            <div class="next js-item-image-next"><span class="glyphicon glyphicon-chevron-right"></span></div>
            <?=Html::img(reset($images)->getUrl('image'), [
                'class' => 'full-image',
            ])?>
        </div>
        <?php if (count($images) > 1):?>
            <div class="item-details-images-list js-item-image-list">
                <?php foreach ($images as $k => $image):?>
                    <?php
                    /* @var $image Image */
                    ?>
                    <div>
                        <a href="<?=$image->getUrl('image')?>" class="thumbnail<?php if ($k == 0):?> active<?php endif;?>">
                            <?=Html::img($image->getUrl('thumbnail'))?>
                        </a>
                    </div>
                <?php endforeach;?>
            </div>
        <?php endif;?>
    </div>
<?php endif;?>


<?php if ($contact instanceof Contact && $tradePoint = $contact->tradePoint):?>
    <?php
    /* @var $tradePoint TradePoint */
    /* @var $partner Partner */
    $partner = $tradePoint->partner;
    ?>
    <div class="item-details-row item-details-contacts col-xs-12">
        <i class="icon-user"></i>
        <?=Html::encode($partner->name)?>
    </div>
    <div class="item-details-row item-details-contacts col-xs-12">
        <i class="icon-phone"></i>
        <?=Html::encode($tradePoint->phone)?>
    </div>
    <div class="item-details-row item-details-contacts col-xs-12">
        <i class="icon-location"></i>
        <?=Html::encode($tradePoint->address)?>
    </div>
<?php else:?>
    <div class="item-details-row item-details-contacts col-md-4 col-xs-12">
        <i class="icon-user"></i>
        <?=$model->getSeller(false)?>
        (<?=Yii::t('frontend/advert', 'private person')?>)
    </div>
    <div class="item-details-row item-details-contacts col-md-4 col-xs-12">
        <i class="icon-phone"></i>
        <?=Html::encode($model->getUserPhone())?>
    </div>
    <?php if ($contact instanceof Contact && $region = $contact->region):?>
        <?php
        /* @var $region Region */
        ?>
        <div class="item-details-row item-details-contacts col-md-4 col-xs-12">
            <i class="icon-location"></i>
            <?=Html::encode($region->site_name)?>
        </div>
    <?php endif;?>
<?php endif;?>

<?php if ($questionForm instanceof QuestionForm):?>
    <div class="item-details-row item-details-question col-xs-12">
        <?=$this->render('_details_message', [
            'model' => $model,
            'questionForm' => $questionForm,
        ])?>
    </div>
<?php endif;?>

<?php if ($answerForm instanceof AnswerForm):
    print $this->render('_details_answer', [
        'model' => $model,
        'answerForm' => $answerForm,
    ]);
endif;?>

<?php if (!empty($model->description)):?>
    <div class="col-xs-12 item-details-row item-details-description">
        <?=nl2br(Html::encode($model->description))?>
    </div>
<?php endif;?>
<?php if ($related->getCount() > 0 && $relatedList = $related->getModels()):?>
    <?php AdvertList::register($this); ?>
    <div class="col-xs-12"><h2><?=Yii::t('frontend/advert', 'Related adverts')?></h2></div>
    <div class="item-details-related">
        <?php foreach ($relatedList as $advert):?>
            <div class="col-lg-6 col-xs-12 list-item">
                <?=$this->render('_list_item', [
                    'model' => $advert,
                    'dataProvider' => $related,
                    'hideDropDown' => true,
                ])?>
            </div>
        <?php endforeach;?>
    </div>
<?php endif;?>

<?php if (($id = Yii::$app->session->getFlash('advert_published')) && $id == $model->id):?>
    <?php
    Modal::begin([
        'id' => 'advertWasPublishedModal',
        'title' => Yii::t('frontend/advert', 'Advert was published'),
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
