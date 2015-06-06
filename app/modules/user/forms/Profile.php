<?php
namespace user\forms;

use Yii;

use user\models\User;
use app\components\PhoneValidator;
use user\forms\Register;

/**
 * Профиль пользователя
 */
class Profile extends \yii\base\Model
{
    public $email;
    public $name;
    public $phone;
    public $phone_canonical;

    /**
     * Правила валидации
     * @return []
     */
    public function rules()
    {
        return [
            [['email', 'name', 'phone'], 'required'],
            ['email', 'filter', 'filter' => 'strtolower'],
            ['email', 'email'],
            ['email', 'unique',
                'targetClass' => User::className(),
                'targetAttribute' => 'email',
                'filter' => function($query) {
                    /* @var $query \yii\db\ActiveQuery */
                    $query->andWhere(['<>', 'id', Yii::$app->user->getIdentity()->id]);
                },
                'message' => Yii::t('frontend/user', 'This e-mail already exists'),
            ],
            [['phone'],
                PhoneValidator::className(),
                'canonicalAttribute' => 'phone_canonical',
                'targetClass' => User::className(),
                'targetAttribute' => 'phone_canonical',
                'filter' => function($query) {
                    /* @var $query \yii\db\ActiveQuery */
                    $query->andWhere(['<>', 'id', Yii::$app->user->getIdentity()->id]);
                },
                'message' => Yii::t('frontend/user', 'This phone already exists'),
            ],
            ['email', 'string', 'max' => Register::MAX_EMAIL_LENGTH],
            ['name', 'string', 'max' => Register::MAX_NAME_LENGTH],
        ];
    }

    /**
     * Подписи атрибутов
     * @return []
     */
    public function attributeLabels()
    {
        return [
            'email' => Yii::t('frontend/user', 'New e-mail'),
            'name' => Yii::t('frontend/user', 'Name'),
            'phone' => Yii::t('frontend/user', 'Phone'),
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
            'phone' => $user->phone,
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