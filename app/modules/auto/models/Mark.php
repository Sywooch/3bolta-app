<?php
namespace app\modules\auto\models;

use yii\db\ActiveRecord;

class Mark extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%auto_mark}}';
    }
}