<?php
namespace auto\widgets;

use Yii;

use yii\helpers\Json;
use yii\helpers\Html;

use auto\models\Mark;

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
     * @var string заголовок модального окна
     */
    public $header;

    /**
     * @var string ссылка для получения марок
     */
    public $markUrl;

    /**
     * @var string ссылка для получения моделей
     */
    public $modelUrl;

    /**
     * @var string ссылка для получения серий
     */
    public $serieUrl;

    /**
     * @var string ссылка для получения модфикиций
     */
    public $modificationUrl;

    /**
     * @var string шаблон элемента
     */
    public $itemTemplate = '<div class="checkbox {$jsClass}"><label><input type="checkbox" {$checked} name="{$attributeName}" value="{$id}" />{$name}</label></div>';

    /**
     * @var string
     */
    public $markName;

    /**
     * @var string
     */
    public $modelName;

    /**
     * @var string
     */
    public $serieName;

    /**
     * @var string
     */
    public $modificationName;

    /**
     * @var []
     */
    public $markIds = [];

    /**
     * @var []
     */
    public $modelIds = [];

    /**
     * @var []
     */
    public $serieIds = [];

    /**
     * @var []
     */
    public $modificationIds = [];

    /**
     * Получить список марок
     * @return array
     */
    protected function getMarkList()
    {
        $markList = [];

        $res = Mark::find()->all();
        foreach ($res as $i) {
            $markList[$i->id] = $i->name;
        }

        return $markList;
    }

    /**
     * Сгенерировать панель модального окна
     * @parm string $class
     * @param string $label
     */
    protected function renderPanel($class, $label)
    {
        print Html::beginTag('div', [
            'class' => 'panel panel-default choose-auto-panel col-xs-3 col-sm-3',
        ]);
        print Html::tag('label', $label, [
            'class' => 'control-label',
        ]);
        print Html::beginTag('div', [
            'class' => 'choose-auto-panel-body',
        ]);
        print Html::beginTag('div', [
            'class' => 'form-group ' . $class
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
        ChooseAutomobileAssets::register($this->getView());
        $js = "
            new chooseAutomobile({
                'wrapper'           : '#{$this->id}',
                'markUrl'           : '{$this->markUrl}',
                'modelUrl'          : '{$this->modelUrl}',
                'serieUrl'          : '{$this->serieUrl}',
                'modificationUrl'   : '{$this->modificationUrl}',
                'template'          : '{$this->itemTemplate}',
                'markName'          : '{$this->markName}',
                'modelName'         : '{$this->modelName}',
                'serieName'         : '{$this->serieName}',
                'modificationName'  : '{$this->modificationName}',
                'markIds'           : " . Json::encode($this->markIds) . ",
                'modelIds'          : " . Json::encode($this->modelIds) . ",
                'serieIds'          : " . Json::encode($this->serieIds) . ",
                'modificationIds'   : " . Json::encode($this->modificationIds) . "
            });
        ";
        $this->getView()->registerJs($js);
    }

    public function init()
    {
        parent::init();

        $this->header = Yii::t('auto', 'Choose automobiles');

        if (!$this->id) {
            $this->id = 'chooseAutomobile' . uniqid();
        }
        if (!$this->markUrl) {
            $this->markUrl = Url::toRoute(['/auto/choose-auto/mark']);
        }
        if (!$this->modelUrl) {
            $this->modelUrl = Url::toRoute(['/auto/choose-auto/model']);
        }
        if (!$this->serieUrl) {
            $this->serieUrl = Url::toRoute(['/auto/choose-auto/serie']);
        }
        if (!$this->modificationUrl) {
            $this->modificationUrl = Url::toRoute(['/auto/choose-auto/modification']);
        }
    }

    public function run()
    {
        print Html::beginTag('div', [
            'class' => 'container choose-auto-container js-choose-auto-container',
            'id' => $this->id,
        ]);
        print Html::tag('div', '', ['class' => 'choose-auto-loader col-xs-3 col-sm-3']);
        $this->renderPanel('choose-auto-mark', Yii::t('advert', 'Choose mark'));
        $this->renderPanel('choose-auto-model', Yii::t('advert', 'Choose model'));
        $this->renderPanel('choose-auto-serie', Yii::t('advert', 'Choose serie'));
        $this->renderPanel('choose-auto-modification', Yii::t('advert', 'Choose modification'));
        print Html::endTag('div');

        $this->registerJs();

        return parent::run();
    }
}