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

        return $this->render('index', [
            'marks' => $marks,
        ]);
    }
}
