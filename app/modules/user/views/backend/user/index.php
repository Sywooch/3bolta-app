<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
$this->title = Yii::t('backend/user', 'User list');
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

    <?= $form->field($searchModel, 'email') ?>

    <?= $form->field($searchModel, 'name') ?>

    <?= $form->field($searchModel, 'status') ?>


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
                    Url::toRoute(['update', 'id' => $data->id])
                );
            },
            'format' => 'html',
        ],
        [
            'attribute' => 'email',
            'value' => function($data) {
                return Html::a(
                    $data->email,
                    Url::toRoute(['update', 'id' => $data->id])
                );
            },
            'format' => 'html',
        ],
        'status',
        'name',
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{update} {delete}',
        ],
    ],
]);
?>
</div>

