<?php
namespace partner\models;

use auto\models\Mark;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Привязка специализаций к партнеру (специализация = марка)
 */
class Specialization extends ActiveRecord
{
    /**
     * Название таблицы
     * @return string
     */
    public static function tableName()
    {
        return '{{%partner_specialization}}';
    }

    /**
     * Правила валидации
     * @return array
     */
    public function rules()
    {
        return [
            [['partner_id', 'mark_id'], 'integer', 'skipOnEmpty' => false],
        ];
    }

    /**
     * Получить марку
     * @return ActiveQuery
     */
    public function getMark()
    {
        return $this->hasOne(Mark::className(), ['id' => 'mark_id']);
    }

    /**
     * Получить партнера
     * @return ActiveQuery
     */
    public function getPartner()
    {
        return $this->hasOne(Partner::tableName(), ['id' => 'partner_id']);
    }
}