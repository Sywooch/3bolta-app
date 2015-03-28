<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel \partner\forms\PartnerSearch */
/* @var $form \yii\widgets\ActiveForm */
/* @var $dataProvider \yii\db\ActiveDataProvider */

$this->title = Yii::t('backend/partner', 'Partners list');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-index">
<?php
if (Yii::$app->user->can('backendCreatePartners')) {
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
        <?= $form->field($searchModel, 'name') ?>
        <?= $form->field($searchModel, 'company_type')->dropDownList($searchModel->getCompanyTypes()) ?>
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
            'attribute' => 'name',
            'value' => function($data) {
                return Html::a(
                    $data->name,
                    Url::toRoute(['update', 'id' => $data->id])
                );
            },
            'format' => 'html',
        ],
        [
            'attribute' => 'company_type',
            'value' => function($data) {
                /* @var $data \partner\models\Partner */
                return $data->getCompanyType();
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

