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
    ];
    public $js = [
        'js/app.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
