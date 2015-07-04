<?php
namespace advert\controllers\frontend;

use advert\components\AdvertApi;
use advert\forms\Form;
use advert\models\Advert;
use advert\models\AdvertImage;
use app\components\Controller;
use user\models\User;
use Yii;
use yii\base\Action;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;

class UserAdvertController extends Controller
{
    /**
     * Фильтры
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'roles' => ['@'],
                        'allow' => true,
                    ]
                ],
                'denyCallback' => function($rule, $action) {
                    if ($action instanceof Action) {
                        /* @var $action Action */
                        return $action->controller->goHome();
                    }
                }
            ]
        ]);
    }

    /**
     * Вывести список объявлений пользователя
     */
    public function actionList()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Advert::findUserList(),
        ]);

        return $this->render('list', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Остановить публикацию объявления
     *
     * @param int $id
     * @throw NotFoundHttpException
     */
    public function actionStopPublication($id)
    {
        $advert = Advert::findUserList()->andWhere(['id' => $id])->one();
        if (!($advert instanceof Advert)) {
            throw new NotFoundHttpException();
        }

        /* @var $advertApi AdvertApi */
        $advertApi = Yii::$app->getModule('advert')->advert;

        $advertApi->stopPublication($advert);

        return $this->redirect(['list']);
    }

    /**
     * Обновить публикацию объявления пользователя до ближайшей доступной даты
     *
     * @param int $id
     * @throws NotFoundHttpException
     */
    public function actionUpdatePublication($id)
    {
        $advert = Advert::findUserList()->andWhere(['id' => $id])->one();
        if (!($advert instanceof Advert)) {
            throw new NotFoundHttpException();
        }

        /* @var $advertApi AdvertApi */
        $advertApi = Yii::$app->getModule('advert')->advert;

        $advertApi->updatePublication($advert);

        return $this->redirect(['list']);
    }

    /**
     * Добавить объявление
     */
    public function actionAppend()
    {
        /* @var $user User */
        $user = Yii::$app->user->getIdentity();

        $model = Form::createNewForUser($user);

        if (Yii::$app->request->isAjax && !empty($_POST['ajax']) && $model->load($_POST)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load($_POST) &&
                $model->loadImages(UploadedFile::getInstances($model, 'uploadImage')) &&
                $model->validate()) {
            $model->setScenario('submit');

            /* @var $advertApi AdvertApi */
            $advertApi = Yii::$app->getModule('advert')->advert;
            if ($advert = $advertApi->appendRegisterAdvert($model)) {
                $link = Url::toRoute(['/advert/catalog/details', 'id' => $advert->id], true);
                Yii::$app->serviceMessage->setMessage(
                    'success',
                    'Объявление успешно создано.<br />'
                    . 'Вы можете просмотреть его по адресу <a href="' . $link . '">' . $link . '</a>',
                    Yii::t('frontend/advert', 'Append advert')
                );
                return $this->redirect(['list']);
            }
            else {
                Yii::$app->serviceMessage->setMessage(
                    'danger',
                    'При создании объявления произошла ошибка<br />'
                    . 'Пожалуйста, обратитесь в службу поддержки',
                    Yii::t('frontend/advert', 'Append advert')
                );
            }
        }

        return $this->render('append', [
            'model' => $model,
            'user' => $user,
        ]);
    }

    /**
     * Удаление изображения с идентификатором = $_POST['key'] для объявления с идентификатором = $_GET['id'].
     * Производится проверка, что объявление действительно принадлежит пользователю.
     */
    public function actionRemoveAdvertImage($id)
    {
        if (!Yii::$app->request->isAjax) {
            throw new ForbiddenHttpException();
        }

        // получить объявление и идентификатор изображение
        $id = (int) $id;
        $imageId = (int) Yii::$app->request->post('key');
        // проверить, что объявление принадлежит авторизованному пользователю
        /* @var $advert Advert */
        $advert = Advert::findUserList()->andWhere(['id' => $id])->one();
        if (!($advert instanceof Advert)) {
            throw new NotFoundHttpException();
        }

        // получить изображение
        /* @var $image AdvertImage */
        $image = $advert->getImages()->andWhere(['id' => $imageId])->one();
        if (!($image instanceof AdvertImage)) {
            throw new NotFoundHttpException();
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $result = [];
        try {
            $image->delete();
            $advert->updateDefaultImage();
        }
        catch (Exception $ex) {
            $result['error'] = Yii::t('frontend/advert', 'System error.');
        }
        return $result;
    }

    /**
     * Редактировать объявление
     *
     * @param int $id
     * @throws NotFoundHttpException
     */
    public function actionEdit($id)
    {
        /* @var $user User */
        $user = Yii::$app->user->getIdentity();

        $advert = Advert::findUserList()->andWhere(['id' => $id])->one();
        if (!($advert instanceof Advert)) {
            throw new NotFoundHttpException();
        }

        $model = Form::createFromExists($advert);

        if (Yii::$app->request->isAjax && !empty($_POST['ajax']) && $model->load($_POST)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load($_POST) &&
                $model->loadImages(UploadedFile::getInstances($model, 'uploadImage')) &&
                $model->validate()) {
            $model->setScenario('submit');

            /* @var $advertApi AdvertApi */
            $advertApi = Yii::$app->getModule('advert')->advert;
            if ($advertApi->updateAdvert($model)) {
                $link = Url::toRoute(['/advert/catalog/details', 'id' => $advert->id], true);
                Yii::$app->serviceMessage->setMessage(
                    'success',
                    'Объявление успешно отредактировано.<br />'
                    . 'Вы можете просмотреть его по адресу <a href="' . $link . '">' . $link . '</a>',
                    Yii::t('frontend/advert', 'Edit advert')
                );
                return $this->redirect(['list']);
            }
            else {
                Yii::$app->serviceMessage->setMessage(
                    'danger',
                    'При редактировании объявления произошла ошибка<br />'
                    . 'Пожалуйста, обратитесь в службу поддержки',
                    Yii::t('frontend/advert', 'Edit advert')
                );
            }
        }

        return $this->render('edit', [
            'model' => $model,
            'user' => $user,
        ]);
    }
}