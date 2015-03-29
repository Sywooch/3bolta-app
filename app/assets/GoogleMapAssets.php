<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * Подключение Google-карт
 */
class GoogleMapAssets extends AssetBundle
{
    public $css = [];
    public $js = [
        'https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&sensor=true',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
