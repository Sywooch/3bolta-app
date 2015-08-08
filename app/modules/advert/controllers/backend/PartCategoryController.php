<?php
namespace advert\controllers\backend;

use Yii;
use advert\forms\PartCategorySearch;
use advert\models\PartCategory;
use yii\web\Response;
use yii\widgets\ActiveForm;
use Exception;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * Управление категориями
 */
class PartCategoryController extends \app\components\BackendController
{
    public function getSubstanceName()
    {
        return Yii::t('backend/advert', 'of category');
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
                        'roles' => ['backendViewAdvertCategory'],
                    ],
                    [
                        'actions' => ['update', 'delete'],
                        'allow' => true,
                        'verbs' => ['GET', 'POST'],
                        'roles' => ['backendUpdateAdvertCategory'],
                    ],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'verbs' => ['GET', 'POST'],
                        'roles' => ['backendCreateAdvertCategory'],
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
     * Создание категории
     */
    public function actionCreate()
    {
        $model = new PartCategory();

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

                $transaction->commit();

                Yii::$app->serviceMessage->setMessage('success', Yii::t('backend/advert', 'Success create category'));

                if (isset($_POST['apply'])) {
                    return $this->redirect(['update', 'id' => $model->id]);
                }
                else {
                    return $this->redirect(['index']);
                }
            }
            catch (Exception $ex) {
                $transaction->rollback();

                Yii::$app->serviceMessage->setMessage('danger', Yii::t('backend/advert', 'Error create a category'));
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Редактирование категории
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

                $transaction->commit();

                Yii::$app->serviceMessage->setMessage('success', Yii::t('backend/advert', 'Success update category'));

                if (isset($_POST['apply'])) {
                    return $this->refresh();
                }
                else {
                    return $this->redirect(['index']);
                }
            }
            catch (Exception $ex) {
                $transaction->rollback();

                Yii::$app->serviceMessage->setMessage('danger', Yii::t('backend/advert', 'Error update a category'));
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Удаление категорий
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
     * Список категорий
     */
    public function actionIndex()
    {
        $searchModel = new PartCategorySearch();
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
        $model = PartCategory::find()->where(['id' => $id])->one();

        if (empty($model)) {
            throw new NotFoundHttpException();
        }

        return $model;
    }
}