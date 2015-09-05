<?php
use yii\db\Migration;

class m150905_124319_advert__remove_old_fields extends Migration
{
    private $table = '{{%advert}}';

    public function safeUp()
    {
        $this->dropColumn($this->table, 'user_name');
        $this->dropColumn($this->table, 'user_phone');
        $this->dropColumn($this->table, 'user_phone_canonical');
        $this->dropColumn($this->table, 'user_email');
        $this->dropColumn($this->table, 'category_id');
        $this->dropColumn($this->table, 'condition_id');
        $this->dropColumn($this->table, 'trade_point_id');
        $this->dropColumn($this->table, 'region_id');
        $this->dropColumn($this->table, 'catalogue_number');
    }

    public function safeDown()
    {
        echo 'this migration has no down' . "\n";
        return false;
    }
}
