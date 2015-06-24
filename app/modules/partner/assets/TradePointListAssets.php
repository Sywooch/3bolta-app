<?php
namespace partner\assets;

use yii\web\AssetBundle;

/**
 * Список торговых точек пользователя
 */
class TradePointListAssets extends AssetBundle
{
    public $sourcePath = '@partner/_assets/trade-point-list/';
    public $baseUrl = '@web/assets';
    public $css = [];
    public $js = [
        'js/app.js',
    ];
    public $depends = [
        'app\assets\FrontendAssets',
    ];
}
