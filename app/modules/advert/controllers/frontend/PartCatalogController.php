<?php
namespace advert\controllers\frontend;

use advert\components\PartsSearchApi;
use advert\components\QuestionsApi;
use advert\exception\QuestionsApiException;
use advert\forms\AnswerForm;
use advert\forms\PartSearch;
use advert\forms\QuestionForm;
use advert\models\Advert;
use advert\models\Question;
use app\components\Controller;
use sammaye\solr\SolrDataProvider;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

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
        return ArrayHelper::merge([
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'question' => ['post'],
                    'answer' => ['post'],
                ],
            ]
        ], parent::behaviors());
    }

    /**
     * Ответ на вопрос по запчасти - отправка формы по AJAX.
     *
     * @param integer $id идентификатор объявления
     * @param string $hash хеш вопроса
     */
    public function actionAnswer($id, $hash)
    {
        if (!Yii::$app->request->isAjax) {
            throw new ForbiddenHttpException();
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        /* @var $questionsApi QuestionsApi */
        $questionsApi = Yii::$app->getModule('advert')->questions;
        // поиск вопроса
        $question = $questionsApi->getQuestionByAnswerId($id, $hash);
        if (!($question instanceof Question)) {
            throw new NotFoundHttpException();
        }

        $answerForm = new AnswerForm([
            'question' => $question,
        ]);

        $answerForm->load($_POST);

        if (!empty($_POST['ajax'])) {
            return ActiveForm::validate($answerForm);
        }

        $result = [
            'success' => false,
            'errorCode' => 0,
        ];

        if ($answerForm->validate()) {
            try {
                if ($questionsApi->answerToQuestion($answerForm)) {
                    $result['success'] = true;
                }
            } catch (QuestionsApiException $ex) {
                $result['success'] = false;
                $result['errorCode'] = $ex->getCode();
            }
        }

        return $result;
    }

    /**
     * Вопрос по запчасти - отправка формы по AJAX.
     *
     * @param integer $id идентификатор объявления
     * @return array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionQuestion($id)
    {
        if (!Yii::$app->request->isAjax) {
            throw new ForbiddenHttpException();
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        $result = [
            'success' => false,
            'errorCode' => 0,
        ];

        /* @var $searchApi PartsSearchApi */
        $searchApi = Yii::$app->getModule('advert')->partsSearch;
        // поиск объявления
        $model = $searchApi->getDetails($id);
        if (!($model instanceof Advert)) {
            throw new NotFoundHttpException();
        }

        // создать форму
        $form = new QuestionForm();
        $form->setAdvert($model);
        if (!Yii::$app->user->isGuest) {
            $form->setUser_id(Yii::$app->user->getId());
        }

        $form->load($_POST);

        if (!empty($_POST['ajax'])) {
            // AJAX-валидация
            return ActiveForm::validate($form);
        }

        if ($form->validate()) {
            /* @var $questionsApi QuestionsApi */
            $questionsApi = Yii::$app->getModule('advert')->questions;
            try {
                if ($questionsApi->createQuestion($form)) {
                    $result['success'] = true;
                }
            } catch (QuestionsApiException $ex) {
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
        /* @var $searchApi PartsSearchApi */
        $searchApi = Yii::$app->getModule('advert')->partsSearch;

        // форма поиска
        $emptyForm = true;
        $searchForm = new PartSearch();
        if ($searchForm->load(Yii::$app->request->getQueryParams())) {
            $searchForm->validate();
            $emptyForm = false;
        }

        /* @var $dataProvider SolrDataProvider */
        $dataProvider = $searchApi->searchItems($searchForm);

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'searchForm' => $searchForm,
            'emptySearchForm' => $emptyForm,
        ]);
    }

    /**
     * Детальная страница объявления
     * @param int $id идентификатор объявления
     * @param string $answer хеш вопроса, на который отвечает владелец объявления
     */
    public function actionDetails($id, $answer = '')
    {
        /* @var $searchApi PartsSearchApi */
        $searchApi = Yii::$app->getModule('advert')->partsSearch;

        $model = $searchApi->getDetails($id);
        if (!($model instanceof Advert)) {
            throw new NotFoundHttpException();
        }

        /* @var $questionsApi QuestionsApi */
        $questionsApi = Yii::$app->getModule('advert')->questions;
        // сформировать форму ответа на вопрос об, если есть хеш ответа
        $answerForm = null;
        if ($answer) {
            $question = $questionsApi->getQuestionByAnswerId($model->id, $answer);
            if ($question) {
                $answerForm = new AnswerForm([
                    'question' => $question,
                ]);
            }
        }

        // модель формы вопроса по запчасти
        // только при условии, что пользователь не отвечает на вопрос
        $questionForm = null;
        if ($model->allowQuestions() && !($answerForm instanceof AnswerForm)) {
            $questionForm = new QuestionForm();
            $questionForm->setAdvert($model);
            if (!Yii::$app->user->isGuest) {
                $questionForm->setUser_id(Yii::$app->user->getId());
            }
        }

        return $this->render('details', [
            'model' => $model,
            'questionForm' => $questionForm,
            'answerForm' => $answerForm,
        ]);
    }
}