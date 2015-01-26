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
            'rules' => [
            ],
        ],
        'user' => [
            'class' => 'app\modules\user\components\UserAuth',
            'identityClass' => 'app\modules\user\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['site/login'],
        ],
        'assetManager' => [
            'linkAssets' => true,
        ],
    ],
], include __DIR__ . '/frontend.local.php');

