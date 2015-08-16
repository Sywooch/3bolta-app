<?php
namespace advert\controllers\frontend;

use Yii;
use app\components\Controller;
use yii\web\NotFoundHttpException;
use advert\models\PartAdvert;

/**
 * Контроллер для вывода объявлений запчастей
 */
class PartCatalogController extends Controller
{
    /**
     * Фильтры
     * @return array
     */
    public function behaviors()
    {
        return \yii\helpers\ArrayHelper::merge([
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'question' => ['post'],
                ],
            ]
        ], parent::behaviors());
    }

    /**
     * Вопрос по запчасти - отправка формы по AJAX.
     *
     * @param integer $id
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionQuestion($id)
    {
        if (!\Yii::$app->request->isAjax) {
            throw new \yii\web\ForbiddenHttpException();
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $result = [
            'success' => false,
            'errorCode' => 0,
        ];

        /* @var $searchApi \advert\components\PartsSearchApi */
        $searchApi = Yii::$app->getModule('advert')->partsSearch;
        // поиск модели
        $model = $searchApi->getDetails($id);
        if (!($model instanceof PartAdvert)) {
            throw new NotFoundHttpException();
        }

        // создать форму
        $form = new \advert\forms\QuestionForm();
        $form->setAdvert($model);
        if (!\Yii::$app->user->isGuest) {
            $form->setUser_id(\Yii::$app->user->getId());
        }

        $form->load($_POST);

        if ($form->validate()) {
            /* @var $questionsApi \advert\components\QuestionsApi */
            $questionsApi = \Yii::$app->getModule('advert')->questions;
            try {
                if ($questionsApi->createQuestion($form)) {
                    $result['success'] = true;
                }
            } catch (\advert\components\QuestionsApiException $ex) {
                $result['success'] = false;
                $result['errorCode'] = $ex->getCode();
            }
        }

        return $result;
    }

    /**
     * Поиск объявлений - список найденных
     */
    public function actionSearch()
    {
        /* @var $searchApi \advert\components\PartsSearchApi */
        $searchApi = Yii::$app->getModule('advert')->partsSearch;

        /* @var $dataProvider \yii\data\ActiveDataProvider */
        $dataProvider = $searchApi->searchItems(Yii::$app->request->getQueryParams());

        return $this->render('list', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Детальная страница объявления
     */
    public function actionDetails($id)
    {
        /* @var $searchApi \advert\components\PartsSearchApi */
        $searchApi = Yii::$app->getModule('advert')->partsSearch;

        $model = $searchApi->getDetails($id);
        if (!($model instanceof PartAdvert)) {
            throw new NotFoundHttpException();
        }

        // модель формы вопроса по запчасти
        $questionForm = null;
        if ($model->allowQuestions()) {
            $questionForm = new \advert\forms\QuestionForm();
            $questionForm->setAdvert($model);
            if (!\Yii::$app->user->isGuest) {
                $questionForm->setUser_id(\Yii::$app->user->getId());
            }
        }

        return $this->render('details', [
            'model' => $model,
            'questionForm' => $questionForm,
        ]);
    }
}