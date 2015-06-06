<?php
namespace partner\controllers\frontend;

use Yii;

use partner\models\Partner;
use partner\models\TradePoint;
use partner\forms\TradePoint as TradePointForm;

use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use partner\filters\CheckPartnerAccessRule;

use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\base\Exception;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Работа с торговыми точками пользователя
 */
class UserTradePointController extends \app\components\Controller
{
    /**
     * @var \partner\components\PartnersApi
     */
    protected $partnersApi;

    /**
     * @var \partner\models\Partner
     */
    protected $partner;

    public function init()
    {
        parent::init();
        $this->partnersApi = Yii::$app->getModule('partner')->api;
        $user = Yii::$app->user->getIdentity();
        if ($user instanceof \user\models\User) {
            $this->partner = $user->partner;
        }
    }

    /**
     * Фильтры
     * @return array
     */
    public function behaviors()
    {
        return \yii\helpers\ArrayHelper::merge([
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'class' => CheckPartnerAccessRule::className(),
                        'allow' => true,
                    ]
                ],
                'denyCallback' => function($rule, $action) {
                    if ($action instanceof \yii\base\Action) {
                        /* @var $action \yii\base\Action */
                        return $action->controller->goHome();
                    }
                }
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                ]
            ]
        ], parent::behaviors());
    }

    /**
     * Редактирование торговой точки
     */
    public function actionEdit($id)
    {
        if (!Yii::$app->request->isAjax) {
            throw new ForbiddenHttpException();
        }

        $tradePoint = TradePoint::findUserList()->andWhere(['id' => (int) $id])->one();
        if (!($tradePoint instanceof TradePoint)) {
            throw new NotFoundHttpException();
        }

        $form = TradePointForm::createFromExists($tradePoint);

        if (!empty($_POST['ajax'])) {
            // AJAX-валидация
            Yii::$app->response->format = Response::FORMAT_JSON;
            $form->load($_POST);
            return ActiveForm::validate($form);
        }

        if ($form->load($_POST) && $form->validate()) {
            // редактирование торговой точки
            Yii::$app->response->format = Response::FORMAT_JSON;
            $result = [
                'id' => null,
                'success' => false,
            ];
            if ($this->partnersApi->updateTradePoint($form, $tradePoint)) {
                $result['success'] = true;
                $result['id'] = $tradePoint->id;
            }
            return $result;
        }

        return $this->renderAjax('form', [
            'model' => $form,
        ]);
    }

    /**
     * Форма создания торговой точки (вызывается в модальном окне)
     */
    public function actionCreate()
    {
        if (!Yii::$app->request->isAjax) {
            throw new ForbiddenHttpException();
        }

        $form = new TradePointForm();

        if (!empty($_POST['ajax'])) {
            // AJAX-валидация
            Yii::$app->response->format = Response::FORMAT_JSON;
            $form->load($_POST);
            return ActiveForm::validate($form);
        }

        if ($form->load($_POST) && $form->validate()) {
            // создание новой торговой точки
            Yii::$app->response->format = Response::FORMAT_JSON;
            $result = [
                'id' => null,
                'success' => false,
            ];
            if ($tradePoint = $this->partnersApi->createTradePoint($form, $this->partner)) {
                $result['success'] = true;
                $result['id'] = $tradePoint->id;
            }
            return $result;
        }

        return $this->renderAjax('form', [
            'model' => $form,
        ]);
    }

    /**
     * Списк торговых точек
     */
    public function actionList()
    {
        $list = TradePoint::findUserList()->all();

        return $this->render('list', [
            'list' => $list,
        ]);
    }
}