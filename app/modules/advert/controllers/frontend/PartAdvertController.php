<?php
namespace advert\controllers\frontend;

use advert\components\PartsApi;
use advert\forms\PartForm;
use advert\models\Advert;
use app\components\Controller;
use Yii;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;

/**
 * Работа с объявлениями
 */
class PartAdvertController extends Controller
{
    /**
     * Подтверждение добавления объявления неавторизованным пользователем
     */
    public function actionConfirm($code)
    {
        /* @var $api PartsApi */
        $api = Yii::$app->getModule('advert')->parts;

        $id = $api->confirmAdvert($code);

        if (!$id) {
            // ошибка, редирект на страницу добавления нового объявления
            return $this->redirect(['append']);
        }
        else {
            // успех
            Yii::$app->session->setFlash('advert_published', $id);
            return $this->redirect(['/advert/part-catalog/details', 'id' => $id]);
        }
    }

    /**
     * Добавление объявления
     */
    public function actionAppend()
    {
        // авторизованного пользователя перенаправляем на другой контроллер
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/advert/user-part-advert/append']);
        }

        /* @var $api PartsApi */
        $api = Yii::$app->getModule('advert')->parts;

        $model = new PartForm();

        if (!empty($_POST['ajax']) && Yii::$app->request->isAjax && $model->load($_POST)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load($_POST) &&
            $model->loadImages(UploadedFile::getInstances($model, 'uploadImage')) &&
            $model->validate()) {
            $model->setScenario('submit');

            // сохраняем объявление
            $advert = $api->appendNotRegisterAdvert($model);
            if ($advert instanceof Advert) {
                Yii::$app->session->setFlash('advert_success_created', $advert->id);
                return $this->refresh();
            }
        }

        $model->setScenario(PartForm::SCENARIO_DEFAULT);

        return $this->render('form/index', [
            'model' => $model,
        ]);
    }
}