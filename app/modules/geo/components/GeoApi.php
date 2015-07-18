<?php
namespace geo\components;

use geo\models\Region;
use Yii;
use yii\base\Component;
use yii\web\Cookie;
use yii\web\Request;
use yii\web\Response;

/**
 * API для работы с геоданными
 */
class GeoApi extends Component
{
    /**
     * Кука для установки региона пользователя
     */
    const REGION_COOKIE_KEY = 'user_region_id';

    /**
     * Получить регион по идентификатору либо по самой модели
     *
     * @param mixed $region идентификатор региона, либо модель класса Region
     * @return Region|null
     */
    public function getRegion($region)
    {
        if (!($region instanceof Region)) {
            $region = Region::find()->andWhere(['id' => (int) $region])->one();
        }

        return $region;
    }

    /**
     * Получить регион пользователя из кук
     * @param boolean $getDefault если передан этот флаг, то в случае, если регион не установлен, возвращает регион по умолчанию
     * @return Region|null
     */
    public function getUserRegion($getDefault = false)
    {
        $region = null;

        /* @var $request Request */
        $request = Yii::$app->request;
        if ($request->cookies->has(self::REGION_COOKIE_KEY)) {
            $cookie = $request->cookies->get(self::REGION_COOKIE_KEY);
            if ($cookie instanceof Cookie) {
                $region = $this->getRegion((int) $cookie->value);
            }
        }

        if (!($region instanceof Region) && $getDefault) {
            $region = Region::find()->andWhere(['as_default' => true])->one();
        }

        return $region;
    }

    /**
     * Установить регион пользователя в куки
     * @param mixed $region идентификатор региона, либо модель класса Region
     */
    public function setUserRegion($region)
    {
        $region = $this->getRegion($region);

        if ($region instanceof Region) {
            /* @var $request Response */
            $response = Yii::$app->response;
            $response->cookies->add(new Cookie([
                'name' => self::REGION_COOKIE_KEY,
                'value' => $region->id,
            ]));
        }
    }

    /**
     * Получить ближайший регион по координатам
     *
     * @param float $lat широта
     * @param float $lng долгота
     * @return Region|null
     */
    public function getNearestRegion($lat, $lng)
    {
        $lat = (float) $lat;
        $lng = (float) $lng;

        $sql = 'SELECT ' . Region::tableName() . '.*, ';
        $sql .= "((ACOS(SIN($lat * PI() / 180) * SIN(" . Region::tableName() . ".center_lat * PI() / 180)";
        $sql .= "+ COS($lat * PI() / 180) * COS(" . Region::tableName() . ".center_lat * PI() / 180)";
        $sql .= "* COS(($lng - " . Region::tableName() . ".center_lng) * PI() / 180)) * 180 / PI())";
        $sql .= "* 60 * 1.1515) as distance";

        $sql .= " FROM " . Region::tableName();
        $sql .= " ORDER BY distance ASC LIMIT 1";

        return Region::findBySql($sql)->one();
    }

    /**
     * Возвращает true, если требуется установка региона пользователя
     * @return boolean
     */
    public function needToSetRegion()
    {
        return $this->getUserRegion() === null;
    }
}