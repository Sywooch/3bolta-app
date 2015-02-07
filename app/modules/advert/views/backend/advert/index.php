<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use advert\models\Advert;

/* @var $this yii\web\View */
$this->title = Yii::t('backend/advert', 'Advert list');
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
    <?= $form->field($searchModel, 'advert_name') ?>
    <?= $form->field($searchModel, 'user_name') ?>
    <?= $form->field($searchModel, 'user_email') ?>
    <?= $form->field($searchModel, 'user_id') ?>
    <?= $form->field($searchModel, 'active')->checkbox() ?>
    <?= $form->field($searchModel, 'category_id')->dropDownList(Advert::getCategoryDropDownList()) ?>
    <?= $form->field($searchModel, 'condition_id')->dropDownList(Advert::getConditionDropDownList()) ?>

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
            'attribute' => 'active',
            'value' => function($data) {
                return $data->active ? Yii::t('main', 'Yes') : Yii::t('main', 'No');
            }
        ],
        [
            'attribute' => 'user_name',
            'value' => function($data) {
                /* @var $data \advert\models\Advert */
                if ($data->user_id && $user = $data->getUser()) {
                    return Html::a(
                        $user->name,
                        Url::toRoute(['/user/user/update', 'id' => $data->id])
                    );
                }
                else if (!empty($data->user_name)) {
                    $ret = $data->user_name;
                    if (!empty($data->user_email)) {
                        $ret .= ' (' . $data->user_email . ')';
                    }
                    return $ret;
                }
            },
            'format' => 'html',
        ],
        [
            'attribute' => 'advert_name',
            'value' => function($data) {
                return Html::a(
                    $data->advert_name,
                    Url::toRoute(['update', 'id' => $data->id])
                );
            },
            'format' => 'html',
        ],
        [
            'attribute' => 'category_id',
            'value' => function($data) {
                /* @var $data \advert\models\Advert */
                $category = $data->getCategory()->one();
                if ($category) {
                    return $category->name;
                }
            }
        ],
        [
            'attribute' => 'condition_id',
            'value' => function($data) {
                /* @var $data \advert\models\Advert */
                $condition = $data->getCondition()->one();
                if ($condition) {
                    return $condition->name;
                }
            }
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{update} {delete}',
        ],
    ],
]);
?>
</div>

