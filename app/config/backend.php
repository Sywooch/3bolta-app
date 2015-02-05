<?php
/**
 * Настройки приложения backend.
 */

use yii\helpers\ArrayHelper;

return ArrayHelper::merge(include __DIR__ . '/common.php', [
    'id' => 'backend',
    'basePath' => dirname(__DIR__),
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '3w66MKhVzvJxAGtim81qmqaRCSRIQr4a',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // base actions
                '/<action:(login|logout)>' => '/backend/dashboard/<action>',
                '/' => '/backend/dashboard/index',

                // roles actions
                '/role/<action:(delete|update)>/<id:(\w+)>' => '/user/role-backend/<action>',
                '/role/<action:(\w+)>' => '/user/role-backend/<action>',

                // users actions
                '/user/<action:(delete|update)>/<id:(\w+)>' => '/user/user-backend/<action>',
                '/user/<action:(\w+)>' => '/user/user-backend/<action>',

                // categories actions
                '/advert/categories/<action:(delete|update)>/<id:(\w+)>' => '/advert/category-backend/<action>',
                '/advert/categories/<action:(\w+)>' => '/advert/category-backend/<action>',

                // storage actions
                '/storage/<action:(delete|update)>/<id:(\w+)>' => '/storage/storage-backend/<action>',
                '/storage/<action:(\w+)>' => '/storage/storage-backend/<action>',

                // handbook actions
                '/handbook/<code:(\w+)>/<action:(delete|update)/<id:(\w+)>' => '/handbook/handbook-value-backend/<action>',
                '/handbook/<code:(\w+)>/<action:(\w+)>' => '/handbook/handbook-value-backend/<action>',

                // auto actions
                '/auto/mark' => '/auto/auto-backend/mark',
                '/auto/model/<mark_id:(\d+)>' => '/auto/auto-backend/model',
                '/auto/serie/<model_id:(\d+)>' => '/auto/auto-backend/serie',
                '/auto/modification/<model_id:(\d+)>/<serie_id:(\d+)>' => '/auto/auto-backend/modification',
            ],
        ],
        'assetManager' => [
            'linkAssets' => true,
        ],
        'user' => [
            'class' => 'user\components\UserAuth',
            'identityClass' => 'user\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['/backend/dashboard/login'],
        ],
        'serviceMessage' => [
            'class' => 'app\components\ServiceMessage',
        ]
    ],
    'modules' => [
        'backend' => [
            'class' => 'backend\Module',
        ]
    ]
], include __DIR__ . '/backend.local.php');
