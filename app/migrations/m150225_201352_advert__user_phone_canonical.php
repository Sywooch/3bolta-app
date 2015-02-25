<?php
use yii\db\Migration;

/**
 * Канонический телефон в объявлениях
 */
class m150225_201352_advert__user_phone_canonical extends Migration
{
    private $table = '{{%advert}}';

    public function safeUp()
    {
        $this->addColumn($this->table, 'user_phone_canonical', 'varchar(255) null');
    }

    public function safeDown()
    {
        $this->dropColumn($this->table, 'user_phone_canonical');
    }
}
