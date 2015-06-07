<?php
/**
 * Форма редактирования/добавления объявления
 */

use yii\helpers\Url;
use advert\forms\Form;
use kartik\widgets\FileInput;
use advert\models\Advert;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use advert\assets\AdvertForm;

use app\assets\FrontendAssets;

$frontendAssets = new FrontendAssets();
$assetsUrl = Yii::$app->assetManager->getPublishedUrl($frontendAssets->sourcePath);

/* @var $this \yii\base\View */
/* @var $model \advert\forms\Form */
/* @var $user \user\models\User */

AdvertForm::register($this);

$existImages = [];
if ($advert = $model->getExists()) {
    foreach ($advert->images as $file) {
        /* @var $file \advert\models\AdvertImage */
        $existImages[] = Html::img($file->file->getUrl(), [
            'class' => 'file-preview-image',
        ]);
    }
}

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
        <div class="advert-form-common col-lg-12">
            <div class="col-lg-12">
                <?=$form->field($model, 'name', [
                    'parts' => ['{icon}' => '<span class="form-control-icon glyphicon glyphicon-bullhorn"></span>'],
                    'inputOptions' => [
                        'class' => 'form-control form-control-with-icon',
                        'placeholder' => Yii::t('frontend/advert', 'Advert title'),
                    ],
                ])->textInput()?>
            </div>
            <div class="col-sm-12 col-lg-12">
                <?=$form->field($model, 'category_id', [
                    'parts' => ['{icon}' => '<span class="form-control-icon glyphicon glyphicon-tag"></span>'],
                    'inputOptions' => [
                        'class' => 'form-control form-control-with-icon',
                        'placeholder' => Yii::t('frontend/advert', 'Part category'),
                    ],
                ])->dropDownList(Advert::getCategoryDropDownList(true))?>
            </div>
            <div class="col-sm-12 col-lg-6">
                <?=$form->field($model, 'condition_id', [
                    'parts' => ['{icon}' => '<span class="form-control-icon glyphicon glyphicon-wrench"></span>'],
                    'inputOptions' => [
                        'class' => 'form-control form-control-with-icon',
                        'placeholder' => Yii::t('frontend/advert', 'Part condition'),
                    ],
                ])->dropDownList(Advert::getConditionDropDownList(true))?>
            </div>
            <div class="col-sm-12 col-lg-6">
                <?=$form->field($model, 'price', [
                    'parts' => ['{icon}' => '<span class="form-control-icon glyphicon glyphicon-ruble"></span>'],
                    'inputOptions' => [
                        'class' => 'form-control form-control-with-icon',
                        'placeholder' => Yii::t('frontend/advert', 'Part price'),
                    ],
                ])->textInput()?>
            </div>
        </div>
    </div>

    <div class="col-lg-12">
        <?=Html::tag('h3', Yii::t('frontend/advert', 'Images'))?>
    </div>

    <div class="col-lg-12">
        <?=$form->field($model, 'uploadImage', [
            'template' => '{input}',
        ])->widget(FileInput::className(), [
            'options' => [
                'accept' => 'image/*',
                'multiple' => true,
                'name' => Html::getInputName($model, 'uploadImage') . '[]',
            ],
            'pluginOptions' => [
                'uploadUrl' => 'ss',
                'multiple' => 'multiple',
                'maxFileCount' => Advert::UPLOAD_MAX_FILES,
                'allowedFileExtensions' => Advert::$_imageFileExtensions,
                'layoutTemplates' => [
                    'actions' => '{delete}',
                ],
                'showUpload' => false,
                'overwriteInitial' => false,
                'dropZoneTitle' => Yii::t('main', 'Drag & drop files here for upload'),
            ],
        ])?>
    </div>

    <div class="col-lg-12">
        <?=Html::tag('h3', Yii::t('frontend/advert', 'Automobiles'))?>
    </div>

    <div class="col-lg-12">
        <?=$form->field($model, 'mark', [
            'template' => '{input}{error}',
        ])->hiddenInput(['value' => ''])?>
    </div>

    <div class="no-content-margin">
        <div class="col-lg-12 advert-form-block-info">
            <img src="<?=$assetsUrl?>/img/warning-2.png" align="left" />
            Обязательным выбором обладает марка. Вы можете выбрать не более 10 марок автомобилей и не более 10 моделей. На кузова и двигатели ограчений нет.
        </div>
    </div>

    <div class="no-content-margin">
        <div class="col-lg-12 advert-form-auto">
            <?=$this->render('_form_choose_auto', [
                'form' => $form,
                'model' => $model,
            ])?>

            <div class="col-lg-12">
                <?=$form->field($model, 'description')->textarea([
                    'placeholder' => Yii::t('frontend/advert', 'Description'),
                    'maxlength' => Form::DESCRIPTION_MAX_LENGTH
                ])?>
            </div>
        </div>
    </div>

    <div class="no-content-margin">
        <div class="advert-form-contacts">
            <div class="col-lg-12">
                <?=Html::tag('h3', Yii::t('frontend/advert', 'Contacts'))?>
            </div>

            <div class="col-lg-12">
                <div class="form-group">
                    В объявлении будет отображаться следующая контактная информация:<br /><br />

                    <strong><?=$model->getAttributeLabel('user_name')?></strong><br />
                    <?=Html::encode($user->name)?><br /><br />
                    <strong><?=$model->getAttributeLabel('user_phone')?></strong><br />
                    <?=Html::encode($user->phone)?><br /><br />

                    Для редактирования контактной информации используйте <a href="<?=Url::toRoute(['/user/profile/index'])?>">профиль</a>.
                </div>
            </div>

            <div class="col-lg-12">
                <?php
                $button = $model->getExists() ? Yii::t('frontend/advert', 'Update advert') : Yii::t('frontend/advert', 'Create advert');
                ?>
                <?=Html::submitButton($button, ['class' => 'btn btn-primary'])?>
            </div>
        </div>
    </div>
<?php $form->end(); ?>