<?php
namespace user\models;

use yii\db\ActiveRecord;

/**
 * Модель аккаунта пользователя в соц. сети.
 * Состоит из:
 * - кода соц. сети;
 * - идентификатора пользователя;
 * - внешнем имени;
 * - страницы в соц. сети.
 */
class SocialAccount extends ActiveRecord
{
    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return '{{%user_social_account}}';
    }

    /**
     * Правила валидации
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['user_id', 'required'],
            ['user_id', 'integer'],
            ['code', 'string', 'max' => 20],
            ['external_uid', 'string', 'max' => 30],
            ['external_name', 'string', 'max' => 255],
            ['external_page', 'string', 'max' => 255],

            ['code', 'unique', 'targetAttribute' => ['code', 'user_id']],
            ['external_uid', 'unique', 'targetAttribute' => ['external_uid', 'code']],
        ];
    }
}