<?php
namespace advert\widgets;

use advert\models\Advert;
use advert\models\AdvertImage;
use kartik\widgets\FileInput;
use storage\models\File;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use advert\assets\AdvertImageInput as AdvertImageInputAssets;

/**
 * Инпут для добавления и редактирования изображений объявления.
 * Является оберткой для FileInput.
 */
class AdvertImageInput extends FileInput
{
    /**
     * @var AdvertImage[]
     */
    public $existsImages = [];

    /**
     * Инициализация по умолчанию
     */
    public function init()
    {
        // опции по умолчанию
        $this->options = ArrayHelper::merge([
            'accept' => 'image/*',
            'multiple' => true,
            'name' => Html::getInputName($this->model, $this->attribute) . '[]',
        ], $this->options);

        // подключить существующие изображения
        $this->pluginOptions['initialPreview'] = [];
        foreach ($this->existsImages as $image) {
            if ($image instanceof AdvertImage && $preview = $image->preview) {
                /* @var $preview File */
                $this->pluginOptions['initialPreview'][] = Html::img($preview->getUrl());
            }
        }
        $this->pluginOptions = ArrayHelper::merge([
            'multiple' => 'multiple',
            'uploadUrl' => '/',
            'maxFileCount' => Advert::UPLOAD_MAX_FILES,
            'allowedFileExtensions' => Advert::$_imageFileExtensions,
            'layoutTemplates' => [
                'actions' => '{delete}',
            ],
            'previewTemplates' => [
                'image' => '<div class="file-preview-frame{frameClass}" id="{previewId}" data-fileindex="{fileindex}">'
                                . '<div class="file-preview-frame-img" style="width: ' . AdvertImage::PREVIEW_WIDTH . 'px;">'
                                . '<img src="{data}" class="file-preview-image" title="{caption}" alt="{caption}" style="width:{width};height:{height}">'
                                . '</div>'
                                . '{footer}'
                                . '</div>',
            ],
            'previewSettings' => [
                'image' => [
                    'width' => 'auto',
                    'height' => AdvertImage::PREVIEW_HEIGHT . 'px'
                ]
            ],
            'fileActionSettings' => [
                'removeIcon' => '<i class="glyphicon glyphicon-remove"></i> ' . \Yii::t('main', 'Remove'),
                'removeTitle' => \Yii::t('main', 'Remove file'),
            ],
            'showRemove' => true,
            'showUpload' => false,
            'overwriteInitial' => false,
            'dropZoneTitle' => Yii::t('main', 'Drag & drop files here for upload'),
        ], $this->pluginOptions);
        parent::init();
    }

    /**
     * Зарегистрировать ассеты
     */
    public function registerAssets()
    {
        AdvertImageInputAssets::register($this->view);
        return parent::registerAssets();
    }
}