<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * Подключение Google-карт
 */
class SelectMapLocationAssets extends AssetBundle
{
    public $sourcePath = '@app/_assets';
    public $baseUrl = '@web/assets';
    public $css = [];
    public $js = [
        'js/select-map-location.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'app\assets\GoogleMapAssets',
    ];
}
