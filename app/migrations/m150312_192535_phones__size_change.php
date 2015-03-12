<?php
use yii\db\Migration;

/**
 * Изменить размер поля "Телефон" во всех таблицах
 */
class m150312_192535_phones__size_change extends Migration
{
    public $tables = [
        '{{%user}}', '{{%advert}}', '{{%partner_address}}',
    ];

    public function safeUp()
    {
        foreach ($this->tables as $table) {
            $phone = $table == '{{%advert}}' ? 'user_phone' : 'phone';
            $phoneCanonical = $table == '{{%advert}}' ? 'user_phone_canonical' : 'phone_canonical';
            $this->alterColumn($table, $phone, 'varchar(19)');
            $this->alterColumn($table, $phoneCanonical, 'varchar(11)');
        }
    }

    public function safeDown()
    {
        foreach ($this->tables as $table) {
            $phone = $table == '{{%advert}}' ? 'user_phone' : 'phone';
            $phoneCanonical = $table == '{{%advert}}' ? 'user_phone_canonical' : 'phone_canonical';
            $this->alterColumn($table, $phone, 'varchar(255)');
            $this->alterColumn($table, $phoneCanonical, 'varchar(255)');
        }
    }
}
