<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Создать роли для доступа к админке.
 * Перед миграцией необходимо выполнить команду:
 * ./yii migrate --migrationPath=@yii/rbac/migrations/
 */
class m150121_141253_user__backend_access_rules extends Migration
{
    public function safeUp()
    {
        /* @var $auth \yii\rbac\AuthManager */
        $auth = Yii::$app->authManager;

        $backendPermission = $auth->createPermission('backend');
        $backendPermission->description = 'Backend access';
        $auth->add($backendPermission);

        $adminRole = $auth->createRole('admin');
        $adminRole->description = 'Administrator';
        $auth->add($adminRole);
        $auth->addChild($adminRole, $backendPermission);
    }

    public function safeDown()
    {
        /* @var $auth \yii\rbac\AuthManager */
        $auth = Yii::$app->authManager;

        $adminRole = $auth->getRole('admin');
        $auth->remove($adminRole);

        $backendPermission = $auth->getPermission('backend');
        $auth->remove($backendPermission);
    }
}
