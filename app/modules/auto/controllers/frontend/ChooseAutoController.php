<?php
namespace auto\controllers\frontend;

use Yii;

use auto\models\Mark;
use auto\models\Model;
use auto\models\Serie;
use auto\models\Modification;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Выбор автомобилей для виджета ChooseAutomobile
 */
class ChooseAutoController extends \app\components\Controller
{
    public function init()
    {
        if (!Yii::$app->request->isAjax) {
            throw new \yii\web\ServerErrorHttpException();
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::init();
    }

    /**
     * Сгенерировать данные
     * @param [] $res массив ActiveRecord
     */
    protected function renderDataArray($res)
    {
        return ArrayHelper::map($res, 'id', function($data, $default) {
            if ($data instanceof \auto\models\ActiveRecord) {
                return [
                    'name' => $data instanceof Serie ? $data->getSearchName() : $data->name,
                    'full_name' => $data->full_name,
                ];
            }
            else {
                return [
                    'name' => '',
                    'full_name' => '',
                ];
            }
        });
    }

    /**
     * Получить марки
     */
    public function actionMark()
    {
        $res = Mark::findOrderByName()->all();
        return $this->renderData($res, []);
    }

    /**
     * Получить модификации
     * @param int $serieId
     * @throws NotFoundHttpException
     */
    public function actionModification()
    {
        $serieId = !empty($_POST['serieId']) ? $_POST['serieId'] : null;
        if (!$serieId) {
            throw new NotFoundHttpException();
        }
        $serie = Serie::find()->andWhere(['id' => $serieId])->one();
        if (!($serie instanceof Serie)) {
            throw new NotFoundHttpException();
        }

        $model = $serie->model;
        if (!($model instanceof Model)) {
            throw new NotFoundHttpException();
        }

        $mark = $model->mark;
        if (!($mark instanceof Mark)) {
            throw new NotFoundHttpException();
        }

        $parents = [
            'mark-' . $mark->id,
            'model-' . $model->id,
            'serie-' . $serie->id,
        ];

        $res = Modification::find()->andWhere([
            'model_id' => $model->id,
            'serie_id' => $serie->id,
        ])->all();

        return $this->renderData($res, $parents);
    }

    /**
     * Получить серии
     * @throws NotFoundHttpException
     */
    public function actionSerie()
    {
        $modelId = !empty($_POST['modelId']) ? $_POST['modelId'] : null;
        if (!$modelId) {
            throw new NotFoundHttpException();
        }
        $model = Model::find()->andWhere(['id' => $modelId])->one();
        if (!($model instanceof Model)) {
            throw new NotFoundHttpException();
        }

        $mark = $model->mark;
        if (!($mark instanceof Mark)) {
            throw new NotFoundHttpException();
        }

        $parents = [];

        $res = Serie::find()->andWhere(['model_id' => $modelId])->all();

        $parents[] = 'mark-' . $mark->id;
        $parents[] = 'model-' . $model->id;

        return $this->renderData($res, $parents);
    }

    /**
     * Получить модели
     * @throws NotFoundHttpException
     */
    public function actionModel()
    {
        $markId = !empty($_POST['markId']) ? $_POST['markId'] : null;
        if (!$markId) {
            throw new NotFoundHttpException();
        }
        $mark = Mark::findOrderByName()->andWhere(['id' => $markId])->one();
        if (!($mark instanceof Mark)) {
            throw new NotFoundHttpException();
        }

        $parents = [];

        $res = Model::find()->andWhere(['mark_id' => $markId])->all();

        $parents[] = 'mark-' . $mark->id;

        return $this->renderData($res, $parents);
    }

    /**
     * Рендер страницы
     * @param [] $data массив данных ActiveRecord
     * @param [] $parents массив родителей вида: mark-id, model-id и т.д.
     * @return []
     */
    protected function renderData($data = [], $parents = [])
    {
        $data = $this->renderDataArray($data);

        $ret = [
            'cnt' => count($data),
            'data' => []
        ];

        $jsClass = [];
        foreach ($parents as $parent) {
            $jsClass[] = 'js-' . $parent;
        }
        $jsClass = implode(' ', $jsClass);

        foreach ($data as $k => $item) {
            $data[$k] = [
                'id' => $k,
                'name' => $item['name'],
                'full_name' => $item['full_name'],
                'jsClass' => $jsClass,
            ];
        }

        $ret['data'] = $data;

        return $ret;
    }
}