<?php
namespace auto\controllers\backend;

use yii\data\ActiveDataProvider;
use auto\models\Mark;
use auto\models\Model;
use auto\models\Serie;
use auto\models\Modification;
use yii\web\NotFoundHttpException;

/**
 * Управление автомобилями
 */
class AutoController extends \app\components\BackendController
{
    public function getSubstanceName()
    {
        return Yii::t('backend/auto', 'of automobile');
    }

    public function behaviors()
    {
        return \yii\helpers\ArrayHelper::merge([
            'access' => [
                'rules' => [
                    [
                        'allow' => true,
                        'verbs' => ['GET'],
                        'roles' => ['backendViewAuto'],
                    ],
                    [
                        'allow' => true,
                        'verbs' => ['POST'],
                        'roles' => ['backendUpdateAuto'],
                    ],
                ],
            ],
        ], parent::behaviors());
    }

    /**
     * Список марок
     */
    public function actionMark()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Mark::findOrderByName(),
        ]);

        return $this->render('mark', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Список моделей марки
     */
    public function actionModel($mark_id)
    {
        $mark = $this->findModel(Mark::className(), $mark_id);

        $dataProvider = new ActiveDataProvider([
            'query' => Model::find()->where(['mark_id' => $mark_id])
        ]);

        return $this->render('model', [
            'mark' => $mark,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * Список серий модели
     */
    public function actionSerie($model_id)
    {
        $model = $this->findModel(Model::className(), $model_id);
        $mark = $model->getMark()->one();

        $dataProvider = new ActiveDataProvider([
            'query' => Serie::find()->where(['model_id' => $model_id])
        ]);

        return $this->render('serie', [
            'model' => $model,
            'mark' => $mark,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * Список модификаций серии
     */
    public function actionModification($model_id, $serie_id)
    {
        $model = $this->findModel(Model::className(), $model_id);
        $serie = $this->findModel(Serie::className(), $serie_id);
        $generation = $serie->getGeneration()->one();
        $mark = $model->getMark()->one();

        $dataProvider = new ActiveDataProvider([
            'query' => Modification::find()->where([
                'serie_id' => $serie_id,
                'model_id' => $model_id
            ])
        ]);

        return $this->render('modification', [
            'mark' => $mark,
            'model' => $model,
            'serie' => $serie,
            'generation' => $generation,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * Возвращает искомую модель
     * @param string $className
     * @param string $id
     * @return \app\components\ActiveRecord
     * @throws NotFoundHttpException
     */
    protected function findModel($className, $id)
    {
        $model = $className::find()->where(['id' => $id])->one();

        if (empty($model)) {
            throw new NotFoundHttpException();
        }

        return $model;
    }
}