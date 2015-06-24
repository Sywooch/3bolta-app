<?php
namespace partner\assets;

use yii\web\AssetBundle;

/**
 * Поиск торговых точек
 */
class TradePointMapAssets extends AssetBundle
{
    public $sourcePath = '@partner/_assets/map/';
    public $baseUrl = '@web/assets';
    public $css = [
        'css/styles.css'
    ];
    public $js = [
        'js/app.js',
    ];
    public $depends = [
        'app\assets\GoogleMapAssets',
        'app\assets\FrontendAssets',
    ];
}
