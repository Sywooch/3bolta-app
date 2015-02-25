<?php
/**
 * Письмо с подтверждением для восстановления пароля
 */

use yii\helpers\Html;

/* @var $this \yii\base\View */
/* @var $user \user\models\User */
/* @var $confirmationLink string */
?>

Уважаемый <?=Html::encode($user->name)?>!<br />
<br />
Для изменения пароля пройдите по ссылке:<br />
<br />
<a href="<?=$confirmationLink?>"><?=$confirmationLink?></a><br />
По ссылке отобразится форма, где вам будет предложено ввести новый пароль и его подтверждение.
<br />
Если вы не запрашивали изменение пароля на сайте 3bolta.com - просто проигнорируйте это письмо.<br />
