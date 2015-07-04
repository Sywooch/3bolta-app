<?php
namespace advert\assets;

/**
 * Ассеты для виджета AdvertImageInput
 */
class AdvertImageInput extends \yii\web\AssetBundle
{
    public $sourcePath = '@advert/_assets/advert-image-input';

    public $baseUrl = '@web';

    public $css = [
        'css/styles.css',
    ];

    public $js = [
        'js/app.js',
    ];

    public $depends = [
        '\app\assets\FrontendAssets',
    ];
}