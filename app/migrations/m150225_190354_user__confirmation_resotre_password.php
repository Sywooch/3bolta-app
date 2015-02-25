<?php
use yii\db\Migration;

/**
 * Строка подтверждения при восстановлении пароля
 */
class m150225_190354_user__confirmation_resotre_password extends Migration
{
    private $table = '{{%user_confirmation}}';

    public function safeUp()
    {
        $this->addColumn($this->table, 'restore_confirmation', 'varchar(32) null');
    }

    public function safeDown()
    {
        $this->dropColumn($this->table, 'restore_confirmation');
    }
}
