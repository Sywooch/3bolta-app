<?php
namespace partner\models;

use Yii;

use handbook\models\HandbookValue;
use yii\helpers\ArrayHelper;
use user\models\User;

/**
 * Модель партнера.
 * Партнер в обязательном порядке должен быть прикреплен к пользователю.
 * user_id - уникальное поле в таблице партнеров
 */
class Partner extends \yii\db\ActiveRecord
{
    /**
     * Название таблицы
     * @return string
     */
    public static function tableName()
    {
        return '{{%partner}}';
    }

    /**
     * Правила валидации
     * @return string
     */
    public function rules()
    {
        return [
            [['user_id', 'name', 'company_type'], 'required'],
            ['company_type', 'in', 'range' => array_keys(self::getCompanyTypes())],
            ['user_id', 'unique'],
            ['name', 'string', 'max' => 100],
        ];
    }

    /**
     * Подписи атрибутов
     * @return []
     */
    public static function attributeLabels()
    {
        return [
            'user_id' => Yii::t('partner', 'Owner'),
            'name' => Yii::t('partner', 'Partner name'),
            'company_type' => Yii::t('partner', 'Company type'),
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

    /**
     * Получить список типов организации для выпадающего списка
     * @return array
     */
    public static function getCompanyTypes()
    {
        return ArrayHelper::map(
            HandbookValue::find()->andWhere(['handbook_code' => 'part_condition'])->all(),
            'id', 'name'
        );
    }

    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->created = date('Y-m-d H:i:s');
        }
        $this->edited = date('Y-m-d H:i:s');

        return parent::beforeSave($insert);
    }
}