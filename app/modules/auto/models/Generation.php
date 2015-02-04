<?php
namespace app\modules\auto\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель поколений
 */
class Generation extends ActiveRecord
{
    /**
     * Таблица
     * @return string
     */
    public static function tableName()
    {
        return '{{%auto_generation}}';
    }

    /**
     * Правила валидации
     * @return type
     */
    public function rules()
    {
        return [
            [['name', 'model_id'], 'required'],
            [['model_id', 'year_begin', 'year_end'], 'integer'],
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
            'name' => Yii::t('auto', 'Generation name'),
            'model_id' => Yii::t('auto', 'Model'),
            'year_begin' => Yii::t('auto', 'Begin production year'),
            'year_end' => Yii::t('auto', 'End production year'),
            'active' => Yii::t('auto', 'Active'),
        ];
    }

    /**
     * Получить модель
     */
    public function getModel()
    {
        return $this->hasOne(Model::className(), ['id' => 'model_id']);
    }

    /**
     * Получить серии
     */
    /**
     * Получить серии
     */
    public function getSeries()
    {
        return $this->hasMany(Serie::className(), ['generation_id' => 'id']);
    }
}