<?php
use kartik\widgets\FileInput;
use advert\models\PartAdvert;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model advert\models\PartCategory */
/* @var $form yii\bootstrap\ActiveForm */

$exists = [];

foreach ($model->getImages()->all() as $file) {
    /* @var $file \advert\models\PartAdvertImage */
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
        'maxFileCount' => PartAdvert::UPLOAD_MAX_FILES,
        'allowedFileExtensions' => PartAdvert::$_imageFileExtensions,
        'layoutTemplates' => [
            'actions' => '{delete}',
        ],
        'overwriteInitial' => false,
    ],
]);
