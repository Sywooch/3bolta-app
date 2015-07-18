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

        /* @var $geoApi GeoApi */
        $geoApi = Yii::$app->getModule('geo')->api;

        $lat = Yii::$app->request->post('lat');
        $lng = Yii::$app->request->post('lng');

        if ($lat !== null && $lng !== null) {
            // получить регион по координатам
            $setRegion = $geoApi->getNearestRegion($lat, $lng);
            if ($setRegion instanceof \geo\models\Region) {
                // установить город пользователя
                $geoApi->setUserRegion($setRegion);
            }
        }

        return [
            'id' => $setRegion instanceof \geo\models\Region ? $setRegion->id : null,
            'name' => $setRegion instanceof \geo\models\Region ? $setRegion->site_name : null
        ];
    }
}