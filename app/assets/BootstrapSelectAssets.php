<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * Кастомный селект на бутстрапе
 */
class BootstrapSelectAssets extends AssetBundle
{
    public $sourcePath = '@vendor/bootstrap-select/bootstrap-select/dist';
    public $baseUrl = '@web';
    public $css = [
        'css/bootstrap-select.min.css',
    ];
    public $js = [
        'js/bootstrap-select.min.js',
        'js/i18n/defaults-ru_RU.min.js',
    ];
    public $depends = [
        'yii\bootstrap\BootstrapAsset',
    ];
}
