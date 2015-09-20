<?php
namespace user\models;

use Exception;
use partner\models\Partner;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\rbac\DbManager;
use yii\rbac\Role;
use yii\web\IdentityInterface;

/**
 * Модель пользователя
 */
class User extends ActiveRecord implements IdentityInterface
{
    /**
     * Тип пользователя - частное лицо
     */
    const TYPE_PRIVATE_PERSON = 1;

    /**
     * Тип пользователя - юридическое лицо
     */
    const TYPE_LEGAL_PERSON = 2;

    /**
     * @var string[] новые роли для установки
     */
    protected $_roles = [];

    /**
     * @var Role[] роли пользователя
     */
    protected $_oldRoles;

    /**
     * @var string новый пароль
     */
    public $new_password;

    const STATUS_ACTIVE = 1; // статус - активен
    const STATUS_WAIT_CONFIRMATION = 2; // статус - требует активации
    const STATUS_LOCKED = 3; // статус - заблокирован

    public static function tableName()
    {
        return '{{%user}}';
    }

    public function rules()
    {
        return [
            [['name', 'email'], 'required'],
            [['email'], 'email'],
            [['email'], 'unique'],
            [['status'], 'integer', 'min' => 0],
            [['last_login', 'roleCodes'], 'safe'],
            [['new_password'], 'required', 'on' => 'create'],
            [['new_password'], 'safe'],

            ['name', 'string', 'max' => 50],
            ['email', 'string', 'max' => 100],
            ['type', 'in', 'range' => array_keys(self::getTypesList())],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => Yii::t('user', 'Name'),
            'email' => Yii::t('user', 'Email'),
            'password' => Yii::t('user', 'Password'),
            'status' => Yii::t('user', 'Status'),
            'last_login' => Yii::t('user', 'Last login'),
            'roleCodes' => Yii::t('user', 'User roles'),
            'new_password' => Yii::t('user', 'New password'),
            'type' => Yii::t('user', 'Type'),
        ];
    }

    /**
     * Перед сохранением
     */
    public function beforeSave($insert)
    {
        if ($this->new_password) {
            $this->password = Yii::$app->getModule('user')->getPasswordHash($this->new_password);
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return self::find()->where(['id' => $id])->one();
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    /**
     * Finds user by username
     *
     * @param  string      $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return self::find()->where(['email' => $username])->one();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return md5(serialize([
            'id' => $this->id,
            'email' => $this->email,
            'password' => $this->password,
        ]));
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() == $authKey;
    }

    /**
     * Validates password
     *
     * @param  string  $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return \Yii::$app->security->validatePassword($password, $this->password);
    }

    /**
     * Returns username
     * @return string
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * Returns full username
     * @return string
     */
    public function getFullUserName()
    {
        return $this->name;
    }

    /**
     * Returns user roles as string array
     * @return array
     */
    public function getRolesStr()
    {
        $ret = [];

        $roles = $this->getRoles();

        foreach ($roles as $role) {
            $ret[] = $role->description;
        }

        return $ret;
    }

    /**
     * Returns user roles as object array
     * @return Role[]
     */
    public function getRoles()
    {
        $ret = !empty($this->_oldRoles) ? $this->_oldRoles : [];

        if (empty($this->_oldRoles) && !$this->isNewRecord) {
            /* @var $authManager DbManager */
            $authManager = Yii::$app->authManager;
            /* @var $db Connection */
            $db = Yii::$app->db;

            $sql = "SELECT item_name "
                . "FROM " . $authManager->assignmentTable . " "
                . "WHERE user_id = '" . $this->id . "'";

            $assignments = $db->createCommand($sql)
                ->queryAll();

            foreach ($assignments as $assignment) {
                $role = $authManager->getRole($assignment['item_name']);
                if ($role) {
                    $ret[] = $role;
                }
            }

            $this->_oldRoles = $ret;
        }

        return $ret;
    }

    /**
     * Returns user roles as code array
     * @return Role[]
     */
    public function getRoleCodes()
    {
        $ret = [];

        $roles = $this->getRoles();

        foreach ($roles as $role) {
            $ret[] = $role->name;
        }

        return $ret;
    }

    /**
     * Установить новые роли из формы
     */
    public function setRoleCodes($roles)
    {
        $roles = is_array($roles) ? $roles : [];

        $oldRoles = $this->getRoleCodes();

        $this->_roles = $roles;
    }

    /**
     * Обновить роли, если требуется
     */
    public function updateRoles()
    {
        $ret = true;

        if (is_array($this->_roles)) {
            /* @var $db Connection */
            $db = Yii::$app->db;
            /* @var $authManager DbManager */
            $authManager = Yii::$app->authManager;

            $transaction = $db->beginTransaction();

            try {
                $db->createCommand()
                    ->delete($authManager->assignmentTable, "user_id='" . $this->id . "'")
                    ->execute();

                foreach ($this->_roles as $role) {
                    if (!$db->createCommand()
                        ->insert($authManager->assignmentTable, [
                            'item_name' => $role,
                            'user_id' => $this->id,
                            'created_at' => time()
                        ])
                        ->execute()) {
                        throw new Exception();
                    }
                }

                $transaction->commit();
            }
            catch (Exception $e) {
                $transaction->rollBack();
                $ret = false;
            }
        }

        return $ret;
    }

    /**
     * Получить социальные сети пользователя
     *
     * @return ActiveQuery
     */
    public function getSocialAccounts()
    {
        return $this->hasMany(SocialAccount::className(), ['user_id' => 'id']);
    }

    /**
     * Вернуть запись из таблички подтверждений.
     * В случае, если ее нет - создает новую.
     *
     * @return UserConfirmation
     */
    public function getConfirmation()
    {
        $res = $this->hasOne(UserConfirmation::className(), ['user_id' => 'id']);
        if (!$res->exists()) {
            $confirmation = new UserConfirmation();
            $confirmation->setAttribute('user_id', $this->id);
            if ($confirmation->save()) {
                return $confirmation;
            }
        }
        return $res->one();
    }

    /**
     * Получить привязку к партнеру
     * @return ActiveQuery
     */
    public function getPartner()
    {
        return $this->hasOne(Partner::className(), ['user_id' => 'id']);
    }

    /**
     * Получить типы пользователей
     * @return array
     */
    public static function getTypesList()
    {
        return [
            self::TYPE_PRIVATE_PERSON => Yii::t('user', 'Private person'),
            self::TYPE_LEGAL_PERSON => Yii::t('user', 'Legal person'),
        ];
    }

    /**
     * Получить описание типа регистрации
     * @return string
     */
    public function getTypeDescription()
    {
        $types = self::getTypesList();
        return isset($types[$this->type]) ? $types[$this->type] : null;
    }

    /**
     * Требует активации пользователя
     *
     * @return boolean
     */
    public function needConfirmation()
    {
        return $this->status == User::STATUS_WAIT_CONFIRMATION;
    }

    /**
     * Пользователь может авторизовываться
     *
     * @return boolean
     */
    public function canLogin()
    {
        return $this->status == User::STATUS_ACTIVE;
    }
}
