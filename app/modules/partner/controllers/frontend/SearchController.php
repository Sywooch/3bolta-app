<?php
namespace partner\controllers\frontend;

use app\components\Controller;
use auto\models\Mark;
use partner\components\SearchApi;
use partner\forms\TradePointMap;
use partner\models\Partner;
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
     * Автокомплит для марок автомобилей
     *
     * @return []
     * @throws ForbiddenHttpException
     */
    public function actionMarkAutocomplete()
    {
        if (!Yii::$app->request->isAjax) {
            throw new ForbiddenHttpException();
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        $ret = [];

        $res = Mark::find()
                ->andWhere(['ilike', 'full_name', (string) Yii::$app->request->get('term')])
                ->limit(3)
                ->all();

        foreach ($res as $row) {
            /* @var $row Mark */
            $ret[] = [
                'label' => $row->full_name,
                'value' => $row->full_name,
            ];
        }

        return $ret;
    }

    /**
     * Автокомплит по названию организации
     *
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionNameAutocomplete()
    {
        if (!Yii::$app->request->isAjax) {
            throw new ForbiddenHttpException();
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        $ret = [];

        $res = Partner::find()
                ->andWhere(['ilike', 'name', (string) Yii::$app->request->get('term')])
                ->limit(3)
                ->all();

        foreach ($res as $row) {
            /* @var $row Partner */
            $ret[] = [
                'label' => $row->name,
                'value' => $row->name,
            ];
        }

        return $ret;
    }

    /**
     * Поиск торговых точек. Поиск происходит внутри локации. Все торговые точки,
     * которые не подходят по запросу по названию, либо по специализации - подсвечиваются как неактивные.
     *
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
            $res = $this->searchApi->search($form);
            foreach ($res as $row) {
                $result['items'][] = [
                    'id' => $row->id,
                    'name' => $row->partner->name,
                    'active' => $row->active,
                    'address' => $row->address,
                    'phone' => $row->phone,
                    'latitude' => $row->latitude,
                    'longitude' => $row->longitude,
                ];
            }
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