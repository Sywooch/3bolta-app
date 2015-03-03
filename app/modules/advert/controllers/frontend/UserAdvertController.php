<?php
namespace advert\controllers\frontend;

use Yii;

use yii\web\NotFoundHttpException;

use yii\filters\AccessControl;
use advert\models\Advert;
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
}