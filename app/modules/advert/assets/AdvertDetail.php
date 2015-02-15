<?php
namespace advert\assets;

/**
 * Ассетсы для карточки объявления
 */
class AdvertDetail extends \yii\web\AssetBundle
{
    public $sourcePath = '@advert/_assets/detail';

    public $baseUrl = '@web';

    public $css = [
        'css/styles.css',
    ];

    public $js = [
        'js/app.js',
    ];

    public $depends = [
        'app\assets\FrontendAssets',
    ];
}