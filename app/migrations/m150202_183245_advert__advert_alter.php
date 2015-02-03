<?php
use yii\db\Migration;

/**
 * Дополнительные колонки в таблице объявлений
 */
class m150202_183245_advert__advert_alter extends Migration
{
    private $table = '{{%advert}}';
    private $tableCategory = '{{%advert_category}}';
    private $tablePhotos = '{{%advert_photo}}';
    private $tableHandbookValue = '{{%handbook_value}}';
    private $tableStorage = '{{%storage}}';

    public function safeUp()
    {
        $this->addColumn($this->table, 'user_email', 'varchar(255) null');
        $this->addColumn($this->table, 'category_id', 'int null');
        $this->addColumn($this->table, 'condition_id', 'int null');
        $this->addColumn($this->table, 'created', 'timestamp not null default now()');
        $this->addColumn($this->table, 'edited', 'timestamp not null default now()');
        $this->addColumn($this->table, 'published', 'timestamp not null');
        $this->addColumn($this->table, 'active', 'boolean not null default true');

        $this->createIndex('advert_active', $this->table, 'active');
        $this->addForeignKey('fk_advert_category_id',
            $this->table, 'category_id',
            $this->tableCategory, 'id',
            'SET DEFAULT', 'SET DEFAULT'
        );

        $this->addForeignKey('fk_advert_condition_id',
            $this->table, 'condition_id',
            $this->tableHandbookValue, 'id',
            'SET DEFAULT', 'SET DEFAULT'
        );

        $this->createTable($this->tablePhotos, [
            'advert_id' => 'int not null',
            'file_id' => 'int not null',
        ]);
        $this->addForeignKey('fk_advert_photo_advert_id',
            $this->tablePhotos, 'advert_id',
            $this->table, 'id',
            'CASCADE', 'CASCADE'
        );
        $this->addForeignKey('fk_advert_photo_file_id',
            $this->tablePhotos, 'file_id',
            $this->tableStorage, 'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable($this->tablePhotos);
        $this->dropColumn($this->table, 'user_email');
        $this->dropColumn($this->table, 'category_id');
        $this->dropColumn($this->table, 'condition_id');
        $this->dropColumn($this->table, 'created');
        $this->dropColumn($this->table, 'edited');
        $this->dropColumn($this->table, 'published');
        $this->dropColumn($this->table, 'active');
    }
}
