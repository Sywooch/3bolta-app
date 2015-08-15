<?php
namespace advert\forms;

use advert\models\PartAdvert;
use auto\models\Mark;
use auto\models\Model;
use auto\models\Modification;
use auto\models\Serie;
use geo\models\Region;
use handbook\models\HandbookValue;
use Yii;
use yii\base\Model as BaseModel;
use yii\helpers\ArrayHelper;

/**
 * Форма поиска запчастей
 */
class PartSearch extends BaseModel
{
    /**
     * Максимальная длина поисковой строки
     */
    const MAX_QUERY_LENGTH = 50;

    /**
     * @var integer марка
     */
    public $a1;

    /**
     * @var integer модель
     */
    public $a2;

    /**
     * @var integer серия
     */
    public $a3;

    /**
     * @var integer модификация
     */
    public $a4;

    /**
     * @var integer Категория
     */
    public $cat;

    /**
     * @var integer Состояние
     */
    public $con;

    /**
     * @var string Запрос по строке
     */
    public $q;

    /**
     * @var float цена "от"
     */
    public $p1;

    /**
     * @var float цена "до"
     */
    public $p2;

    /**
     * @var integer регион
     */
    public $r;

    /**
     * @var float тип продавца
     */
    public $st;

    /**
     * @var boolean показывать также другие регионы
     */
    public $sor = true;

    /**
     * Правила валидации
     * @return array
     */
    public function rules()
    {
        return [
            [['a1', 'a2', 'a3', 'a4', 'cat', 'con'], 'integer'],
            [['p1', 'p2'], 'safe'],
            [['q', 'sor'], 'safe'],
            [['q'], 'filter', 'filter' => function($q) {
                // отсекаем символы
                return strlen($q) > self::MAX_QUERY_LENGTH ? substr($q, 0, self::MAX_QUERY_LENGTH) : $q;
            }],
            [['cat'], 'filter', 'filter' => function($i) {
                // удаляем не нужные категории
                $avail = array_keys(PartAdvert::getCategoryDropDownList());
                return in_array($i, $avail) ? $i : null;
            }],
            [['con'], 'filter', 'filter' => function($i) {
                // удаляем не нужные состояния
                $avail = array_keys(PartAdvert::getConditionDropDownList());
                return in_array($i, $avail) ? $i : null;
            }],
            [['p1', 'p2'], 'filter', 'filter' => function($val) {
                $val = preg_replace('#[\D]+#', '', $val);
                $val = (float) $val;
                return $val > 0 ? $val : null;
            }],
            ['r', 'filter', 'filter' => function($val) {
                $val = (int) $val;
                return Region::find()->andWhere(['id' => $val])->exists() ? $val : null;
            }],
            ['st', 'filter', 'filter' => function($val) {
                $availValues = PartSearch::getSellerTypeDropDown();
                return isset($val, $availValues) ? $val : null;
            }],
        ];
    }

    /**
     * Подпись формы для фронтенда
     * @return string
     */
    public function formName()
    {
        return '_';
    }

    /**
     * Подписи
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'a1' => Yii::t('frontend/advert', 'Mark'),
            'a2' => Yii::t('frontend/advert', 'Model'),
            'a3' => Yii::t('frontend/advert', 'Serie'),
            'a4' => Yii::t('frontend/advert', 'Modification'),
            'cat' => Yii::t('frontend/advert', 'Category'),
            'con' => Yii::t('frontend/advert', 'Condition'),
            'q' => Yii::t('frontend/advert', 'Part name'),
            'p1' => Yii::t('frontend/advert', 'Price from'),
            'p2' => Yii::t('frontend/advert', 'Price to'),
            'r' => Yii::t('frontend/advert', 'Region'),
            'st' => Yii::t('frontend/advert', 'Seller type'),
            'sor' => Yii::t('frontend/advert', 'Show other regions'),
        ];
    }

    /**
     * Получить полное название искомого автомобиля
     *
     * @return string
     */
    public function getAutomobileFullName()
    {
        $ret = null;

        if ($this->a4 && $modification = Modification::find()->andWhere(['id' => $this->a4])->one()) {
            $ret = $modification->full_name;
        }
        else if ($this->a3 && $serie = Serie::find()->andWhere(['id' => $this->a3])->one()) {
            $ret = $serie->full_name;
        }
        else if ($this->a2 && $model = Model::find()->andWhere(['id' => $this->a2])->one()) {
            $ret = $model->full_name;
        }
        else if ($this->a1 && $mark = Mark::find()->andWhere(['id' => $this->a1])->one()) {
            $ret = $mark->full_name;
        }

        return $ret;
    }

    /**
     * По человекопонятному параметру (model, mark, serie, modification)
     * возвращает параметр для поиска (a1, a2, a3, a4).
     *
     * @param string $key
     * @return string
     */
    public static function getAutoParam($key)
    {
        switch ($key) {
            case 'mark':
                $key = 'a1';
                break;
            case 'model':
                $key = 'a2';
                break;
            case 'serie':
                $key = 'a3';
                break;
            case 'modification':
                $key = 'a4';
                break;
        }

        return $key;
    }

    /**
     * Получить тип продавца в зависимости от настроек
     * @return array
     */
    public static function getSellerTypeDropDown()
    {
        $ret = [
            '' => '',
            0 => Yii::t('frontend/advert', 'private person')
        ];
        return ArrayHelper::merge($ret, ArrayHelper::map(HandbookValue::find()->andWhere([
            'handbook_code' => 'company_type'
        ])->all(), 'id', 'name'));
    }

    /**
     * Получить идентификаторы регионов
     * @return array
     */
    public static function getRegionsDropDownList()
    {
        return ArrayHelper::map(Region::find()->all(), 'id', 'site_name');
    }
}