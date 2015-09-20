<?php
namespace user\controllers\frontend;

use app\components\Controller;
use user\components\ExternalUser;
use user\components\UserApi;
use user\exception\UserApiException;
use user\forms\ChangePassword as ChangePasswordForm;
use user\forms\Login as LoginForm;
use user\forms\LostPassword as LostPasswordForm;
use user\forms\Register as RegisterForm;
use user\models\User;
use Yii;
use yii\base\Action;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class UserController extends Controller
{
    /**
     * Разделение прав доступа для авторизованных и неавторизованных пользователей.
     * В случае недоступности - редирект на главную страницу.
     *
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge([
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
                    if ($action instanceof Action) {
                        /* @var $action Action */
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
        /* @var $api UserApi */
        $api = Yii::$app->getModule('user')->api;

        // активировать пользователя и авторизовать его в случае успеха
        try {
            if (($userId = $api->confirmUserRegistration($code)) &&
                ($user = User::find()->where(['id' => $userId])->one())) {
                // авторизовать пользователя в случае успеха
                Yii::$app->user->login($user);
            }
        }
        catch (UserApiException $ex) {
            throw NotFoundHttpException();
        }

        return $this->goHome();
    }

    /**
     * Регистрация пользователя
     */
    public function actionRegister()
    {
        $model = new RegisterForm();

        // если была авторизация через соц. сети
        $externalUser = ExternalUser::createFromSession();
        if ($externalUser instanceof ExternalUser) {
            $model->setAttributes([
                'name' => $externalUser->name,
                'email' => $externalUser->email,
            ]);
        }

        if (!empty($_POST['ajax']) && Yii::$app->request->isAjax && $model->load($_POST)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load($_POST) && $model->validate()) {
            /* @var $api UserApi */
            $api = Yii::$app->getModule('user')->api;
            try {
                // регистрация пользователя
                $user = $api->registerUser($model);
                if ($externalUser instanceof ExternalUser) {
                    // прикрепить к пользователю соц. сеть
                    $api->attachUserServiceAccount($user, $externalUser);
                }
                // очистить данные соц. сети
                ExternalUser::removeAttributesFromSession();
                Yii::$app->session->setFlash('user_success_registered', $user->id);
                return $this->refresh();
            }
            catch (UserApiException $ex) {
                Yii::$app->serviceMessage->setMessage(
                    'danger',
                    'При регистрации произошла ошибка (код ошибки: ' . $ex->getCode() . ')<br />'
                    . 'Пожалуйста, обратитесь в службу поддержки',
                    Yii::t('frontend/user', 'Register')
                );
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
            'errorCode' => 0,
        ];

        if ((!empty($_POST) && $form->load($_POST)) && $form->validate()) {
            /* @var $api UserApi */
            $api = Yii::$app->getModule('user')->api;
            try {
                $result['success'] = $api->lostPassword($form);
                $result['email'] = $form->getUser()->email;
            } catch (UserApiException $ex) {
                $result['success'] = false;
                $result['errorCode'] = $ex->getCode();
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
        /* @var $api UserApi */
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

        if ($form->load($_POST) && $form->validate()) {
            try {
                $api->changePassword($user, $form);
                Yii::$app->session->setFlash('password_success_updated', true);
            }
            catch (UserApiException $ex) { }
            return $this->refresh();
        }

        return $this->render('change_password', [
            'model' => $form,
        ]);
    }
}