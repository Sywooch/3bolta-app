<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Категории запчастей
 */
class m150131_114240_advert__category extends Migration
{
    private $table = '{{%advert_category}}';

    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => 'pk',
            'sort' => 'int not null',
            'parent_id' => 'int null',
            'lft' => 'int not null',
            'rgt' => 'int not null',
            'depth' => 'int not null',
            'name' => 'varchar (255) not null',
        ]);
        $this->addForeignKey('fk_advert_category_parent_id',
            $this->table, 'parent_id',
            $this->table, 'id', 'CASCADE', 'CASCADE'
        );
        $this->createIndex('advert_category_index', $this->table, 'lft, rgt, depth');

        // the empty root category for nested sets provider
        $this->insert($this->table, [
            'id' => '1',
            'sort' => '1',
            'parent_id' => null,
            'lft' => 1,
            'rgt' => 1,
            'depth' => 0,
            'name' => 'Root',
        ]);
    }

    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
