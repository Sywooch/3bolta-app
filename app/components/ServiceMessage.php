<?php

namespace app\components;

use Yii;

/**
 * Вывод сервисных сообщений
 */
class ServiceMessage extends \yii\base\Component
{
    const WARNING = 1;
    const INFO = 2;
    const SUCCESS = 3;
    const ERROR = 4;

    /**
     * Вывести сообщение
     * @param int $messageType
     * @param string $message
     */
    public function setMessage($messageType, $message, $title = null)
    {
        $class = 'alert-' . $messageType;

        Yii::$app->session->setFlash('alert', [
            'body' => $message,
            'options' => [
                'class' => $class
            ],
            'title' => $title
        ]);
    }
}