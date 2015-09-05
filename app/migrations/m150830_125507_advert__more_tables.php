<?php
use yii\db\Migration;

/**
 * Разделение таблицы объявлений по разным таблицам
 */
class m150830_125507_advert__more_tables extends Migration
{
    private $table = '{{%advert}}';
    private $tableContacts = '{{%advert_contact}}';
    private $tablePartAdvert = '{{%advert_part_param}}';
    private $tableRegion = '{{%region}}';
    private $tableTradePoint = '{{%partner_trade_point}}';

    public function safeUp()
    {
        $this->createTable($this->tableContacts, [
            'advert_id' => 'int not null',
            'user_name' => 'varchar(50) null',
            'user_phone' => 'varchar(19) null',
            'user_phone_canonical' => 'varchar(11) null',
            'user_email' => 'varchar(100) null',
            'region_id' => 'int null',
            'trade_point_id' => 'int null',
            'PRIMARY KEY (advert_id)',
        ]);
        $this->createIndex('advert_contact_phone_canonical', $this->tableContacts, 'user_phone_canonical', true);
        $this->addForeignKey('fk_advert_contact_advert_id',
            $this->tableContacts, 'advert_id', $this->table, 'id',
            'CASCADE', 'CASCADE'
        );
        $this->addForeignKey('fk_advert_contact_region_id',
            $this->tableContacts, 'region_id', $this->tableRegion, 'id',
            'SET NULL', 'CASCADE'
        );
        $this->addForeignKey('fk_advert_contact_trade_point_id',
            $this->tableContacts, 'trade_point_id', $this->tableTradePoint, 'id',
            'CASCADE', 'CASCADE'
        );

        $this->createTable($this->tablePartAdvert, [
            'advert_id' => 'int not null',
            'catalogue_number' => 'varchar(100) null',
            'condition_id' => 'int null',
            'category_id' => 'int null',
            'PRIMARY KEY (advert_id)',
        ]);
        $this->createIndex('advert_part_param_condition_id', $this->tablePartAdvert, 'condition_id');
        $this->createIndex('advert_part_param_category_id', $this->tablePartAdvert, 'category_id');
        $this->addForeignKey('fk_advert_part_param_advert_id',
            $this->tablePartAdvert, 'advert_id', $this->table, 'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable($this->tablePartAdvert);
        $this->dropTable($this->tableContacts);
    }
}
