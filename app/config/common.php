<?php
/**
 * Общие настройки приложения.
 * Глобальный конфиг.
 */

use yii\helpers\ArrayHelper;

require_once __DIR__ . '/env.local.php';

$config = ArrayHelper::merge([
    'basePath' => dirname(__DIR__),
    'language' => 'ru',
    'bootstrap' => ['log'],
    'aliases' => ArrayHelper::merge([
        '@' => dirname(__DIR__),
        '@backendUrl' => APP_BACKEND_ABSOLUTE_URL,
        '@frontendUrl' => APP_FRONTEND_ABSOLUTE_URL,
    ], include __DIR__ . '/aliases.php'),
    'components' => [
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                    'sourceLanguage' => 'en',
                    'fileMap' => [
                    ],
                ],
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'charset' => 'utf8',
            'tablePrefix' => 'app_',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
            'viewPath' => '@app/mail',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
    ],
    'modules' => [
        'user' => [
            'class' => 'user\Module',
        ],
        'advert' => [
            'class' => 'advert\Module',
        ],
        'handbook' => [
            'class' => 'handbook\Module',
        ],
        'storage' => [
            'class' => 'storage\Module',
            'repository' => [
                'advert' => [
                    'baseUrl' => APP_STORAGE_ABSOLUTE_URL . '/advert/',
                    'basePath' => realpath(__DIR__ . '/../../') . '/storage/advert',
                ],
                'media' => [
                    'baseUrl' => APP_STORAGE_ABSOLUTE_URL . '/media/',
                    'basePath' => realpath(__DIR__ . '/../../') . '/storage/media',
                ],
            ]
        ],
        'auto' => [
            'class' => 'auto\Module',
            'components' => [
                'externalDb' => [
                    'class' => 'yii\db\Connection',
                    'charset' => 'utf8',
                    'tablePrefix' => 'car_',
                ],
            ],
        ],
    ],
    'params' => [
    ],
], include __DIR__ . '/common.local.php');


if (defined('YII_ENV_DEV') && YII_ENV_DEV == true) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';
}

return $config;

