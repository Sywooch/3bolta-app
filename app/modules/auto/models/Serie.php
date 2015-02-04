<?php
namespace app\modules\auto\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Серии
 */
class Serie extends ActiveRecord
{
    /**
     * Название таблицы
     * @return string
     */
    public static function tableName()
    {
        return '{{%auto_serie}}';
    }

    /**
     * Правила валидации
     * @return type
     */
    public function rules()
    {
        return [
            [['name', 'model_id'], 'required'],
            [['model_id'], 'integer'],
            [['generation_id'], 'integer', 'skipOnEmpty' => true],
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
            'name' => Yii::t('auto', 'Serie name'),
            'model_id' => Yii::t('auto', 'Model'),
            'geneartion_id' => Yii::t('auto', 'Generation'),
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
     * Получить поколение
     */
    public function getGeneration()
    {
        return $this->hasOne(Generation::className(), ['id' => 'generation_id']);
    }

    /**
     * Получить модификации
     */
    public function getModifications()
    {
        return $this->hasMany(Modification::className(), ['serie_id' => 'id']);
    }
}