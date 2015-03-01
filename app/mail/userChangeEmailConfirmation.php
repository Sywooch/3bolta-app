<?php
/**
 * Письмо с подтверждением e-mail пользователя в профиле при измении почтового ящика
 */

use yii\helpers\Html;

/* @var $this \yii\base\View */
/* @var $user \user\models\User */
/* @var $confirmationLink string */
?>

Здравствуйте, <?=Html::encode($user->name)?>!<br />
<br />
Для того, чтобы изменить ваш e-mail в профие на сайте 3bolta.com, пройдите по ссылке:<br />
<br />
<a href="<?=$confirmationLink?>" target="_blank"><?=$confirmationLink?></a><br />
