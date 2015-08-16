<?php
namespace advert\forms;

/**
 * Модель ответа на вопрос
 */
class AnswerForm extends yii\base\Model
{
    /**
     * @var string уникальный идентификатор вопроса
     */
    protected $_question_uuid;

    /**
     * @var AdvertQuestion модель вопроса
     */
    protected $_question;

    /**
     * @var string ответ
     */
    public $answer;

    /**
     * Установить уникальный идентификатор вопроса.
     * Если вопрос существует, также подключает модель.
     * Если вопроса не существует - генерирует исключение.
     *
     * @param string $val GUID вопроса
     * @throws \yii\base\Exception
     */
    public function setQuestion_uuid($val)
    {
        $question = null;

        if (preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/i', $val)) {
            $question = \advert\models\AdvertQuestion::find()->andWhere(['answer_hash' => $val])->one();
        }

        if ($question instanceof \advert\models\AdvertQuestion) {
            $this->_question = $question;
            $this->_question_uuid = $question->hash;
        }
        else {
            throw new \yii\base\Exception();
        }
    }

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
     * @return AdvertQuestion
     */
    public function getQuestion()
    {
        return $this->_question;
    }

    /**
     * Правила валидации
     *
     * @return array
     */
    public function rules()
    {
        return [
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
            'answer' => \Yii::t('frontend/advert', 'Your answer'),
        ];
    }
}