<?php
namespace handbook\models;

use Yii;
use yii\db\ActiveRecord;

class Handbook extends ActiveRecord
{
    /**
     * Название таблицы
     * @return string
     */
    public static function tableName()
    {
        return '{{%handbook}}';
    }

    /**
     * Правила валидации
     * @return []
     */
    public function rules()
    {
        return [
            [['code', 'name'], 'required'],
        ];
    }

    /**
     * Подписи к полям
     * @return []
     */
    public function attributeLabels()
    {
        return [
            'code' => Yii::t('handbook', 'Code'),
            'name' => Yii::t('handbook', 'Name'),
        ];
    }

    /**
     * Возвращает доступные коды для справочников
     * @return []
     */
    public static function getAvailCode()
    {
        $ret = [];

        $res = self::find()->all();

        foreach ($res as $i) {
            $ret[] = $i['code'];
        }

        return $ret;
    }
}