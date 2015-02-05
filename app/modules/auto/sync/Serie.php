<?php
namespace auto\sync;

/**
 * Синхронизация серий
 */
class Serie extends Base
{
    protected function getTableName()
    {
        return '{{%auto_serie}}';
    }

    protected function getExternalTableName()
    {
        return '{{%serie}}';
    }

    protected function formatValue($external)
    {
        return [
            'id' => $external['id_car_serie'],
            'model_id' => $external['id_car_model'],
            'generation_id' => $external['id_car_generation'],
            'name' => $external['name'],
        ];
    }
}