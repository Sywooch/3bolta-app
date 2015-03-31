<?php
namespace app\controllers;

use yii\web\Controller;

use Yii;
use advert\models\Advert;

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
        /* @var $advertApi \advert\components\SearchApi */
        $advertApi = Yii::$app->getModule('advert')->search;

        // получить марки
        $marks = $advertApi->getDistinctMark();

        // получить последние опубликованные объявления
        $lastAdverts = Advert::findActiveAndPublished()->orderBy('published DESC')->limit(6)->all();

        return $this->render('index', [
            'marks' => $marks,
            'lastAdverts' => $lastAdverts,
        ]);
    }
}
