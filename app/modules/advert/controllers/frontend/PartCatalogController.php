<?php
namespace advert\controllers\frontend;

use Yii;
use app\components\Controller;
use yii\web\NotFoundHttpException;
use advert\models\PartAdvert;

/**
 * Контроллер для вывода объявлений запчастей
 */
class PartCatalogController extends Controller
{
    /**
     * Поиск объявлений - список найденных
     */
    public function actionSearch()
    {
        /* @var $searchApi \advert\components\PartsSearchApi */
        $searchApi = Yii::$app->getModule('advert')->partsSearch;

        /* @var $dataProvider \yii\data\ActiveDataProvider */
        $dataProvider = $searchApi->searchItems(Yii::$app->request->getQueryParams());

        return $this->render('list', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Детальная страница объявления
     */
    public function actionDetails($id)
    {
        /* @var $searchApi \advert\components\PartsSearchApi */
        $searchApi = Yii::$app->getModule('advert')->partsSearch;

        $model = $searchApi->getDetails($id);

        if (!($model instanceof PartAdvert)) {
            throw new NotFoundHttpException();
        }

        return $this->render('details', [
            'model' => $model,
        ]);
    }
}