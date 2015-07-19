<?php
namespace geo\widgets;

use geo\components\GeoApi;
use geo\forms\SelectRegion;
use geo\models\Region;
use Yii;
use yii\base\Widget;

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

        // форма для выбора региона вручную
        $form = new SelectRegion();
        if ($userRegion instanceof Region) {
            $form->regionId = $userRegion->id;
        }

        return $this->render('user_region', [
            'selectRegion' => $form,
            'userRegion' => $userRegion,
            'needToSetRegion' => $needToSetRegion,
        ]);
    }
}