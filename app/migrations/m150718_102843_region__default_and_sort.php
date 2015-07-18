<?php
use yii\db\Migration;

/**
 * Регион по умолчанию и сортировка
 */
class m150718_102843_region__default_and_sort extends Migration
{
    private $table = '{{%region}}';

    public function safeUp()
    {
        $this->addColumn($this->table, 'as_default', 'boolean not null default false');
        $this->addColumn($this->table, 'sort', 'smallint not null default 100');
        $this->createIndex('region_as_default_idx', $this->table, 'as_default');
    }

    public function safeDown()
    {
        $this->dropColumn($this->table, 'as_default');
        $this->dropColumn($this->table, 'sort');
    }
}
