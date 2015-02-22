<?php
use yii\db\Migration;

/**
 * Поле "Телефон" пользователя
 */
class m150222_102628_user__phone extends Migration
{
    private $table = '{{%user}}';

    public function safeUp()
    {
        $this->addColumn($this->table, 'phone', 'varchar(100) null');
        $this->addColumn($this->table, 'phone_canonical', 'varchar(100) null');
        $this->createIndex('user_phone_canonical', $this->table, 'phone_canonical');
    }

    public function safeDown()
    {
        $this->dropColumn($this->table, 'phone');
        $this->dropColumn($this->table, 'phone_canonical');
    }
}
