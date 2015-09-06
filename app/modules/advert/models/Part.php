<?php
namespace advert\models;

use advert\components\PartsIndex;
use auto\models\Mark;
use auto\models\Model;
use auto\models\Modification;
use auto\models\Serie;
use handbook\models\HandbookValue;
use Yii;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * Модель объявления запчасти. Здесь производится:
 * - привязка объявления к автомобилям;
 * - привязка объявления к модели AdvertPartParam;
 * - остальные действия, которых недостаточно в основной модели Advert.
 */
class Part extends Advert
{
    /**
     * Связующая таблица марок
     */
    const TABLE_MARK = '{{%advert_mark}}';

    /**
     * Связующая таблица моделей
     */
    const TABLE_MODEL = '{{%advert_model}}';

    /**
     * Связующая таблица модификаций
     */
    const TABLE_MODIFICATION = '{{%advert_modification}}';

    /**
     * Связующая таблица серий
     */
    const TABLE_SERIE = '{{%advert_serie}}';

    /**
     * @var array массив привязки к маркам
     */
    protected $_marks;

    /**
     * @var array массив привязки к моделям
     */
    protected $_models;

    /**
     * @var array массив привязки к сериям
     */
    protected $_series;

    /**
     * @var array массив привязки к модификациям
     */
    protected $_modifications;

    /**
     * Правила валидации
     * @return array
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['marks', 'models', 'series', 'modifications'], 'safe'],
        ]);
    }

    /**
     * Подписи атрибутов
     * @return array
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'marks' => Yii::t('advert', 'Choose mark'),
            'models' => Yii::t('advert', 'Choose model'),
            'series' => Yii::t('advert', 'Choose serie'),
            'modifications' => Yii::t('advert', 'Choose modificaion'),
        ]);
    }

    /**
     * Обновить автомобили, если требуется
     */
    public function updateAutomobiles()
    {
        $this->attachMark(is_array($this->_marks) ? $this->_marks : []);
        $this->attachModel(is_array($this->_models) ? $this->_models : []);
        $this->attachSerie(is_array($this->_series) ? $this->_series : []);
        $this->attachModification(is_array($this->_modifications) ? $this->_modifications : []);
    }

    /**
     * После сохранения очистить автомобили, привязку к параметрам.
     *
     * @param mixed $insert
     * @param mixed $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        unset ($this->partParam);
        unset ($this->mark);
        unset ($this->model);
        unset ($this->serie);
        unset ($this->modification);

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Получить привязку к параметрами объявления запчасти:
     * - номер запчасти;
     * - состояние;
     * - категория.
     *
     * @return ActiveQuery
     */
    public function getPartParam()
    {
        return $this->hasOne(PartParam::className(), ['advert_id' => 'id']);
    }

    /**
     * Получить марки автомобилей
     * @return ActiveQuery
     */
    public function getMark()
    {
        return $this->hasMany(Mark::className(), ['id' => 'mark_id'])
            ->viaTable(self::TABLE_MARK, ['advert_id' => 'id']);
    }

    /**
     * Получить модели автомобилей
     * @return ActiveQuery
     */
    public function getModel()
    {
        return $this->hasMany(Model::className(), ['id' => 'model_id'])
            ->viaTable(self::TABLE_MODEL, ['advert_id' => 'id']);
    }

    /**
     * Получить серии автомобилей
     * @return ActiveQuery
     */
    public function getSerie()
    {
        return $this->hasMany(Serie::className(), ['id' => 'serie_id'])
            ->viaTable(self::TABLE_SERIE, ['advert_id' => 'id']);
    }

    /**
     * Получить модификации автомобилей
     * @return ActiveQuery
     */
    public function getModification()
    {
        return $this->hasMany(Modification::className(), ['id' => 'modification_id'])
            ->viaTable(self::TABLE_MODIFICATION, ['advert_id' => 'id']);
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
            case self::TABLE_MARK:
                $xrefColumn = 'mark_id';
                break;
            case self::TABLE_MODEL:
                $xrefColumn = 'model_id';
                break;
            case self::TABLE_SERIE:
                $xrefColumn = 'serie_id';
                break;
            case self::TABLE_MODIFICATION:
                $xrefColumn = 'modification_id';
                break;
            default:
                throw new Exception();
        }

        return $xrefColumn;
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
     * @param array $ids массив идентификаторов автомобилей
     * @throws Exception
     */
    protected function attachAutomobile($tableName, $ids)
    {
        $this->clearAutomobiles($tableName);

        $xrefColumn = $this->getAutoXrefColumn($tableName);

        // сгенерировать строки для записи
        $rows = [];
        foreach ($ids as $id) {
            if ($id) {
                $rows[] = [$id, $this->id];
            }
        }

        if (!empty($rows)) {
            $this->getDb()->createCommand()
                ->batchInsert($tableName, [$xrefColumn, 'advert_id'], $rows)
                ->execute();
        }
    }

