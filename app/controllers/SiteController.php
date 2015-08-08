<?php
namespace app\controllers;

use yii\web\Controller;

use Yii;
use advert\models\PartAdvert;

/**
 * Основной контроллер сайта
 */
class SiteController extends Controller
{
    /**
     * Главная страница
     */
    public function actionIndex()
    {
        /* @var $advertApi \advert\components\PartsSearchApi */
        $advertApi = Yii::$app->getModule('advert')->partsSearch;

        // получить марки
        $marks = $advertApi->getDistinctMark();

        return $this->render('index', [
            'marks' => $marks,
        ]);
    }
}
