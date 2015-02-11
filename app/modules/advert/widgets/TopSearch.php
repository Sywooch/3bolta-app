<?php
namespace advert\widgets;

use Yii;
use yii\helpers\Html;
use auto\widgets\ChooseAutomobile;
use yii\bootstrap\Modal;

/**
 * Виджет поиска по автозапчастям вверху шапки
 */
class TopSearch extends \yii\bootstrap\Widget
{
    /**
     * Вывести виджет выбора автомобиля
     */
    protected function searchAutomobileRender()
    {

    }

    /**
     * Вывести модальное окно выбора автомобиля
     */
    protected function searchAutomobile()
    {
        $modal = Modal::begin([
            'header' => Yii::t('frontend/advert', 'Choose automobile'),
            'size' => Modal::SIZE_LARGE,
            'toggleButton' => [
                'type' => 'button',
                'label' => Yii::t('frontend/advert', 'Toogle automobile'),
            ]
        ]);
        print ChooseAutomobile::widget([
            'panelClass' => 'col-xs-2 col-sm-2',
            'containerClass' => 'choose-auto',
            'markName' => 'mark',
            'modelName' => 'model',
            'serieName' => 'serie',
            'modificationName' => 'modification',
            'itemTemplate' => '<div class="checkbox {$jsClass}"><label><input type="radio" {$checked} name="{$attributeName}" value="{$id}" />{$name}</label></div>',
        ]);
        $modal->end();
    }

    public function init()
    {
    }

    public function run()
    {
        parent::init();
        print Html::beginTag('div', [
            'class' => 'panel panel-default',
            'id' => $this->options['id'],
        ]);
        print Html::beginTag('div', [
            'class' => 'panel-body'
        ]);
        $this->searchAutomobile();
        print Html::endTag('div');
        print Html::endTag('div');

        return parent::run();
    }
}