<?php
use yii\db\Migration;

/**
 * Ключ на телефоне во всех сущностях, где нужен телефон
 */
class m150329_100644_main__phone_canonical_index extends Migration
{
    private $tables = [
        '{{%advert}}' => 'user_phone_canonical',
        '{{%partner_trade_point}}' => 'phone_canonical',
    ];

    protected function getCanonical($str)
    {
        return str_replace(['{', '}', '%'], '', $str);
    }

    public function safeUp()
    {
        foreach ($this->tables as $table => $attribute) {
            $indexName = $this->getCanonical($table) . '_' . $attribute;
            $this->createIndex($indexName, $table, $attribute);
        }
    }

    public function safeDown()
    {
        foreach ($this->tables as $table => $attribute) {
            $indexName = $this->getCanonical($table) . '_' . $attribute;
            $this->dropIndex($indexName, $table);
        }
    }
}
