<?php
namespace auto\models;

use Yii;
use yii\db\ActiveRecord;

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
     * Правила валидации
     * @return type
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
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
            'name' => Yii::t('auto', 'Mark name'),
            'active' => Yii::t('auto', 'Active'),
        ];
    }

    /**
     * Условие по умолчанию
     */
    public static function find()
    {
        return parent::find()->orderBy('name ASC');
    }

    /**
     * Получить модели
     */
    public function getModels()
    {
        return $this->hasMany(Model::className(), ['mark_id' => 'id']);
    }
}