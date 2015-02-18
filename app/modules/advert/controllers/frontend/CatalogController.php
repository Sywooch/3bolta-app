<?php
namespace advert\controllers\frontend;

use Yii;
use app\components\Controller;
use yii\web\NotFoundHttpException;
use advert\models\Advert;

/**
 * Контроллер для вывода объявлений
 */
class CatalogController extends Controller
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

        return $this->render('list', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Детальная страница объявления
     */
    public function actionDetails($id)
    {
        /* @var $searchApi \advert\components\SearchApi */
        $searchApi = Yii::$app->getModule('advert')->search;

        $model = $searchApi->getDetails($id);

        if (!($model instanceof Advert)) {
            throw new NotFoundHttpException();
        }

        return $this->render('details', [
            'model' => $model,
        ]);
    }
}