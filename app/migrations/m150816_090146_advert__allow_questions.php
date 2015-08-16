<?php
use yii\db\Migration;

class m150816_090146_advert__allow_questions extends Migration
{
    private $table = '{{%advert}}';

    public function safeUp()
    {
        $this->addColumn($this->table, 'allow_questions', 'boolean not null default true');
    }

    public function safeDown()
    {
        $this->dropColumn($this->table, 'allow_questions');
    }
}
