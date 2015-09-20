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
        'http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css',
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
