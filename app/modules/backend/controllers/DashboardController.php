<?php
namespace backend\controllers;

use Yii;
use backend\forms\Login as LoginForm;
use backend\components\BaseBackendController;

/**
 * Бекенд: авторизация, восстановление пароля и главная страница.
 */
class DashboardController extends BaseBackendController
{
    public function behaviors()
    {
        $filters = parent::behaviors();

        return \yii\helpers\ArrayHelper::merge([
            'access' => [
                'rules' => [
                    [
                        'actions' => ['login'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['index', 'logout'],
                        'allow' => true,
                        'roles' => ['backend'],
                    ]
                ],
            ]
        ], parent::behaviors());
    }

    /**
     * Авторизация
     * @return string
     */
    public function actionLogin()
    {
        $this->layout = '@app/modules/backend/views/layouts/_base';

        $form = new LoginForm();

        if ($form->load(Yii::$app->request->post()) && $form->login()) {
            return $this->goBack();
        }

        return $this->render('login', [
            'model' => $form
        ]);
    }

    /**
     * Логаут
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goBack();
    }

    public function actionIndex()
    {
        return $this->render('index', [

        ]);
    }
}