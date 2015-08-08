<?php
namespace advert\assets;

/**
 * Ассетсы для формы поиска запчасти
 */
class PartAdvertSearch extends \yii\web\AssetBundle
{
    public $sourcePath = '@advert/_assets/search';

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