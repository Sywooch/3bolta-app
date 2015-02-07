<?php
use yii\db\Migration;

/**
 * Колонка published не обязательна
 */
class m150207_091506_advert__advert_published_constraint extends Migration
{
    private $table = '{{%advert}}';

    public function safeUp()
    {
        $this->dropColumn($this->table, 'published');
        $this->addColumn($this->table, 'published', 'timestamp null');
    }
}
