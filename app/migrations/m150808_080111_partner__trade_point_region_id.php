<?php

use yii\db\Schema;
use yii\db\Migration;

class m150808_080111_partner__trade_point_region_id extends Migration
{
    private $table = '{{%partner_trade_point}}';
    private $tableRegion = '{{%region}}';

    public function safeUp()
    {
        $this->addColumn($this->table, 'region_id', 'int null');
        $this->addForeignKey('fk_partner_trade_point_region_id',
            $this->table, 'region_id', $this->tableRegion, 'id',
            'SET NULL', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropColumn($this->table, 'region_id');
    }
}
