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
class AdvertPartParam extends ActiveRecord
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
     * Получить название состояния
     * @return string
     */
    public function getConditionName()
    {
        if ($this->condition_id && $this->condition instanceof HandbookValue) {
            return $this->condition->name;
        }

        return '';
    }

    /**
     * Выпадающий список категорий
     * @param boolean $getFirstEmpty получать первый пустой элемент
     * @return array
     */
    public static function getCategoryDropDownList($getFirstEmpty = false)
    {
        $ret = [];

        if ($getFirstEmpty) {
            $ret[''] = '';
        }

        $categories = PartCategory::find()->all();
        foreach ($categories as $category) {
            $ret[$category->id] = $category->getFormatName();
        }

        return $ret;
    }

    /**
     * Выпадающий список состояния запчасти
     * @param boolean $getFirstEmpty получать первый пустой элемент
     * @return array
     */
    public static function getConditionDropDownList($getFirstEmpty = false)
    {
        $ret = [];

        if ($getFirstEmpty) {
            $ret[''] = '';
        }

        $values = HandbookValue::find()->andWhere(['handbook_code' => 'part_condition'])->all();
        foreach ($values as $value) {
            $ret[$value->id] = $value->name;
        }

        return $ret;
    }

    /**
     * Получить массив дерева категорий
     * @return array
     */
    public function getCategoriesTree()
    {
        $ret = [];

        if ($this->category_id && $category = $this->category) {
            $ret[$category->id] = $category->name;
            $previewDepth = $category->depth;
            if ($previewDepth > 1) {
                $list = PartCategory::find()
                    ->andWhere(['<', 'lft', $category->lft])
                    ->orderBy('lft DESC')
                    ->all();
                foreach ($list as $i) {
                    if ($i->depth == $previewDepth) {
                        continue;
                    }
                    $previewDepth = $i->depth;
                    $ret[$i->id] = $i->name;
                    if ($previewDepth == 1) {
                        break;
                    }
                }
            }
        }

        return array_reverse($ret, true);
    }

    /**
     * Привязка к объявлению
     * @return ActiveRecord
     */
    public function getAdvert()
    {
        return $this->hasOne(PartAdvert::className(), ['id' => 'advert_id']);
    }
}