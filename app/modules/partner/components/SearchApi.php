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
     * Поиск торговых точек по заполненной форме поиска
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
            $n = $searchForm->name;
            $s = $searchForm->getSpecialization();

            if (trim($n) || $s) {
                // также нужно подключить таблицу партнера
                $query->joinWith('partner');
            }

            if (!is_null($c)) {
                // поиск по координатам
                $query->andWhere(['between', 'latitude', $c['sw']['lat'], $c['ne']['lat']]);
                $query->andWhere(['between', 'longitude', $c['sw']['lng'], $c['ne']['lng']]);
            }
            if (trim($n)) {
                // поиск по имени партнера
                $query->andWhere(['like', Partner::tableName() . '.name', $n]);
            }
            if ($s) {
                // поиск по специализации
                $query->joinWith('partner.specialization');
                $query->andWhere([Specialization::tableName() . '.mark_id' => $s]);
            }

            $ret = $query->all();
        }

        return $ret;
    }
}