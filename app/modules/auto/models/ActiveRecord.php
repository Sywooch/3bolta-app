<?php
namespace auto\models;

use Yii;

/**
 * Базовый класс для всех моделей этого модуля
 */
abstract class ActiveRecord extends \app\components\ActiveRecord
{
    public function rules()
    {
        return [
            [['active'], 'boolean'],
            [['name, full_name'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => Yii::t('auto', 'Name'),
            'full_name' => Yii::t('auto', 'Full name'),
            'active' => Yii::t('auto', 'Active'),
        ];
    }
}