<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * Подключение Google JS API (используется для определения местоположения пользователя)
 */
class GoogleJSAPIAssets extends AssetBundle
{
    public $css = [];
    public $js = [
        'http://www.google.com/jsapi',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
