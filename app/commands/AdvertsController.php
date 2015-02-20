<?php
namespace app\commands;

use Yii;

use yii\console\Controller;
use advert\models\Advert;

/**
 * Работа с объявлениями
 */
class AdvertsController extends Controller
{
    /**
     * Выслать уведомление владельцам объявлений, о том, что публикация объявления будет прекращена завтра.
     */
    public function actionCheckExpiration()
    {
        /* @var $advertApi \advert\components\AdvertApi */
        $advertApi = Yii::$app->getModule('advert')->advert;

        // получить объявления, публикация которых закончится завтра
        $res = Advert::findActiveAndPublished()
            ->andWhere("date(advert.published_to) = date(now()) + 1");

        foreach ($res->each() as $advert) {
            $advertApi->sendExpiredConfirmation($advert);
        }
    }
}
