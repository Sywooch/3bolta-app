<?php
namespace user\models;

/**
 * Модель подтверждений пользователя
 */
class UserConfirmation extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%user_confirmation}}';
    }

    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['email'], 'email'],
            [['email', 'email_confirmation'], 'safe'],
        ];
    }
}