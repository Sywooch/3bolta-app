<?php
namespace user\forms;

use user\forms\Register;
use user\models\User;
use Yii;
use yii\base\Model;
use yii\db\ActiveQuery;

/**
 * Профиль пользователя
 */
class Profile extends Model
{
    public $email;
    public $name;

    /**
     * Правила валидации
     * @return array
     */
    public function rules()
    {
        return [
            [['email', 'name'], 'required'],
            ['email', 'filter', 'filter' => 'strtolower'],
            ['email', 'email'],
            ['email', 'unique',
                'targetClass' => User::className(),
                'targetAttribute' => 'email',
                'filter' => function($query) {
                    /* @var $query ActiveQuery */
                    $query->andWhere(['<>', 'id', Yii::$app->user->getIdentity()->id]);
                },
                'message' => Yii::t('frontend/user', 'This e-mail already exists'),
            ],
            ['email', 'string', 'max' => Register::MAX_EMAIL_LENGTH],
            ['name', 'string', 'max' => Register::MAX_NAME_LENGTH],
        ];
    }

    /**
     * Подписи атрибутов
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'email' => Yii::t('frontend/user', 'New e-mail'),
            'name' => Yii::t('frontend/user', 'Name'),
        ];
    }

    /**
     * Создать форму на основе модели пользователя
     *
     * @param User $user
     * @return \self
     */
    public static function createFromUser(User $user)
    {
        $model = new self();
        $model->setAttributes([
            'email' => $user->email,
            'name' => $user->name,
        ]);
        return $model;
    }

    /**
     * Маскированный e-mail
     *
     * @return string
     */
    public function getMaskedEmail()
    {
        $ret = $this->email;

        if (($parts = explode('@', $ret)) && !empty($parts[0]) && strlen($parts[0]) > 2) {
            $ret = $parts[0][0];
            $ret .= '***';
            $ret .= $parts[0][strlen($parts[0]) - 1];
            $ret .= '@' . $parts[1];
        }

        return $ret;
    }
}