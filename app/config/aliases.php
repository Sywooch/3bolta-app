<?php
/**
 * Алиасы для модулей
 */

$ret = [];

$modulesDir = dirname(__DIR__) . '/modules';

if (is_dir($modulesDir)) {
    $dir = new DirectoryIterator($modulesDir);

    foreach ($dir as $module) {
        if ($module != '.'  && $module != '..' && is_dir($modulesDir . '/' . $module)) {
            $ret['@' . $module] = $modulesDir . '/' . $module;
        }
    };
}

return $ret;
