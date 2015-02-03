<?php
use yii\db\Migration;

/**
 * Структура автомобилей
 */
class m150202_191518_auto__auto_structure extends Migration
{
    private $tableMark = '{{%auto_mark}}';
    private $tableModel = '{{%auto_model}}';
    private $tableGeneration = '{{%auto_generation}}';
    private $tableSerie = '{{%auto_serie}}';
    private $tableModification = '{{%auto_modification}}';

    public function safeUp()
    {
        $this->createTable($this->tableMark, [
            'id' => 'pk',
            'name' => 'varchar(255) not null',
            'active' => 'boolean not null default true',
        ]);
        $this->createIndex('auto_mark_active', $this->tableMark, 'active');

        $this->createTable($this->tableModel, [
            'id' => 'pk',
            'mark_id' => 'int not null',
            'name' => 'varchar(255) not null',
            'active' => 'boolean not null default true',
        ]);
        $this->createIndex('auto_model_mark_id', $this->tableModel, 'mark_id');
        $this->createIndex('auto_model_active', $this->tableModel, 'active');

        $this->createTable($this->tableGeneration, [
            'id' => 'pk',
            'model_id' => 'int not null',
            'name' => 'varchar(255) not null',
            'year_begin' => 'smallint null',
            'year_end' => 'smallint null',
            'active' => 'boolean not null default true',
        ]);
        $this->createIndex('auto_generation_model_id', $this->tableGeneration, 'model_id');
        $this->createIndex('auto_generation_active', $this->tableGeneration, 'active');
        $this->createIndex('auto_generation_years', $this->tableGeneration, 'year_begin, year_end');

        $this->createTable($this->tableSerie, [
            'id' => 'pk',
            'model_id' => 'int not null',
            'generation_id' => 'int null',
            'name' => 'varchar(255) not null',
            'active' => 'boolean not null default true',
        ]);
        $this->createIndex('auto_serie_generation_id', $this->tableSerie, 'generation_id');
        $this->createIndex('auto_serie_active', $this->tableSerie, 'active');

        $this->createTable($this->tableModification, [
            'id' => 'pk',
            'model_id' => 'int not null',
            'serie_id' => 'int not null',
            'name' => 'varchar(255) not null',
            'year_begin' => 'smallint null',
            'year_end' => 'smallint null',
            'active' => 'boolean not null default true',
            'deleted' => 'boolean not null default false',
        ]);
        $this->createIndex('auto_modification_model_id', $this->tableModification, 'model_id');
        $this->createIndex('auto_modification_serie_id', $this->tableModification, 'serie_id');
        $this->createIndex('auto_modification_active', $this->tableModification, 'active');
    }

    public function safeDown()
    {
        $this->dropTable($this->tableModification);
        $this->dropTable($this->tableSerie);
        $this->dropTable($this->tableGeneration);
        $this->dropTable($this->tableModel);
        $this->dropTable($this->tableMark);
    }
}
