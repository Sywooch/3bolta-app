<?php
use yii\db\Migration;

class m150222_105014_user__first_name_drop extends Migration
{
    private $table = '{{%user}}';

    public function safeUp()
    {
        $this->renameColumn($this->table, 'first_name', 'name');
        $this->dropColumn($this->table, 'second_name');
        $this->dropColumn($this->table, 'last_name');
    }

    public function safeDown()
    {
        echo __CLASS__ . ' do not have migration down' . "\n";
        return false;
    }
}
