<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * Ассеты для главной страницы
 */
class MainPageAssets extends AssetBundle
{
    public $sourcePath = '@app/_assets';
    public $baseUrl = '@web';
    public $css = [
        'css/main.css',
    ];
    public $js = [];
    public $depends = [
        'app\assets\FrontendAssets',
    ];
}
