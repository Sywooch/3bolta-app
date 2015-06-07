<?php
namespace app\widgets;

use Yii;

use yii\helpers\Json;
use yii\helpers\Html;
use app\assets\SelectMapLocationAssets;
use yii\helpers\ArrayHelper;

/**
 * Виджет выбора местоположения.
 *
 * В виджет необходимо передать:
 * - model - модель формы или ActiveRecord;
 * - attribute - атрибут для ввода адреса;
 * - attributeLatitude - атрибут для ввода широты;
 * - attributeLongitude - атрибут для ввода долготы;
 * - renderWidgetMap - функция для рендера виджета, в нее передается переменная $map - html карты.
 */
class SelectMapLocation extends \yii\base\Widget
{
    /**
     * @var \yii\web\View
     */
    public $view;

    /**
     * @var \yii\base\Model
     */
    public $model;

    /**
     * @var string атрибут для ввода адреса
     */
    public $attribute;

    /**
     * @var string атрибут для ввода широты
     */
    public $attributeLatitude;

    /**
     * @var string атрибут для ввода долготы
     */
    public $attributeLongitude;

    /**
     * @var array атрибуты для враппера карты
     */
    public $wrapperOptions;

    /**
     * @var array атрибуты для текстового инпута
     */
    public $textOptions = ['class' => 'form-control'];

    /**
     * @var array опции для JS-плагина
     */
    public $jsOptions = [];

    /**
     * @var callable функция для рендера виджета
     */
    public $renderWidgetMap;

    /**
     * Вывод плагина
     */
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
            $this->wrapperOptions['style'] = 'width: 100%; height: 500px;';
        }
        SelectMapLocationAssets::register($this->view);

        // получить идентификаторы инпутов
        $address = Html::getInputId($this->model, $this->attribute);
        $latitude = Html::getInputId($this->model, $this->attributeLatitude);
        $longitude = Html::getInputId($this->model, $this->attributeLongitude);

        $jsOptions = ArrayHelper::merge($this->jsOptions, [
            'address'           => '#' . $address,
            'latitude'          => '#' . $latitude,
            'longitude'         => '#' . $longitude,
        ]);
        // сообщение о ненайденном адресе
        if (!isset($jsOptions['addressNotFound'])) {
            $jsOptions['addressNotFound'] = Yii::t('main', 'Address not found');
        }
        $this->view->registerJs(new \yii\web\JsExpression('
            $(document).ready(function() {
                $(\'#' . $this->wrapperOptions['id'] . '\').selectLocation(' . Json::encode($jsOptions) . ');
            });
        '));
        $mapHtml = Html::tag('div', '', $this->wrapperOptions);
        $mapHtml .= Html::activeHiddenInput($this->model, $this->attributeLatitude);
        $mapHtml .= Html::activeHiddenInput($this->model, $this->attributeLongitude);

        if (is_callable($this->renderWidgetMap)) {
            return call_user_func_array($this->renderWidgetMap, [$mapHtml]);
        }
        else {
            return Html::activeInput('text', $this->model, $this->attribute, $this->textOptions) . $mapHtml;
        }
    }
}