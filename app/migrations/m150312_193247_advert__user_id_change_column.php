<?php
use yii\db\Migration;

/**
 * Колонка user_id теперь число
 */
class m150312_193247_advert__user_id_change_column extends Migration
{
    private $table = '{{%advert}}';
    private $tableUser = '{{%user}}';

    public function safeUp()
    {/*
        $this->addColumn($this->table, 'user_id_copy', 'int null');
        $sql = 'UPDATE ' . $this->table . ' SET user_id_copy = user_id';
        $this->db->createCommand($sql)->execute();
        $this->dropColumn($this->table, 'user_id');
        $this->renameColumn($this->table, 'user_id_copy', 'user_id');*/
        $this->alterColumn($this->table, 'user_id', 'int USING (trim(user_id)::integer)');
        $this->addForeignKey('fk_advert_user_id', $this->table, 'user_id', $this->tableUser, 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_advert_user_id', $this->table);
        $this->alterColumn($this->table, 'user_id', 'varchar(255)');
    }
}
