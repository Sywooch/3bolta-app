<?php
namespace user\widgets;

use Yii;
use user\forms\LostPassword;

/**
 * Модальное окно восстановления пароля - указать e-mail
 */
class LostPasswordModal extends \yii\bootstrap\Widget
{
    public function run()
    {
        if (!Yii::$app->user->isGuest) {
            return;
        }
        $model = new LostPassword();
        return $this->render('lost_password', [
            'model' => $model,
        ]);
    }
}