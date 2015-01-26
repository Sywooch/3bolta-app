<?php
/* @var $this yii\web\View */
/* @var $model \app\modules\user\models\User */

$this->title = Yii::t('backend', 'Create {modelClass}', [
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
