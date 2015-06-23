<?php
namespace partner\controllers\frontend;

use app\components\Controller;
use partner\components\SearchApi;
use partner\forms\TradePointMap;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * Поиск торговых точек на карте или в виде списка
 */
class SearchController extends Controller
{
    /**
     * @var SearchApi
     */
    protected $searchApi;

    public function init()
    {
        $this->searchApi = Yii::$app->getModule('partner')->search;
    }

    /**
     * Поиск торговых точек
     * Доступ только по AJAX
     *
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionSearch()
    {
        if (!Yii::$app->request->isAjax) {
            throw new ForbiddenHttpException();
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        $result = [
            'items' => [],
        ];

        $form = new TradePointMap();
        if ($form->load($_POST) && $form->validate()) {
            // поиск торговых точек
            $res = $this->searchApi->search($searchForm);
            print_r($res);exit();
        }

        return $result;
    }

    /**
     * Вывод карты с торговыми точками и формы для поиска торговых точек
     */
    public function actionIndex()
    {
        $searchForm = new TradePointMap();

        return $this->render('map', [
            'searchForm' => $searchForm,
        ]);
    }
}