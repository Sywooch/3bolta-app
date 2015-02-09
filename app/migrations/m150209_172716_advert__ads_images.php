<?php
use yii\db\Migration;

/**
 * Таблица привязки картинок к объявлению
 */
class m150209_172716_advert__ads_images extends Migration
{
    private $table = '{{%advert_image}}';
    private $tableAdvert = '{{%advert}}';
    private $tableStorage = '{{%storage}}';

    public function safeUp()
    {
        $this->createTable($this->table, [
            'advert_id' => 'int null',
            'file_id' => 'int null',
        ]);
        $this->addForeignKey('fk_advert_image_advert_id',
            $this->table, 'advert_id',
            $this->tableAdvert, 'id',
            'CASCADE', 'CASCADE'
        );
        $this->addForeignKey('fk_advert_image_file_id',
            $this->table, 'file_id',
            $this->tableStorage, 'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
