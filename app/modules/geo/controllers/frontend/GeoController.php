<?php
namespace geo\controllers\frontend;

use app\components\Controller;
use geo\components\GeoApi;
use Yii;
use yii\web\ForbiddenHttpException;

/**
 * Контроллер для работы с геоданными
 */
class GeoController extends Controller
{
    /**
     * @var GeoApi
     */
    protected $geoApi;

    /**
     * Фильтры
     * @return array
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'select-region' => ['post'],
                    'detect-user-region' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Инициализация
     */
    public function init()
    {
        $this->geoApi = Yii::$app->getModule('geo')->api;
    }

    /**
     * Ручное переключение региона
     */
    public function actionSelectRegion()
    {
        if (!Yii::$app->request->isAjax) {
            // доступ только по AJAX
            throw new ForbiddenHttpException();
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $result = [
            'success' => false,
            'id' => null,
            'name' => null,
        ];

        $form = new \geo\forms\SelectRegion();

        if ($form->load($_POST) && $form->validate()) {
            // переключить регион
            $region = $this->geoApi->setUserRegion($form->regionId);
            if ($region instanceof \geo\models\Region) {
                $result['success'] = true;
                $result['id'] = $region->id;
                $result['name'] = $region->site_name;
            }
        }

        return $result;
    }

    /**
     * По широте и долготе определяет ближайший регион и устанавливает его в куки
     */
    public function actionDetectUserRegion()
    {
        if (!Yii::$app->request->isAjax) {
            // доступ только по AJAX
            throw new ForbiddenHttpException();
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $setRegion = null;

        $lat = Yii::$app->request->post('lat');
        $lng = Yii::$app->request->post('lng');

        if ($lat !== null && $lng !== null) {
            // получить регион по координатам
            $setRegion = $this->geoApi->getNearestRegion($lat, $lng);
            if ($setRegion instanceof \geo\models\Region) {
                // установить город пользователя
                $this->geoApi->setUserRegion($setRegion);
            }
        }

        return [
            'id' => $setRegion instanceof \geo\models\Region ? $setRegion->id : null,
            'name' => $setRegion instanceof \geo\models\Region ? $setRegion->site_name : null
        ];
    }
}