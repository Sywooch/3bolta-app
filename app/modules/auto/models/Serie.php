<?php
namespace auto\models;

use Yii;

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
        return array_merge(parent::rules(), [
            [['model_id'], 'required'],
            [['model_id'], 'integer'],
            [['generation_id'], 'integer', 'skipOnEmpty' => true],
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
            'geneartion_id' => Yii::t('auto', 'Generation'),
        ]);
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

    /**
     * Получить имя для формирования в модальном окне поиска
     * Состоит из поколения и годов
     *
     * @return string
     */
    public function getSearchName()
    {
        $ret = $this->name;

        $generation = $this->generation_id ? $this->generation : null;
        $model = $this->model;

        if ($generation && $generation->name && trim($model->name) != trim($generation->name)) {
            $ret = $generation->name . ' ' . $ret;
        }
        else if ($generation && $generation->year_begin) {
            $ret .= ' (' . $generation->year_begin . '-';
            if ($generation->year_end) {
                $ret .= $generation->year_end;
            }
            else {
                $ret .= '...';
            }
            $ret .= ')';
        }

        return $ret;
    }
}