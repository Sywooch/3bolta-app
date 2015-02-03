<?php
namespace app\modules\auto\sync;

use Yii;

/**
 * Синхронизация моделей
 */
class Model extends Base
{
    protected function getTableName()
    {
        return '{{%auto_model}}';
    }

    protected function getExternalTableName()
    {
        return '{{%model}}';
    }

    protected function formatValue($external)
    {
        return [
            'id' => $external['id_car_model'],
            'mark_id' => $external['id_car_mark'],
            'name' => $external['name'],
        ];
    }
}