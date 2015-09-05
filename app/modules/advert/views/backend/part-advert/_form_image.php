<?php
use kartik\widgets\FileInput;
use advert\models\Advert;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model advert\models\PartCategory */
/* @var $form yii\bootstrap\ActiveForm */

$exists = [];

foreach ($model->getImages()->all() as $file) {
    /* @var $file \advert\models\AdvertImage */
    $exists[] = Html::img($file->file->getUrl(), [
        'class' => 'file-preview-image',
    ]);
}
print $form->field($model, 'uploadImage')->widget(FileInput::className(), [
    'options' => [
        'accept' => 'image/*',
        'multiple' => true,
        'name' => Html::getInputName($model, 'uploadImage') . '[]',
    ],
    'pluginOptions' => [
        'initialPreview' => $exists,
        'uploadUrl' => 'ss',
        'multiple' => 'multiple',
        'maxFileCount' => Advert::UPLOAD_MAX_FILES,
        'allowedFileExtensions' => Advert::$_imageFileExtensions,
        'layoutTemplates' => [
            'actions' => '{delete}',
        ],
        'overwriteInitial' => false,
    ],
]);
