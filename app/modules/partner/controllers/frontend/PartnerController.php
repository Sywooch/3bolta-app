<?php
namespace partner\controllers\frontend;

use app\components\Controller;
use partner\components\PartnersApi;
use partner\exception\PartnersApiException;
use partner\filters\CheckPartnerAccessRule;
use partner\forms\Partner as PartnerForm;
use partner\forms\TradePoint as TradePointForm;
use partner\models\Partner;
use partner\models\TradePoint;
use user\models\User;
use Yii;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\rest\Action;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Работа с информацией о компании-партнере: редактирование данных, торговых точек
 */
class PartnerController extends Controller
{
    /**
     * @var PartnersApi
     */
    protected $partnersApi;

    /**
     * @var Partner
     */
    protected $partner;

    public function init()
    {
        parent::init();
        $this->partnersApi = Yii::$app->getModule('partner')->api;
        $user = Yii::$app->user->getIdentity();
        if ($user instanceof User) {
            $this->partner = $user->partner;
        }
    }

    /**
     * Фильтры
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge([
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'class' => CheckPartnerAccessRule::className(),
                        'allow' => true,
                    ]
                ],
                'denyCallback' => function($rule, $action) {
                    if ($action instanceof Action) {
                        /* @var $action Action */
                        return $action->controller->goHome();
                    }
                }
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete-trade-point' => ['post'],
                ]
            ]
        ], parent::behaviors());
    }

    /**
     * Редактирование торговой точки
     */
    public function actionEditTradePoint($id)
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
                'errorCode' => 0,
            ];
            try {
                $result['success'] = $this->partnersApi->updateTradePoint($form, $tradePoint);
                $result['id'] = $tradePoint->id;
            } catch (PartnersApiException $ex) {
                $result['success'] = false;
                $result['errorCode'] = $ex->getCode();
            }
            return $result;
        }

        return $this->renderAjax('trade-point-form', [
            'model' => $form,
        ]);
    }

    /**
     * Форма создания торговой точки (вызывается в модальном окне)
     */
    public function actionCreateTradePoint()
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
                'errorCode' => 0,
            ];
            try {
                $tradePoint = $this->partnersApi->createTradePoint($form, $this->partner);
                $result['success'] = true;
                $result['id'] = $tradePoint->id;
            }
            catch (PartnersApiException $ex) {
                $result['success'] = false;
                $result['errorCode'] = $ex->getCode();
            }
            return $result;
        }

        return $this->renderAjax('form', [
            'model' => $form,
        ]);
    }

    /**
     * Главная страница для редактирования информации о компании
     */
    public function actionIndex()
    {
        $list = TradePoint::findUserList()->all();
        $partnerForm = PartnerForm::createFromPartner($this->partner);

        if (!empty($_POST['ajax']) && Yii::$app->request->isAjax) {
            // AJAX-валидация
            Yii::$app->response->format = Response::FORMAT_JSON;
            $partnerForm->load($_POST);
            return ActiveForm::validate($partnerForm);
        }

        if ($partnerForm->load($_POST) && $partnerForm->validate()) {
            try {
                $this->partnersApi->updatePartnerData($partnerForm, $this->partner->user);
                Yii::$app->session->setFlash('partner_success_update', true);
            } catch (PartnersApiException $ex) {
                Yii::$app->session->setFlash('partner_error_update', true);
            }
            return $this->refresh();
        }

        return $this->render('index', [
            'list' => $list,
            'partnerForm' => $partnerForm,
        ]);
    }

    /**
     * Удаление торговой точки
     */
    public function actionDeleteTradePoint($id)
    {
        $tradePoint = TradePoint::findUserList()->andWhere(['id' => (int) $id])->one();

        if (!($tradePoint instanceof TradePoint)) {
            throw new NotFoundHttpException();
        }

        try {
            $tradePoint->delete();
        } catch (Exception $ex) { }

        return $this->redirect(['list']);
    }
}