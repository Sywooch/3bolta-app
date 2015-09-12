<?php
namespace advert\controllers\frontend;

use Yii;

use advert\components\PartsApi;
use advert\exception\PartsApiException;
use advert\forms\PartForm;
use app\components\Controller;
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

        try {
            $id = $api->confirmAdvert($code);
            if ($id) {
                // успех
                Yii::$app->session->setFlash('advert_published', $id);
                return $this->redirect(['/advert/part-catalog/details', 'id' => $id]);
            }
        }
        catch (PartsApiException $ex) {
           // ошибка, редирект на страницу добавления нового объявления
            Yii::$app->serviceMessage->setMessage(
                'danger',
                'При подтверждении объявления произошла ошибка (код ошибки: ' . $ex->getCode() . ')<br />'
                . 'Пожалуйста, обратитесь в службу поддержки',
                Yii::t('frontend/advert', 'Append advert')
            );
        }
        return $this->redirect(['append']);
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
            try {
                $advert = $api->appendNotRegisterAdvert($model);
                Yii::$app->session->setFlash('advert_success_created', $advert->id);
                return $this->refresh();
            }
            catch (PartsApiException $ex) {
                Yii::$app->serviceMessage->setMessage(
                    'danger',
                    'При создании объявления произошла ошибка (код ошибки: ' . $ex->getCode() . '<br />'
                    . 'Пожалуйста, обратитесь в службу поддержки',
                    Yii::t('frontend/advert', 'Append advert')
                );
            }
        }

        $model->setScenario(PartForm::SCENARIO_DEFAULT);

        return $this->render('form/index', [
            'model' => $model,
        ]);
    }
}