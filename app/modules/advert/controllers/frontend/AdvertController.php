<?php
namespace advert\controllers\frontend;

use Yii;
use app\components\Controller;

/**
 * Контроллер для вывода объявлений
 */
class AdvertController extends Controller
{
    /**
     * Поиск объявлений - список найденных
     */
    public function actionSearch()
    {
        /* @var $searchApi \advert\components\SearchApi */
        $searchApi = Yii::$app->getModule('advert')->search;

        /* @var $dataProvider \yii\data\ActiveDataProvider */
        $dataProvider = $searchApi->searchItems(Yii::$app->request->getQueryParams());

        ?><pre><?php print_r($dataProvider->getModels());
        ?><pre><?php print_r($dataProvider);exit();

        return $this->render('list', [
        ]);
    }
}