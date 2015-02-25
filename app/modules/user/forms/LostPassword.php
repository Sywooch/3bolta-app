<?php
namespace user\forms;

use Yii;
use user\models\User;

/**
 * Форма восстановления пароля - указать e-mail
 */
class LostPassword extends \yii\base\Model
{
    public $email;
    protected $_user;

    public function rules()
    {
        return [
            ['email', 'required'],
            ['email', 'email'],
            ['email', function ($attribute, $params) {
                // получить пользователя, если существует, иначе - ошибка
                $res = User::find()->where(['email' => $this->{$attribute}])->one();
                if (!($res instanceof User)) {
                    $this->addError($attribute, Yii::t('frontend/user', 'User not found'));
                }
                else {
                    $this->_user = $res;
                }
            }],
        ];
    }

    /**
     * Подписи атрибутов
     * @return []
     */
    public function attributeLabels()
    {
        return [
            'email' => Yii::t('frontend/user', 'E-mail'),
        ];
    }

    /**
     * Получить найденного пользователя
     * @return User
     */
    public function getUser()
    {
        return $this->_user;
    }
}