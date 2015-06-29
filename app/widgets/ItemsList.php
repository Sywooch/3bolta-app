<?php
namespace app\widgets;

use yii\helpers\Html;
use yii\widgets\InputWidget;

/**
 * Виджет выбора элементов из списка
 */
class ItemsList extends InputWidget
{
    /**
     * @var array элементы списка
     */
    public $items = [];

    /**
     * @var array опции виджета
     */
    public $options = ['class' => 'list-group'];

    /**
     * @var array опции элемента списка
     */
    public $itemOptions = ['class' => 'list-group-item'];

    public function run()
    {
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->id;
        }
        $this->options['id'] .= '-wrapper';
        $html = Html::beginTag('div', $this->options);
        foreach ($this->items as $value => $item) {
            $link = Html::tag('a', $item, array_merge($this->itemOptions, ['data-value' => $value]));
            $html .= $link;
        }
        $html .= Html::endTag('div');
        $html .= Html::activeHiddenInput($this->model, $this->attribute);

        $inputId = Html::getInputId($this->model, $this->attribute);
        $this->view->registerJs(new \yii\web\JsExpression("
            $(document).ready(function() {
                $('#{$this->options['id']} a').click(function() {
                    $(this).addClass('active').siblings().removeClass('active');
                    $('#$inputId').val($(this).data('value')).trigger('change');
                });
                var currentValue = $('#$inputId').val();
                $('#{$this->options['id']} a[data-value=\"' + currentValue + '\"]').trigger('click');
            });
        "));
        return $html;
    }
}