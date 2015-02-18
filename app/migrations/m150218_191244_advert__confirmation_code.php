<?php
use yii\db\Migration;

/**
 * Код подтверждения публикации объявления для неавторизованных пользователей
 */
class m150218_191244_advert__confirmation_code extends Migration
{
    private $table = '{{%advert}}';

    public function safeUp()
    {
        $this->addColumn($this->table, 'confirmation', 'varchar(32) null');
    }

    public function safeDown()
    {
        $this->dropColumn($this->table, 'confirmation');
    }
}
