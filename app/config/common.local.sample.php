<?php
/**
 * Общие настройки приложения.
 * Локальный конфиг.
 */

return [
    'components' => [
        'db' => [
            'dsn' => 'pgsql:host=localhost;dbname=database',
            'username' => 'login',
            'password' => 'password',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.yandex.ru',
                'username' => 'smtp_username',
                'password' => 'smtp_password',
                'port' => '465',
                'encryption' => 'ssl',
            ],
        ],
    ],
];

