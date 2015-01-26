<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Поле для фиксации даты последнего посещения
 */
class m150122_131723_user__last_login_field extends Migration
{
    private $table = '{{%user}}';

    public function safeUp()
    {
        $this->addColumn($this->table, 'last_login', "TIMESTAMP NULL");
    }

    public function safeDown()
    {
        $this->dropColumn($this->table, 'last_login');
    }
}
