<?php
namespace user\controllers\frontend;

use Yii;

use yii\base\Exception;
use user\forms\Profile;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\web\Response;
use yii\filters\AccessControl;

use user\forms\ChangePassword;

class ProfileController extends \app\components\Controller
{
    /**
     * @var \user\components\UserApi
     */
    protected $userApi;

    /**
     * @var \user\models\User
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
        return \yii\helpers\ArrayHelper::merge([
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
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
     * изменить e-mail пользователя по коду подтверждения,
     * пришедшему в письме
     */
    public function actionChangeEmail($code)
    {
        try {
            $this->userApi->changeUserEmail($this->user, $code);
            Yii::$app->session->setFlash('email_change_success');
        }
        catch (Exception $ex) {
            Yii::$app->session->setFlash('email_change_error', $ex->getMessage());
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

        // изменить e-mail
        if (!empty($_POST['ajax']) && ($_POST['ajax'] == 'change-email' || $_POST['ajax'] == 'profile') && Yii::$app->request->isAjax) {
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
                if ($this->userApi->setNewUserEmail($this->user, $profile)) {
                    Yii::$app->session->setFlash('email_change_message', $profile->email);
                }
                else {
                    Yii::$app->session->setFlash('email_change_error', Yii::t('frontend/user', 'Change e-mail error'));
                }
            }

            return $this->redirect(Url::toRoute(['index']) . '#change-email');
        }
        else if (!empty($_POST[$profileId])) {
            // изменения в профиле
            $profile->load($_POST);
            if (($profile->validate()) && $this->userApi->updateUserProfile($this->user, $profile)) {
                Yii::$app->session->setFlash('profile_success_update');
            }

            return $this->redirect(Url::toRoute(['index']) . '#profile');
        }

        // форма изменения пароля
        $changePassword = new ChangePassword();
        if (!empty($_POST['ajax']) && $_POST['ajax'] == 'change-password' && Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $changePassword->load($_POST);
            return ActiveForm::validate($changePassword);
        }
        if ($changePassword->load($_POST)) {
            if ($changePassword->validate() && ($this->userApi->changePassword($this->user, $changePassword))) {
                Yii::$app->session->setFlash('password_success_changed', true);
            }
            return $this->redirect(Url::toRoute(['index']) . '#change-password');
        }

        return $this->render('index', [
            'changePassword' => $changePassword,
            'profile' => $profile,
        ]);
    }
}