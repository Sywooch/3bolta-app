<?php
namespace app\widgets;

use Yii;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use app\widgets\Modal;

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

            ob_start();
            Modal::begin([
                'id' => 'serviceMessage',
                'title' => $title,
            ]);
            print $body;
            Modal::end();

            $this->view->registerJs(new \yii\web\JsExpression("$('#serviceMessage').modal('show');"));

            return ob_get_clean();
        }

        return '';
    }
}