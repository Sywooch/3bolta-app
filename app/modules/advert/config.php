<?php
/**
 * Конфигурация модуля объявлений
 */

return [
    'components' => [
        'partsSearch' => [
            'class' => 'advert\components\PartsSearchApi',
        ],
        'parts' => [
            'class' => 'advert\components\PartsApi',
        ],
        'questions' => [
            'class' => 'advert\components\QuestionsApi',
        ],
    ],
];