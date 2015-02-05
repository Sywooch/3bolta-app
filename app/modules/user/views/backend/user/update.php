<?php
/* @var $this yii\web\View */
/* @var $model \user\models\User */

$this->title = Yii::t('backend', 'Update {modelClass}', [
    'modelClass' => Yii::t('backend/user', 'User'),
]) . ' ' . $model->email;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Users list'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-update">

    <?=$this->render('_form', [
        'model' => $model,
    ])?>

</div>
