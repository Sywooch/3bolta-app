<?php
use yii\db\Migration;

/**
 * Телефон торговой точки может быть равен указанному в профиле
 */
class m150606_115210_partner__trade_point_phone_equal extends Migration
{
    private $table = '{{%partner_trade_point}}';

    public function safeUp()
    {
        $this->addColumn($this->table, 'phone_from_profile', 'boolean not null default true');
        $this->dropColumn($this->table, 'phone');
        $this->dropColumn($this->table, 'phone_canonical');
        $this->addColumn($this->table, 'phone', 'varchar(255) null');
        $this->addColumn($this->table, 'phone_canonical', 'varchar(255) null');
    }

    public function safeDown()
    {
        $this->dropColumn($this->table, 'phone_from_profile');
        $this->dropColumn($this->table, 'phone');
        $this->dropColumn($this->table, 'phone_canonical');
        $this->addColumn($this->table, 'phone', 'varchar(255) not null');
        $this->addColumn($this->table, 'phone_canonical', 'varchar(255) not null');
    }
}
