<?php
namespace auto\models;

use Yii;
use yii\db\ActiveRecord;

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
        return [
            [['name', 'mark_id'], 'required'],
            [['mark_id'], 'integer'],
            [['active'], 'boolean'],
        ];
    }

    /**
     * Подписи атрибутов
     * @return []
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => Yii::t('auto', 'Model name'),
            'mark_id' => Yii::t('auto', 'Mark'),
            'active' => Yii::t('auto', 'Active'),
        ];
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