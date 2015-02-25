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
];