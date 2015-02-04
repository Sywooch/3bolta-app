<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $mark app\modules\auto\models\Mark */
/* @var $this yii\web\View */
$this->title = Html::encode($mark->name);
$this->params['breadcrumbs'][] = [
    'url' => ['mark'],
    'label' => Yii::t('backend/auto', 'Mark list'),
];
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
                    Url::toRoute(['serie', 'model_id' => $data->id])
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
            'value' => function($data) use ($mark) {
                return $mark->name . ' ' . $data->name;
            }
        ],
        [
            'label' => Yii::t('backend/auto', 'Series'),
            'value' => function($data) {
                return Html::a(
                    $data->getSeries()->count(),
                    Url::toRoute(['serie', 'model_id' => $data->id])
                );
            },
            'format' => 'html',
        ],
        [
            'label' => Yii::t('backend/auto', 'Modifications'),
            'value' => function($data) {
                return $data->getModifications()->count();
            },
            'format' => 'html',
        ],
    ],
]);
?>
</div>

