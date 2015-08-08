<?php
namespace app\commands;

use Yii;

use yii\console\Controller;
use advert\models\PartAdvert;

/**
 * Работа с объявлениями
 */
class PartAdvertsController extends Controller
{
    /**
     * Выслать уведомление владельцам объявлений, о том, что публикация объявления будет прекращена завтра.
     */
    public function actionCheckExpiration()
    {
        /* @var $advertApi \advert\components\PartsApi */
        $advertApi = Yii::$app->getModule('advert')->parts;

        // получить объявления, публикация которых закончится завтра
        $res = PartAdvert::findActiveAndPublished()
            ->andWhere("date(advert.published_to) = date(now()) + 1");

        foreach ($res->each() as $advert) {
            $advertApi->sendExpiredConfirmation($advert);
        }
    }
}
