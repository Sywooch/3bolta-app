<?php
use yii\db\Migration;

/**
 * Перенос данных по разным таблицам
 */
class m150830_131110_advert__more_tables_move_data extends Migration
{
    private $table = '{{%advert}}';
    private $tableContact = '{{%advert_contact}}';
    private $tablePartParam = '{{%advert_part_param}}';

    public function safeUp()
    {
        $res = $this->db->createCommand('SELECT * FROM ' . $this->table)->query();
        while ($row = $res->read()) {
            $this->insert($this->tableContact, [
                'advert_id' => $row['id'],
                'user_name' => $row['user_name'],
                'user_phone' => $row['user_phone'],
                'user_phone_canonical' => $row['user_phone_canonical'],
                'user_email' => $row['user_email'],
                'region_id' => $row['region_id'],
                'trade_point_id' => $row['trade_point_id'],
            ]);
            $this->insert($this->tablePartParam, [
                'advert_id' => $row['id'],
                'catalogue_number' => $row['catalogue_number'],
                'condition_id' => $row['condition_id'],
                'category_id' => $row['category_id'],
            ]);
        }
    }

    public function safeDown()
    {
        $this->truncateTable($this->tablePartParam);
        $this->truncateTable($this->tableContact);
    }
}
