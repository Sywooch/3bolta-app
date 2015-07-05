<?php
use yii\db\Migration;

/**
 * Таблица с регионами
 */
class m150705_085953_region__table extends Migration
{
    private $table = '{{%region}}';

    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => 'pk',
            'external_id' => 'uuid not null',
            'region_code' => 'varchar(2) not null',
            'canonical_name' => 'varchar(255) not null',
            'official_name' => 'varchar(255) not null',
            'short_name' => 'varchar(255) not null',
            'site_name' => 'varchar(255) null',
        ]);
        $this->createIndex('region_external_id', $this->table, 'external_id');
    }

    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
