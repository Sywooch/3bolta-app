<?php
use yii\db\Migration;

/**
 * Вопрос по запчасти
 */
class m150816_083707_message__advert_question extends Migration
{
    protected $table = '{{%advert_question}}';
    protected $tablePartAdvert = '{{%advert}}';
    protected $tableUser = '{{%user}}';

    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => 'pk',
            'advert_id' => 'int not null',
            'hash' => 'uuid not null default uuid_in(md5(random()::text || now()::text)::cstring)',
            'to_user_id' => 'int null',
            'to_user_name' => 'varchar(255) not null',
            'to_user_email' => 'varchar(255) not null',
            'from_user_id' => 'int null',
            'from_user_name' => 'varchar(255) not null',
            'from_user_email' => 'varchar(255) not null',
            'question' => 'text not null',
            'answer' => 'text not null',
        ]);

        $this->addForeignKey('fk_advert_question_advert_id',
            $this->table, 'advert_id', $this->tablePartAdvert, 'id',
            'CASCADE', 'CASCADE'
        );
        $this->createIndex('advert_question_hash', $this->table, 'hash', true);
        $this->addForeignKey('fk_advert_question_to_user_id',
            $this->table, 'to_user_id', $this->tableUser, 'id',
            'CASCADE', 'CASCADE'
        );
        $this->addForeignKey('fk_advert_question_from_user_id',
            $this->table, 'from_user_id', $this->tableUser, 'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
