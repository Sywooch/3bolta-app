<?php
namespace advert\models;

use Yii;
use yii\db\ActiveRecord;
use app\extensions\nestedsets\NestedSetsBehavior;

/**
 * Модель категории запчастей
 */
class Category extends ActiveRecord
{
    /**
     * @var int предыдущее значение parent_id
     */
    private $_previewParentId;

    /**
     * @var int предыдущее значение sort
     */
    private $_previewSort;

    /**
     * @var boolean заблокировать запись
     */
    private $lockSave = false;

    /**
     * Название таблицы
     * @return string
     */
    public static function tableName()
    {
        return '{{%advert_category}}';
    }

    /**
     * Правила валидации
     * @return []
     */
    public function rules()
    {
        return [
            [['name', 'sort'], 'required'],
            [['parent_id', 'sort'], 'integer', 'skipOnEmpty' => true],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sort' => Yii::t('advert', 'Sort'),
            'name' => Yii::t('advert', 'Category name'),
            'parent_id' => Yii::t('advert', 'Parent category'),
        ];
    }

    /**
     * Поведения
     * @return []
     */
    public function behaviors()
    {
        return [
            'tree' => [
                'class' => NestedSetsBehavior::className(),
                'leftAttribute' => 'lft',
                'rightAttribute' => 'rgt',
                'depthAttribute' => 'depth',
            ]
        ];
    }

    /**
     * Сохранение
     * @param boolean $runValidation
     * @param [] $attributeNames
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if (
            (!$this->isNewRecord && $this->_previewParentId == $this->parent_id && $this->_previewSort == $this->sort) ||
            $this->lockSave) {
            $this->_previewParentId = $this->parent_id;
            $this->_previewSort = $this->sort;
            $this->lockSave = false;
            return parent::save($runValidation, $attributeNames);
        }

        $parentWhere = $this->parent_id ? ['parent_id' => $this->parent_id] : ['parent_id' => null];

        // новая дочерняя запись
        // получить предыдущую запись
        $node = self::find()
            ->andWhere(['<=', 'sort', $this->sort])
            ->andWhere($parentWhere)
            ->orderBy('sort ASC')
            ->one();
        if (!empty($node)) {
            // вставить после предыдущей записью
            $this->lockSave = true;
            return $this->insertAfter($node, $runValidation, $attributeNames);
        }

        // получить следующую запись
        $node = self::find()
            ->andWhere(['>=', 'sort', $this->sort])
            ->andWhere($parentWhere)
            ->orderBy('sort ASC')
            ->one();
        if (!empty($node)) {
            // вставить перед следующей записью
            $this->lockSave = true;
            return $this->insertBefore($node, $runValidation, $attributeNames);
        }

        // просто добавить к родителю
        if (!empty($this->parent_id)) {
            $parent = self::find()
                ->andWhere(['id' => $this->parent_id])
                ->one();
            if (!empty($parent)) {
                $this->lockSave = true;
                return $this->prependTo($parent, $runValidation, $attributeNames);
            }
        }

        // во всех остальных случаях добавляем в корень root
        $root = self::findRoot()->one();
        $this->lockSave = true;
        return $this->prependTo($root, $runValidation, $attributeNames);
    }

    public function afterFind()
    {
        $this->_previewParentId = $this->parent_id;
        $this->_previewSort = $this->sort;
        $this->lockSave = false;

        return parent::afterFind();
    }

    /**
     * @return ActiveQuery
     */
    public static function find()
    {
        return parent::find()
            ->where(['not', ['depth' => 0]])
            ->orderBy('lft ASC, sort ASC, id ASC');
    }

    /**
     * @return ActiveQuery
     */
    public static function findRoot()
    {
        return parent::find()->where(['depth' => 0]);
    }

    /**
     * Выпадающий список для парентов
     * @return []
     */
    public static function getParentsList()
    {
        $ret = ['' => ''];

        $parents = self::find()->all();

        foreach ($parents as $parent) {
            $ret[$parent->id] = str_repeat('--', (int) ($parent->depth - 1)) . $parent->name;
        }

        return $ret;
    }

    /**
     * Возвращает имя с дефисами спереди для определения уровня вложенности
     * @return string
     */
    public function getFormatName()
    {
        if ($this->depth > 0) {
            $ret = str_repeat('--', $this->depth - 1);
        }
        $ret .= $this->name;
        return $ret;
    }
}
