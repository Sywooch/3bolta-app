<?php
namespace partner\controllers\backend;

use Yii;

use partner\forms\TradePointSearch;
use partner\models\TradePoint;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;
use yii\base\Exception;

/**
 * Управление торговыми точками
 */
class TradePointController extends \app\components\BackendController
{
    public function getSubstanceName()
    {
        return Yii::t('backend/partner', 'of trade point');
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
                        'roles' => ['backendViewTradePoints'],
                    ],
                    [
                        'actions' => ['update', 'delete'],
                        'allow' => true,
                        'verbs' => ['GET', 'POST'],
                        'roles' => ['backendUpdateTradePoints'],
                    ],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'verbs' => ['GET', 'POST'],
                        'roles' => ['backendCreateTradePoints'],
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
        $searchModel = new TradePointSearch();
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
        $model = new TradePoint();

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

                Yii::$app->serviceMessage->setMessage('success', Yii::t('backend/partner', 'Success create trade point'));

                if (isset($_POST['apply'])) {
                    return $this->redirect(['update', 'id' => $model->id]);
                }
                else {
                    return $this->redirect(['index']);
                }
            }
            catch (Exception $ex) {
                $transaction->rollback();
                Yii::$app->serviceMessage->setMessage('danger', Yii::t('backend/partner', 'Error create a trade point'));
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
        $model = TradePoint::find()->andWhere(['id' => $id])->one();
        if (!($model instanceof TradePoint)) {
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

                Yii::$app->serviceMessage->setMessage('success', Yii::t('backend/partner', 'Success update trade point'));

                if (isset($_POST['apply'])) {
                    return $this->redirect(['update', 'id' => $model->id]);
                }
                else {
                    return $this->redirect(['index']);
                }
            }
            catch (Exception $ex) {
                $transaction->rollback();
                Yii::$app->serviceMessage->setMessage('danger', Yii::t('backend/partner', 'Error update a trade point'));
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
        $model = TradePoint::find()->andWhere(['id' => $id])->one();
        if (!($model instanceof TradePoint)) {
            throw new NotFoundHttpException();
        }

        $transaction = TradePoint::getDb()->beginTransaction();

        try {
            if (!$model->delete()) {
                throw new Exception();
            }
            $transaction->commit();

            Yii::$app->serviceMessage->setMessage('success', Yii::t('backend/partner', 'Trade point success deleted'));
        }
        catch (Exception $ex) {
            $transaction->rollBack();
            Yii::$app->serviceMessage->setMessage('danger', Yii::t('backend/partner', 'Trade point delete error'));
        }

        return $this->redirect(['index']);
    }
}
