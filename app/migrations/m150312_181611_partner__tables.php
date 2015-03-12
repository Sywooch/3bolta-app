<?php
use yii\db\Migration;

/**
 * Таблицы для партнеров:
 * - партнер - запись, название, привязка к пользователю;
 * - адрес - запись, привязка к партнеру.
 */
class m150312_181611_partner__tables extends Migration
{
    private $table = '{{%partner}}';
    private $tableAddress = '{{%partner_address}}';
    private $tableUser = '{{%user}}';

    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => 'pk',
            'created' => 'timestamp not null default now()',
            'edited' => 'timestamp not null default now()',
            'user_id' => 'int not null',
            'company_type' => 'int not null',
            'name' => 'varchar(100) not null',
            'UNIQUE (user_id)',
        ]);

        $this->addForeignKey('fk_partner_user_id',
            $this->table, 'user_id',
            $this->tableUser, 'id',
            'CASCADE', 'CASCADE'
        );

        $this->createTable($this->tableAddress, [
            'id' => 'pk',
            'created' => 'timestamp not null default now()',
            'edited' => 'timestamp not null default now()',
            'partner_id' => 'int not null',
            'latitude' => 'float(6) not null',
            'longitude' => 'float(6) not null',
            'coordinates' => 'point not null',
            'address' => 'varchar(255) not null',
            'phone' => 'varchar(255) not null',
            'phone_canonical' => 'varchar(255) not null',
        ]);

        $this->addForeignKey('fk_partner_address_partner_id',
            $this->tableAddress, 'partner_id',
            $this->table, 'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable($this->tableAddress);
        $this->dropTable($this->table);
    }
}
