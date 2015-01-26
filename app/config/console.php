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
], include __DIR__ . '/console.local.php');

