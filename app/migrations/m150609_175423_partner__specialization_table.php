<?php
use yii\db\Migration;

/**
 * Специализация партнеров
 */
class m150609_175423_partner__specialization_table extends Migration
{
    private $table = '{{%partner_specialization}}';
    private $tablePartner = '{{%partner}}';
    private $tableMark = '{{%auto_mark}}';

    public function safeUp()
    {
        $this->createTable($this->table, [
            'partner_id' => 'int not null',
            'mark_id' => 'int not null',
            'unique (partner_id, mark_id)',
        ]);
        $this->addForeignKey('fk_partner_specialization_partner_id',
            $this->table, 'partner_id', $this->tablePartner, 'id',
            'CASCADE', 'CASCADE'
        );
        $this->addForeignKey('fk_partner_specialization_mark_id',
            $this->table, 'mark_id', $this->tableMark, 'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
