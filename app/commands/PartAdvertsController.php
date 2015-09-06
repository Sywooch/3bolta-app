<?php
namespace app\commands;

use advert\components\PartsApi;
use advert\components\PartsIndex;
use advert\models\Advert;
use advert\models\Part;
use Yii;
use yii\console\Controller;

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
        /* @var $advertApi PartsApi */
        $advertApi = Yii::$app->getModule('advert')->parts;

        // получить объявления, публикация которых закончится завтра
        $res = Advert::findActiveAndPublished()
            ->andWhere('date(' . Advert::tableName() . '.published_to) = date(now()) + 1');

        foreach ($res->each() as $advert) {
            $advertApi->sendExpiredConfirmation($advert);
        }
    }

    /**
     * Удалить просроченные объявления из индекса
     */
    public function actionRemoveExpired()
    {
        /* @var $partsIndex PartsIndex */
        $partsIndex = Yii::$app->getModule('advert')->partsIndex;
        $partsIndex->deleteExpired();
    }

    /**
     * Переиндексировать все опубликованные объявления
     */
    public function actionReindex()
    {
        $query = Part::findActiveAndPublished();

        /* @var $partsIndex PartsIndex */
        $partsIndex = Yii::$app->getModule('advert')->partsIndex;

        $partsIndex->debugMode = true;
        $updated = $partsIndex->reindexByActiveQuery($query);
        $this->stdout("Updated complete: $updated\n");
    }
}
