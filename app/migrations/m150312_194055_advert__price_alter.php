<?php
use yii\db\Migration;

/**
 * Изменить колонку цены
 */
class m150312_194055_advert__price_alter extends Migration
{
    private $table = '{{%advert}}';

    public function safeUp()
    {
        $this->alterColumn($this->table, 'price', 'float(11)');
    }

    public function safeDown()
    {
        $this->alterColumn($this->table, 'price', 'numeric(9,2)');
    }
}
