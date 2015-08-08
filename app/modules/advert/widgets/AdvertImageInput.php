<?php
namespace advert\widgets;

use advert\assets\AdvertImageInput as AdvertImageInputAssets;
use advert\models\PartAdvert;
use advert\models\PartAdvertImage;
use kartik\widgets\FileInput;
use storage\models\File;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Инпут для добавления и редактирования изображений объявления.
 * Является оберткой для FileInput.
 */
class AdvertImageInput extends FileInput
{
    /**
     * @var PartAdvertImage[]
     */
    public $existsImages = [];

    /**
     * @var mixed ссылка для удаления фотографий
     */
    public $removeImageUrl;

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
        $this->pluginOptions['initialPreviewConfig'] = [];
        foreach ($this->existsImages as $image) {
            if ($image instanceof PartAdvertImage && $preview = $image->preview) {
                /* @var $preview File */
                $this->pluginOptions['initialPreviewConfig'][] = [
                    'catpion' => basename($preview->real_name),
                    'url' => Url::to($this->removeImageUrl),
                    'key' => $image->id,
                    'removeClass' => 'test',
                ];
                $this->pluginOptions['initialPreview'][] = Html::img($preview->getUrl());
            }
        }
        $this->pluginOptions = ArrayHelper::merge([
            'multiple' => 'multiple',
            'maxFileCount' => PartAdvert::UPLOAD_MAX_FILES,
            'allowedFileExtensions' => PartAdvert::$_imageFileExtensions,
            'layoutTemplates' => [
                'actions' => '{delete}',
                'footer' => '<div class="file-thumbnail-footer">'
                    . '{actions}'
                    . '</div>',
            ],
            'previewTemplates' => [
                'image' => '<div class="file-preview-frame{frameClass}" id="{previewId}" data-fileindex="{fileindex}">'
                                . '<div class="file-preview-frame-img" style="width: ' . PartAdvertImage::PREVIEW_WIDTH . 'px;">'
                                . '<img src="{data}" class="file-preview-image" title="{caption}" alt="{caption}" style="width:{width};height:{height}">'
                                . '</div>'
                                . '{footer}'
                                . '</div>',
            ],
            'initialPreviewShowDelete' => true,
            'previewSettings' => [
                'image' => [
                    'width' => 'auto',
                    'height' => PartAdvertImage::PREVIEW_HEIGHT . 'px'
                ]
            ],
            'fileActionSettings' => [
                'removeIcon' => '<i class="glyphicon glyphicon-remove"></i> ' . \Yii::t('main', 'Remove'),
                'removeTitle' => Yii::t('main', 'Remove file'),
            ],
            'uploadUrl' => '',
            'isUploadable' => false,
            'showRemove' => true,
            'showUpload' => false,
            'overwriteInitial' => false,
            'dropZoneTitle' => Yii::t('main', 'Drag & drop files here for upload'),
            'ajaxDeleteSettings' => [
                'type' => 'post',
            ],
            'deleteExtraData' => [
                Yii::$app->request->csrfParam => Yii::$app->request->csrfToken,
            ]
        ], $this->pluginOptions);
        parent::init();
    }

    /**
     * Зарегистрировать ассеты
     */
    public function registerAssets()
    {
        parent::registerAssets();
        AdvertImageInputAssets::register($this->view);
    }
}