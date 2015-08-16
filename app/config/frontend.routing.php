<?php
/**
 * Роутеры для фронтенда
 */

return [
    '/' => 'site/index',

    // выбор автомобилей
    '/auto/choose/<action:(\w+)>' => '/auto/choose-auto/<action>',

    // объявления
    '/ads/parts/search' => '/advert/part-catalog/search',
    '/ads/parts/details/<id:(\d+)>' => '/advert/part-catalog/details',
    '/ads/parts/details/<id:(\d+)>/question' => '/advert/part-catalog/question',

    // работа с объявлениями
    '/ads/parts/append' => '/advert/part-advert/append',
    '/ads/parts/confirmation/<code:(\w+)>' => '/advert/part-advert/confirm',

    // работа с пользователями
    '/registration' => '/user/user/register',
    '/registration/confirmation/<code:(\w+)>' => '/user/user/confirmation',
    '/login' => '/user/user/login',
    '/logout' => '/user/user/logout',
    '/restore/lost-password' => '/user/user/lost-password',
    '/restore/change-password/<code:(\w+)>' => '/user/user/change-password',

    // профиль
    '/profile' => '/user/profile/index',
    '/profile/change-email/<code:(\w+)>' => '/user/profile/change-email',
    '/profile/<action:(change-password|update-company-data|update-email|update-contact-data)>' => '/user/profile/<action>',

    // объявления пользователя
    '/cabinet/ads/parts' => '/advert/user-part-advert/list',
    '/cabinet/ads/parts/update-publication/<id:(\d+)>' => '/advert/user-part-advert/update-publication',
    '/cabinet/ads/parts/stop-publication/<id:(\d+)>' => '/advert/user-part-advert/stop-publication',
    '/cabinet/ads/parts/<id:(\d+)>/edit/' => '/advert/user-part-advert/edit',
    '/cabinet/ads/parts/<id:(\d+)>/remove-image' => '/advert/user-part-advert/remove-advert-image',
    '/cabinet/ads/parts/append' => '/advert/user-part-advert/append',

    // торговые точки
    '/cabinet/company' => '/partner/partner/index',
    '/cabinet/company/tps/create' => '/partner/partner/create-trade-point',
    '/cabinet/company/tps/edit/<id:(\d+)>' => '/partner/partner/edit-trade-point',
    '/cabinet/company/tps/delete/<id:(\d+)>' => '/partner/partner/delete-trade-point',

    // поиск по торговым точкам
    '/trade-points/map' => '/partner/search/index',
    '/trade-points/search' => '/partner/search/search',
    '/trade-points/search-by-name' => '/partner/search/name-autocomplete',
    '/trade-points/search-by-mark' => '/partner/search/mark-autocomplete',

    // работа с геоданными
    '/geo/detect-user-region' => '/geo/geo/detect-user-region',
    '/geo/select-region' => '/geo/geo/select-region',
];