<?php
namespace advert\assets;

/**
 * Ассетсы для списка объявлений
 */
class AdvertList extends \yii\web\AssetBundle
{
    public $sourcePath = '@advert/_assets/list';

    public $baseUrl = '@web';

    public $css = [
        'css/styles.css',
    ];

    public $js = [
        'js/app.js',
    ];

    public $depends = [
        'app\assets\FrontendAssets',
    ];
}