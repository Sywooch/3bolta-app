<?php
namespace auto\sync;

/**
 * Синхронизация поколений
 */
class Generation extends Base
{
    protected function getTableName()
    {
        return '{{%auto_generation}}';
    }

    protected function getExternalTableName()
    {
        return '{{%generation}}';
    }

    protected function formatValue($external)
    {
        return [
            'id' => $external['id_car_generation'],
            'model_id' => $external['id_car_model'],
            'name' => $external['name'],
            'year_begin' => $external['year_begin'],
            'year_end' => $external['year_end'],
        ];
    }
}