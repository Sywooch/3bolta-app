<?php
use yii\db\Migration;

/**
 * Создать индексы и доп. колонку
 */
class m150220_192404_advert__published_to extends Migration
{
    private $table = '{{%advert}}';

    public function safeUp()
    {
        $this->addColumn($this->table, 'published_to', 'timestamp null');
        $this->createIndex('advert_published_constraint', $this->table, 'active, published, published_to');
    }

    public function safeDown()
    {
        $this->dropColumn($this->table, 'published_to');
        $this->dropIndex('advert_published_constraint', $this->table);
    }
}
