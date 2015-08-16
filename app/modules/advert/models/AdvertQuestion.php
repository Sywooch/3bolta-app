<?php
namespace advert\models;

use app\components\ActiveRecord;
use app\helpers\DataHelper;
use user\models\User;
use yii\db\ActiveQuery;

/**
 * Модель вопроса к объявлению
 */
class AdvertQuestion extends ActiveRecord
{
    /**
     * Максимальная длина e-mail
     */
    const MAX_EMAIL_LENGTH = 255;

    /**
     * Максимальная длина имен
     */
    const MAX_NAME_LENGTH = 255;

    /**
     * Название таблицы
     * @return string
     */
    public static function tableName()
    {
        return '{{%advert_question}}';
    }

    /**
     * Правила валидации
     * @return array
     */
    public function rules()
    {
        return [
            [['advert_id', 'to_user_name', 'to_user_email', 'from_user_name', 'from_user_email'], 'required'],
            [['advert_id', 'to_user_id', 'from_user_id'], 'integer'],
            [['to_user_email', 'from_user_email'], 'email'],
            [['to_user_email', 'from_user_email'], 'string', 'max' => self::MAX_EMAIL_LENGTH],
            [['to_user_name', 'from_user_name'], 'string', 'max' => self::MAX_NAME_LENGTH],
            ['question', 'string', 'skipOnEmpty' => false],
            ['answer', 'string', 'skipOnEmpty' => false],
            ['hash', 'safe'],
        ];
    }

    /**
     * Получить получателя
     * @return ActiveQuery
     */
    public function getToUser()
    {
        return $this->hasOne(User::className(), ['id' => 'to_user_id']);
    }

    /**
     * Получить отправителя
     * @return ActiveQuery
     */
    public function getFromUser()
    {
        return $this->hasOne(User::className(), ['id' => 'from_user_id']);
    }


    /**
     * Получить объявление
     * @return ActiveQuery
     */
    public function getAdvert()
    {
        return $this->hasOne(PartAdvert::className(), ['id' => 'advert_id']);
    }

    /**
     * Перед сохранением - сгенерировать уникальный идентификатор
     *
     * @param boolean $insert
     * @return boolean
     */
    public function beforeSave($insert)
    {
        if (!$this->hash) {
            $this->hash = DataHelper::guid();
        }

        return parent::beforeSave($insert);
    }
}