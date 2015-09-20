<?php
use yii\db\Migration;

/**
 * Поля для OAuth-авторизации
 */
class m150920_100605_user__social_uid_fields extends Migration
{
    private $table = '{{%user_social_account}}';
    private $tableUser = '{{%user}}';

    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => 'pk',
            'user_id' => 'int not null',
            'code' => 'varchar(20) not null',
            'external_uid' => 'varchar(30) not null',
            'external_name' => 'varchar(255) not null',
            'external_page' => 'varchar(255) not null',
        ]);
        $this->createIndex('user_social_account_user_id_code_unique_idx', $this->table, 'user_id,code', true);
        $this->createIndex('user_social_account_external_uid_code_unique_idx', $this->table, 'external_uid,code', true);
        $this->addForeignKey('fk_user_social_account_user_id',
            $this->table, 'user_id', $this->tableUser, 'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
