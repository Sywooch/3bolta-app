<?php
namespace app\modules\auto\models;

use yii\db\ActiveRecord;

class Serie extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%auto_serie}}';
    }
}