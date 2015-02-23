<?php
/**
 * Письмо с подтверждением e-mail пользователя при регистрации
 */

use yii\helpers\Html;

/* @var $this \yii\base\View */
/* @var $user \user\models\User */
/* @var $confirmationLink string */
?>

Здравствуйте, <?=Html::encode($user->name)?>!<br />
<br />
Вы зарегистрировались на сайте 3bolta.com . Для того, чтобы подтвердить свой
e-mail <?=Html::encode($user->email)?> и завершить регистрацию необходимо пройти по ссылке:<br />
<br />
<a href="<?=$confirmationLink?>" target="_blank"><?=$confirmationLink?></a><br />
<br />
Если вы не регистрировались на сайте 3bolta.com - просто проигнорируйте это письмо.<br />
