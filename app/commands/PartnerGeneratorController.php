<?php
namespace app\commands;

class PartnerGeneratorController extends \yii\console\Controller
{
    public function actionIndex()
    {
        $partner = \partner\models\Partner::find()->one();

        $latitudeFrom = 55.09955999703691;
        $latitudeTo = 56.031362468082634;
        $longitudeFrom = 35.186303085449254;
        $longitudeTo = 40.679467147949254;

        for ($x = 0; $x < 100; $x++) {
            $latitude = rand($latitudeFrom * 10000, $latitudeTo * 10000) / 10000;
            $longitude = rand($longitudeFrom * 10000, $longitudeTo * 10000) / 10000;
            $phone = rand(70000000000, 79999999999);

            $tradePoint = new \partner\models\TradePoint();
            $tradePoint->setAttributes([
                'latitude' => $latitude,
                'longitude' => $longitude,
                'phone' => $phone,
                'address' => 'Moscow',
                'partner_id' => $partner->id,
            ]);
            $tradePoint->save();
        }
    }
}