<?php
namespace app\modules\auto\sync;

/**
 * Синхронизация модификацией
 */
class Modification extends Base
{
    protected function getTableName()
    {
        return '{{%auto_modification}}';
    }

    protected function getExternalTableName()
    {
        return '{{%modification}}';
    }

    protected function formatValue($external)
    {
        return [
            'id' => $external['id_car_modification'],
            'serie_id' => $external['id_car_serie'],
            'model_id' => $external['id_car_model'],
            'year_begin' => $external['start_production_year'],
            'year_end' => $external['end_production_year'],
            'name' => $external['name'],
        ];
    }
}