<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $mark auto\models\Mark */
/* @var $model auto\models\Model */
/* @var $serie auto\models\Serie */
/* @var $generation auto\models\Generation */
/* @var $this yii\web\View */
$this->title = '';
if ($mark) {
    $this->title .= $mark->name . ' ';
}
if ($generation) {
    $this->title .= $generation->name . ' ';
}
else {
    $this->title .= $model->name . ' ';
}
$this->title .= $serie->name . ' ';
$this->title = Html::encode($this->title);
$this->params['breadcrumbs'][] = [
    'url' => ['mark'],
    'label' => Yii::t('backend/auto', 'Mark list'),
];
if ($mark) {
    $this->params['breadcrumbs'][] = [
        'url' => ['model', 'mark_id' => $mark->id],
        'label' => Html::encode($mark->name),
    ];
}
if ($mark && $model) {
    $this->params['breadcrumbs'][] = [
        'url' => ['serie', 'model_id' => $model->id],
        'label' => Html::encode($mark->name) . ' ' . Html::encode($model->name),
    ];
}
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-index">
<?php
print GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'id',
        [
            'attribute' => 'active',
            'value' => function($data) {
                return $data->active ? Yii::t('main', 'Yes') : Yii::t('main', 'No');
            }
        ],
        'name',
    ],
]);
?>
</div>

