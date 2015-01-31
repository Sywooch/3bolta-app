<?php
/* @var $this yii\web\View */
/* @var $model \app\modules\advert\models\Category */

$this->title = Yii::t('backend', 'Update {modelClass}', [
    'modelClass' => Yii::t('backend/advert', 'Category'),
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Advert categories list'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-update">

    <?=$this->render('_form', [
        'model' => $model,
    ])?>

</div>
