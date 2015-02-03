<?php
namespace app\modules\auto\models;

use yii\db\ActiveRecord;

class Generation extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%auto_generation}}';
    }
}