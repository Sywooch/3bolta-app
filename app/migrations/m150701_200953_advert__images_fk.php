<?php

use yii\db\Schema;
use yii\db\Migration;

class m150701_200953_advert__images_fk extends Migration
{
    private $table = '{{%advert_image}}';
    private $tableStorage = '{{%storage}}';
    private $tableAdvert = '{{%advert}}';

    public function safeUp()
    {
        $this->db->createCommand('delete from {{%advert_image}} where advert_id not in (select id from {{%advert}})')->execute();
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
        $this->addForeignKey('fk_advert_image_image_id',
            $this->table, 'image_id',
            $this->tableStorage, 'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function down()
    {
        echo "m150701_200953_advert__images_fk cannot be reverted.\n";

        return false;
    }
}
