<?php

namespace app\modules\user\components;

use \app\modules\user\models\User;

/**
 * Переопределение класса yii\web\User
 */
class UserAuth extends \yii\web\User
{
    /**
     * Переопределить метод afterLogin
     * @param yii\web\IdentityInterface $identity the user identity information
     * @param boolean $cookieBased whether the login is cookie-based
     * @param integer $duration number of seconds that the user can remain in logged-in status.
     */
    protected function afterLogin($identity, $cookieBased, $duration)
    {
        if ($this->identity instanceof User) {
            /* @var $user \app\modules\user\models\User */
            $user = $this->identity;
            $user->last_login = date('Y-m-d H:i:s');
            $user->save(true, ['last_login']);
        }
        return parent::afterLogin($identity, $cookieBased, $duration);
    }
}