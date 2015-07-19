<?php
namespace geo\widgets;

use geo\components\GeoApi;
use geo\forms\SelectRegion;
use geo\models\Region;
use Yii;
use yii\base\Widget;
use geo\assets\UserRegion as UserRegionAssets;

/**
 * Виджет выбора региона пользователя
 */
class UserRegion extends Widget
{
    public function run()
    {
        /* @var $geoApi GeoApi */
        $geoApi = Yii::$app->getModule('geo')->api;

        // выбранный регион пользователя, либо регион по умолчанию
        $userRegion = $geoApi->getUserRegion(true);

        // флаг, указывающий на необходимость определения местоположения пользователя
        $needToSetRegion = $geoApi->needToSetRegion();

        UserRegionAssets::register($this->view);

        return $this->render('user_region', [
            'userRegion' => $userRegion,
            'needToSetRegion' => $needToSetRegion,
        ]);
    }
}