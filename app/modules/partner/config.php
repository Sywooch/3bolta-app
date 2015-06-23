<?php
/**
 * Конфигурация модуля партнеров
 */
return [
    'components' => [
        'api' => [
            'class' => '\partner\components\PartnersApi',
        ],
        'search' => [
            'class' => '\partner\components\SearchApi',
        ],
    ],
];