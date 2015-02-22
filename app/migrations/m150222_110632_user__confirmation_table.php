<?php
use yii\db\Migration;

/**
 * Таблица с подтверждениями пользователей
 */
class m150222_110632_user__confirmation_table extends Migration
{
    private $table = '{{%user_confirmation}}';
    private $tableUser = '{{%user}}';

    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => 'pk',
            'user_id' => 'int not null',
            'email' => 'varchar(255) null',
            'email_confirmation' => 'varchar(255) null',
        ]);
        $this->addForeignKey('fk_user_confirmation_user_id',
            $this->table, 'user_id', $this->tableUser, 'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
