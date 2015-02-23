<?php
namespace user\forms;

use Yii;

use user\models\User;

/**
 * Форма авторизации
 */
class Login extends \yii\base\Model
{
    /**
     * @var string логин
     */
    public $username;

    /**
     * @var string пароль
     */
    public $password;

    /**
     * @var User модель пользователя
     */
    private $_user = false;

    /**
     * Правила валидации
     * @return []
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Подписи
     * @return []
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('frontend/user', 'E-mail'),
            'password' => Yii::t('frontend/user', 'Password'),
        ];
    }

    /**
     * Валидация пароля. Перед валидацией должно быть заполнено поле username
     * и не должно быть любых других ошибок.
     *
     * @param string $attribute
     * @param [] $params
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            /* @var $user \user\models\User */
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, Yii::t('frontend/user', 'Incorrect username or password.'));
            }
            else {
                // проверить статус пользователя
                switch ($user->status) {
                    case User::STATUS_LOCKED:
                        $this->addError($attribute, Yii::t('frontend/user', 'Account locked'));
                        break;
                    case User::STATUS_WAIT_CONFIRMATION:
                        $this->addError($attribute, Yii::t('frontend/user', 'Need activation'));
                        $this->sendEmailActivation($user);
                        break;
                }
            }
        }
    }

    /**
     * По логину пользователя возвращает его модель
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false && $this->username) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }

    /**
     * Отправить уведомление об активации аккаунта пользователю $user.
     * Отправлять можно только один раз в 10 минут одному пользователю.
     *
     * @param User $user
     */
    protected function sendEmailActivation(User $user)
    {
        /* @var $api \user\components\UserApi */
        $api = Yii::$app->getModule('user')->api;

        $activationsEmail = Yii::$app->session['login_activations_email'];

        if (!is_array($activationsEmail)) {
            $activationsEmail = [];
        }

        if (!isset($activationsEmail[$user->email]) ||
            time() - $activationsEmail[$user->email] >= 10*60) {
            try {
                $api->sendEmailConfirmation($user);
            }
            catch (\yii\base\Exception $ex) { }
            $activationsEmail[$user->email] = time();
        }

        Yii::$app->session['login_activations_email'] = $activationsEmail;
    }
}
