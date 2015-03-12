<?php
use yii\db\Migration;

/**
 * Изменить размеры полей name, email, user_name
 */
class m150312_195558_advert__name_email_user_name extends Migration
{
    private $table = '{{%advert}}';

    public function safeUp()
    {
        $this->alterColumn($this->table, 'advert_name', 'varchar(50)');
        $this->alterColumn($this->table, 'user_name', 'varchar(50)');
        $this->alterColumn($this->table, 'user_email', 'varchar(100)');
    }

    public function safeDown()
    {
        $this->alterColumn($this->table, 'advert_name', 'varchar(255)');
        $this->alterColumn($this->table, 'user_name', 'varchar(255)');
        $this->alterColumn($this->table, 'user_email', 'varchar(255)');
    }
}
