<?php
namespace app\widgets;

use yii\web\View as BaseView;

/**
 * Кастомная вьюха
 */
class View extends BaseView
{
    /**
     * @var string заголовок H1 в заголовке
     */
    public $pageH1;

    /**
     * @var string HTML для дополнения к заголовку
     */
    public $pageH1Extend;
}