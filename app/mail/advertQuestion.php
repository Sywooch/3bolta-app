<?php

use advert\models\PartAdvert;
use yii\base\View;
use yii\helpers\Html;

/**
 * Вопрос по объявлению, отправка владельцу объявления
 */

/* @var $this View */
/* @var $toUserName string */
/* @var $toUserEmail string */
/* @var $toUserId integer|null */
/* @var $fromUserName string */
/* @var $fromUserId integer|null */
/* @var $advertId integer */
/* @var $advertLink string */
/* @var $answerLink string */
/* @var $question string */
/* @var $advert PartAdvert */
?>

Здравствуйте, <?=Html::encode($toUserName)?>!<br />
<br />
На ваше объявление <a href="<?=$advertLink?>"><?=Html::encode($advert->advert_name)?></a> поступил вопрос
на сайте 3bolta.com от пользователя по имени <?=Html::encode($fromUserName)?>. <br />
<br />
-------------------------------<br />
<?=nl2br(Html::encode($question))?><br />
-------------------------------<br />
<br />
Для того, чтобы ответить на вопрос, пожалуйста, пройдите по ссылке: <a href="<?=$answerLink?>"><?=$answerLink?></a>.<br />
