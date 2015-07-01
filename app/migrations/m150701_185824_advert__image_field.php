<?php
use yii\db\Migration;

/**
 * Поле для сжатого изображения
 */
class m150701_185824_advert__image_field extends Migration
{
    private $table = '{{%advert_image}}';

    public function safeUp()
    {
        $this->createTable('{{%advert_image_new}}', [
            'id' => 'pk',
            'file_id' => 'int not null',
            'advert_id' => 'int not null',
            'thumb_id' => 'int null',
            'preview_id' => 'int null',
            'image_id' => 'int null',
            'is_preview' => 'boolean not null default false',
        ]);
        $this->db->createCommand('INSERT INTO {{%advert_image_new}} SELECT file_id, advert_id, thumb_id, preview_id, is_preview FROM {{%advert_image}}')->execute();
        $this->dropTable($this->table);
        $this->renameTable('{{%advert_image_new}}', $this->table);
    }

    public function down()
    {
        echo "m150701_185824_advert__image_field cannot be reverted.\n";

        return false;
    }
}
