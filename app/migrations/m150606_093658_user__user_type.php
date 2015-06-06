<?php
use yii\db\Migration;

/**
 * Тип пользователя: private person или partner
 */
class m150606_093658_user__user_type extends Migration
{
    private $table = '{{%user}}';

    public function safeUp()
    {
        $this->addColumn($this->table, 'type', 'smallint not null default 1');
    }

    public function safeDown()
    {
        $this->dropColumn($this->table, 'type');
    }
}
