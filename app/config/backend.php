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
            'baseUrl' => '/backend/',
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
            ],
        ],
        'assetManager' => [
            'linkAssets' => true,
        ],
        'user' => [
            'class' => 'app\modules\user\components\UserAuth',
            'identityClass' => 'app\modules\user\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['/backend/dashboard/login'],
        ],
        'serviceMessage' => [
            'class' => 'app\components\ServiceMessage',
        ]
    ],
    'modules' => [
        'backend' => [
            'class' => 'app\modules\backend\Module',
        ]
    ]
], include __DIR__ . '/backend.local.php');
