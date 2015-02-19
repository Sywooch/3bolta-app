<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use advert\models\Advert;
use advert\forms\Form;
use kartik\widgets\FileInput;
use yii\bootstrap\Modal;
use app\widgets\JS;

/* @var $form advert\forms\Form */
/* @var $this yii\base\View */
/* @var $form yii\bootstrap\ActiveForm */
?>
<div class="col-xs-12">
    <?=Html::tag('h2', Yii::t('frontend/advert', 'Append advert'))?>
</div>

<div class="col-xs-12 block-info block-info-primary">
    Вы не авторизованы на сайте.<br />
    Неавторизованные пользователи могут опубликовать только одно объявление на ограниченный срок без возможности продления. Подробнее <a href="#">здесь</a>.<br />
    Вы можете <a href="#">зарегистрироваться</a> или <a href="#">авторизоваться</a> на сайте, чтобы публиковать объявления без ограничений.
</div>

<?php
$form = ActiveForm::begin([
    'id' => 'create-advert',
    'enableClientValidation' => false,
    'enableAjaxValidation' => true,
]);
?>
    <div class="col-sm-12 col-lg-4">
        <?=$form->field($model, 'name')->textInput()?>
    </div>
    <div class="col-sm-12 col-lg-4">
        <?=$form->field($model, 'category_id')->dropDownList(Advert::getCategoryDropDownList(true))?>
    </div>
    <div class="col-sm-12 col-lg-2">
        <?=$form->field($model, 'condition_id')->dropDownList(Advert::getConditionDropDownList(true))?>
    </div>
    <div class="col-sm-12 col-lg-2">
        <?=$form->field($model, 'price')->textInput()?>
    </div>

    <div class="col-xs-12">
        <?=Html::tag('h3', Yii::t('frontend/advert', 'Images'))?>
    </div>

    <div class="col-sm-12">
        <?=$form->field($model, 'uploadImage')->widget(FileInput::className(), [
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
                'overwriteInitial' => false,
            ],
        ])?>
    </div>

    <div class="col-xs-12">
        <?=Html::tag('h3', Yii::t('frontend/advert', 'Automobiles'))?>
    </div>

    <div class="col-xs-12 block-info block-info-primary">
        Обязательным выбором обладает марка. Вы можете выбрать не более 10 марок автомобилей и не более 10 моделей. На кузова и двигатели ограчений нет.
    </div>

    <div class="col-xs-12">
        <?=$form->field($model, 'mark', [
            'template' => '{input}{error}',
        ])->hiddenInput(['value' => ''])?>
    </div>

    <div class="col-sm-12">
        <?=$this->render('_choose_auto', [
            'form' => $form,
            'model' => $model,
        ])?>
    </div>

    <div class="col-sm-12">
        <?=$form->field($model, 'description')->textarea(['maxlength' => Form::DESCRIPTION_MAX_LENGTH])?>
    </div>

    <div class="col-xs-12">
        <?=Html::tag('h3', Yii::t('frontend/advert', 'Contacts'))?>
    </div>

    <div class="col-sm-12 col-lg-4">
        <?=$form->field($model, 'user_name')->textInput()?>
    </div>

    <div class="col-sm-12 col-lg-4">
        <?=$form->field($model, 'user_phone')->textInput()?>
    </div>

    <div class="col-sm-12 col-lg-4">
        <?=$form->field($model, 'user_email')->textInput()?>
    </div>

    <div class="col-xs-12">
        <?=Html::submitButton(Yii::t('frontend/advert', 'Create advert'), ['class' => 'btn btn-success']);?>
    </div>
<?php $form->end(); ?>

<?php print $this->render('_success_created'); ?>