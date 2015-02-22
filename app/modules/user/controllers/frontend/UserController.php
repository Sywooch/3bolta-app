<?php
namespace user\controllers\frontend;

use Yii;

use yii\widgets\ActiveForm;
use yii\web\Response;
use user\forms\Register as RegisterForm;

class UserController extends \app\components\Controller
{
    /**
     * Регистрация пользователя
     */
    public function actionRegister()
    {
        if (!Yii::$app->user->isGuest) {
            // авторизованных пользователей перенаправляем на домашнюю
            return $this->goHome();
        }

        $model = new RegisterForm();

        if (!empty($_POST['ajax']) && Yii::$app->request->isAjax && $model->load($_POST)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load($_POST) && $model->validate()) {
            /* @var $api \user\components\UserApi */
            $api = Yii::$app->getModule('user')->api;
            $user = $api->registerUser($model);
            ?><pre><?php print_r($user);exit();
        }

        return $this->render('register', [
            'model' => $model,
        ]);
    }
}