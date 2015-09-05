<?php
/**
 * Уведомление об окончании публикации объявления по причине истечения срока действия.
 */

use yii\helpers\Html;
use app\helpers\Date;
use yii\helpers\Url;

/* @var $advert \advert\models\Advert */
/* @var $this \yii\base\View */

if (!$advert->user_id):?>
    Здравствуйте, <?=Html::encode($advert->user_name)?>!<br />
    <br />
    Ваше объявление "<?=Html::encode($advert->advert_name)?>" опубликовано
    на сайте 3bolta.com до <?=Date::formatDate($advert->published_to, false)?>.<br />
    Продлевать публикацию объявлений могут только зарегистрированные пользователи.<br />
    <br />
    Если вы хотите продлить свое объявление, либо добавить новое, пожалуйста
    <a href="<?=Url::toRoute(['/user/user/register'], true)?>" target="_blank">зарегистрируйтесь</a>.
<?php else:?>

<?php endif; ?>
