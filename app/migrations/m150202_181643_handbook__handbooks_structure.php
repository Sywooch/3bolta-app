<?php

use yii\db\Migration;

/**
 * Структура справочников
 */
class m150202_181643_handbook__handbooks_structure extends Migration
{
    private $table = '{{%handbook}}';
    private $tableValues = '{{%handbook_value}}';

    public function safeUp()
    {
        $this->createTable($this->table, [
            'code' => 'varchar(255)',
            'name' => 'varchar(255)',
            'PRIMARY KEY (code)',
        ]);

        $this->createTable($this->tableValues, [
            'id' => 'pk',
            'handbook_code' => 'varchar(255) not null',
            'sort' => 'smallint null',
            'name' => 'varchar(255)',
        ]);

        $this->createIndex('sort', $this->tableValues, 'sort');

        $this->addForeignKey('fk_handbook_value_handbook_id',
            $this->tableValues, 'handbook_code',
            $this->table, 'code',
            'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable($this->tableValues);
        $this->dropTable($this->table);
    }
}
