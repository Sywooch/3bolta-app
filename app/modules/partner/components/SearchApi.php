<?php
namespace partner\components;

use partner\forms\TradePointMap;
use partner\models\Partner;
use partner\models\Specialization;
use partner\models\TradePoint;
use yii\base\Component;

/**
 * API для работы с торговыми точками: поиск торговых точек на карте
 */
class SearchApi extends Component
{
    /**
     * Поиск торговых точек по заполненной форме поиска.
     * Поиск происходит внутри локации. Все торговые точки,
     * которые не подходят по запросу по названию, либо по специализации - подсвечиваются как неактивные.
     *
     * @param TradePointMap $searchForm
     * @return TradePoint[]
     */
    public function search(TradePointMap $searchForm)
    {
        $ret = [];

        if ($searchForm->validate()) {
            $query = TradePoint::find();

            $c = $searchForm->getCoordinatesArray();
            $n = strtoupper($searchForm->name);
            $s = strtoupper($searchForm->specialization);

            if (!is_null($c)) {
                // поиск по координатам
                $query->andWhere(['between', 'latitude', $c['sw']['lat'], $c['ne']['lat']]);
                $query->andWhere(['between', 'longitude', $c['sw']['lng'], $c['ne']['lng']]);
            }

            foreach ($query->each() as $row) {
                $ret[] = $row;

                /* @var $row TradePoint */
                $row->active = true;
                /* @var $partner Partner */
                $partner = $row->partner;
                if (trim($n)) {
                    // фильтр по имени
                    $partnerName = strtoupper($partner->name);
                    if (strpos($partnerName, $n) === false) {
                        $row->active = false;
                        continue;
                    }
                }

                if (trim($s)) {
                    // фильтр по марке автомобиля
                    $row->active = false;

                    $marks = $partner->getMarkNames();
                    foreach ($marks as $markName) {
                        $markName = strtoupper($markName);
                        if (strpos($markName, $s) !== false) {
                            $row->active = true;
                            break;
                        }
                    }
                }
            }
        }

        return $ret;
    }
}