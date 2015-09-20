<?php
/**
 * Настройки приложения frontend.
 * Локальный конфиг.
 */

return [
    'components' => [
        // Компонент для подключения к соц. сетям
        'socialAuthClientCollection' => [
            'clients' => [
                // Google
                'google' => [
                    'clientId' => '',
                    'clientSecret' => '',
                ],
                // Facebook
                'facebook' => [
                    'clientId' => '',
                    'clientSecret' => '',
                ],
                // VKontakte
                'vkontakte' => [
                    'clientId' => '',
                    'clientSecret' => '',
                ],
            ],
        ],
    ],
];


