<?php

use advert\assets\AdvertForm;
use advert\forms\PartForm;
use advert\models\Advert;
use advert\models\AdvertPartParam;
use advert\models\PartAdvert;
use advert\widgets\AdvertImageInput;
use app\assets\FrontendAssets;
use app\components\PhoneValidator;
use yii\base\View;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\widgets\MaskedInput;

$frontendAssets = new FrontendAssets();
$assetsUrl = Yii::$app->assetManager->getPublishedUrl($frontendAssets->sourcePath);

AdvertForm::register($this);

$model->resetOutputValues();

/* @var $form PartForm */
/* @var $this View */
/* @var $form ActiveForm */
?>
<div class="col-lg-12">
    <?=Html::tag('h1', Yii::t('frontend/advert', 'Place an advert'))?>
</div>

<div class="no-content-margin">
    <div class="col-lg-12 advert-form-block-info">
        <img src="<?=$assetsUrl?>/img/warning-1.png" align="left" />
        Вы не авторизованы на сайте.<br />
        Неавторизованные пользователи могут опубликовать только одно объявление на ограниченный срок без возможности продления. Подробнее <a href="#">здесь</a>.<br />
        Вы можете <a href="#">зарегистрироваться</a> или <a href="#">авторизоваться</a> на сайте, чтобы публиковать объявления без ограничений.
    </div>
</div>

