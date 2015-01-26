<?php

use yii\db\Schema;
use yii\db\Migration;

class m150121_130417_user__user_table extends Migration
{
    private $table = '{{%user}}';

    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => 'pk',
            'email' => "varchar(255) not null",
            'password' => "varchar(255) not null",
            'status' => "smallint not null default '0'",
            'first_name' => "varchar(255) not null",
            'second_name' => "varchar(255) null",
            'last_name' => "varchar(255) null",
        ]);
    }

    public function safeDown() {
        $this->dropTable($this->table);
    }
}
