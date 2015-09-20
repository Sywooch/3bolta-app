<?php
use yii\db\Migration;

/**
 * В модели пользователя больше не нужен номер телефона
 */
class m150920_131301_user__drop_phone extends Migration
{
    private $table = '{{%user}}';

    public function safeUp()
    {
        $this->dropColumn($this->table, 'phone');
        $this->dropColumn($this->table, 'phone_canonical');
    }

    public function safeDown()
    {
        echo "Can\'t rollback this migration.\n";
        return false;
    }
}
