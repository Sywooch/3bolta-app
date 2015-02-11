<?php
namespace auto\widgets;

use yii\helpers\Json;
use yii\helpers\Html;

use yii\helpers\Url;

use auto\assets\ChooseAutomobile as ChooseAutomobileAssets;

/**
 * Виджет модального окна для выбора автомобилей
 */
class ChooseAutomobile extends \yii\base\Widget
{
    /**
     * @var идентификатор контейнера
     */
    public $id;

    /**
     * @var string класс для панели
     */
    public $panelClass;

    /**
     * @var string класс для контейнера
     */
    public $containerClass;

    /**
     * @var [] параметры для JS-плагина
     */
    public $pluginOptions = [
        'multipleSelect' => false,
        'markUrl' => null,
        'modelUrl' => null,
        'serieUrl' => null,
        'modificationUrl' => null,
        'markName' => null,
        'modelName' => null,
        'serieName' => null,
        'modificationName' => null,
        'markIds' => [],
        'modelIds' => [],
        'serieIds' => [],
        'modificationIds' => [],
        'markWrapper' => null,
        'modelWrapper' => null,
        'serieWrapper' => null,
        'modificationWrapper' => null,
        'renderItem' => null,
    ];

    public function __construct($config = array())
    {
        if (isset($config['pluginOptions'])) {
            $config['pluginOptions'] = \yii\helpers\ArrayHelper::merge($this->pluginOptions, $config['pluginOptions']);
        }
        parent::__construct($config);
    }

    /**
     * Сгенерировать панель модального окна
     * @parm string $class
     * @param string $label
     */
    public function renderPanel($class, $label)
    {
        $panelClass = $this->panelClass ? $this->panelClass : 'panel panel-default choose-auto-panel col-xs-3 col-sm-3';
        print Html::beginTag('div', [
            'class' => $panelClass,
        ]);
        print Html::tag('label', $label, [
            'class' => 'control-label',
        ]);
        print Html::beginTag('div', [
            'class' => 'choose-auto-panel-body',
        ]);
        print Html::beginTag('div', [
            'class' => $class
        ]);
        print Html::endTag('div');
        print Html::endTag('div');
        print Html::endTag('div');
    }

    /**
     * Зарегистрировать скрипты
     */
    protected function registerJs()
    {
        $pluginOptions = Json::encode($this->pluginOptions);
        ChooseAutomobileAssets::register($this->getView());
        $js = "$('#{$this->id}').chooseAutomobile({$pluginOptions});";
        $this->getView()->registerJs($js);
    }

    public function init()
    {
        parent::init();

        if (!$this->id) {
            $this->id = 'chooseAutomobile' . uniqid();
        }
        if (!$this->pluginOptions['markUrl']) {
            $this->pluginOptions['markUrl'] = Url::toRoute(['/auto/choose-auto/mark']);
        }
        if (!$this->pluginOptions['modelUrl']) {
            $this->pluginOptions['modelUrl'] = Url::toRoute(['/auto/choose-auto/model']);
        }
        if (!$this->pluginOptions['serieUrl']) {
            $this->pluginOptions['serieUrl'] = Url::toRoute(['/auto/choose-auto/serie']);
        }
        if (!$this->pluginOptions['modificationUrl']) {
            $this->pluginOptions['modificationUrl'] = Url::toRoute(['/auto/choose-auto/modification']);
        }

        $containerClass = $this->containerClass ? $this->containerClass : 'container choose-auto-container';
        print Html::beginTag('div', [
            'class' => 'js-choose-auto-container ' . $containerClass,
            'id' => $this->id,
        ]);
    }

    public function run()
    {
        print Html::endTag('div');

        $this->registerJs();

        return parent::run();
    }
}