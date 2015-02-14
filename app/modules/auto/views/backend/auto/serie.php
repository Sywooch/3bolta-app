<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $mark auto\models\Mark */
/* @var $model auto\models\Model */
/* @var $this yii\web\View */
$this->title = $model->full_name;
$this->params['breadcrumbs'][] = [
    'url' => ['mark'],
    'label' => Yii::t('backend/auto', 'Mark list'),
];
if ($mark) {
    $this->params['breadcrumbs'][] = [
        'url' => ['model', 'mark_id' => $mark->id],
        'label' => Html::encode($mark->full_name) . ' ',
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
            'value' => function($data) {
                return $data->full_name;
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

