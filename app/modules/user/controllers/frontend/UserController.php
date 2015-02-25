<?php
namespace user\controllers\frontend;

use Yii;

use yii\filters\AccessControl;

use user\models\User;
use yii\widgets\ActiveForm;
use yii\web\Response;
use user\forms\Register as RegisterForm;
use user\forms\Login as LoginForm;
use user\forms\LostPassword as LostPasswordForm;
use user\forms\ChangePassword as ChangePasswordForm;

use yii\web\ForbiddenHttpException;

class UserController extends \app\components\Controller
{
    /**
     * Разделение прав доступа для авторизованных и неавторизованных пользователей.
     * В случае недоступности - редирект на главную страницу.
     */
    public function behaviors()
    {
        return \yii\helpers\ArrayHelper::merge([
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['confirmation', 'register', 'login', 'lost-password', 'change-password'],
                        'roles' => ['?'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout'],
                        'roles' => ['@'],
                        'allow' => true,
                    ]
                ],
                'denyCallback' => function($rule, $action) {
                    if ($action instanceof \yii\base\Action) {
                        /* @var $action \yii\base\Action */
                        return $action->controller->goHome();
                    }
                }
            ]
        ], parent::behaviors());
    }

    /**
     * Подтверждение регистрации пользователя.
     *
     * @param string $code
     */
    public function actionConfirmation($code)
    {
        /* @var $api \user\components\UserApi */
        $api = Yii::$app->getModule('user')->api;

        // активировать пользователя и авторизовать его в случае успеха
        if (($userId = $api->confirmUserRegistration($code)) &&
            ($user = User::find()->where(['id' => $userId])->one())) {
            // авторизовать пользователя в случае успеха
            Yii::$app->user->login($user);
        }

        return $this->goHome();
    }

    /**
     * Регистрация пользователя
     */
    public function actionRegister()
    {
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

    /**
     * Авторизация
     */
    public function actionLogin()
    {
        $model = new LoginForm();

        if (!empty($_POST['ajax']) && Yii::$app->request->isAjax && $model->load($_POST)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load($_POST) && $model->validate() && ($user = $model->getUser())) {
            // успешно свалидировано
            Yii::$app->user->login($user);
        }

        return $this->goHome();
    }

    /**
     * Выход
     */
    public function actionLogout()
    {
        if (!Yii::$app->user->isGuest) {
            Yii::$app->user->logout();
        }
        return $this->goBack();
    }

    /**
     * Восстановление пароля: выслать e-mail подтверждение
     */
    public function actionLostPassword()
    {
        if (!Yii::$app->request->isAjax) {
            throw new ForbiddenHttpException();
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $form = new LostPasswordForm();

        if (!empty($_POST['ajax']) && $form->load($_POST)) {
            // AJAX-валидация
            return ActiveForm::validate($form);
        }

        $result = [
            'success' => false,
            'email' => null,
        ];

        if ((!empty($_POST) && $form->load($_POST)) && $form->validate()) {
            /* @var $api \user\components\UserApi */
            $api = Yii::$app->getModule('user')->api;
            if ($api->lostPassword($form)) {
                $result['success'] = true;
                $result['email'] = $form->getUser()->email;
            }
        }

        return $result;
    }

    /**
     * Восстановление пароля - установка нового пароля
     * @param string $code код подтверждения
     */
    public function actionChangePassword($code)
    {
        /* @var $api \user\components\UserApi */
        $api = Yii::$app->getModule('user')->api;

        if (Yii::$app->session->getFlash('password_success_updated')) {
            return $this->render('change_password_success');
        }

        // найти пользователя
        $user = $api->getUserByRestoreConfirmation($code);
        if (!($user instanceof User)) {
            return $this->goHome();
        }

        $form = new ChangePasswordForm();

        if (Yii::$app->request->isAjax && !empty($_POST['ajax']) && $form->load($_POST)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($form);
        }

        if ($form->load($_POST) && $form->validate() && $api->changePassword($user, $form)) {
            Yii::$app->session->setFlash('password_success_updated', true);
            return $this->refresh();
        }

        return $this->render('change_password', [
            'model' => $form,
        ]);
    }
}