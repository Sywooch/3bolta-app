<?php

use yii\db\Schema;
use yii\db\Migration;

class m150214_104630_auto__site_name extends Migration
{
    protected $tables = [
        '{{%auto_mark}}',
        '{{%auto_model}}',
        '{{%auto_generation}}',
        '{{%auto_serie}}',
        '{{%auto_modification}}'
    ];

    public function safeUp()
    {
        foreach ($this->tables as $table) {
            $this->addColumn($table, 'full_name', 'varchar(255) null');
        }
    }

    public function safeDown()
    {
        foreach ($this->tables as $table) {
            $this->dropColumn($table, 'full_name');
        }
    }
}
