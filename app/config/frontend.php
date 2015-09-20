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
            'rules' => include __DIR__ . '/frontend.routing.php',
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
        'socialAuthClientCollection' => [
            // компонент для коллекций OAuth-авторизации через социальные сети
            'class' => '\yii\authclient\Collection',
            'clients' => [
                // VKontakte
                'vkontakte' => [
                    'class' => 'yii\authclient\clients\VKontakte',
                    'attributeNames' => [
                        'uid', 'first_name', 'last_name', 'screen_name',
                        'photo', 'email', 'domain',
                    ],
                    'scope' => 'email',
                ],
                // Facebook
                'facebook' => [
                    'class' => 'yii\authclient\clients\Facebook',
                ],
                // Google
                'google' => [
                    'class' => 'yii\authclient\clients\GoogleOAuth',
                ],
            ]
        ],
        'serviceMessage' => [
            'class' => 'app\components\ServiceMessage',
        ]
    ],
], include __DIR__ . '/frontend.local.php');

