<?php
namespace advert\controllers\frontend;

use Yii;
use yii\web\UploadedFile;
use yii\web\Response;
use yii\widgets\ActiveForm;

use advert\forms\Form;
use advert\models\Advert;

use app\components\Controller;

/**
 * Работа с объявлениями
 */
class AdvertController extends Controller
{
    /**
     * Подтверждение добавления объявления неавторизованным пользователем
     */
    public function actionConfirm($code)
    {
        /* @var $api \advert\components\AdvertApi */
        $api = Yii::$app->getModule('advert')->advert;

        $id = $api->confirmAdvert($code);

        if (!$id) {
            // ошибка, редирект на страницу добавления нового объявления
            return $this->redirect(['append']);
        }
        else {
            // успех
            Yii::$app->session->setFlash('advert_published', $id);
            return $this->redirect(['/advert/catalog/details', 'id' => $id]);
        }
    }

    /**
     * Добавление объявления
     */
    public function actionAppend()
    {
        // авторизованного пользователя перенаправляем на другой контроллер
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/advert/user-advert/append']);
        }

        /* @var $api \advert\components\AdvertApi */
        $api = Yii::$app->getModule('advert')->advert;

        $model = new Form();

        if (!empty($_POST['ajax']) && Yii::$app->request->isAjax && $model->load($_POST)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load($_POST)) {

            $model->setScenario('submit');
            if ($files = UploadedFile::getInstances($model, 'uploadImage')) {
                $model->setUploadImage($files);
            }

            if ($model->validate()) {
                // сохраняем объявление
                $advert = $api->appendNotRegisterAdvert($model);
                if ($advert instanceof Advert) {
                    Yii::$app->session->setFlash('advert_success_created', $advert->id);
                    return $this->refresh();
                }
            }
        }

        $model->setScenario(Form::SCENARIO_DEFAULT);

        return $this->render('form/index', [
            'model' => $model,
        ]);
    }
}