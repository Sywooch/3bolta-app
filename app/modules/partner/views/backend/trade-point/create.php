<?php
/* @var $this yii\web\View */
/* @var $model \partner\models\TradePoint */

$this->title = Yii::t('backend', 'Create {modelClass}', [
    'modelClass' => $this->context->getSubstanceName(),
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend/partner', 'Trade points list'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-update">
    <?=$this->render('_form', [
        'model' => $model,
    ])?>
</div>
