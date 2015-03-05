<?php
namespace app\commands;

use Yii;

use yii\console\Controller;
use user\models\User;

/**
 * Работа с пользователями: создать
 */
class UsersController extends Controller
{
    /**
     * @var string e-mail нового пользователя
     */
    public $email;

    /**
     * @var string имя нового пользователя
     */
    public $name;

    /**
     * @var string телефон в формате +7 (xxx) xxx-xx-xx
     */
    public $phone;

    /**
     * @var string пароль нового пользователя
     */
    public $password;

    public function options($actionID)
    {
        $options = [
            'admin-create' => [
                'password', 'email', 'name', 'phone',
            ],
        ];

        return isset($options[$actionID]) ? $options[$actionID] : [];
    }

    /**
     * Создать пользователя
     */
    public function actionAdminCreate()
    {
        $module = Yii::$app->getModule('user');

        $user = new User();

        $user->setAttributes([
            'email' => $this->email,
            'name' => $this->name,
            'phone' => $this->phone,
            'new_password' => $this->password,
            'status' => User::STATUS_ACTIVE
        ]);

        if (!$user->validate()) {
            $this->stderr("Model has errors: \n");
            $this->stderr(print_r($user->getErrors(), true));
            return 1;
        }

        if ($user->save()) {
            // добавить пользователя в группу admin
            /* @var $auth \yii\rbac\AuthManager */
            $auth = Yii::$app->authManager;
            $role = $auth->getRole('admin');
            $auth->assign($role, $user->id);

            $this->stdout("Complete. Create user model: " . $user->id . "\n");
        }
        else {
            $this->stderr("Error creating user\n");
        }
    }
}
