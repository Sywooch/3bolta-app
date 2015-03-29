<?php
/**
 * Скрипты для виджета выбора автомобилей
 */
namespace auto\assets;

class ChooseAutomobile extends \yii\web\AssetBundle
{
    public $sourcePath = '@auto/_assets';

    public $js = [
        'js/chooseAutomobile.js',
    ];

    public $css = [
        'css/style.css',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}