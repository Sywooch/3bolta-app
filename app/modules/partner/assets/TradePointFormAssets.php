<?php
namespace partner\assets;

use yii\web\AssetBundle;

/**
 * Форма редактирования/создания торговой точки
 */
class TradePointFormAssets extends AssetBundle
{
    public $sourcePath = '@partner/_assets';
    public $baseUrl = '@web/assets';
    public $css = [];
    public $js = [
        'js/trade-point-form.js',
    ];
    public $depends = [
        'app\assets\FrontendAssets',
    ];
}
