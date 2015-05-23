<?php
namespace app\widgets;

use Yii;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap\Modal;

/**
 * Виджет отображения сервисного сообщения
 */
class ServiceMessage extends \yii\base\Widget
{
    public function run()
    {
        if (Yii::$app->session->hasFlash('alert')) {
            $body = ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'body');
            $options = ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'options');
            $title = ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'title');

            $class = 'block-info-primary';
            $classTitle = '';
            if (!empty($options['class']) && $options['class'] == 'alert-success') {
                $class = 'block-info-success';
                $classTitle = '';
            }
            else if (!empty($options['class']) && $options['class'] == 'alert-danger') {
                $class = 'block-info-danger';
                $classTitle = '';
            }

            if ($title) {
                $title = '<h2 class="' . $classTitle . '"><span class="glyphicon glyphicon-info-sign"></span> ' . $title . '</h2>';
            }
            ob_start();
            Modal::begin([
                'id' => 'serviceMessage',
                'header' => $title,
                'toggleButton' => false,
            ]);
            print $body;
            Modal::end();

            $this->view->registerJs(new \yii\web\JsExpression("$('#serviceMessage').modal('show');"));

            return ob_get_clean();
        }

        return '';
    }
}