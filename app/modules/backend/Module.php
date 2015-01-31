<?php
namespace app\modules\backend;

use Yii;

/**
 * Модуль для бекенда
 */
class Module extends \yii\base\Module
{
    /**
     * Генерация левого меню для бекенда
     * @return []
     */
    public function getMenu()
    {
        $user = Yii::$app->user;

        return [
            [
                'label' => Yii::t('backend', 'Users'),
                'icon' => '',
                'visible' => $user->can('backendViewUser') || $user->can('backendRoleAdmin'),
                'options'=>['class'=>'treeview'],
                'active' => !empty(Yii::$app->controller->module) && Yii::$app->controller->module->id == 'user',
                'items' => [
                    [
                        'label' => Yii::t('backend', 'Users list'),
                        'icon' => '',
                        'url' => ['/user/user-backend/index'],
                        'visible' => $user->can('backendViewUser'),
                        'active' => Yii::$app->controller->id == 'user-backend',
                    ],
                    [
                        'label' => Yii::t('backend', 'Roles list'),
                        'icon' => '',
                        'url' => ['/user/role-backend/index'],
                        'visible' => $user->can('backendRoleAdmin'),
                        'active' => Yii::$app->controller->id == 'role-backend',
                    ]
                ]
            ],
            [
                'label' => Yii::t('backend', 'Advert categories'),
                'icon' => '',
                'url' => ['/advert/category-backend/index'],
                'visible' => $user->can('backendViewAdvertCategory'),
                'options'=>['class'=>'treeview'],
                'active' => !empty(Yii::$app->controller->module) && Yii::$app->controller->module->id == 'advert' && Yii::$app->controller->id == 'category-backend',
            ],
        ];
    }
}