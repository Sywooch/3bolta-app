<?php
namespace app\components;

use Yii;

/**
 * Переопределить класс для работы с почтовыми сообщениями
 */
class MailMessage extends \yii\swiftmailer\Message
{
    public function setSubject($subject)
    {
        // добавить префикс
        $prefix = !empty(Yii::$app->params['siteBrand']) ?
            '[' . Yii::$app->params['siteBrand'] . ']' :
            null;
        if (!empty($prefix)) {
            $subject = $prefix . ' ' . $subject;
        }

        return parent::setSubject($subject);
    }
}