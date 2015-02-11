<?php
namespace backend;

use Yii;
use handbook\models\Handbook;

/**
 * Модуль для бекенда
 */
class Module extends \app\components\Module
{
    /**
     * Генерация левого меню для бекенда
     * @return []
     */
    public function getMenu()
    {
        $user = Yii::$app->user;

        // вывод всех справочников
        $handbookMenu = [];
        $res = Handbook::find()->all();
        foreach ($res as $i) {
            $handbookMenu[] = [
                'label' => $i->name,
                'icon' => '',
                'url' => ['/handbook/handbook-value/index', 'code' => $i->code],
                'visible' => $user->can('backendViewHandbookValues'),
                'active' => Yii::$app->controller->id == 'handbook-value',
            ];
        }

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
                        'url' => ['/user/user/index'],
                        'visible' => $user->can('backendViewUser'),
                        'active' => Yii::$app->controller->id == 'user',
                    ],
                    [
                        'label' => Yii::t('backend', 'Roles list'),
                        'icon' => '',
                        'url' => ['/user/role/index'],
                        'visible' => $user->can('backendRoleAdmin'),
                        'active' => Yii::$app->controller->id == 'role',
                    ]
                ]
            ],
            [
                'label' => Yii::t('backend', 'Advert'),
                'icon' => '',
                'url' => ['/advert/category/index'],
                'visible' => $user->can('backendViewAdvertCategory') || $user->can('backendViewAdvert'),
                'options'=>['class'=>'treeview'],
                'active' => !empty(Yii::$app->controller->module) && Yii::$app->controller->module->id == 'advert',
                'items' => [
                    [
                        'label' => Yii::t('backend', 'Advert categories'),
                        'icon' => '',
                        'url' => ['/advert/category/index'],
                        'visible' => $user->can('backendViewAdvertCategory'),
                        'options'=>[],
                        'active' => !empty(Yii::$app->controller->module) && Yii::$app->controller->module->id == 'advert' && Yii::$app->controller->id == 'category',
                    ],
                    [
                        'label' => Yii::t('backend', 'Adverts list'),
                        'icon' => '',
                        'url' => ['/advert/advert/index'],
                        'visible' => $user->can('backendViewAdvert'),
                        'options'=>[],
                        'active' => !empty(Yii::$app->controller->module) && Yii::$app->controller->module->id == 'advert' && Yii::$app->controller->id == 'advert',
                    ],
                ]
            ],
            [
                'label' => Yii::t('backend', 'Handbook'),
                'icon' => '',
                'visible' => $user->can('backendViewHandbookValues'),
                'options'=>['class'=>'treeview'],
                'active' => !empty(Yii::$app->controller->module) && Yii::$app->controller->module->id == 'handbook',
                'items' => $handbookMenu,
            ],
            [
                'label' => Yii::t('backend', 'Automobiles'),
                'icon' => '',
                'url' => ['/auto/auto/mark'],
                'visible' => $user->can('backendViewAuto'),
                'options'=>[],
                'active' => !empty(Yii::$app->controller->module) && Yii::$app->controller->module->id == 'auto',
            ],
            [
                'label' => Yii::t('backend', 'File storage'),
                'icon' => '',
                'url' => ['/storage/storage/index'],
                'visible' => $user->can('backendViewFile'),
                'options'=>[],
                'active' => !empty(Yii::$app->controller->module) && Yii::$app->controller->module->id == 'storage',
            ],
        ];
    }
}