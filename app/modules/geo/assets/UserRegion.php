<?php
namespace geo\assets;

/**
 * Скрипты для определения и установки региона пользователя
 */
class UserRegion extends \yii\web\AssetBundle
{
    public $sourcePath = '@geo/_assets/user-region';

    public $baseUrl = '@web';

    public $css = [
    ];

    public $js = [
        'js/app.js',
    ];

    public $depends = [
        'app\assets\GoogleJSAPIAssets',
        'app\assets\FrontendAssets',
    ];
}