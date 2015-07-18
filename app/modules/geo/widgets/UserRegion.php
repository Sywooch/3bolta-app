<?php
namespace geo\widgets;

use yii\base\Widget;

/**
 * Виджет выбора региона пользователя
 */
class UserRegion extends Widget
{
    public function run()
    {
        /* @var $geoApi \geo\components\GeoApi */
        $geoApi = \Yii::$app->getModule('geo')->api;

        // выбранный регион пользователя, либо регион по умолчанию
        $userRegion = $geoApi->getUserRegion(true);

        // флаг, указывающий на необходимость определения местоположения пользователя
        $needToSetRegion = $geoApi->needToSetRegion();

        return $this->render('user_region', [
            'userRegion' => $userRegion,
            'needToSetRegion' => $needToSetRegion,
        ]);
    }
}