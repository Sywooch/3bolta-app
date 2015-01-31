<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Таблица с файловыми хранилищами
 */
class m150131_185522_storage__storage_table extends Migration
{
    private $table = '{{%storage}}';

    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => 'pk',
            'created' => 'timestamp not null',
            'repository' => 'varchar(255) not null',
            'size' => 'int not null',
            'real_name' => 'varchar(255) not null',
            'file_path' => 'varchar(255) not null',
            'mime_type' => 'varchar(255) not null',
            'uploader_addr' => 'inet not null',
            'is_image' => 'boolean null',
            'width' => 'smallint null',
            'height' => 'smallint null',
        ]);
        $this->createIndex('repository', $this->table, 'repository');
        $this->createIndex('is_image', $this->table, 'is_image');
    }

    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
