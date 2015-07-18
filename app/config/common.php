<?php
/**
 * Общие настройки приложения.
 * Глобальный конфиг.
 */

use yii\helpers\ArrayHelper;

require_once __DIR__ . '/env.local.php';

$config = ArrayHelper::merge([
    'basePath' => dirname(__DIR__),
    'language' => 'ru-RU',
    'sourceLanguage' => 'en-US',
    'bootstrap' => ['log'],
    'aliases' => ArrayHelper::merge([
        '@' => dirname(__DIR__),
        '@backendUrl' => APP_BACKEND_ABSOLUTE_URL,
        '@frontendUrl' => APP_FRONTEND_ABSOLUTE_URL,
    ], include __DIR__ . '/aliases.php'),
    'components' => [
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => true,
            'viewPath' => '@app/mail',
            'messageConfig' => [
                'charset' => 'UTF-8',
                'from' => 'info@3bolta.com',
                'bcc' => 'info@3bolta.com',
            ],
            'messageClass' => 'app\components\MailMessage',
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                    'forceTranslation' => true,
                    'sourceLanguage' => 'ru-RU',
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
        'partner' => [
            'class' => 'partner\Module',
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
        'geo' => [
            'class' => 'geo\Module',
        ],
    ],
    'params' => [
        'defaultEmailFrom' => 'info@3bolta.com',
        'siteName' => '3bolta.com',
        'siteBrand' => '3bolta.com',
        'rulesRoute' => '#',
    ],
], include __DIR__ . '/common.local.php');


if (defined('YII_ENV_DEV') && YII_ENV_DEV == true) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';
}

return $config;

