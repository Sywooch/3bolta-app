<?php
namespace user\controllers\frontend;

use Yii;

use user\models\User;
use yii\widgets\ActiveForm;
use yii\web\Response;
use user\forms\Register as RegisterForm;

class UserController extends \app\components\Controller
{
    /**
     * Подтверждение регистрации пользователя.
     *
     * @param string $code
     */
    public function actionConfirmation($code)
    {
        if (!Yii::$app->user->isGuest) {
            // авторизованных пользователей перенаправляем на домашнюю
            return $this->goHome();
        }

        /* @var $api \user\components\UserApi */
        $api = Yii::$app->getModule('user')->api;

        // активировать пользователя и авторизовать его в случае успеха
        if (($userId = $api->confirmUserRegistration($code)) &&
            ($user = User::find()->where(['id' => $userId])->one())) {
            ?><pre><?php print_r($user);exit();
        }

        return $this->goHome();
    }

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
            if ($user instanceof User) {
                Yii::$app->session->setFlash('user_success_registered', $user->id);
                return $this->refresh();
            }
        }

        $registeredUser = null;
        if ($id = (int) Yii::$app->session->getFlash('user_success_registered')) {
            // показать сообщение об успешности регистрации
            $registeredUser = User::find()->where(['id' => $id])->one();
        }

        return $this->render('register', [
            'model' => $model,
            'registeredUser' => $registeredUser,
        ]);
    }
}