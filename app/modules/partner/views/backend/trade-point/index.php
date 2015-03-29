<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel \partner\forms\TradePointSearch */
/* @var $form \yii\widgets\ActiveForm */
/* @var $dataProvider \yii\db\ActiveDataProvider */

$this->title = Yii::t('backend/partner', 'Trade points list');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-index">
<?php
if (Yii::$app->user->can('backendCreateTradePoints')) {
    print Html::tag('p', Html::a(Yii::t('backend', 'Create {modelClass}', [
        'modelClass' => $this->context->getSubstanceName(),
    ]), ['create'], ['class' => 'btn btn-success']));
}
?>

<div class="user-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
        <?= $form->field($searchModel, 'id') ?>
        <?= $form->field($searchModel, 'partner_id') ?>
        <?= $form->field($searchModel, 'address') ?>
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
        'created',
        'edited',
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
            'attribute' => 'partner_id',
            'value' => function($data) {
                /* @var $data \partner\models\TradePoint */
                if ($data->partner instanceof \partner\models\Partner) {
                    return Html::a(
                        $data->partner->name,
                        Url::toRoute(['/partner/partner/update', 'id' => $data->partner->id])
                    );
                }
                return '';
            },
            'format' => 'html',
        ],
        'address',
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{update} {delete}',
        ],
    ],
]);
?>
</div>

