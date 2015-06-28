<?php
namespace app\widgets;

use yii\bootstrap\Modal as ModalBootstrap;
use yii\helpers\Html;

/**
 * Обертка для виджета модального окна
 */
class Modal extends ModalBootstrap
{
    /**
     * @var string заголовок модального окна
     */
    public $title;

    /**
     * @var boolean кнопка включения модального окна
     */
    public $toggleButton = false;

    public function init()
    {
        if (!is_null($this->title) && is_null($this->header)) {
            // заголовок
            $this->header = Html::tag('h2', $this->title);
        }
        parent::init();
    }
}