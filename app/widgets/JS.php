<?php
namespace app\widgets;

use Yii;
use yii\base\Widget;
use yii\web\View;

/**
 * Виджет вставки JavaScript
 */
class JS extends Widget
{
    /**
     * @var string ключ скрипта
     */
    public $key;

    public static function begin($config = array())
    {
        ob_start();
        return parent::begin();
    }

    public static function end()
    {
        $widget = parent::end();

        $output = ob_get_clean();
        if (Yii::$app->request->isAjax) {
            echo $output;
        }
        else {
            $output = preg_replace('/<script[^>]*>/i', '', $output);
            $output = str_ireplace('</script>', '', $output);
            $widget->view->registerJs($output, View::POS_READY, $widget->key);
        }
    }
}