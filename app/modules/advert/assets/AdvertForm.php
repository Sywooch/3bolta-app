<?php
namespace advert\assets;

/**
 * Ассетсы для формы добавления объявления
 */
class AdvertForm extends \yii\web\AssetBundle
{
    public $sourcePath = '@advert/_assets/form';

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