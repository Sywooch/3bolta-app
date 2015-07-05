<?php
use yii\db\Migration;

/**
 * Поля для центральной точки регионов
 */
class m150705_102019_region__center_coordinates extends Migration
{
    private $table = '{{%region}}';

    public function safeUp()
    {
        $this->addColumn($this->table, 'center_lat', "float null");
        $this->addColumn($this->table, 'center_lng', "float null");
    }

    public function safeDown()
    {
        $this->dropColumn($this->table, 'center_lat');
        $this->dropColumn($this->table, 'center_lng');
    }
}
