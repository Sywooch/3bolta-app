<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Таблица объявлений пользователей
 */
class m150131_112355_advert__advert_table extends Migration
{
    private $table = '{{%advert}}';

    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => 'pk',
            'advert_name' => 'varchar (255) not null',
            'user_id' => 'varchar (255) null',
            'user_name' => 'varchar (255) null',
            'user_phone' => 'varchar (255) null',
            'price' => 'numeric(9,2) not null',
            'description' => 'text',
        ]);
    }

    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
