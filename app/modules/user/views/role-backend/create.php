<?php
/* @var $this yii\web\View */
/* @var $role yii\rbac\Role */

$this->title = Yii::t('backend', 'Create {modelClass}', [
    'modelClass' => Yii::t('backend/user', 'Role'),
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Roles list'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-update">

    <?=$this->render('_form', [
        'model' => $form,
        'permissions' => $permissions,
    ])?>

</div>
