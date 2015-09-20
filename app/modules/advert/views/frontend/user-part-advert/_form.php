<?php
/**
 * Форма редактирования/добавления объявления
 */

use advert\assets\AdvertForm;
use advert\forms\PartForm;
use advert\models\Advert;
use advert\models\PartParam;
use advert\models\Part;
use advert\widgets\AdvertImageInput;
use app\assets\FrontendAssets;
use app\widgets\ItemsList;
use user\models\User;
use yii\base\View;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$frontendAssets = new FrontendAssets();
$assetsUrl = Yii::$app->assetManager->getPublishedUrl($frontendAssets->sourcePath);

/* @var $this View */
/* @var $model PartForm */
/* @var $user User */

AdvertForm::register($this);

$model->resetOutputValues();

$form = ActiveForm::begin([
    'id' => 'create-advert',
    'validateOnSubmit' => true,
    'validateOnChange' => false,
    'validateOnType' => false,
    'validateOnBlur' => false,
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
                        'maxlength' => PartParam::CATALOGUE_NUMBER_MAX_LENGTH,
                    ],
                ])->textInput()?>
            </div>
            <div class="clearfix"></div>
            <div class="col-sm-12 col-md-6">
                <?=$form->field($model, 'category_id', [
                    'parts' => ['{icon}' => '<span class="form-control-icon icon-tag"></span>'],
                    'inputOptions' => [
                        'class' => 'form-control form-control-with-icon',
                        'placeholder' => Yii::t('frontend/advert', 'Part category'),
                    ],
                ])->dropDownList(Part::getCategoryDropDownList(true))?>
            </div>
            <div class="col-sm-12 col-md-6">
                <?=$form->field($model, 'condition_id', [
                    'parts' => ['{icon}' => '<span class="form-control-icon icon-wrench"></span>'],
                    'inputOptions' => [
                        'class' => 'form-control form-control-with-icon',
                        'placeholder' => Yii::t('frontend/advert', 'Part condition'),
                    ],
                ])->dropDownList(Part::getConditionDropDownList(true))?>
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
        ])->widget(AdvertImageInput::className(), [
            'existsImages' => $model->getExists() ? $model->getExists()->images : [],
            'removeImageUrl' => $model->getExists() ? ['remove-advert-image', 'id' => $model->getExists()->id] : '',
        ])?>
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
            <?=$this->render('_form_choose_auto', [
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
            <?php if ($user->type != User::TYPE_LEGAL_PERSON):?>
                <div class="col-md-12">
                    <?=Html::tag('h3', Yii::t('frontend/advert', 'Contacts'))?>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        В объявлении будет отображаться следующая контактная информация:<br /><br />

                        <strong><?=$model->getAttributeLabel('user_name')?></strong><br />
                        <?=Html::encode($user->name)?><br /><br />
                        <strong><?=$model->getAttributeLabel('user_phone')?></strong><br />
                        <?=Html::encode($user->phone)?><br /><br />

                        Для редактирования контактной информации используйте <a href="<?=Url::toRoute(['/user/profile/index'])?>">профиль</a>.
                    </div>
                </div>
            <?php else:?>
                <div class="col-md-12">
                    <?=Html::tag('h3', Yii::t('frontend/advert', 'Trade point'))?>
                </div>
                <?php $tradePoints = $model->getTradePointsDropDown(); ?>
                <?php if (empty($tradePoints)):?>
                    <div class="col-md-12">
                        <p>
                            В настройках вашей организации не добавлено еще ни одной торговой точки.
                            Пожалуйста, пройдите в <a href="<?=Url::toRoute(['/partner/partner/index'])?>">настройки организации</a>
                            и добавьте хотя бы одну торговую точку.
                        </p>
                    </div>
                <?php else:?>
                    <div class="col-md-12 advert-form-trade-point">
                        Выберите торговую точку из списка:
                        <?=$form->field($model, 'trade_point_id')->widget(ItemsList::className(), [
                            'items' => $model->getTradePointsDropDown(),
                        ])?>
                    </div>
                <?php endif;?>
            <?php endif;?>

            <div class="col-md-12">
                <?=$form->field($model, 'allow_questions')->checkbox()?>
            </div>

            <div class="col-md-12">
                <?php
                $button = $model->getExists() ? Yii::t('frontend/advert', 'Update advert') : Yii::t('frontend/advert', 'Place an advert');
                ?>
                <?=Html::submitButton($button, ['class' => 'btn btn-primary'])?>
            </div>
        </div>
    </div>
<?php $form->end(); ?>