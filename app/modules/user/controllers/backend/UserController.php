<?php
namespace user\controllers\backend;

use Yii;
use user\forms\UserSearch;
use user\models\User;
use yii\web\Response;
use yii\widgets\ActiveForm;
use Exception;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * Управление пользователями
 */
class UserController extends \app\components\BackendController
{
    public function getSubstanceName()
    {
        return Yii::t('backend/user', 'of user');
    }

    public function behaviors()
    {
        return \yii\helpers\ArrayHelper::merge([
            'access' => [
                'rules' => [
                    [
                        'actions' => ['index', 'update'],
                        'allow' => true,
                        'verbs' => ['GET'],
                        'roles' => ['backendViewUser'],
                    ],
                    [
                        'actions' => ['update', 'delete'],
                        'allow' => true,
                        'verbs' => ['GET', 'POST'],
                        'roles' => ['backendUpdateUser'],
                    ],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'verbs' => ['GET', 'POST'],
                        'roles' => ['backendCreateUser'],
                    ]
                ],
            ],
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ], parent::behaviors());
    }

    /**
     * Создание пользователя
     */
    public function actionCreate()
    {
        $model = new User();

        if (Yii::$app->request->isAjax && $model->load($_POST)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load($_POST) && $model->validate()) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                if (!$model->save()) {
                    throw new Exception();
                }

                if (!$model->updateRoles()) {
                    throw new Exception();
                }

                $transaction->commit();

                Yii::$app->serviceMessage->setMessage('success', Yii::t('backend/user', 'Success create user'));

                if (isset($_POST['apply'])) {
                    return $this->redirect(['update', 'id' => $model->id]);
                }
                else {
                    return $this->redirect(['index']);
                }
            }
            catch (Exception $ex) {
                $transaction->rollback();

                Yii::$app->serviceMessage->setMessage('danger', Yii::t('backend/user', 'Error create a user'));
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Редактирование пользователя
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isAjax && $model->load($_POST)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load($_POST) && $model->validate()) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                if (!$model->save()) {
                    throw new Exception();
                }

                if (!$model->updateRoles()) {
                    throw new Exception();
                }

                $transaction->commit();

                Yii::$app->serviceMessage->setMessage('success', Yii::t('backend/user', 'Success update user'));

                if (isset($_POST['apply'])) {
                    return $this->refresh();
                }
                else {
                    return $this->redirect(['index']);
                }
            }
            catch (Exception $ex) {
                $transaction->rollback();

                Yii::$app->serviceMessage->setMessage('danger', Yii::t('backend/user', 'Error update a user'));
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Удаление пользователя
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        try {
            $model->delete();

            return $this->redirect(['index']);
        } catch (Exception $ex) {
            throw new ServerErrorHttpException();
        }
    }

    /**
     * Список пользователей
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Возвращает искомую модель
     * @param string $id
     * @return \app\components\ActiveRecord
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $model = User::find()->where(['id' => $id])->one();

        if (empty($model)) {
            throw new NotFoundHttpException();
        }

        return $model;
    }
}