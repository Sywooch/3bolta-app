<?php
namespace user\widgets;

use Yii;

use user\forms\Login;

/**
 * Вывод модального окна авторизации
 */
class LoginModal extends \yii\bootstrap\Widget
{
    public function run()
    {
        if (!Yii::$app->user->isGuest) {
            return;
        }

        $model = new Login();

        return $this->render('login', [
            'model' => $model,
        ]);
    }
}