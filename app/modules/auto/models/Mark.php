<?php
namespace auto\models;

use Yii;

/**
 * Модель марок
 */
class Mark extends ActiveRecord
{
    /**
     * Таблица
     * @return string
     */
    public static function tableName()
    {
        return '{{%auto_mark}}';
    }

    /**
     * Условие по умолчанию
     */
    public static function findOrderByName()
    {
        return parent::find()->orderBy('mark.name ASC');
    }

    /**
     * Получить модели
     */
    public function getModels()
    {
        return $this->hasMany(Model::className(), ['mark_id' => 'id']);
    }
}