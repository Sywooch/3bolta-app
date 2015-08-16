<?php
namespace app\helpers;

/**
 * Хелпер по специфичным типам данных
 */
class DataHelper
{
    /**
     * Возвращает уникальный uuid.
     * Пример взят со страницы http://php.net/manual/en/function.com-create-guid.php
     *
     * @return string
     */
    public static function guid()
    {
        mt_srand((double) microtime() * 10000);
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);
        $left_curly = chr(123);
        $right_curly = chr(125);
        $uuid = $left_curly
            . substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12)
            . $right_curly;
        return $uuid;
    }
}