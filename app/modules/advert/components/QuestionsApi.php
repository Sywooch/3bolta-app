<?php
namespace advert\components;

use advert\forms\AnswerForm;
use advert\forms\QuestionForm;
use advert\models\AdvertQuestion;
use advert\models\PartAdvert;
use yii\base\Component;
use yii\base\Exception;

/**
 * API для работы с вопросами к объявлению
 */
class QuestionsApi extends Component
{
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

        $question->setAttributes([
            'answer' => $form->answer,
        ]);

        $transaction = $question->getDb()->beginTransaction();
        try {
            if (!$question->save()) {
                throw new Exception();
            }

            // TODO: отправка e-mail

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

            // TODO: отправка e-mail получателю

            $transaction->commit();

            $ret = true;
        }
        catch (Exception $ex) {
            $transaction->rollBack();
            throw new QuestionsApiException('', QuestionsApiException::DATABASE_ERROR);
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