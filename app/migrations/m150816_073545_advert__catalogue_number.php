<?php
use yii\db\Migration;

/**
 * Номер по каталогу в запчастях
 */
class m150816_073545_advert__catalogue_number extends Migration
{
    private $table = '{{%advert}}';

    public function safeUp()
    {
        $this->addColumn($this->table, 'catalogue_number', 'varchar(100) null');
        $this->alterColumn($this->table, 'advert_name', 'varchar(100)');
    }

    public function safeDown()
    {
        $this->dropColumn($this->table, 'catalogue_number');
        $this->alterColumn($this->table, 'advert_name', 'varchar(255)');
    }
}
