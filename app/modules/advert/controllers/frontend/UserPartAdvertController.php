<?php
namespace advert\controllers\frontend;

use advert\components\PartsApi;
use advert\forms\PartForm;
use advert\models\PartAdvert;
use advert\models\PartAdvertImage;
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

class UserPartAdvertController extends Controller
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
            'query' => PartAdvert::findUserList(),
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
        $advert = PartAdvert::findUserList()->andWhere(['id' => $id])->one();
        if (!($advert instanceof PartAdvert)) {
            throw new NotFoundHttpException();
        }

        /* @var $advertApi PartsApi */
        $advertApi = Yii::$app->getModule('advert')->parts;

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
        $advert = PartAdvert::findUserList()->andWhere(['id' => $id])->one();
        if (!($advert instanceof PartAdvert)) {
            throw new NotFoundHttpException();
        }

        /* @var $advertApi PartsApi */
        $advertApi = Yii::$app->getModule('advert')->parts;

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

        $model = PartForm::createNewForUser($user);

        if (Yii::$app->request->isAjax && !empty($_POST['ajax']) && $model->load($_POST)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load($_POST) &&
                $model->loadImages(UploadedFile::getInstances($model, 'uploadImage')) &&
                $model->validate()) {
            $model->setScenario('submit');

            /* @var $advertApi PartsApi */
            $advertApi = Yii::$app->getModule('advert')->parts;
            if ($advert = $advertApi->appendRegisterAdvert($model)) {
                $link = Url::toRoute(['/advert/part-catalog/details', 'id' => $advert->id], true);
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
        /* @var $advert PartAdvert */
        $advert = PartAdvert::findUserList()->andWhere(['id' => $id])->one();
        if (!($advert instanceof PartAdvert)) {
            throw new NotFoundHttpException();
        }

        // получить изображение
        /* @var $image PartAdvertImage */
        $image = $advert->getImages()->andWhere(['id' => $imageId])->one();
        if (!($image instanceof PartAdvertImage)) {
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

        $advert = PartAdvert::findUserList()->andWhere(['id' => $id])->one();
        if (!($advert instanceof PartAdvert)) {
            throw new NotFoundHttpException();
        }

        $model = PartForm::createFromExists($advert);

        if (Yii::$app->request->isAjax && !empty($_POST['ajax']) && $model->load($_POST)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load($_POST) &&
                $model->loadImages(UploadedFile::getInstances($model, 'uploadImage')) &&
                $model->validate()) {
            $model->setScenario('submit');

            /* @var $advertApi PartsApi */
            $advertApi = Yii::$app->getModule('advert')->parts;
            if ($advertApi->updateAdvert($model)) {
                $link = Url::toRoute(['/advert/part-catalog/details', 'id' => $advert->id], true);
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