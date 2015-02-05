<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
$this->title = Yii::t('backend/storage', 'File list');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-index">
<?php
print Html::tag('p', Html::a(Yii::t('backend', 'Create {modelClass}', [
    'modelClass' => Yii::t('backend/storage', 'File')
]), ['create'], ['class' => 'btn btn-success']));
?>

<div class="user-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($searchModel, 'id') ?>

    <?= $form->field($searchModel, 'size') ?>

    <?= $form->field($searchModel, 'real_name') ?>

    <?= $form->field($searchModel, 'uploader_addr') ?>

    <?= $form->field($searchModel, 'repository')->dropDownList(Yii::$app->getModule('storage')->repository) ?>

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
        'repository',
        'size',
        'real_name',
        [
            'attribute' => 'is_image',
            'value' => function($data) {
                return $data->is_image ? Yii::t('main', 'Yes') : Yii::t('main', 'No');
            }
        ],
        'uploader_addr',
        [
            'attribute' => 'file_path',
            'value' => function($data) {
                return Html::a(
                    Html::encode($data->file_path),
                    $data->getUrl()
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

