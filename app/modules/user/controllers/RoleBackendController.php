<?php
namespace user\controllers;

use Yii;
use yii\data\ArrayDataProvider;
use yii\web\Response;
use yii\widgets\ActiveForm;
use user\forms\Role as RoleForm;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use Exception;

/**
 * Управление правами
 */
class RoleBackendController extends \backend\components\BaseBackendController
{
    public function behaviors()
    {
        return \yii\helpers\ArrayHelper::merge([
            'access' => [
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['backendRoleAdmin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ], parent::behaviors());
    }

    /**
     * Редактирование роли
     */
    public function actionUpdate($id)
    {
        /* @var $authManager \yii\rbac\DbManager */
        $authManager = Yii::$app->authManager;

        $role = $this->findRole($id);

        $permissions = $authManager->getPermissions();
        $childrens = $authManager->getChildren($id);

        $form = RoleForm::createFromRole($role, $childrens);

        if (Yii::$app->request->isAjax && $form->load($_POST)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($form);
        }

        if ($form->load($_POST) && $form->validate()) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $role->description = $form->description;
                $authManager->update($role->name, $role);
                $authManager->removeChildren($role);
                foreach ($form->permissions as $child) {
                    $permission = isset($permissions[$child]) ? $permissions[$child] : null;
                    if ($permission) {
                        $authManager->addChild($role, $permission);
                    }
                }

                Yii::$app->serviceMessage->setMessage('success', Yii::t('backend/user', 'Success update role'));

                $transaction->commit();

                return $this->redirect(['index']);
            }
            catch (Exception $e) {
                $transaction->rollback();

                Yii::$app->serviceMessage->setMessage('danger', Yii::t('backend/user', 'Error update role'));
            }
        }

        return $this->render('update', [
            'form' => $form,
            'role' => $role,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Создать роль
     */
    public function actionCreate()
    {
        /* @var $authManager \yii\rbac\DbManager */
        $authManager = Yii::$app->authManager;

        $form = new RoleForm();
        $form->setScenario('create');

        $permissions = $authManager->getPermissions();

        if (Yii::$app->request->isAjax && $form->load($_POST)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($form);
        }

        if ($form->load($_POST) && $form->validate()) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $role = $authManager->createRole($form->getName());
                $role->description = $form->description;

                if (!$authManager->add($role)) {
                    throw new Exception();
                }

                foreach ($form->permissions as $child) {
                    $permission = isset($permissions[$child]) ? $permissions[$child] : null;
                    if (!empty($permission)) {
                        $authManager->addChild($role, $permission);
                    }
                }

                Yii::$app->serviceMessage->setMessage('success', Yii::t('backend/user', 'Success create role'));

                $transaction->commit();

                return $this->redirect(['index']);
            }
            catch (Exception $ex) {
                $transaction->rollback();

                Yii::$app->serviceMessage->setMessage('danger', Yii::t('backend/user', 'Error creating a role'));
            }
        }

        return $this->render('create', [
            'form' => $form,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Удаление роли
     */
    public function actionDelete($id)
    {
        /* @var $authManager \yii\rbac\DbManager */
        $authManager = Yii::$app->authManager;

        $role = $this->findRole($id);

        try {
            $authManager->remove($role);

            return $this->redirect(['index']);
        } catch (Exception $ex) {
            throw new ServerErrorHttpException();
        }
    }

    /**
     * Список ролей
     */
    public function actionIndex()
    {
        /* @var $authManager \yii\rbac\DbManager */
        $authManager = Yii::$app->authManager;

        $provider = new ArrayDataProvider([
            'allModels' => $authManager->getRoles(),
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $provider,
            'authManager' => $authManager,
        ]);
    }

    /**
     * Возвращает искомую роль
     * @param string $id
     * @return \yii\rbac\Role
     * @throws NotFoundHttpException
     */
    protected function findRole($id)
    {
        /* @var $authManager \yii\rbac\DbManager */
        $authManager = Yii::$app->authManager;

        $role = $authManager->getRole($id);

        if (empty($role)) {
            throw new NotFoundHttpException();
        }

        return $role;
    }
}