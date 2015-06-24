<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * Ассеты для специфичных шрифтов
 */
class FontelloAssets extends AssetBundle
{
    public $sourcePath = '@app/_assets/';
    public $baseUrl = '@web';
    public $css = [
        'fontello/css/fontello.css',
        'fontello/css/fontello-embed.css',
        'fontello/css/fontello-codes.css',
        'fontello/css/animation.css',
    ];
    public $js = [
    ];
    public $depends = [
    ];
}
