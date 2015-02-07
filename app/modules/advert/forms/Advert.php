<?php
namespace advert\forms;

use Yii;

class Advert extends \yii\base\Model
{
    /**
     * Максимальная длина описания
     */
    const DESCRIPTION_MAX_LENGTH = 255;

    public $name;
    public $category_id;
    public $condition_id;
    public $description;
    public $user_name;
    public $user_id;
    public $user_phone;
    public $user_email;

    /**
     * Правила валидации
     * @return []
     */
    public function rules()
    {
        return [
            [['name', 'category_id', 'condition_id'], 'required'],
            [['category_id', 'condition_id'], 'integer'],
            [['description'], 'string', 'max' => self::DESCRIPTION_MAX_LENGTH],
            [['user_name', 'user_phone', 'user_email'], 'required', 'when' => function($model) {
                return empty($model->user_id);
            }],
            [['user_email'], 'email', 'when' => function($model) {
                return empty($model->user_id);
            }],
        ];
    }

    /**
     * Подписи полей
     * @return []
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('advert', 'Part name'),
            'category_id' => Yii::t('advert', 'Part category'),
            'condition_id' => Yii::t('advert', 'Part condition'),
            'description' => Yii::t('advert', 'Advert description'),
            'user_name' => Yii::t('advert', 'Contact name'),
            'user_phone' => Yii::t('advert', 'Contact phone'),
            'user_email' => Yii::t('advert', 'Contact email'),
            'user_id' => Yii::t('advert', 'User id'),
        ];
    }
}