<?php
$form = ActiveForm::begin([
    'id' => 'create-advert',
    'enableClientValidation' => false,
    'enableAjaxValidation' => true,
    'options' => [
        'enctype' => 'multipart/form-data',
    ],
    'fieldConfig' => [
        'template' => '{input}{icon}{error}',
        'parts' => ['{icon}' => ''],
    ]
]);
?>
    <div class="no-content-margin">
        <div class="advert-form-common col-md-12">
            <div class="col-sm-12 col-md-6">
                <?=$form->field($model, 'name', [
                    'parts' => ['{icon}' => '<span class="form-control-icon icon-megaphone"></span>'],
                    'inputOptions' => [
                        'class' => 'form-control form-control-with-icon',
                        'placeholder' => Yii::t('frontend/advert', 'Part name'),
                        'maxlength' => Advert::NAME_MAX_LENGTH,
                    ],
                ])->textInput()?>
            </div>
            <div class="col-sm-12 col-md-6">
                <?=$form->field($model, 'catalogue_number', [
                    'parts' => ['{icon}' => '<span class="form-control-icon icon-barcode"></span>'],
                    'inputOptions' => [
                        'class' => 'form-control form-control-with-icon',
                        'placeholder' => Yii::t('frontend/advert', 'Catalogue number'),
                        'maxlength' => AdvertPartParam::CATALOGUE_NUMBER_MAX_LENGTH,
                    ],
                ])->textInput()?>
            </div>
            <div class="col-sm-12 col-md-6">
                <?=$form->field($model, 'category_id', [
                    'parts' => ['{icon}' => '<span class="form-control-icon icon-tag"></span>'],
                    'inputOptions' => [
                        'class' => 'form-control form-control-with-icon',
                        'placeholder' => Yii::t('frontend/advert', 'Part category'),
                    ],
                ])->dropDownList(PartAdvert::getCategoryDropDownList(true))?>
            </div>
            <div class="col-sm-12 col-md-6">
                <?=$form->field($model, 'condition_id', [
                    'parts' => ['{icon}' => '<span class="form-control-icon icon-wrench"></span>'],
                    'inputOptions' => [
                        'class' => 'form-control form-control-with-icon',
                        'placeholder' => Yii::t('frontend/advert', 'Part condition'),
                    ],
                ])->dropDownList(PartAdvert::getConditionDropDownList(true))?>
            </div>
            <div class="clearfix"></div>
            <div class="col-sm-12 col-md-6">
                <?=$form->field($model, 'region_id', [
                    'parts' => ['{icon}' => '<span class="form-control-icon icon-location"></span>'],
                    'inputOptions' => [
                        'class' => 'form-control form-control-with-icon js-select-region-dropdown',
                        'data-live-search' => 'true',
                        'placeholder' => Yii::t('frontend/advert', 'Region'),
                    ],
                ])->dropDownList(PartForm::getRegionsDropDownList())?>
            </div>
            <div class="col-sm-12 col-md-6">
                <?=$form->field($model, 'price', [
                    'parts' => ['{icon}' => '<span class="form-control-icon icon-rouble"></span>'],
                    'inputOptions' => [
                        'class' => 'form-control form-control-with-icon',
                        'placeholder' => Yii::t('frontend/advert', 'Part price'),
                    ],
                ])->textInput()?>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <?=Html::tag('h3', Yii::t('frontend/advert', 'Images'))?>
    </div>


    <div class="col-md-12">
        <?=$form->field($model, 'uploadImage', [
            'template' => '{input}',
        ])->widget(AdvertImageInput::className())?>
    </div>

    <div class="col-md-12">
        <?=Html::tag('h3', Yii::t('frontend/advert', 'Automobiles'))?>
    </div>

    <div class="col-md-12">
        <?=$form->field($model, 'mark', [
            'template' => '{input}{error}',
        ])->hiddenInput(['value' => ''])?>
    </div>

    <div class="no-content-margin">
        <div class="col-md-12 advert-form-block-info">
            <img src="<?=$assetsUrl?>/img/warning-2.png" align="left" />
            Обязательным выбором обладает марка. Вы можете выбрать не более 10 марок автомобилей и не более 10 моделей. На кузова и двигатели ограчений нет.
        </div>
    </div>

    <div class="no-content-margin">
        <div class="col-md-12 advert-form-auto">
            <?=$this->render('_choose_auto', [
                'form' => $form,
                'model' => $model,
            ])?>

            <div class="col-md-12">
                <?=$form->field($model, 'description')->textarea([
                    'placeholder' => Yii::t('frontend/advert', 'Description'),
                    'maxlength' => Advert::DESCRIPTION_MAX_LENGTH
                ])?>
            </div>
        </div>
    </div>

    <div class="no-content-margin">
        <div class="advert-form-contacts">
            <div class="col-md-12">
                <?=Html::tag('h3', Yii::t('frontend/advert', 'Contacts'))?>
            </div>

            <div class="col-sm-12 col-md-4">
                <?=$form->field($model, 'user_name', [
                    'parts' => ['{icon}' => '<span class="form-control-icon icon-user"></span>'],
                    'inputOptions' => [
                        'class' => 'form-control form-control-with-icon',
                        'placeholder' => Yii::t('frontend/advert', 'Contact face'),
                    ],
                ])->textInput()?>
            </div>

            <div class="col-sm-12 col-md-4">
                <?=$form->field($model, 'user_phone', [
                    'errorOptions' => [
                        'encode' => false,
                    ],
                    'parts' => ['{icon}' => '<span class="form-control-icon icon-phone"></span>'],
                ])->widget(MaskedInput::className(), [
                    'mask' => PhoneValidator::PHONE_MASK,
                    'options' => [
                        'class' => 'form-control form-control-with-icon',
                        'placeholder' => Yii::t('frontend/advert', 'Contact phone'),
                    ]
                ])?>
            </div>

            <div class="col-sm-12 col-md-4">
                <?=$form->field($model, 'user_email', [
                    'errorOptions' => [
                        'encode' => false,
                    ],
                    'parts' => ['{icon}' => '<span class="form-control-icon icon-mail-alt"></span>'],
                    'inputOptions' => [
                        'class' => 'form-control form-control-with-icon',
                        'placeholder' => Yii::t('frontend/advert', 'E-mail'),
                    ],
                ])->textInput()?>
            </div>

            <div class="col-md-12">
                <?=$form->field($model, 'allow_questions')->checkbox()?>
            </div>

            <div class="col-xs-12">
                <?=Html::submitButton(Yii::t('frontend/advert', 'Place an advert'), ['class' => 'btn btn-primary']);?>
            </div>
        </div>
    </div>
<?php $form->end(); ?>

<?php print $this->render('_success_created'); ?>