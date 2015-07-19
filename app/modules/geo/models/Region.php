<?php
namespace geo\models;

use app\components\ActiveRecord;
use Yii;
use yii\db\ActiveQuery;

/**
 * Модель региона
 */
class Region extends ActiveRecord
{
    /**
     * Таблица
     * @return string
     */
    public static function tableName()
    {
        return '{{%region}}';
    }

    /**
     * Правила валидации
     * @return array
     */
    public function rules()
    {
        return [
            [['external_id', 'region_code', 'canonical_name', 'official_name', 'short_name', 'site_name'], 'required'],
            [['center_lat', 'center_lng'], 'number', 'min' => -180, 'max' => 180],
            ['region_code', 'string', 'length' => 2],
            [['canonical_name', 'short_name', 'official_name', 'site_name'], 'string', 'max' => 255],
            ['external_id', 'string'],
        ];
    }

    /**
     * Подписи атрибутов
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'region_code' => Yii::t('geo', 'Region code'),
            'canonical_name' => Yii::t('geo', 'Canonical name'),
            'official_name' => Yii::t('geo', 'Official name'),
            'short_name' => Yii::t('geo', 'Region type'),
            'site_name' => Yii::t('geo', 'Site name'),
            'center_lat' => Yii::t('geo', 'Center latitude'),
            'center_lng' => Yii::t('geo', 'Center longitude'),
        ];
    }

    /**
     * Сортировка по умолчанию
     * @return ActiveQuery
     */
    public static function find()
    {
        return parent::find()->orderBy('sort ASC, site_name ASC');
    }
}