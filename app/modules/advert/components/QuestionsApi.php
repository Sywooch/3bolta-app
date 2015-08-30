<?php
namespace advert\components;

use advert\forms\AnswerForm;
use advert\forms\QuestionForm;
use advert\models\AdvertQuestion;
use advert\models\PartAdvert;
use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\Url;

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
     * @return AdvertQuestion|null
     */
    public function getQuestionByAnswerId($advertId, $answerHash)
    {
        try {
            return AdvertQuestion::find()->andWhere([
                'advert_id' => (int) $advertId,
                'hash' => (string) $answerHash,
            ])->one();
        } catch (Exception $ex) { }

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

        /* @var $question AdvertQuestion */
        $question = $form->getQuestion();
        if (!($question instanceof AdvertQuestion)) {
            throw new QuestionsApiException('', QuestionsApiException::QUESTION_NOT_FOUND);
        }

        /* @var $advert PartAdvert */
        $advert = $question->advert;
        if (!($advert instanceof PartAdvert)) {
            throw new QuestionsApiException('', QuestionsApiException::QUESTION_NOT_FOUND);
        }

        $question->setAttributes([
            'answer' => $form->answer,
        ]);

        $transaction = $question->getDb()->beginTransaction();
        try {
            if ($question->to_user_id && !$question->save()) {
                // если получатель зарегистрирован - сохраняем ответ в БД
                throw new Exception();
            }

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

            // удаляем вопрос из БД, если отправитель незарегистрированный пользователь
            if (!$question->from_user_id) {
                $question->delete();
            }

            $transaction->commit();

            $ret = true;
        } catch (Exception $ex) {
            $transaction->rollBack();
            throw new QuestionsApiException('', QuestionsApiException::DATABASE_ERROR);
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
        if (!($advert instanceof PartAdvert) || $advert->isNewRecord) {
            throw new QuestionsApiException('', QuestionsApiException::ADVERT_NOT_FOUND);
        }

        if (!$form->validate()) {
            throw new QuestionsApiException('', QuestionsApiException::VALIDATION_ERROR);
        }

        $model = new AdvertQuestion();

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
                throw new Exception();
            }

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
            $transaction->rollBack();
            throw new QuestionsApiException('', QuestionsApiException::DATABASE_ERROR, $ex);
        }

        return $ret;
    }
}

/**
 * Класс исключений для API
 */
class QuestionsApiException extends Exception
{
    const ADVERT_NOT_FOUND = 1; // не установлено или не найдено объявление
    const VALIDATION_ERROR = 2; // ошибка валидации формы
    const DATABASE_ERROR = 3; // ошибка БД
    const QUESTION_NOT_FOUND = 4; // вопрос не найден
}