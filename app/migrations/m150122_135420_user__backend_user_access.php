<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Права доступа к модулю пользователей
 */
class m150122_135420_user__backend_user_access extends Migration
{
    private $permissions = [
        'backendViewUser' => 'View users',
        'backendUpdateUser' => 'Update users',
        'backendCreateUser' => 'Create users',
        'backendRoleAdmin' => 'View and change user roles',
    ];

    public function safeUp()
    {
        /* @var $authManager \yii\rbac\DbManager */
        $authManager = Yii::$app->authManager;

        /* @var $backendRole \yii\rbac\Item */
        $adminRole = $authManager->getRole('admin');

        foreach ($this->permissions as $permission => $description) {
            $child = $authManager->createPermission($permission);
            $child->description = $description;
            $authManager->add($child);
            $authManager->addChild($adminRole, $child);
        }
    }

    public function safeDown()
    {
        /* @var $authManager \yii\rbac\DbManager */
        $authManager = Yii::$app->authManager;

        foreach ($this->permissions as $permission => $name) {
            $child = $authManager->getPermission($permission);
            $authManager->remove($child);
        }
    }
}
