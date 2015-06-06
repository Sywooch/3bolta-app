<?php
namespace partner\assets;

use yii\web\AssetBundle;

/**
 * Список торговых точек пользователя
 */
class TradePointListAssets extends AssetBundle
{
    public $sourcePath = '@partner/_assets';
    public $baseUrl = '@web/assets';
    public $css = [];
    public $js = [
        'js/trade-point-list.js',
    ];
    public $depends = [
        'app\assets\FrontendAssets',
    ];
}
