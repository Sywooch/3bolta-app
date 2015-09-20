<?php
namespace user\controllers\frontend;

use app\components\Controller;
use user\components\UserApi;
use user\components\ExternalUser;
use user\exception\UserApiException;
use user\forms\Login;
use user\models\User;
use Yii;
use yii\authclient\clients\Facebook;
use yii\authclient\clients\GoogleOAuth;
use yii\authclient\clients\Twitter;
use yii\authclient\Collection;
use yii\authclient\OAuth2;
use \Exception;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;
use yii\widgets\ActiveForm;
use user\api\RegistrationApi;
use user\exception\RegistrationApiException;

/**
 * Авторизация пользователей через соц. сеть
 */
class ExternalAuthController extends Controller
{
    /**
     * @var UserApi
     */
    protected $userApi;

    public function init()
    {
        parent::init();
        $this->userApi = Yii::$app->getModule('user')->api;
    }

    /**
     * Авторизация через соц. сеть. Перенаправляет пользователя в социальную сеть.
     *
     * @param string $key google, facebook, vkontakte etc.
     */
    public function actionRedirect($key)
    {
        // если были установлены данные из соц. сети - удаляем их
        ExternalUser::removeAttributesFromSession();

        /* @var $oauthCollection Collection */
        $oauthCollection = Yii::$app->socialAuthClientCollection;

        // получить OAuth клиента для авторизации
        /* @var $client OAuth2 */
        try {
            $client = $oauthCollection->getClient($key);

            if (!$client) {
                throw new InvalidParamException();
            }

            // установить обратный URL
            $client->setReturnUrl(Url::to(['response', 'key' => $key], true));

            // перенаправление пользователя в соц. сеть
            return $this->redirect($client->buildAuthUrl());
        }
        catch (InvalidParamException $ex) {
            // если клиент не найден
            throw new NotFoundHttpException();
        }
    }

        /**
     * Авторизация через соц. сеть. Принимает авторизацию из социальной сети.
     *
     * @param string $key google, facebook, twitter, etc
     */
    public function actionResponse($key)
    {
        /* @var $oauthCollection Collection */
        $oauthCollection = Yii::$app->socialAuthClientCollection;

        // по умолчанию - пустая модель
        $externalUser = new ExternalUser();

        // получить OAuth клиента для авторизации
        /* @var $client OAuth2 */
        try {
            $client = $oauthCollection->getClient($key);

            // код авторизации приложения
            $authCode = Yii::$app->request->get('code');
            // получить данные из соц.сети
            $externalUser = $this->getSocialUserFromClient($client, $authCode);
            // получить существующего пользователя
            /* @var $existsUser User */
            $existsUser = $externalUser->getInternalUser();

            if (!Yii::$app->user->isGuest) {
                // в данный момент пользователь уже авторизован на сайте
                // привязываем к нему новую соц. сеть и возвращаемся в профиль
                $this->userApi->attachUserServiceAccount(Yii::$app->user->identity, $externalUser);
                return $this->redirect(['/user/profile/index']);
            }

            // пользователя не существует, регистрируем
            if (!($existsUser instanceof User)) {
                $existsUser = $this->userApi->registerSocialUser($externalUser);
            }

            // требуется активация аккаунта, автоматически активируем, если есть e-mail
            if ($existsUser && $externalUser->email && $existsUser->needConfirmation()) {
                $existsUser = $this->userApi->trustUserConfirmation($existsUser);
            }

            if ($existsUser && $existsUser->canLogin()) {
                // прикрепить к пользователю аккаунт соц. сети
                $this->userApi->attachUserServiceAccount($existsUser, $externalUser);
                // авторизовать пользователя
                Yii::$app->user->login($existsUser);
            }

            // если были установлены данные из соц. сети - удаляем их
            ExternalUser::removeAttributesFromSession();
        }
        catch (InvalidParamException $ex) {
            // если клиент не найден
            throw new NotFoundHttpException();
        }
        catch (UserApiException $ex) {
            // ошибка регистрации, автризации или активации
            if ($ex->getCode() == UserApiException::REGISTRATION_ERROR) {
                // ошибка регистрации пользователя, скорее всего несвалидированы данные
                // перенаправляем пользователя на страницу регистрации с заполнением всех существующих полей
                $externalUser->setAttributesToSession();
                return $this->redirect(['/user/user/register']);
            }
        }
        catch (Exception $ex) {
            // любая другая ошибка
        }

        return $this->goHome();
    }

    /**
     * На основе подключения к соц. сети $client и кода авторизации $authCode приложения пытается сформировать модель ExternalUser.
     * В случае ошибки генерирует Exception
     * @param mixed $client facebook, vkontakte, google etc
     * @param string $authCode код авторизации приложения
     * @throws InvalidParamException
     * @throws Exception
     * @return ExternalUser
     */
    protected function getSocialUserFromClient($client, $authCode)
    {
        $externalUser = null;

        if ($client instanceof \yii\authclient\clients\VKontakte) {
            return $this->userApi->fetchVkontakteUserData($client, $authCode);
        }
        else if ($client instanceof Facebook) {
            return $this->userApi->fetchFacebookUserData($client, $authCode);
        }
        else if ($client instanceof GoogleOAuth) {
            return $this->userApi->fetchGoogleUserData($client, $authCode);
        }
        else if ($client instanceof \yii\authclient\clients\YandexOAuth) {
            return $this->userApi->fetchYandexUserData($client, $authCode);
        }

        throw new InvalidParamException();
    }
}