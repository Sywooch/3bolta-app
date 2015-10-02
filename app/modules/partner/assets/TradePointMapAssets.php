<?php
namespace partner\assets;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;

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
        'kalyabin\maplocation\GoogleMapAssets',
        'app\assets\FrontendAssets',
    ];
}
