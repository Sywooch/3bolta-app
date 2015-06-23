<?php
/**
 * Роутеры для фронтенда
 */

return [
    '/' => 'site/index',

    // выбор автомобилей
    '/auto/choose/<action:(\w+)>' => '/auto/choose-auto/<action>',

    // объявления
    '/ads/search' => '/advert/catalog/search',
    '/ads/details/<id:(\d+)>' => '/advert/catalog/details',

    // работа с объявлениями
    '/ads/append' => '/advert/advert/append',
    '/ads/confirmation/<code:(\w+)>' => '/advert/advert/confirm',

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
    '/cabinet/ads' => '/advert/user-advert/list',
    '/cabinet/ads/update-publication/<id:(\d+)>' => '/advert/user-advert/update-publication',
    '/cabinet/ads/stop-publication/<id:(\d+)>' => '/advert/user-advert/stop-publication',
    '/cabinet/ads/edit/<id:(\d+)>' => '/advert/user-advert/edit',
    '/cabinet/ads/append' => '/advert/user-advert/append',

    // торговые точки
    '/cabinet/company' => '/partner/partner/index',
    '/cabinet/company/tps/create' => '/partner/partner/create-trade-point',
    '/cabinet/company/tps/edit/<id:(\d+)>' => '/partner/partner/edit-trade-point',
    '/cabinet/company/tps/delete/<id:(\d+)>' => '/partner/partner/delete-trade-point',

    // поиск по торговым точкам
    '/trade-points/map' => '/partner/search/index',
];