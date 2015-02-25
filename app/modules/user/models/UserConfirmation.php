<?php
namespace user\models;

/**
 * Модель подтверждений пользователя
 */
class UserConfirmation extends \yii\db\ActiveRecord
{
    /**
     * Название таблицы
     * @return string
     */
    public static function tableName()
    {
        return '{{%user_confirmation}}';
    }

    /**
     * Правила валидации
     * @return []
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['email'], 'email'],
            [['email', 'email_confirmation', 'restore_confirmation'], 'safe'],
        ];
    }

    /**
     * Получить пользователя
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}