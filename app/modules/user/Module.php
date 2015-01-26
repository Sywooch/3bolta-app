<?php

namespace app\modules\user;

use app\modules\user\models\User;

/**
 * Модуль пользователей
 */
class Module extends \yii\base\Module
{
    /**
     * Вернуть захешированный пароль
     * @param string $password
     * @return string
     */
    public function getPasswordHash($password)
    {
        return \Yii::$app->security->generatePasswordHash($password);
    }
}