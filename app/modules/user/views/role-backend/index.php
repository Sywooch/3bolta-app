<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
$this->title = Yii::t('backend/user', 'Role list');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-index">
<?php
print Html::tag('p', Html::a(Yii::t('backend', 'Create {modelClass}', [
    'modelClass' => Yii::t('backend/user', 'Role')
]), ['create'], ['class' => 'btn btn-success']));

print GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'label' => Yii::t('backend/user', 'Name'),
            'value' => function($data) {
                return $data->name;
            }
        ],
        [
            'label' => Yii::t('backend/user', 'Description'),
            'value' => function($data) {
                return Html::a(
                    Html::encode($data->description),
                    Url::toRoute(['/user/role-backend/update', 'id' => $data->name])
                );
            },
            'format' => 'html',
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{update} {delete}',
        ],
    ],
]);
?>
</div>

