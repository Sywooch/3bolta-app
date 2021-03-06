<?php
/**
 * Вывести сообщение об успешном добавлении объявления
 */

use app\widgets\JS;
use app\widgets\Modal;
use advert\models\Advert;

$model = null;
if ($id = Yii::$app->session->getFlash('advert_success_created')) {
    $model = Advert::find()->where(['id' => (int) $id])->one();
}

/* @var $this yii\base\View */

if ($model instanceof Advert && !$model->user_id) {
    Modal::begin([
        'title' => Yii::t('frontend/advert', 'Advert was created'),
        'clientOptions' => [
            'show' => true,
        ],
    ]);
    ?>
    Ваше объявление успешно добавлено!
    <br /><br />
    Для того, чтобы это объявление было опубликовано
    и его смогли просматривать другие пользователи - мы отправили на ваш e-mail адрес
    <strong><?=$model->user_email?></strong> с ссылкой для подтверждения публикации.<br /><br />
    Пожалуйста, пройдите по этой ссылке и ваше объявление будет видно другим пользователям.
    <?php
    Modal::end();
}
