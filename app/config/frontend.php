<?php
/**
 * Настройки приложения frontend.
 */

use yii\helpers\ArrayHelper;

return ArrayHelper::merge(include __DIR__ . '/common.php', [
    'id' => 'frontend',
    'basePath' => dirname(__DIR__),
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '3w66MKhVzvJxAGtim81qmqaRCSRIQr4a',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => [
                '/' => 'site/index',

                // выбор автомобилей
                '/auto/choose/<action:(\w+)>' => '/auto/choose-auto/<action>',

                // объявления
                '/search' => '/advert/catalog/search',
                '/details/<id:(\d+)>' => '/advert/catalog/details',

                // работа с объявлениями
                '/ads/append' => '/advert/advert/append',
            ],
        ],
        'user' => [
            'class' => 'user\components\UserAuth',
            'identityClass' => 'user\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['site/login'],
        ],
        'assetManager' => [
            'linkAssets' => true,
            'bundles' => [
                'yii\bootstrap\BootstrapPluginAsset' => [
                    'sourcePath' => '@app/_assets/bootstrap',
                    'js' => [
                        'js/bootstrap.min.js',
                    ]
                ],
                'yii\bootstrap\BootstrapAsset' => [
                    'sourcePath' => '@app/_assets/bootstrap',
                    'css' => [
                        'css/bootstrap.min.css',
                        'css/bootstrap-theme.min.css',
                    ],
                ],

            ],
        ],
    ],
], include __DIR__ . '/frontend.local.php');

