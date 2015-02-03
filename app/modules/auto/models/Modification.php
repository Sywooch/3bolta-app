<?php
namespace app\modules\auto\models;

use yii\db\ActiveRecord;

class Modification extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%auto_modification}}';
    }
}