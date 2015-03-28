<?php
use yii\db\Migration;

/**
 * Переименовать таблицу из partner_address в partner_trade_point
 */
class m150328_095458_partner__adress_trade_point_rename extends Migration
{
    private $previewTableName = '{{%partner_address}}';
    private $newTableName = '{{%partner_trade_point}}';

    public function safeUp()
    {
        $this->renameTable($this->previewTableName, $this->newTableName);
    }

    public function safeDown()
    {
        $this->renameTable($this->newTableName, $this->previewTableName);
    }
}
