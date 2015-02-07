<?php
/* @var $this yii\web\View */
/* @var $model \advert\models\Category */

$this->title = Yii::t('backend', 'Update {modelClass}', [
    'modelClass' => $this->context->getSubstanceName(),
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Advert categories list'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-update">

    <?=$this->render('_form', [
        'model' => $model,
    ])?>

</div>
