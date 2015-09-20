<?php
use yii\db\Migration;

class m150920_183046_user__social_account_page_as_null extends Migration
{
    private $table = '{{%user_social_account}}';

    public function safeUp()
    {
        $this->db->createCommand('ALTER TABLE ' . $this->table . ' ALTER COLUMN external_page DROP NOT NULL')->execute();
    }

    public function safeDown()
    {
        $this->db->createCommand('ALTER TABLE ' . $this->table . ' ALTER COLUMN external_page SET NOT NULL')->execute();
    }
}
