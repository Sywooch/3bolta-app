<?php
namespace partner\controllers\backend;

use Yii;

use partner\forms\PartnerSearch;
use partner\models\Partner;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;
use yii\base\Exception;

/**
 * Управление партнерами
 */
class PartnerController extends \app\components\BackendController
{
    public function getSubstanceName()
    {
        return Yii::t('backend/partner', 'of partner');
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
                        'roles' => ['backendViewPartners'],
                    ],
                    [
                        'actions' => ['update', 'delete'],
                        'allow' => true,
                        'verbs' => ['GET', 'POST'],
                        'roles' => ['backendUpdatePartners'],
                    ],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'verbs' => ['GET', 'POST'],
                        'roles' => ['backendCreatePartners'],
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
     * Список
     */
    public function actionIndex()
    {
        $searchModel = new PartnerSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Создание
     */
    public function actionCreate()
    {
        $model = new Partner();

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

                Yii::$app->serviceMessage->setMessage('success', Yii::t('backend/partner', 'Success create partner'));

                if (isset($_POST['apply'])) {
                    return $this->redirect(['update', 'id' => $model->id]);
                }
                else {
                    return $this->redirect(['index']);
                }
            }
            catch (Exception $ex) {
                $transaction->rollback();
                Yii::$app->serviceMessage->setMessage('danger', Yii::t('backend/partner', 'Error create a partner'));
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Изменение
     */
    public function actionUpdate($id)
    {
        $model = Partner::find()->andWhere(['id' => $id])->one();
        if (!($model instanceof Partner)) {
            throw new NotFoundHttpException();
        }

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

                Yii::$app->serviceMessage->setMessage('success', Yii::t('backend/partner', 'Success update partner'));

                if (isset($_POST['apply'])) {
                    return $this->redirect(['update', 'id' => $model->id]);
                }
                else {
                    return $this->redirect(['index']);
                }
            }
            catch (Exception $ex) {
                $transaction->rollback();
                Yii::$app->serviceMessage->setMessage('danger', Yii::t('backend/partner', 'Error update a partner'));
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Удаление
     */
    public function actionDelete($id)
    {
        $model = Partner::find()->andWhere(['id' => $id])->one();
        if (!($model instanceof Partner)) {
            throw new NotFoundHttpException();
        }

        $transaction = Partner::getDb()->beginTransaction();

        try {
            if (!$model->delete()) {
                throw new Exception();
            }
            $transaction->commit();

            Yii::$app->serviceMessage->setMessage('success', Yii::t('backend/partner', 'Partner success deleted'));
        }
        catch (Exception $ex) {
            $transaction->rollBack();
            Yii::$app->serviceMessage->setMessage('danger', Yii::t('backend/partner', 'Partner delete error'));
        }

        return $this->redirect(['index']);
    }
}
