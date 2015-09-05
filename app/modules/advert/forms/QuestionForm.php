<?php
namespace advert\forms;

use advert\models\AdvertQuestion;
use advert\models\Advert;
use user\models\User;
use Yii;
use yii\base\Model;

/**
 * Форма вопроса по объявлению.
 * Если передать идентификатор пользователя, то необязательно указывать
 * e-mail и имя отправителя.
 */
class QuestionForm extends Model
{
    /**
     * @var string имя отправителя
     */
    public $user_name;

    /**
     * @var string e-mail отправителя
     */
    public $user_email;

    /**
     * @var string вопрос
     */
    public $question;

    /**
     * Код, который для неавторизованного пользователя должен быть равен csrfToken.
     * Можно устанавливать с помощью JavaScript, чтобы не заставлять пользователя вводить captcha вручную.
     * @var string
     */
    public $captcha;

    /**
     * @var Advert модель объявления
     */
    protected $_advert;

    /**
     * @var integer идентификатор отправителя
     */
    protected $_user_id;

    /**
     * Установить идентификатор отправителя. Если отправитель существует - устанавливает
     * также e-mail и имя.
     *
     * @param int $val
     */
    public function setUser_id($val)
    {
        $val = (int) $val;

        if ($val) {
            // получить пользователя
            $user = User::find()->andWhere(['id' => $val])->one();
            if ($user instanceof User) {
                $this->_user_id = $user->id;
                $this->user_email = $user->email;
                $this->user_name = $user->name;
            }
        }
        else {
            $this->_user_id = null;
        }
    }

    /**
     * Получить идентификатор отправителя
     *
     * @return integer
     */
    public function getUser_id()
    {
        return $this->_user_id;
    }

    /**
     * Установить объявление
     * @param Advert $val
     */
    public function setAdvert($val)
    {
        if ($val instanceof Advert && $val->allowQuestions()) {
            $this->_advert = $val;
        }
        else {
            $this->_advert = null;
        }
    }

    /**
     * Получить объявление
     *
     * @return Advert|null
     */
    public function getAdvert()
    {
        return $this->_advert;
    }

    /**
     * Правила валидации
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['question', 'required'],
            [['user_name', 'user_email', 'captcha'], 'required', 'when' => function($form) {
                /* @var $form QuestionForm */
                return !$form->getUser_id();
            }],
            ['captcha', function($attribute, $params = array()) {
                /* @var $request \yii\web\Request */
                $request = \Yii::$app->request;
                $value = $this->{$attribute};
                if ($value != $request->post($request->csrfParam)) {
                    $this->addError($attribute, 'Error');
                }
            }],
            ['user_name', 'string', 'max' => AdvertQuestion::MAX_NAME_LENGTH],
            ['user_email', 'string', 'max' => AdvertQuestion::MAX_EMAIL_LENGTH],
            ['user_email', 'email', 'skipOnEmpty' => true],
            ['question', 'string'],
        ];
    }

    /**
     * Подписи атрибутов
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'user_name' => Yii::t('frontend/advert', 'Your name'),
            'user_email' => Yii::t('frontend/advert', 'Your e-mail'),
            'question' => Yii::t('frontend/advert', 'Your question'),
        ];
    }
}