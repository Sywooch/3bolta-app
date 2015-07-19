<?php
namespace geo\widgets;

use geo\assets\UserRegion as UserRegionAssets;
use geo\components\GeoApi;
use geo\forms\SelectRegion;
use Yii;
use yii\bootstrap\Widget;
use geo\models\Region;

/**
 * Модальное окно для выбора региона
 */
class SelectRegionModal extends Widget
{
    public function run()
    {
        /* @var $geoApi GeoApi */
        $geoApi = Yii::$app->getModule('geo')->api;

        // выбранный регион пользователя, либо регион по умолчанию
        $userRegion = $geoApi->getUserRegion(true);

        // форма для выбора региона вручную
        $form = new SelectRegion();
        if ($userRegion instanceof Region) {
            $form->regionId = $userRegion->id;
        }

        UserRegionAssets::register($this->view);

        return $this->render('select_region', [
            'selectRegion' => $form,
        ]);
    }
}