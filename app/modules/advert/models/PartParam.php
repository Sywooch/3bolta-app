<?php
namespace advert\models;

use yii\db\ActiveRecord;
use handbook\models\HandbookValue;
use Yii;
use yii\db\ActiveQuery;

/**
 * Параметры объявления запчастей:
 * - номер каталога;
 * - состояние;
 * - категория.
 */
class PartParam extends ActiveRecord
{
    /**
     * Максимальное количество символов в каталожном номере
     */
    const CATALOGUE_NUMBER_MAX_LENGTH = 100;

    /**
     * Таблица
     * @return string
     */
    public static function tableName()
    {
        return '{{%advert_part_param}}';
    }

    /**
     * Правила валидации
     * @return array
     */
    public function rules()
    {
        return [
            ['advert_id', 'required'],
            ['advert_id', 'integer'],
            [['condition_id', 'category_id'], 'required'],
            [['condition_id', 'category_id'], 'integer'],
            ['catalogue_number', 'string', 'max' => self::CATALOGUE_NUMBER_MAX_LENGTH, 'skipOnEmpty' => true],
        ];
    }

    /**
     * Подписи атрибутов
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'catalogue_number' => Yii::t('advert', 'Catalogue number'),
            'condition_id' => Yii::t('advert', 'Part condition'),
            'category_id' => Yii::t('advert', 'Part category'),
        ];
    }

    /**
     * Получить категорию
     * @return ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(PartCategory::className(), ['id' => 'category_id']);
    }

    /**
     * Получить состояние запчасти
     * @return ActiveQuery
     */
    public function getCondition()
    {
        return $this->hasOne(HandbookValue::className(), ['id' => 'condition_id'])
                ->where(['handbook_code' => 'part_condition']);
    }

    /**
     * Привязка к объявлению
     * @return ActiveRecord
     */
    public function getAdvert()
    {
        return $this->hasOne(Part::className(), ['id' => 'advert_id']);
    }
}