<?php

namespace app\controllers;

use Yii;
use advert\components\PartsSearchApi;
use yii\web\Controller;

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
        /* @var $advertApi PartsSearchApi */
        $advertApi = Yii::$app->getModule('advert')->partsSearch;

        // получить марки
        $marks = $advertApi->getDistinctMark();

        return $this->render('index', [
            'marks' => $marks,
        ]);
    }
}
