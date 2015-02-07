<?php
namespace advert\models;

use Yii;

use user\models\User;
use handbook\models\HandbookValue;

use auto\models\Mark;
use auto\models\Model;
use auto\models\Serie;
use auto\models\Modification;

use yii\base\Exception;

/**
 * Модель объявления
 */
class Advert extends \yii\db\ActiveRecord
{
    public $chooseMark;
    public $chooseModel;
    public $chooseSerie;
    public $chooseModification;

    /**
     * Таблица
     * @return string
     */
    public static function tableName()
    {
        return '{{%advert}}';
    }

    /**
     * Правила валидации
     * @return []
     */
    public function rules()
    {
        return [
            [['advert_name', 'price', 'condition_id', 'category_id'], 'required'],
            [['price'], 'filter', 'filter' => function($value) {
                return str_replace(',', '.', $value);
            }],
            [['price'], 'number', 'min' => 1, 'max' => 9999999],
            [['description'], 'safe'],
            [['user_id', 'condition_id', 'category_id'], 'integer'],
            [['user_name', 'user_phone', 'user_email'], 'required', 'when' => function($model) {
                // обязательна либо привязка к пользователю, либо координаты пользователя
                return empty($model->user_id);
            }],
            [['user_email'], 'email', 'when' => function($model) {
                return empty($model->user_id);
            }],
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
            'advert_name' => Yii::t('advert', 'Part name'),
            'price' => Yii::t('advert', 'Part price'),
            'condition_id' => Yii::t('advert', 'Part condition'),
            'category_id' => Yii::t('advert', 'Part category'),
            'user_name' => Yii::t('advert', 'Contact name'),
            'user_phone' => Yii::t('advert', 'Contact phone'),
            'user_email' => Yii::t('advert', 'Contact email'),
            'user_id' => Yii::t('advert', 'User id'),
            'active' => Yii::t('advert', 'Part active'),
            'description' => Yii::t('advert', 'Advert description'),
            'chooseMark' => Yii::t('advert', 'Choose mark'),
            'chooseModel' => Yii::t('advert', 'Choose model'),
            'chooseSerie' => Yii::t('advert', 'Choose serie'),
            'chooseModification' => Yii::t('advert', 'Choose modificaion'),
        ];
    }

    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->created = date('Y-m-d H:i:s');
        }
        $this->edited = date('Y-m-d H:i:s');

        return parent::beforeSave($insert);
    }

    /**
     * Получить пользователя
     * @return yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Получить категорию
     * @return yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }

    /**
     * Получить состояние запчасти
     * @return yii\db\ActiveQuery
     */
    public function getCondition()
    {
        return $this->hasOne(HandbookValue::className(), ['id' => 'condition_id'])
                ->where(['handbook_code' => 'part_condition']);
    }

    /**
     * Получить марки автомобилей
     * @return yii\db\ActiveQuery
     */
    public function getMark()
    {
        $this->hasMany(Mark::className(), ['id' => 'mark_id'])
            ->viaTable('{{%advert_mark}}', ['advert_id' => 'id']);
    }

    /**
     * Получить модели автомобилей
     * @return yii\db\ActiveQuery
     */
    public function getModel()
    {
        $this->hasMany(Model::className(), ['id' => 'model_id'])
            ->viaTable('{{%advert_model}}', ['advert_id' => 'id']);
    }

    /**
     * Получить серии автомобилей
     * @return yii\db\ActiveQuery
     */
    public function getSerie()
    {
        $this->hasMany(Serie::className(), ['id' => 'serie_id'])
            ->viaTable('{{%advert_serie}}', ['advert_id' => 'id']);
    }

    /**
     * Получить модификации автомобилей
     * @return yii\db\ActiveQuery
     */
    public function getModification()
    {
        $this->hasMany(Modification::className(), ['id' => 'modifiction_id'])
            ->viaTable('{{%advert_modification}}', ['advert_id' => 'id']);
    }

    /**
     * По названию таблицы возвращает колонку для связи с автомобилем.
     * Таблица должна быть любой из:
     * - mark;
     * - model;
     * - serie;
     * - modification;
     * В случае ошибки генерирует Exception
     *
     * @param string $tableName
     * @throws Exception
     */
    protected function getAutoXrefColumn($tableName)
    {
        $xrefColumn = '';

        switch ($tableName) {
            case '{{%advert_mark}}':
                $xrefColumn = 'mark_id';
                break;
            case '{{%advert_model}}':
                $xrefColumn = 'model_id';
                break;
            case '{{%advert_serie}}':
                $xrefColumn = 'serie_id';
                break;
            case '{{%advert_modification}}':
                $xrefColumn = 'modification_id';
                break;
            default:
                throw new Exception();
        }
    }

    /**
     * Очистить привязку по автомобилям.
     * Передается название таблицы для привязки и массив идентификаторов автомобиля.
     * Таблица должна быть любой из:
     * - mark;
     * - model;
     * - serie;
     * - modification;
     *
     * В случае, если запись новая - генерирует Exception.
     *
     * @param string $tableName
     * @return string
     * @throws Exception
     */
    protected function clearAutomobiles($tableName)
    {
        if ($this->isNewRecord) {
            throw new Exception();
        }

        $this->getDb()->createCommand()
            ->delete($tableName, 'advert_id=:id', [
                ':id' => $this->id
            ])
            ->execute();
    }

    /**
     * Прикрепить к объявлению автомобиль.
     * Передается название таблицы для привязки и массив идентификаторов автомобиля.
     * Таблица должна быть любой из:
     * - mark;
     * - model;
     * - serie;
     * - modification;
     *
     * В случае, если запись новая - генерирует Exception.
     *
     * @param string $tableName название таблицы для привязки
     * @param [] $ids массив идентификаторов автомобилей
     * @throws Exception
     */
    protected function attachAutomobile($tableName, $ids)
    {
        $this->clearAutomobiles($tableName);

        $xrefColumn = $this->getAutoXrefColumn($tableName);

        // сгенерировать строки для записи
        $rows = [];
        foreach ($ids as $id) {
            $rows[] = [
                $xrefColumn => $id,
                'advert_id' => $this->id,
            ];
        }

        if (!empty($ids)) {
            $this->getDb()->createCommand()
                ->batchInsert($tableName, [$xrefColumn, 'advert_id'], $rows)
                ->execute();
        }
    }

    /**
     * Прикрепить к объявлению марки.
     * В случае, если это новая запись - генерирует Exception.
     * @param [] $markIds
     */
    public function attachMark($markIds)
    {
        $this->attachAutomobile('{{%advert_mark}}', $markIds);
    }

    /**
     * Прикрепить к объявлению модели.
     * В случае, если это новая запись - генерирует Exception.
     * @param [] $modelIds
     */
    public function attachModel($modelIds)
    {
        $this->attachAutomobile('{{%advert_model}}', $modelIds);
    }

    /**
     * Прикрепить к объявлению серии.
     * В случае, если это новая запись - генерирует Exception.
     * @param [] $serieIds
     */
    public function attachSerie($serieIds)
    {
        $this->attachAutomobile('{{%advert_serie}}', $serieIds);
    }

    /**
     * Прикрепить к объявлению модификации.
     * В случае, если это новая запись - генерирует Exception.
     * @param [] $modificationIds
     */
    public function attachModification($modificationIds)
    {
        $this->attachAutomobile('{{%advert_modification}}', $modificationIds);
    }

    /**
     * Выпадающий список категорий
     * @return []
     */
    public static function getCategoryDropDownList()
    {
        $ret = [];

        $categories = Category::find()->all();
        foreach ($categories as $category) {
            $ret[$category->id] = $category->getFormatName();
        }

        return $ret;
    }

    /**
     * Выпадающий список состояния запчасти
     * @return []
     */
    public static function getConditionDropDownList()
    {
        $ret = [];

        $values = HandbookValue::find()->andWhere(['handbook_code' => 'part_condition'])->all();
        foreach ($values as $value) {
            $ret[$value->id] = $value->name;
        }

        return $ret;
    }
}