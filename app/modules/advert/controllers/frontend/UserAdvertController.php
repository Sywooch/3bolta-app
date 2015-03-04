<?php
namespace advert\controllers\frontend;

use Yii;

use yii\web\NotFoundHttpException;

use yii\web\UploadedFile;

use yii\web\Response;
use yii\widgets\ActiveForm;

use yii\filters\AccessControl;
use advert\models\Advert;
use advert\forms\Form;
use yii\helpers\Url;

use yii\data\ActiveDataProvider;

class UserAdvertController extends \app\components\Controller
{
    /**
     * Фильтры
     * @return []
     */
    public function behaviors()
    {
        return \yii\helpers\ArrayHelper::merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'roles' => ['@'],
                        'allow' => true,
                    ]
                ],
                'denyCallback' => function($rule, $action) {
                    if ($action instanceof \yii\base\Action) {
                        /* @var $action \yii\base\Action */
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

        /* @var $advertApi \advert\components\AdvertApi */
        $advertApi = Yii::$app->getModule('advert')->advert;

        $advertApi->updatePublication($advert);

        return $this->redirect(['list']);
    }

    /**
     * Редактировать объявление
     *
     * @param int $id
     * @throws NotFoundHttpException
     */
    public function actionEdit($id)
    {
        /* @var $user \user\models\User */
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

        if ($model->load($_POST)) {
            $model->setScenario('submit');
            if ($files = UploadedFile::getInstances($model, 'uploadImage')) {
                $model->setUploadImage($files);
            }

            /* @var $advertApi \advert\components\AdvertApi */
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