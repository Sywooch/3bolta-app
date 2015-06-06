<?php
namespace partner\forms;

use Yii;

/**
 * Форма поиска торговых точек пользователя.
 * Простая форма, без валидации, т.к. она предназначена только для
 * взаимодействия с виджетом app\widgets\SelectMapLocation
 */
class MyTradePointSearch extends \yii\base\Model
{
    public $address;
    public $latitude;
    public $longitude;

    public function attributeLabels()
    {
        return [
            'address' => Yii::t('frontend/partner', 'Search by address'),
            'latitude' => Yii::t('frontend/partner', 'Search by latitude'),
            'longitude' => Yii::t('frontend/partner', 'Search by longitude'),
        ];
    }
}