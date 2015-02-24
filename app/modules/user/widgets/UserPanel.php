<?php
namespace user\widgets;

use Yii;

/**
 * Виджет панели пользователя в шапке сайта
 */
class UserPanel extends \yii\bootstrap\Widget
{
    public function run()
    {
        $user = null;

        if (!Yii::$app->user->isGuest) {
            $user = Yii::$app->user->getIdentity();
        }

        return $this->render('user_panel', [
            'user' => $user,
        ]);
    }
}