<?php
namespace user\widgets;

use user\forms\LostPassword;

/**
 * Модальное окно восстановления пароля - указать e-mail
 */
class LostPasswordModal extends \yii\bootstrap\Widget
{
    public function run()
    {
        $model = new LostPassword();
        return $this->render('lost_password', [
            'model' => $model,
        ]);
    }
}