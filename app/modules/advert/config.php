<?php
/**
 * Конфигурация модуля объявлений
 */

return [
    'components' => [
        'partsSearch' => [
            'class' => 'advert\components\PartsSearchApi',
        ],
        'partsIndex' => [
            'class' => 'advert\components\PartsIndex',
        ],
        'parts' => [
            'class' => 'advert\components\PartsApi',
        ],
        'questions' => [
            'class' => 'advert\components\QuestionsApi',
        ],
    ],
];