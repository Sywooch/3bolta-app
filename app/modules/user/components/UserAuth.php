<?php

namespace user\components;

use yii\web\IdentityInterface;
use \user\models\User;

/**
 * Переопределение класса yii\web\User
 */
class UserAuth extends \yii\web\User
{
    const DEFAULT_LOGIN_DURATION = 2592000;
    const ERROR_USER_LOCKED = 1;
    const ERROR_USER_NOT_CONFIRMED = 2;
    const ERROR_UNKNOWN = 3;

    /**
     * @var int код ошибки авторизации пользователя
     */
    protected $loginError;

    /**
     * @var User модель пользователя, попытавшегося авторизоваться
     */
    protected $loginUserModel;

    /**
     * Получить ошибку авторизации.
     *
     * @return int|null
     */
    public function getLoginError()
    {
        return $this->loginError;
    }

    /**
     * Получить модель пользователя, пытающегося авторизоваться.
     *
     * @return User|null
     */
    public function getLoginUserModel()
    {
        return $this->loginUserModel;
    }

    /**
     * Переопределить метод beforeLogin.
     * Проверяе возможность пользователя авторизовываться на сайте.
     * Статус пользователя должен быть равен STATIS_ACTIVE, иначе - ошибка в поле loginError.
     *
     * @param IdentityInterface $identity the user identity information
     * @param boolean $cookieBased whether the login is cookie-based
     * @param integer $duration number of seconds that the user can remain in logged-in status.
     * If 0, it means login till the user closes the browser or the session is manually destroyed.
     * @return boolean whether the user should continue to be logged in
     */
    protected function beforeLogin($identity, $cookieBased, $duration)
    {
        $this->loginError = null;
        $this->loginUserModel = null;

        if ($identity instanceof User) {
            // проверить статус пользователя
            switch ($identity->status) {
                case User::STATUS_LOCKED:
                    $this->loginError = self::ERROR_USER_LOCKED;
                    break;

                case User::STATUS_WAIT_CONFIRMATION:
                    $this->loginError = self::ERROR_USER_NOT_CONFIRMED;
                    break;
            }
            if (!$identity->canLogin()) {
                $this->loginError = self::ERROR_UNKNOWN;
            }
            if ($this->loginError !== null) {
                // была ошибка
                $this->loginUserModel = $identity;
                return false;
            }
        }
        return parent::beforeLogin($identity, $cookieBased, $duration);
    }

    /**
     * Переопределить метод afterLogin
     * @param yii\web\IdentityInterface $identity the user identity information
     * @param boolean $cookieBased whether the login is cookie-based
     * @param integer $duration number of seconds that the user can remain in logged-in status.
     */
    protected function afterLogin($identity, $cookieBased, $duration)
    {
        if ($this->identity instanceof User) {
            /* @var $user \user\models\User */
            $user = $this->identity;
            $user->last_login = date('Y-m-d H:i:s');
            $user->save(true, ['last_login']);
        }
        return parent::afterLogin($identity, $cookieBased, $duration);
    }

    /**
     * Переопределить метод login.
     * По умолчанию пользователь авторизуется на 30 дней.
     *
     * @param IdentityInterface $identity
     * @param int $duration
     */
    public function login(IdentityInterface $identity, $duration = 0)
    {
        if ($duration == 0) {
            $duration = self::DEFAULT_LOGIN_DURATION;
        }
        return parent::login($identity, $duration);
    }
}