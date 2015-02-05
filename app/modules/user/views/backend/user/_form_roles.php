<?php
/* @var $this yii\web\View */
/* @var $model user\models\User */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $authManager \yii\rbac\DbManager */
$authManager = Yii::$app->authManager;

$allRoles = $authManager->getRoles();

$items = [];

foreach ($allRoles as $role) {
    $items[$role->name] = $role->description;
}

print $form->field($model, 'roleCodes')->checkboxList($items);