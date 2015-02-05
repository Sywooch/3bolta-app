<?php
namespace user\forms;

use yii\base\Model;
use Yii;

/**
 * Форма редактирования роли
 */

class Role extends Model
{
    /**
     * @var string название роли
     */
    protected $_name;

    /**
     * @var boolean новая роль
     */
    protected $_isNewRole = true;

    /**
     * @var string описание роли
     */
    public $description;

    /**
     * @var [] права для роли
     */
    public $permissions = [];

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['name', 'description', 'permissions'], 'required'],
            [['name'], 'validateExistent', 'on' => 'create'],
            [['name'], 'filter', 'filter' => 'trim', 'on' => 'create'],
            [['name'], 'match', 'pattern' => '#^[a-z0-9]+$#i', 'on' => 'create'],
        ];
    }

    /**
     * Валидация поля name - оно должно быть уникальным.
     *
     * @param string $attribute
     * @param array $params
     */
    public function validateExistent($attribute, $params)
    {
        $name = trim($this->{$attribute});

        if (!empty($name)) {
            // получить роль, если она существует
            /* @var $authManager \yii\rbac\DbManager */
            $authManager = Yii::$app->authManager;

            $role = $authManager->getRole($name);

            if (!empty($role)) {
                $this->addError($attribute, Yii::t('backend/user', 'Role already exists'));
            }
        }
    }

    /**
     * @return array of attribute lables
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('backend/user', 'Name'),
            'description' => Yii::t('backend/user', 'Description'),
            'permissions' => Yii::t('backend/user', 'Permissions'),
        ];
    }

    /**
     * Создать модель из роли
     * @param \yii\rbac\Role $role
     * @param \yii\rbac\Permission[] $permissions
     * @return \user\forms\Role
     */
    public static function createFromRole(\yii\rbac\Role $role, $permissions)
    {
        $form = new self();
        $form->_name = $role->name;
        $form->_isNewRole = false;
        $form->description = $role->description;

        $form->permissions = [];

        foreach ($permissions as $permission) {
            $form->permissions[] = $permission->name;
        }

        return $form;
    }

    /**
     * Возвращает поле name
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Установить поле name только если работаем с новой записью
     * @param string $name
     */
    public function setName($name)
    {
        if ($this->_isNewRole) {
            $this->_name = $name;
        }
    }

    /**
     * Возвращает true, если работа ведется с новой ролью
     * @return boolean
     */
    public function getIsNewRecord()
    {
        return $this->_isNewRole === true;
    }
}