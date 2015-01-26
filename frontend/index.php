<?php
/**
 * Точка входа для приложения frontend
 */

require(__DIR__ . '/../app/config/env.local.php');
require(__DIR__ . '/../app/vendor/autoload.php');
require(__DIR__ . '/../app/vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../app/config/frontend.php');

(new yii\web\Application($config))->run();
