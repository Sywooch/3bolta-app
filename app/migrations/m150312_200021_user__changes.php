<?php
use yii\db\Migration;

/**
 * Таблица пользователей, изменения
 */
class m150312_200021_user__changes extends Migration
{
    private $table = '{{%user}}';

    public function safeUp()
    {
        $this->alterColumn($this->table, 'name', 'varchar(50)');
        $this->alterColumn($this->table, 'email', 'varchar(100)');
    }

    public function safeDown()
    {
        $this->alterColumn($this->table, 'name', 'varchar(255)');
        $this->alterColumn($this->table, 'email', 'varchar(255)');
    }
}
