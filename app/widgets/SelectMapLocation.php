<?php
namespace app\widgets;

use yii\helpers\Html;
use app\assets\SelectMapLocationAssets;

/**
 * Виджет выбора местоположения.
 *
 * В виджет необходимо передать:
 * - address - селектор инпута, где находится адрес;
 * - setLatitude - селектор инпута, где хранится широта, либо js-фнукция для установки широты;
 * - getLatitude - селектор инпута, где хранится широта, либо js-фнукция для получени широты;
 * - setLongitude - селектор инпута, где хранится долгота, либо js-функция для установки долготы;
 * - getLongitude - селектор инпута, где хранится широта, либо js-фнукция для получени долготы;
 */
class SelectMapLocation extends \yii\base\Widget
{
    /**
     * @var [] атрибуты для враппера карты
     */
    public $wrapperOptions;

    /**
     * @var string селектор адреса
     */
    public $address;

    /**
     * @var mixed установка широты
     */
    public $setLatitude;

    /**
     * @var mixed получение широты
     */
    public $getLatitude;

    /**
     * @var mixed установка долготы
     */
    public $setLongitude;

    /**
     * @var mixed получение долготы
     */
    public $getLongitude;

    public function run()
    {
        parent::run();
        if (!isset($this->wrapperOptions)) {
            $this->wrapperOptions = [];
        }
        if (!isset($this->wrapperOptions['id'])) {
            $this->wrapperOptions['id'] = $this->id;
        }
        if (!isset($this->wrapperOptions['style'])) {
            $this->wrapperOptions['style'] = 'width: 100%; height: 300px;';
        }
        SelectMapLocationAssets::register($this->view);
        $jsOptions = [
            'address'           => $this->address,
            'setLatitude'       => $this->setLatitude,
            'setLongitude'      => $this->setLongitude,
            'getLatitude'       => $this->getLatitude,
            'getLongitude'      => $this->getLongitude,
        ];
        $this->view->registerJs(new \yii\web\JsExpression('
            $(document).ready(function() {
                $(\'#' . $this->id . '\').selectLocation(' . json_encode($jsOptions) . ');
            });
        '));
        return Html::tag('div', '', $this->wrapperOptions);
    }
}