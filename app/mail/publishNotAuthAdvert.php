<?php
/**
 * E-mail о подтверждении публикации объявления для неавторизованного пользователя
 */

use yii\helpers\Html;

/* @var $advert \advert\models\PartAdvert */
/* @var $this \yii\base\View */
/* @var $confirmationLink string */
?>
Здравствуйте, <?=Html::encode($advert->user_name)?>!<br /><br />

Вы добавили на сайт 3bolta.com объявление "<?=Html::encode($advert->advert_name)?>".<br />
<br />
<b>Контактное лицо:</b> <?=Html::encode($advert->user_name)?><br />
<b>Контактный телефон:</b> <?=Html::encode($advert->user_phone)?><br />
<br />
Для подтверждения публикации объявления пройдите по ссылке: <br /><br />
<a href="<?=$confirmationLink?>" target="_blank"><?=$confirmationLink?></a><br /><br />
После этого ваше объявление будет опубликовано и доступно для просмотра другим пользователям
сайта. Если вы не размещали объявления на сайте 3bolta.com, просто проигнорируйте это письмо.
