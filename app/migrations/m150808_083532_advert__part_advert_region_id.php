<?php
use yii\db\Migration;

/**
 * Регион в объявлениях
 */
class m150808_083532_advert__part_advert_region_id extends Migration
{
    private $table = '{{%advert}}';
    private $tableRegion = '{{%region}}';

    public function safeUp()
    {
        $this->addColumn($this->table, 'region_id', 'int null');
        $this->addForeignKey('fk_advert_region_id',
            $this->table, 'region_id', $this->tableRegion, 'id',
            'SET NULL', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropColumn($this->table, 'region_id');
    }
}
