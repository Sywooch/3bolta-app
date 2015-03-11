<?php
/* @var $this yii\web\View */
/* @var $model \handbook\models\HandbookValue */

$this->title = Yii::t('backend', 'Create {modelClass}', [
    'modelClass' => $this->context->getSubstanceName(),
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend/handbook', 'Value list') . ' "' . $model->handbook->name . '"', 'url' => ['index', 'code' => $model->handbook_code]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-update">

    <?=$this->render('_form', [
        'model' => $model,
    ])?>

</div>
