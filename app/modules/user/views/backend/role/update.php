<?php
/* @var $this yii\web\View */
/* @var $role yii\rbac\Role */

$this->title = Yii::t('backend', 'Update {modelClass}', [
    'modelClass' => $this->context->getSubstanceName(),
]) . ' ' . $role->description;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Roles list'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-update">

    <?=$this->render('_form', [
        'model' => $form,
        'permissions' => $permissions,
    ])?>

</div>
