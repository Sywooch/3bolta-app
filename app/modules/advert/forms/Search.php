<?php
namespace advert\forms;

use Yii;

use advert\models\Advert;

use auto\models\Mark;
use auto\models\Model;
use auto\models\Serie;
use auto\models\Modification;

/**
 * Форма поиска вверху
 */
class Search extends \yii\base\Model
{
    /**
     * Максимальная длина поисковой строки
     */
    const MAX_QUERY_LENGTH = 50;

    /**
     * Автомобили: марка, модель, серия, модификация
     */
    public $a1;
    public $a2;
    public $a3;
    public $a4;

    /**
     * Категория
     */
    public $cat;

    /**
     * Состояние
     */
    public $con;

    /**
     * Запрос
     */
    public $q;

    /**
     * Правила валидации
     * @return []
     */
    public function rules()
    {
        return [
            [['a1', 'a2', 'a3', 'a4', 'cat', 'con'], 'integer'],
            [['q'], 'safe'],
            [['q'], 'filter', 'filter' => function($q) {
                // отсекаем символы
                return strlen($q) > self::MAX_QUERY_LENGTH ? substr($q, 0, self::MAX_QUERY_LENGTH) : $q;
            }],
            [['cat'], 'filter', 'filter' => function($i) {
                // удаляем не нужные категории
                $avail = array_keys(Advert::getCategoryDropDownList());
                return in_array($i, $avail) ? $i : null;
            }],
            [['con'], 'filter', 'filter' => function($i) {
                // удаляем не нужные состояния
                $avail = array_keys(Advert::getConditionDropDownList());
                return in_array($i, $avail) ? $i : null;
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
     * @return []
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
}