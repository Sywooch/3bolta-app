<?php
use yii\db\Migration;

/**
 * Пароль теперь не обязателен
 */
class m150920_135751_user__password_drop_null extends Migration
{
    private $table = '{{%user}}';

    public function safeUp()
    {
        $this->db->createCommand('ALTER TABLE ' . $this->table . ' ALTER COLUMN password DROP NOT NULL')->execute();
    }

    public function safeDown()
    {
        $this->db->createCommand('ALTER TABLE ' . $this->table . ' ALTER COLUMN password SET NOT NULL')->execute();
    }
}
