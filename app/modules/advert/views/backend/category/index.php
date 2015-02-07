<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
$this->title = Yii::t('backend', 'Advert categories list');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-index">
<?php
print Html::tag('p', Html::a(Yii::t('backend', 'Create {modelClass}', [
    'modelClass' => $this->context->getSubstanceName(),
]), ['create'], ['class' => 'btn btn-success']));
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
                    str_repeat('--', (int) ($data->depth - 1)) . $data->id,
                    Url::toRoute(['update', 'id' => $data->id])
                );
            },
            'format' => 'html',
        ],
        [
            'attribute' => 'name',
            'value' => function($data) {
                return Html::a(
                    $data->name,
                    Url::toRoute(['update', 'id' => $data->id])
                );
            },
            'format' => 'html',
        ],
        'sort',
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{update} {delete}',
        ],
    ],
]);
?>
</div>

