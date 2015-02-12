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
     * Получить марки
     */
    public function actionMark()
    {
        $res = Mark::findOrderByName()->all();
        $data = ArrayHelper::map($res, 'id', 'name');
        return $this->renderData($data, '', []);
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

        $model = $serie->getModel()->one();
        if (!($model instanceof Model)) {
            throw new NotFoundHttpException();
        }

        $mark = $model->getMark()->one();
        if (!($mark instanceof Mark)) {
            throw new NotFoundHttpException();
        }

        $parents = [
            'mark-' . $mark->id,
            'model-' . $model->id,
            'serie-' . $serie->id,
        ];

        $generation = $serie->getGeneration()->one();

        $prefixName = $mark->name . ' ' . $model->name . ' (' . $serie->name . ')';
        if ($generation && $generation->year_begin && $generation->name) {
            $prefixName .= ' ' . $generation->name . ' (' . $generation->year_begin . ' - ';
            if ($generation->year_end) {
                $prefixName .= $generation->year_end;
            }
            else {
                $prefixName .= '...';
            }
            $prefixName .= ')';
        }

        $res = Modification::find()->andWhere([
            'model_id' => $model->id,
            'serie_id' => $serie->id,
        ])->all();
        $data = ArrayHelper::map($res, 'id', 'name');

        return $this->renderData($data, $prefixName, $parents);
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

        $mark = $model->getMark()->one();
        if (!($mark instanceof Mark)) {
            throw new NotFoundHttpException();
        }

        $parents = [];

        $res = Serie::find()->andWhere(['model_id' => $modelId])->all();
        $data = [];
        foreach ($res as $i) {
            $item = ' (' . $i->name . ')';
            $generation = $i->getGeneration()->one();
            if ($generation && $generation->name && $generation->year_begin) {
                $item .= ' ' . $generation->name;
                $item .= ' (' . $generation->year_begin . ' - ';
                if (!empty($generation->year_end)) {
                    $item .= $generation->year_end;
                }
                else {
                    $item .= '...';
                }
                $item .= ')';
            }
            $data[$i->id] = $item;
        }

        $parents[] = 'mark-' . $mark->id;
        $parents[] = 'model-' . $model->id;

        return $this->renderData($data, $mark->name . ' ' . $model->name, $parents);
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
        $data = ArrayHelper::map($res, 'id', 'name');

        $parents[] = 'mark-' . $mark->id;

        return $this->renderData($data, $mark->name, $parents);
    }

    /**
     * Рендер страницы
     * @param [] $data массив данных: ключ - идентификатор, значение - название
     * @param string $prefixName префикс к названиям
     * @param [] $parents массив родителей вида: mark-id, model-id и т.д.
     * @return []
     */
    protected function renderData($data = [], $prefixName = '', $parents = [])
    {
        $ret = [
            'cnt' => count($data),
            'data' => []
        ];

        $jsClass = [];
        foreach ($parents as $parent) {
            $jsClass[] = 'js-' . $parent;
        }
        $jsClass = implode(' ', $jsClass);

        foreach ($data as $k => $name) {
            $data[$k] = [
                'id' => $k,
                'name' => $prefixName ? $prefixName . ' ' . $name : $name,
                'jsClass' => $jsClass,
            ];
        }

        $ret['data'] = $data;

        return $ret;
    }
}