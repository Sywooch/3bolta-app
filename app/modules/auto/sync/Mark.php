<?php
namespace auto\sync;

/**
 * Синхронизация марок
 */
class Mark extends Base
{
    protected function getTableName()
    {
        return '{{%auto_mark}}';
    }

    protected function getExternalTableName()
    {
        return '{{%mark}}';
    }

    protected function formatValue($external)
    {
        return [
            'id' => $external['id_car_mark'],
            'name' => $external['name'],
        ];
    }
}