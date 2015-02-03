<?php
namespace app\modules\auto\models;

use yii\db\ActiveRecord;

class Model extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%auto_model}}';
    }
}