<?php
namespace user\components;

use user\models\SocialAccount;
use user\models\User;
use Yii;
use yii\base\Exception;
use yii\base\Object;
use yii\helpers\Json;

/**
 * Модель пользователя в социальной сети.
 * Используется для абстракции от типа социальной сети.
 * Принимаются все необходимые параметры для формирования итоговой модели пользователя.
 */
class ExternalUser extends Object
{
    /**
     * @var string код социальной сети: facebook, google, vkontakte etc
     */
    protected $code;

    /**
     * @var string идентификатор пользователя внутри социальной сети
     */
    public $id;

    /**
     * @var string email пользователя
     */
    public $email;

    /**
     * @var string имя пользователя
     */
    public $name;

    /**
     * @var string имя пользователя в соц. сети
     */
    public $external_name;

    /**
     * @var string URL страницы пользователя в соц.сеть
     */
    public $external_page;

    /**
     * Установить код социальной сети
     *
     * @param string $code
     * @throws Exception генерирует Exception, сети с таким кодом нет
     */
    public function setCode($code)
    {
        switch ($code) {
            case 'facebook':
            case 'google':
            case 'vkontakte';
                $this->code = $code;
                break;
            default:
                throw new Exception('Unknown social');
        }
    }

    /**
     * Получить код соц. сети
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Установить данные социального пользователя в сесиию
     */
    public function setAttributesToSession()
    {
        Yii::$app->session->set('external_social_user', Json::encode([
            'code' => $this->code,
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'external_name' => $this->external_name,
            'external_page' => $this->external_page,
        ]));
    }

    /**
     * Воссоздает объект SocialUser на основе данных, установленных методом setAttributesToSession в сессию.
     * Если данных нет, или произошла ошибка парсинга json - возвращает null.
     *
     * @return SocialUser|null
     */
    public static function createFromSession()
    {
        $ret = null;

        try {
            $data = Yii::$app->session->get('external_social_user');
            if (trim($data) && $socialData = Json::decode($data)) {
                $ret = new ExternalUser($socialData);
            }
        }
        catch (Exception $ex) {
            $ret = null;
        }

        return $ret;
    }

    /**
     * Удаляет данные из сессии
     */
    public static function removeAttributesFromSession()
    {
        Yii::$app->session->remove('external_social_user');
    }

    /**
     * Получить модель пользователя, если пользователь, привязанный к этой соц сети существует,
     * либо если существует пользователь с таким e-mail.
     *
     * @return User|null
     */
    public function getInternalUser()
    {
        $ret = null;

        if ($this->code && $this->id) {
            // сначала ищем по идентификатору в соц. сети
            $ret = User::find()
                ->joinWith('socialAccounts')
                ->andWhere([
                    SocialAccount::tableName() . '.code' => $this->code,
                    SocialAccount::tableName() . '.external_uid' => $this->id
                ])
                ->groupBy(User::tableName() . '.id')
                ->one();
        }

        if (!($ret instanceof User) && !empty($this->email)) {
            // поиск по e-mailу
            $ret = User::find()->andWhere(['email' => (string) $this->email])->one();
        }

        return $ret;
    }
}