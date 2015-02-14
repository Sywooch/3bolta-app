<?php
namespace auto\models;

use Yii;

/**
 * Модели автомобилей
 */
class Model extends ActiveRecord
{
    /**
     * Название таблицы
     * @return string
     */
    public static function tableName()
    {
        return '{{%auto_model}}';
    }

    /**
     * Правила валидации
     * @return type
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['mark_id'], 'required'],
            [['mark_id'], 'integer'],
        ]);
    }

    /**
     * Подписи атрибутов
     * @return []
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'mark_id' => Yii::t('auto', 'Mark'),
        ]);
    }

    /**
     * Получить марку
     */
    public function getMark()
    {
        return $this->hasOne(Mark::className(), ['id' => 'mark_id']);
    }

    /**
     * Получить поколения
     * @return []
     */
    public function getGenerations()
    {
        return $this->hasMany(Generation::className(), ['model_id' => 'id']);
    }

    /**
     * Получить серии
     */
    public function getSeries()
    {
        return $this->hasMany(Serie::className(), ['model_id' => 'id']);
    }

    /**
     * Получить модификации
     */
    public function getModifications()
    {
        return $this->hasMany(Modification::className(), ['model_id' => 'id']);
    }
}