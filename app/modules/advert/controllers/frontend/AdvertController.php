<?php
namespace advert\controllers\frontend;

use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

use advert\forms\Form;

use app\components\Controller;

/**
 * Работа с объявлениями
 */
class AdvertController extends Controller
{
    /**
     * Добавление объявления
     */
    public function actionAppend()
    {
        /* @var $api \advert\components\AdvertApi */
        $api = Yii::$app->getModule('advert')->advert;

        $model = new Form();

        if (!empty($_POST['ajax']) && Yii::$app->request->isAjax && $model->load($_POST)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load($_POST)) {
            $model->setScenario('submit');
            if ($model->validate()) {
                // сохраняем объявление
                $advert = $api->appendNotRegisterAdvert($model);
            }
        }

        $model->setScenario(Form::SCENARIO_DEFAULT);

        return $this->render('form/index', [
            'model' => $model,
        ]);
    }
}