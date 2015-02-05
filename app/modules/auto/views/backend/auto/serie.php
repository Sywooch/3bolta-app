<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $mark auto\models\Mark */
/* @var $model auto\models\Model */
/* @var $this yii\web\View */
$this->title = '';
if ($mark) {
    $this->title .= Html::encode($mark->name) . ' ';
}
$this->title .= Html::encode($model->name);
$this->params['breadcrumbs'][] = [
    'url' => ['mark'],
    'label' => Yii::t('backend/auto', 'Mark list'),
];
if ($mark) {
    $this->params['breadcrumbs'][] = [
        'url' => ['model', 'mark_id' => $mark->id],
        'label' => Html::encode($mark->name) . ' ',
    ];
}
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-index">
<?php
print GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'attribute' => 'id',
            'value' => function($data) {
                return Html::a(
                    $data->id,
                    Url::toRoute(['modification', 'model_id' => $data->model_id, 'serie_id' => $data->id])
                );
            },
            'format' => 'html',
        ],
        [
            'attribute' => 'active',
            'value' => function($data) {
                return $data->active ? Yii::t('main', 'Yes') : Yii::t('main', 'No');
            }
        ],
        [
            'attribute' => 'name',
            'value' => function($data) use ($model, $mark) {
                $ret = '';
                if (!empty($mark)) {
                    $ret .= $mark->name . ' ';
                }
                if ($generation = $data->getGeneration()->one()) {
                    $ret .= $generation->name . ' (' . $generation->year_begin . '-' . $generation->year_end . ') ';
                }
                else {
                    $ret .= $model->name . ' ';
                }
                $ret .= $data->name;
                return $ret;
            }
        ],
        [
            'label' => Yii::t('backend/auto', 'Modifications'),
            'value' => function($data) {
                return Html::a(
                    $data->getModifications()->count(),
                    Url::toRoute(['modification', 'model_id' => $data->model_id, 'serie_id' => $data->id])
                );
            },
            'format' => 'html',
        ],
    ],
]);
?>
</div>

