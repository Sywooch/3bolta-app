<?php
/**
 * Настройки для консольного приложения.
 * Глобальный конфиг.
 */

use yii\helpers\ArrayHelper;

return ArrayHelper::merge(include __DIR__ . '/common.php', [
    'id' => 'console',
    'bootstrap' => ['log', 'gii'],
    'controllerNamespace' => 'app\commands',
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => include __DIR__ . '/frontend.routing.php',
        ],
    ],
    'params' => [
        // крон-задания
        'cron-jobs' => [
            // демон по ресайзу картинок
            'adverts-images/resize' => '* * * * *',
            // проверка завершающих публикаций
            'part-adverts/check-expiration' => '0 5 * * *',
            // очистка завершенных публикаций из индекса
            'part-adverts/remove-expired' => '0 * * * *',
            // переиндексация поиска
            'part-adverts/reindex' => '0 0 * * *',
        ]
    ]
], include __DIR__ . '/console.local.php');

