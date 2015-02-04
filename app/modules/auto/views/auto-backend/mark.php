<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
$this->title = Yii::t('backend/auto', 'Mark list');
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
                    Url::toRoute(['model', 'mark_id' => $data->id])
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
        'name',
        [
            'label' => Yii::t('backend/auto', 'Models'),
            'value' => function($data) {
                return Html::a(
                    $data->getModels()->count(),
                    Url::toRoute(['model', 'mark_id' => $data->id])
                );
            },
            'format' => 'html',
        ],
    ],
]);
?>
</div>

