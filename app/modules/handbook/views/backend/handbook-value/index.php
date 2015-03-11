<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
$this->title = Yii::t('backend/handbook', 'Value list') . ' "' . $searchModel->handbook->name . '"';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-index">
<?php
print Html::tag('p', Html::a(Yii::t('backend', 'Create {modelClass}', [
    'modelClass' => $this->context->getSubstanceName(),
]), ['create', 'code' => $searchModel->handbook_code], ['class' => 'btn btn-success']));
?>

<div class="user-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($searchModel, 'id') ?>

    <?= $form->field($searchModel, 'name') ?>


    <div class="form-group">
        <?= Html::submitButton(Yii::t('backend', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('backend', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
print GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'attribute' => 'id',
            'value' => function($data) {
                return Html::a(
                    $data->id,
                    Url::toRoute(['update', 'id' => $data->id, 'code' => $data->handbook_code])
                );
            },
            'format' => 'html',
        ],
        'sort',
        [
            'attribute' => 'name',
            'value' => function($data) {
                return Html::a(
                    Html::encode($data->name),
                    Url::toRoute(['update', 'id' => $data->id, 'code' => $data->handbook_code])
                );
            },
            'format' => 'html',
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{update} {delete}',
            'buttons' => [
                'update' => function($url, $model, $key) {
                    return Html::a('<span class="glyphicon glyphicon-pencil"></span>', Url::toRoute(['update',
                        'id' => $model->id,
                        'code' => $model->handbook_code
                    ]));
                },
                'delete' => function($url, $model, $key) {
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', Url::toRoute(['delete',
                        'id' => $model->id,
                        'code' => $model->handbook_code
                    ]), [
                        'title' => Yii::t('yii', 'Delete'),
                        'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                        'data-method' => 'post',
                        'data-pjax' => '0',
                    ]);
                }
            ]
        ],
    ],
]);
?>
</div>

