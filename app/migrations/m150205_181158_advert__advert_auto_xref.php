<?php
use yii\db\Migration;

/**
 * Таблицы для связи объявлений с автомобилями
 */
class m150205_181158_advert__advert_auto_xref extends Migration
{
    private $table = '{{%advert}}';
    private $tableMark = '{{%advert_mark}}';
    private $tableModel = '{{%advert_model}}';
    private $tableSerie = '{{%advert_serie}}';
    private $tableModification = '{{%advert_modification}}';

    public function safeUp()
    {
        $this->createTable($this->tableMark, [
            'advert_id' => 'int not null',
            'mark_id' => 'int not null',
        ]);
        $this->addForeignKey('fk_advert_mark_advert_id',
            $this->tableMark, 'advert_id',
            $this->table, 'id',
            'CASCADE', 'CASCADE'
        );
        $this->createIndex('advert_mark_mark_id', $this->tableMark, 'mark_id');

        $this->createTable($this->tableModel, [
            'advert_id' => 'int not null',
            'model_id' => 'int not null',
        ]);
        $this->addForeignKey('fk_advert_model_advert_id',
            $this->tableModel, 'advert_id',
            $this->table, 'id',
            'CASCADE', 'CASCADE'
        );
        $this->createIndex('advert_model_model_id', $this->tableModel, 'model_id');

        $this->createTable($this->tableSerie, [
            'advert_id' => 'int not null',
            'serie_id' => 'int not null',
        ]);
        $this->addForeignKey('fk_advert_serie_advert_id',
            $this->tableSerie, 'advert_id',
            $this->table, 'id',
            'CASCADE', 'CASCADE'
        );
        $this->createIndex('advert_serie_serie_id', $this->tableSerie, 'serie_id');

        $this->createTable($this->tableModification, [
            'advert_id' => 'int not null',
            'modification_id' => 'int not null',
        ]);
        $this->addForeignKey('fk_advert_modification_advert_id',
            $this->tableModification, 'advert_id',
            $this->table, 'id',
            'CASCADE', 'CASCADE'
        );
        $this->createIndex('advert_modification_modification_id', $this->tableModification, 'modification_id');
    }

    public function safeDown()
    {
        $this->dropTable($this->tableMark);
        $this->dropTable($this->tableModel);
        $this->dropTable($this->tableSerie);
        $this->dropTable($this->tableModification);
    }
}
