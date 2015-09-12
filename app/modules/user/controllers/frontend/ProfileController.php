<?php
namespace user\controllers\frontend;

use app\components\Controller;
use user\components\UserApi;
use user\exception\UserApiException;
use user\forms\ChangePassword;
use user\forms\Profile;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\rest\Action;
use yii\web\Response;
use yii\web\User;
use yii\widgets\ActiveForm;

/**
 * Работа с профилем
 */
class ProfileController extends Controller
{
    /**
     * @var UserApi
     */
    protected $userApi;

    /**
     * @var User
     */
    protected $user;

    public function init()
    {
        $this->userApi = Yii::$app->getModule('user')->api;
        $this->user = Yii::$app->user->getIdentity();

        return parent::init();
    }

    /**
     * Разделение прав доступа для авторизованных и неавторизованных пользователей.
     * В случае недоступности - редирект на главную страницу.
     */
    public function behaviors()
    {
        return ArrayHelper::merge([
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
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
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    // к изменениям формы доступ только через POST
                    'change-password' => ['post'],
                    'update-contact-data' => ['post'],
                    'update-email' => ['post'],
                    'update-company-data' => ['post'],
                ]
            ]
        ], parent::behaviors());
    }

    /**
     * изменить e-mail пользователя по коду подтверждения,
     * пришедшему в письме
     */
    public function actionChangeEmail($code)
    {
        try {
            $this->userApi->changeUserEmail($this->user, $code);
            Yii::$app->session->setFlash('email_change_success');
        }
        catch (UserApiException $ex) {
            Yii::$app->session->setFlash('email_change_error', $ex->getMessage());
        }

        return $this->redirect(Url::toRoute(['index']) . '#change-email');
    }

    /**
     * Форма изменения пароля
     */
    public function actionChangePassword()
    {
        // форма изменения пароля
        $changePassword = new ChangePassword();

        if (!empty($_POST['ajax']) && Yii::$app->request->isAjax) {
            // AJAX-валидация
            Yii::$app->response->format = Response::FORMAT_JSON;
            $changePassword->load($_POST);
            return ActiveForm::validate($changePassword);
        }

        if ($changePassword->load($_POST) && $changePassword->validate()) {
            try {
                $this->userApi->changePassword($this->user, $changePassword);
                Yii::$app->session->setFlash('password_success_changed', true);
            }
            catch (UserApiException $ex) {
                Yii::$app->session->setFlash('password_error_changed', true);
            }
        }

        return $this->redirect(Url::toRoute(['index']) . '#change-password');
    }

    /**
     * Форма изменения контактных данных
     */
    public function actionUpdateContactData()
    {
        $profile = Profile::createFromUser($this->user);

        if (!empty($_POST['ajax']) && Yii::$app->request->isAjax) {
            // AJAX-валидация
            Yii::$app->response->format = Response::FORMAT_JSON;
            $profile->load($_POST);
            return ActiveForm::validate($profile);
        }

        $profileId = $profile->formName();
        if (!empty($_POST[$profileId])) {
            // изменения в профиле
            $profile->load($_POST);
            if (($profile->validate()) && $this->userApi->updateUserProfile($this->user, $profile)) {
                Yii::$app->session->setFlash('profile_success_update', true);
            }
            else {
                Yii::$app->session->setFlash('profile_error_update', true);
            }
        }

        return $this->redirect(Url::toRoute(['index']) . '#profile');
    }

    /**
     * Изменение e-mail
     */
    public function actionUpdateEmail()
    {
        $profile = Profile::createFromUser($this->user);

        if (!empty($_POST['ajax']) && Yii::$app->request->isAjax) {
            // AJAX-валидация
            Yii::$app->response->format = Response::FORMAT_JSON;
            $profile->load($_POST);
            return ActiveForm::validate($profile);
        }

        $profileId = $profile->formName();
        if (!empty($_POST[$profileId]) && !empty($_POST[$profileId]['email'])) {
            // изменить e-mail
            $profile->setAttributes([
                'email' => $_POST[$profileId]['email']
            ]);
            $isValid = $profile->validate();
            if ($isValid && $this->user->email == $profile->email) {
                Yii::$app->session->setFlash('email_change_error', Yii::t('frontend/user', 'E-mail has no changes'));
            }
            elseif ($isValid) {
                try {
                    $this->userApi->setNewUserEmail($this->user, $profile);
                    Yii::$app->session->setFlash('email_change_message', $profile->email);
                }
                catch (UserApiException $ex) {
                    Yii::$app->session->setFlash('email_change_error', Yii::t('frontend/user', 'Change e-mail error'));
                }
            }
        }

        return $this->redirect(Url::toRoute(['index']) . '#change-email');
    }

    /**
     * Профиль - главная страница
     */
    public function actionIndex()
    {
        // форма профиля
        $profile = Profile::createFromUser($this->user);
        // форма изменения пароля
        $changePassword = new ChangePassword();

        return $this->render('index', [
            'changePassword' => $changePassword,
            'profile' => $profile,
            'user' => $this->user,
        ]);
    }
}