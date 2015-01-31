<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Привязка объявлений к категориям
 */
class m150131_120221_advert__advert_category_xref extends Migration
{
    private $table = '{{%advert_category_xref}}';
    private $tableAdvert = '{{%advert}}';
    private $tableCategory = '{{%advert_category}}';

    public function safeUp()
    {
        $this->createTable($this->table, [
            'advert_id' => 'int not null',
            'category_id' => 'int not null',
        ]);
        $this->addForeignKey('fk_advert_category_xref_advert_id',
            $this->table, 'advert_id',
            $this->tableAdvert, 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_advert_category_xref_category_id',
            $this->table, 'category_id',
            $this->tableCategory, 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
