<?php
namespace app\components;

use yii\helpers\StringHelper;

/**
 * Надстройка над ActiveRecord
 */
class ActiveRecord extends \yii\db\ActiveRecord
{
    /**
     * Условие по умолчанию
     * @return \yii\db\ActiveQuery
     */
    public static function find()
    {
        $calledClass = get_called_class();
        $class = strtolower(StringHelper::basename($calledClass::className()));
        return parent::find()->from($calledClass::tableName() . ' ' . $class);
    }
}