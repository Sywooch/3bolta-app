<?php
use yii\db\Migration;

/**
 * Превью для изображений объявлений
 */
class m150214_135222_advert__image_preview extends Migration
{
    private $table = '{{%advert_image}}';
    private $tableStorage = '{{%storage}}';

    public function safeUp()
    {
        $this->addColumn($this->table, 'is_preview', 'boolean null');
        $this->addColumn($this->table, 'thumb_id', 'int not null');
        $this->addColumn($this->table, 'preview_id', 'int null');

        $this->createIndex('advert_image_is_preview', $this->table, 'is_preview');
        $this->addForeignKey('fk_advert_image_thumb_id',
            $this->table, 'thumb_id',
            $this->tableStorage, 'id',
            'CASCADE', 'CASCADE'
        );
        $this->addForeignKey('fk_advert_image_preview_id',
            $this->table, 'preview_id',
            $this->tableStorage, 'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropColumn($this->table, 'is_preview');
        $this->dropColumn($this->table, 'thumb_id');
        $this->dropColumn($this->table, 'preview_id');
    }
}
