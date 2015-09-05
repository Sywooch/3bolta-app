<?php
namespace advert\forms;

use advert\models\Question;
use Yii;
use yii\base\Model;

/**
 * Модель ответа на вопрос
 */
class AnswerForm extends Model
{
    /**
     * @var string уникальный идентификатор вопроса
     */
    protected $_question_uuid;

    /**
     * @var Question модель вопроса
     */
    protected $_question;

    /**
     * @var string ответ
     */
    public $answer;

    /**
     * Получить GUID вопроса
     * @return string
     */
    public function getQuestion_uid()
    {
        return $this->_question_uuid;
    }

    /**
     * Получить модель вопроса
     *
     * @return Question
     */
    public function getQuestion()
    {
        return $this->_question;
    }

    /**
     * Установить вопрос. Помимо всего прочего еще устанавливает и question_uuid.
     *
     * @param Question модель вопроса
     */
    public function setQuestion(Question $question)
    {
        $this->_question = $question;
        $this->_question_uuid = $question->hash;
    }

    /**
     * Правила валидации
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['answer', 'required'],
            ['answer', 'string'],
        ];
    }

    /**
     * Подписи атрибутов
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'answer' => Yii::t('frontend/advert', 'Your answer'),
        ];
    }
}