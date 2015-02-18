<?php
/**
 * Конфигурация модуля объявлений
 */

return [
    'components' => [
        'search' => [
            'class' => 'advert\components\SearchApi',
        ],
        'advert' => [
            'class' => 'advert\components\AdvertApi',
        ],
    ],
];