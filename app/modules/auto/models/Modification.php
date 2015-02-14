<?php
namespace auto\models;

use Yii;

class Modification extends ActiveRecord
{
    /**
     * Название таблицы
     * @return string
     */
    public static function tableName()
    {
        return '{{%auto_modification}}';
    }

    /**
     * Правила валидации
     * @return type
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['model_id', 'serie_id'], 'required'],
            [['model_id', 'serie_id'], 'integer'],
            [['model_id', 'year_begin', 'year_end'], 'integer'],
        ]);
    }

    /**
     * Подписи атрибутов
     * @return []
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'model_id' => Yii::t('auto', 'Model'),
            'serie_id' => Yii::t('auto', 'Serie'),
            'year_begin' => Yii::t('auto', 'Begin production year'),
            'year_end' => Yii::t('auto', 'End production year'),
        ]);
    }
}