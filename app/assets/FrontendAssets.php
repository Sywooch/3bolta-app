<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * Ассеты для фронтенд-приложения
 */
class FrontendAssets extends AssetBundle
{
    public $sourcePath = '@app/_assets';
    public $baseUrl = '@web';
    public $css = [
        'css/styles.css',
        'css/sidebar.simple.css',
    ];
    public $js = [
        'js/lodash.min.js',
        'js/loader.js',
        'js/app.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'app\assets\BootstrapSelectAssets',
        'app\assets\FontelloAssets',
    ];
}
