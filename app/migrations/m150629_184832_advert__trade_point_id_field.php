<?php
use yii\db\Migration;

/**
 * Привязка к торговым точкам в объявлениях
 */
class m150629_184832_advert__trade_point_id_field extends Migration
{
    private $table = '{{%advert}}';
    private $tableTradePoint = '{{%partner_trade_point}}';

    public function safeUp()
    {
        $this->addColumn($this->table, 'trade_point_id', 'int null');
        $this->addForeignKey('fk_advert_trade_point_id',
            $this->table, 'trade_point_id', $this->tableTradePoint, 'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropColumn($this->table, 'trade_point_id');
    }
}
