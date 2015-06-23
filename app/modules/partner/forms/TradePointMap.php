<?php
namespace partner\forms;

use auto\models\Mark;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Модель формы для поиска торговых точек на карте:
 * - координаты левого нижнего угла карты;
 * - координаты правого верхнего угла карты;
 * - специализация;
 * - поиск по вхождению строки;
 */
class TradePointMap extends Model
{
    /**
     * @var array массив координат вида: array('ne' => array('lat' => <float>, 'lng' => <float>), 'sw' => ...);
     */
    protected $_coordinates;

    /**
     * @var int специализация (выбор идентификатора марки)
     */
    protected $_specialization;

    /**
     * @var string поиск по вхождению строки
     */
    public $name;

    /**
     * Правила валидации
     * @return array
     */
    public function rules()
    {
        return [
            ['name', 'string', 'max' => 255],
            [['specialization', 'coordinates'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'specialization' => Yii::t('frontend/partner', 'Specialization'),
            'name' => Yii::t('frontend/partner', 'Search by organization name'),
            'coordinates' => Yii::t('frontend/partner', 'Coordinates'),
        ];
    }

    /**
     * Установка координат в переменную _coordinates.
     * Будут установлены всегда только валидные координаты, иначе - null.
     *
     * @param string $val
     */
    public function setCoordinates($val)
    {
        $val = (string) $val;

        $this->_coordinates = null;

        try {
            $arr = Json::decode($val, true);
            $arr['ne'] = [
                'lat' => isset($arr['ne']['lat']) ? (float) $arr['ne']['lat'] : null,
                'lng' => isset($arr['ne']['lng']) ? (float) $arr['ne']['lng'] : null,
            ];
            $arr['sw'] = [
                'lat' => isset($arr['sw']['lat']) ? (float) $arr['sw']['lat'] : null,
                'lng' => isset($arr['sw']['lng']) ? (float) $arr['sw']['lng'] : null,
            ];
            if (!is_null($arr['ne']['lat']) || !is_null($arr['ne']['lng']) ||
                !is_null($arr['sw']['lat']) || !is_null($arr['sw']['lat'])) {
                $this->_coordinates = $arr;
            }
        }
        catch (Exception $ex) {
            $this->_coordinates = null;
        }
    }

    /**
     * Получить координаты в виде строки
     * @return string
     */
    public function getCoordinates()
    {
        return is_array($this->_coordinates) ? Json::encode($this->_coordinates) : '';
    }

    /**
     * Установить специализацию
     *
     * @param int $val
     */
    public function setSpecialization($val)
    {
        $val = (int) $val;
        $this->_specialization = $val ? $val : null;
    }

    /**
     * Получить специализацию
     *
     * @return int
     */
    public function getSpecialization()
    {
        return $this->_specialization;
    }

    /**
     * Получить координаты в виде массива
     * @return array
     */
    public function getCoordinatesArray()
    {
        return $this->_coordinates;
    }

    /**
     * Получить массив специализаций (марок) для вставки в автокомплит
     *
     * @return array
     */
    public function getSpecializationAutocomplete()
    {
        return array_values(ArrayHelper::map(Mark::find()->all(), 'id', function($data) {
            /* @var $data Mark */
            return [
                'label' => $data->name,
                'value' => $data->id,
            ];
        }));
    }
}