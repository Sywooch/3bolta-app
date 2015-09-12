<?php
namespace advert\components;

use advert\forms\AnswerForm;
use advert\forms\QuestionForm;
use advert\models\Question;
use advert\models\Advert;
use Yii;
use yii\base\Component;
use Exception;
use yii\helpers\Url;
use advert\exception\QuestionsApiException;

/**
 * API для работы с вопросами к объявлению
 */
class QuestionsApi extends Component
{
    /**
     * По идентификатору объявления и идентификатору хеша вопроса
     * возвращает модель вопроса AnswerHash.
     *
     * В случае ошибки возвращает null.
     *
     * @param integer $advertId идентификатор объвления
     * @param string $answerHash хеш вопроса
     * @return Question|null
     */
    public function getQuestionByAnswerId($advertId, $answerHash)
    {
        if (strlen($answerHash) == 32) {
            return Question::find()->andWhere([
                'advert_id' => (int) $advertId,
                'hash' => (string) $answerHash,
            ])->one();
        }

        return null;
    }

    /**
     * Ответить на вопрос. На вход передается уже полностью заполненная форма ответа.
     * В случае успеха возвращает true, иначе - генерирует QuestionsApiException.
     *
     * @param AnswerForm $form
     * @return boolean
     * @throws QuestionsApiException
     */
    public function answerToQuestion(AnswerForm $form)
    {
        $ret = false;

        if (!$form->validate()) {
            throw new QuestionsApiException('', QuestionsApiException::VALIDATION_ERROR);
        }

        /* @var $question Question */
        $question = $form->getQuestion();
        if (!($question instanceof Question)) {
            throw new QuestionsApiException('', QuestionsApiException::QUESTION_NOT_FOUND);
        }

        /* @var $advert Advert */
        $advert = $question->advert;
        if (!($advert instanceof Advert)) {
            throw new QuestionsApiException('', QuestionsApiException::QUESTION_NOT_FOUND);
        }

        $question->setAttributes([
            'answer' => $form->answer,
        ]);

        $transaction = $question->getDb()->beginTransaction();
        try {
            if ($question->to_user_id && !$question->save()) {
                // если получатель зарегистрирован - сохраняем ответ в БД
                throw new QuestionsApiException('', QuestionsApiException::VALIDATION_ERROR);
            }

            // удаляем вопрос из БД, если отправитель незарегистрированный пользователь
            if (!$question->from_user_id) {
                $question->delete();
            }

            // отправить e-mail уведомление
            Yii::$app->mailer->compose('advertReplyQuestion', [
                'advert' => $advert,
                'toUserName' => $question->from_user_name,
                'toUserEmail' => $question->from_user_email,
                'question' => $question->question,
                'answer' => $question->answer,
                'advertLink' => Url::toRoute(['/advert/part-catalog/details',
                    'id' => $advert->id
                ], true),
            ])
            ->setTo($question->from_user_email)
            ->setSubject(Yii::t('mail_subjects', 'Reply a question'))
            ->send();

            $transaction->commit();

            $ret = true;
        } catch (Exception $ex) {
            $transaction->rollBack();
            $ret = false;
            QuestionsApiException::throwUp($ex);
        }

        return $ret;
    }

    /**
     * Создать вопрос по объявлению.
     * Модель формы должна быть уже полностью заполнена.
     * В случае ошибки генерирует QuestionsApiException, иначе - возвращает true.
     *
     * @param QuestionForm $form
     * @return boolean
     * @throws QuestionsApiException
     */
    public function createQuestion(QuestionForm $form)
    {
        $ret = false;

        $advert = $form->getAdvert();
        if (!($advert instanceof Advert) || $advert->isNewRecord) {
            throw new QuestionsApiException('', QuestionsApiException::ADVERT_NOT_FOUND);
        }

        if (!$form->validate()) {
            throw new QuestionsApiException('', QuestionsApiException::VALIDATION_ERROR);
        }

        $model = new Question();

        // установить данные из формы
        $model->setAttributes([
            'advert_id' => $advert->id,
            'from_user_id' => $form->getUser_id(),
            'from_user_name' => $form->user_name,
            'from_user_email' => $form->user_email,
            'to_user_id' => $advert->user_id,
            'to_user_email' => $advert->getUserEmail(),
            'to_user_name' => $advert->getUserName(),
            'question' => $form->question,
            'answer' => '',
        ]);

        if (!$model->validate()) {
            throw new QuestionsApiException('', QuestionsApiException::VALIDATION_ERROR);
        }

        $transaction = $model->getDb()->beginTransaction();

        try {
            if (!$model->save()) {
                // ошибка сохранения
                throw new QuestionsApiException('', QuestionsApiException::VALIDATION_ERROR);
            }

            // отправить e-mail уведомление
            Yii::$app->mailer->compose('advertQuestion', [
                'advert' => $advert,
                'toUserName' => $advert->getUserName(),
                'toUserEmail' => $advert->getUserEmail(),
                'toUserId' => $advert->user_id,
                'fromUserName' => $model->from_user_name,
                'fromUserId' => $form->getUser_id(),
                'advertId' => $advert->id,
                'advertLink' => Url::toRoute(['/advert/part-catalog/details',
                    'id' => $advert->id
                ], true),
                'answerLink' => Url::toRoute(['/advert/part-catalog/details',
                    'id' => $advert->id,
                    'answer' => $model->hash,
                ], true),
                'question' => $model->question,
            ])
            ->setTo($advert->getUserEmail())
            ->setSubject(Yii::t('mail_subjects', 'Advert question'))
            ->send();

            $transaction->commit();

            $ret = true;
        }
        catch (Exception $ex) {
            $ret = false;
            $transaction->rollBack();
            QuestionsApiException::throwUp($ex);
        }

        return $ret;
    }
}