    /**
     * Прикрепить к объявлению марки.
     * В случае, если это новая запись - генерирует Exception.
     * @param array $markIds
     */
    public function attachMark($markIds)
    {
        $this->attachAutomobile(self::TABLE_MARK, $markIds);
    }

    /**
     * Прикрепить к объявлению модели.
     * В случае, если это новая запись - генерирует Exception.
     * @param array $modelIds
     */
    public function attachModel($modelIds)
    {
        $this->attachAutomobile(self::TABLE_MODEL, $modelIds);
    }

    /**
     * Прикрепить к объявлению серии.
     * В случае, если это новая запись - генерирует Exception.
     * @param array $serieIds
     */
    public function attachSerie($serieIds)
    {
        $this->attachAutomobile(self::TABLE_SERIE, $serieIds);
    }

    /**
     * Прикрепить к объявлению модификации.
     * В случае, если это новая запись - генерирует Exception.
     * @param array $modificationIds
     */
    public function attachModification($modificationIds)
    {
        $this->attachAutomobile(self::TABLE_MODIFICATION, $modificationIds);
    }

    /**
     * Возвращает массив идентификаторов привязанных марок
     * @return array
     */
    public function getMarks()
    {
        if ($this->_marks === null) {
            $this->_marks = array_values(ArrayHelper::map($this->mark, 'id', 'id'));
        }
        return $this->_marks;
    }

    /**
     * Возвращает массив идентификаторов привязанных моделей
     * @return array
     */
    public function getModels()
    {
        if ($this->_models === null) {
            $this->_models = array_values(ArrayHelper::map($this->model, 'id', 'id'));
        }
        return $this->_models;
    }

    /**
     * Возвращает массив идентификаторов привязанных серий
     * @return array
     */
    public function getSeries()
    {
        if ($this->_series === null) {
            $this->_series = array_values(ArrayHelper::map($this->serie, 'id', 'id'));
        }
        return $this->_series;
    }

    /**
     * Возвращает массив идентификаторов привязанных модификаций
     * @return array
     */
    public function getModifications()
    {
        if ($this->_modifications === null) {
            $this->_modifications = array_values(ArrayHelper::map($this->modification, 'id', 'id'));
        }
        return $this->_modifications;
    }

    /**
     * Установить новые марки
     * @param array $ids
     */
    public function setMarks($ids)
    {
        if (is_array($ids)) {
            $this->_marks = $ids;
        }
        else {
            $this->_marks = [];
        }
    }

    /**
     * Установить новые модели
     * @param array $ids
     */
    public function setModels($ids)
    {
        if (is_array($ids)) {
            $this->_models = $ids;
        }
        else {
            $this->_models = [];
        }
    }

    /**
     * Установить новые серии
     * @param array $ids
     */
    public function setSeries($ids)
    {
        if (is_array($ids)) {
            $this->_series = $ids;
        }
        else {
            $this->_series = [];
        }
    }

    /**
     * Установить новые модификации
     * @param array $ids
     */
    public function setModifications($ids)
    {
        if (is_array($ids)) {
            $this->_modifications = $ids;
        }
        else {
            $this->_modifications = [];
        }
    }

    /**
     * Получить название состояния
     * @return string
     */
    public function getConditionName()
    {
        if ($this->partParam instanceof PartParam) {
            /* @var $partParam PartParam */
            $partParam = $this->partParam;
            if ($partParam->condition instanceof HandbookValue) {
                return $partParam->condition->name;
            }
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

        /* @var $partParam PartParam */
        $partParam = $this->partParam;
        if ($partParam->category_id && $category = $partParam->category) {
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
     * Удалить объявление из индекса
     *
     * @throws Exception
     */
    public function deleteIndex()
    {
        /* @var $partsIndex PartsIndex */
        $partsIndex = \Yii::$app->getModule('advert')->partsIndex;

        try {
            $result = $partsIndex->deleteOne($this);
        }
        catch (\Exception $ex) {
            throw new Exception();
        }
    }

    /**
     * Обновить поисковый индекс объявления.
     * В случае ошибки генерирует исключение.
     *
     * @throws Exception
     */
    public function updateIndex()
    {
        if (strtotime($this->published_to) <= time() || !$this->active) {
            return;
        }

        /* @var $partsIndex PartsIndex */
        $partsIndex = \Yii::$app->getModule('advert')->partsIndex;

        try {
            $result = $partsIndex->reindexOne($this);
            if ($result->getStatus() != 0) {
                throw new \Exception();
            }
        }
        catch (\Exception $ex) {
            throw new Exception();
        }
    }
}