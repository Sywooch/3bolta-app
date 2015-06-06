<?php
namespace partner\filters;

use user\models\User as UserModel;
use yii\web\User;
use yii\base\Action;
use yii\web\Request;

/**
 * Расширение для фильтра AccessControl.
 * Текущий пользователь должен быть авторизован и его тип должен соответствовать TYPE_LEGAL_PERSON.
 * Иначе - запрет доступа
 */
class CheckPartnerAccessRule extends \yii\filters\AccessRule
{
    /**
     * Проверка доступа. Если пользователь не авторизован, либо его тип не равен TYPE_LEGAL_PERSON - возвращает false
     * @param Action $action the action to be performed
     * @param User $user the user object
     * @param Request $request
     * @return boolean|null true if the user is allowed, false if the user is denied, null if the rule does not apply to the user
     */
    public function allows($action, $user, $request)
    {
        if ($user->isGuest) {
            return !$this->allow;
        }

        /* @var $modelUser \user\models\User */
        $modelUser = $user->getIdentity();

        if ($modelUser->isNewRecord) {
            return !$this->allow;
        }
        else if ($modelUser->type == UserModel::TYPE_LEGAL_PERSON) {
            return $this->allow;
        }

        return !$this->allow;
    }
}