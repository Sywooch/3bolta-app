<?php
namespace storage\controllers\backend;

use Yii;
use storage\forms\FileSearch;
use yii\web\Response;
use yii\widgets\ActiveForm;
use Exception;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;
use storage\forms\UploadFile;
use storage\models\File;

/**
 * Управление файлами
 */
class StorageController extends \app\components\BaseBackendController
{
    public function getSubstanceName()
    {
        return Yii::t('backend/storage', 'of file');
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
                        'roles' => ['backendViewFile'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'verbs' => ['POST'],
                        'roles' => ['backendDeleteFile'],
                    ],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'verbs' => ['GET', 'POST'],
                        'roles' => ['backendUploadFile'],
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
     * Загрузка файла
     */
    public function actionCreate()
    {
        $model = new UploadFile();

        if ($model->load($_POST)) {
            $uploadedFile = UploadedFile::getInstance($model, 'file');
            $model->setFile($uploadedFile);
            if ($model->validate()) {
                $file = File::uploadFile($model->getStorage(), $model->getFile());
                if ($file instanceof File) {
                    Yii::$app->serviceMessage->setMessage('success', Yii::t('backend/storage', 'Success upload file'));
                    return $this->redirect(['index']);
                }
                else {
                    Yii::$app->serviceMessage->setMessage('danger', Yii::t('backend/storage', 'Error upload file'));
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Просмотр файла
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        return $this->render('update', [
            'model' => $model,
        ]);
    }
    /**
     * Удаление файла
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
     * Список файлов
     */
    public function actionIndex()
    {
        $searchModel = new FileSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Возвращает искомую модель
     * @param string $id
     * @return \yii\db\ActiveRecord
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $model = File::find()->where(['id' => $id])->one();

        if (empty($model)) {
            throw new NotFoundHttpException();
        }

        return $model;
    }
}