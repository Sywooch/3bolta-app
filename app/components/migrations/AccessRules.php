<?php

namespace app\components\migrations;

use yii\db\Migration;

/**
 * Миграция для управления правами доступа.
 */
abstract class AccessRules extends Migration
{
    /**
     * Возвращает права доступа для установки
     * @return []
     */
    protected abstract function getPermissions();

    public function safeUp()
    {
        /* @var $authManager \yii\rbac\DbManager */
        $authManager = \Yii::$app->authManager;

        /* @var $backendRole \yii\rbac\Item */
        $adminRole = $authManager->getRole('admin');

        foreach ($this->getPermissions() as $permission => $description) {
            $child = $authManager->createPermission($permission);
            $child->description = $description;
            $authManager->add($child);
            $authManager->addChild($adminRole, $child);
        }
    }

    public function safeDown()
    {
        /* @var $authManager \yii\rbac\DbManager */
        $authManager = \Yii::$app->authManager;

        foreach ($this->getPermissions() as $permission => $name) {
            $child = $authManager->getPermission($permission);
            $authManager->remove($child);
        }
    }
}
