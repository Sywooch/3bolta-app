<?php

use advert\models\Advert;
use yii\base\View;
use yii\helpers\Html;

/**
 * Ответ на вопрос по объявлению
 */

/* @var $this View */
/* @var $toUserName string */
/* @var $toUserEmail string */
/* @var $advertLink string */
/* @var $question string */
/* @var $answer string */
/* @var $advert Advert */
?>

Здравствуйте, <?=Html::encode($toUserName)?>!<br />
<br />
На ваш вопрос по объявлению <a href="<?=$advertLink?>"><?=Html::encode($advert->advert_name)?></a> на сайте 3bolta.com
поступил ответ.<br />
<br />
Ответ:<br />
<br />
-------------------------------<br />
<?=nl2br(Html::encode($answer))?><br />
-------------------------------<br />
<br />
Ваш вопрос был:<br />
<br />
-------------------------------<br />
<?=nl2br(Html::encode($question))?><br />
-------------------------------<br />
<br />