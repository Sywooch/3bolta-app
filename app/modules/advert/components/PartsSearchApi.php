<?php
namespace advert\components;

use advert\forms\PartSearch;
use advert\models\Advert;
use advert\models\Contact;
use advert\models\PartParam;
use advert\models\Part;
use auto\models\Mark;
use geo\components\GeoApi;
use geo\models\Region;
use partner\models\Partner;
use Yii;
use yii\base\Component;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;

/**
 * API для поиска запчастей
 */
class PartsSearchApi extends Component
{
    /**
     * Получить запрос ActiveQuery.
     * Если передать $joinParams, то к запросу будет присоединена модель AdvertPartParam.
     * Если передать $joinContacts, то к запросу будет присоединена модель AdvertContact.
     * Если передать $joinPartners, то к запросу будет присоединена модель TradePoint и Partner.
     *
     * @param boolean $joinParams
     * @param boolean $joinContacts
     * @return ActiveQuery
     */
    protected function getSearchQuery($joinParams = false, $joinContacts = false, $joinPartners = false)
    {
        $query = Part::findActiveAndPublished();

        if ($joinParams) {
            $query->joinWith('partParam');
        }

        if ($joinContacts && !$joinPartners) {
            $query->joinWith('contact');
        }
        else if ($joinContacts && $joinPartners) {
            $query->joinWith('contact.tradePoint.partner', true, 'INNER JOIN');
        }

        return $query;
    }

    /**
     * Сформировать запрос по ключевому слову
     *
     * @param ActiveQuery $query
     */
    protected function makeKeywordQuery(ActiveQuery $query, $q)
    {
        if ($q) {
            $query->andFilterWhere(['or',
                ['like', Advert::tableName() . '.advert_name', $q],
                ['like', PartParam::tableName() . '.catalogue_number', $q],
            ]);
        }
    }

    /**
     * Cформировать запрос по автомобилям
     *
     * @param ActiveQuery $query
     * @param mixed $mark массив идентификаторов или идентификатор
     * @param mixed $model массив идентификаторов или идентификатор
     * @param mixed $serie массив идентификаторов или идентификатор
     * @param mixed $modification массив идентификаторов или идентификатор
     * @param boolean $any искать совпадение по любому автомобилю
     */
    protected function makeAutoQuery(ActiveQuery $query, $mark, $model, $serie, $modification, $any = false)
    {
        $or = [$any ? 'or' : 'and'];
        if ($mark) {
            $query->joinWith('mark');
            $or[] = ['mark.id' => $mark];
        }
        if ($model) {
            $query->joinWith('model');
            $or[] = ['model.id' => $model];
        }
        if ($serie) {
            $query->joinWith('serie');
            $or[] = ['serie.id' => $serie];
        }
        if ($modification) {
            $query->joinWith('modification');
            $or[] = ['modification.id' => $modification];
        }
        if (count($or) != 1) {
            $query->andWhere($or);
        }
    }

    /**
     * Сформировать запрос по категории
     * @param ActiveQuery $query
     * @param int $category
     */
    protected function makeCategoryQuery(ActiveQuery $query, $category)
    {
        if ($category) {
            $query->andWhere([PartParam::tableName() . '.category_id' => (int) $category]);
        }
    }

    /**
     * Сформировать запрос по состоянию
     * @param ActiveQuery $query
     * @param int $condition
     */
    protected function makeConditionQuery(ActiveQuery $query, $condition)
    {
        if ($condition) {
            $query->andWhere([PartParam::tableName() . '.condition_id' => (int) $condition]);
        }
    }

    /**
     * Установить запрос по региону (только если не установлен параметр "Искать в других регионах")
     *
     * @param ActiveQuery $query
     * @param Region|null $region модель региона или null
     * @param boolean $searchOtherRegion
     */
    protected function makeRegionQuery(ActiveQuery $query, $region, $searchOtherRegion)
    {
        if (!$searchOtherRegion && $region instanceof Region) {
            $query->andWhere([Contact::tableName() . '.region_id' => $region->id]);
        }
    }

    /**
     * Установить запрос по цене (от и до)
     *
     * @param ActiveQuery $query
     * @param float $priceFrom
     * @param float $priceTo
     */
    protected function makePriceQuery(ActiveQuery $query, $priceFrom, $priceTo)
    {
        $priceFrom = (float) $priceFrom;
        $priceTo = (float) $priceTo;

        if ($priceFrom > 0) {
            $query->andWhere(['>=', Advert::tableName() . '.price', $priceFrom]);
        }

        if ($priceTo > 0 && $priceTo >= $priceFrom) {
            $query->andWhere(['<=', Advert::tableName() . '.price', $priceTo]);
        }
    }

    /**
     * Запрос по типу продавца:
     * - пустая строка - нет поиска по продавцу;
     * - 0 - всегда частное лицо;
     * - остальные цифры - это всегда тип юр. лица.
     *
     * @param ActiveQuery $query
     * @param mixed $sellerType
     */
    protected function makeSellerQuery(ActiveQuery $query, $sellerType)
    {
        if ($sellerType == '' || is_null($sellerType)) {
            return;
        }

        $sellerType = (int) $sellerType;

        if ($sellerType == 0) {
            // частное лицо, не должно быть привязки к торговой точке
            $query->andWhere([Contact::tableName() . '.trade_point_id' => null]);
        }
        else {
            // в остальном - это тип продавца
            $query->andWhere(['not', [Contact::tableName() . '.trade_point_id' => null]]);
            $query->andWhere([
                Partner::tableName() . '.company_type' => $sellerType
            ]);
        }
    }

    /**
     * Установить сортировку
     *
     * @param ActiveQuery $query
     * @param Region|null $region модель региона или null
     * @param boolean $searchOtherRegion
     */
    public function makeSort(ActiveQuery $query, $region, $searchOtherRegion)
    {
        $sort = [];
        $group = [Advert::tableName() . '.id'];

        if ($searchOtherRegion && $region instanceof Region) {
            // искать в других регионах, значит сортируем по ближайшим регионам
            /* @var $region Region */
            /* @var $geoApi GeoApi */
            $geoApi = Yii::$app->getModule('geo')->api;
            // сортировка по регионам
            $regionIds = $geoApi->getNearestRegionsIds($region);
            if (!empty($regionIds)) {
                $s = 'CASE ' . Contact::tableName() . '.region_id ' . " \n";
                $x = 0;
                foreach ($regionIds as $regionId) {
                    $s .= 'WHEN ' . $regionId . ' THEN ' . ++$x . " \n";
                }
                $s .= 'END ' . "\n";
                $sort[] = new Expression($s);
            }
            $group[] = Contact::tableName() . '.region_id';
        }

        // сортировка по дате
        $sort[] = new Expression(Advert::tableName() . '.published DESC');

        $query->orderBy($sort);

        $query->groupBy(implode(', ', $group));
    }

    /**
     * Получить результат поиска
     * @param array $queryParams массив из $_REQUEST
     * @return ActiveDataProvider
     */
    public function searchItems($queryParams = [])
    {
        $form = new PartSearch();

        $region = null;

        $form->load($queryParams);
        $form->validate();

        if ($form->r && $form->validate()) {
            // получить модель региона, в котором ищем
            $region = Region::find()->andWhere(['id' => (int) $form->r])->one();
        }

        $joinParams = !empty($form->q) || !empty($form->cat) || !empty($form->con);
        $joinContacts = ($form->st != '' && !is_null($form->st)) || $region instanceof Region;
        $joinPartners = (int) $form->st > 0;

        $query = $this->getSearchQuery($joinParams, $joinContacts, $joinPartners);

        if ($form->validate()) {
            // сформировать запрос по ключевому слову
            $this->makeKeywordQuery($query, $form->q);
            // сформировать запрос по автомобилям
            $this->makeAutoQuery($query, $form->a1, $form->a2, $form->a3, $form->a4);
            // сформировать запрос по категории
            $this->makeCategoryQuery($query, $form->cat);
            // сформировать запрос по состоянию
            $this->makeConditionQuery($query, $form->con);
            // запрос по региону
            $this->makeRegionQuery($query, $region, (boolean) $form->sor);
            // запрос по типу продавца
            $this->makeSellerQuery($query, $form->st);
            // запрос по цене
            $this->makePriceQuery($query, $form->p1, $form->p2);
        }

        // своя сортировка
        $this->makeSort($query, $region, (boolean) $form->sor);

//        echo $query->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql;
//        exit();
        return new ActiveDataProvider([
            'query' => $query,
        ]);
    }

    /**
     * Возвращает дерево автомобилей, привязанных к объявлению.
     * Структура возвращаемого массива:
     * ['mark' => [1 => ['name' => ..., 'parent' => null, 'parent_id' => null, 'query' => ['mark' => 1]], ..],
     * ['model' => [33 => ['name' => ..., 'parent' => 'mark', 'parent_id' => 1, 'query' => ['mark' => 1, 'model' => 33], ...]],
     * [...]
     * @param Part $advert
     * @return array
     */
    protected function getAdvertAutomobileTree(Part $advert)
    {
        $r = ArrayHelper::map(array_merge(
            $advert->mark, $advert->model,
            $advert->serie, $advert->modification
        ), 'id', function($data) {
            /* @var $data ActiveRecord */
            $queryParam = strtolower(StringHelper::basename($data->className()));
            $parentKey = null;
            $parent = null;
            switch ($queryParam) {
                case 'model':
                    $parentKey = $data->mark_id;
                    $parent = 'mark';
                    break;
                case 'serie':
                    $parentKey = $data->model_id;
                    $parent = 'model';
                    break;
                case 'modification':
                    $parentKey = $data->serie_id;
                    $parent = 'serie';
                    break;
            }
            return [
                'query' => [$queryParam => $data->id],
                'name' => $data->full_name,
                'parent_id' => $parentKey,
                'parent' => $parent,
            ];
        }, function($data) {
            /* @var $data ActiveRecord */
            return strtolower(StringHelper::basename($data->className()));
        });

        foreach ($r as $k1 => $items) {
            foreach ($items as $k2 => $i) {
                if (isset($i['parent'], $i['parent_id']) && isset($r[$i['parent']][$i['parent_id']])) {
                    $parent = $r[$i['parent']][$i['parent_id']];
                    $r[$k1][$k2]['query'] = ArrayHelper::merge($parent['query'], $i['query']);
                    $r[$i['parent']][$i['parent_id']]['remove'] = true;
                }
            }
        }

        foreach ($r as $k1 => $items) {
            foreach ($items as $k2 => $i) {
                if (!empty($i['remove'])) {
                    unset ($r[$k1][$k2]);
                }
            }
        }

        return $r;
    }

    /**
     * Возвращает массив ссылок на поиск по автомобилям, привязанным к объявлению.
     *
     * @param array путь к поиску
     * @param Part $advert
     * @return array
     */
    public function getAutomobilesLink($route, Part $advert)
    {
        $result = [];

        $data = $this->getAdvertAutomobileTree($advert);

        $searchModel = new PartSearch();
        foreach ($data as $k1 => $items) {
            foreach ($items as $k2 => $i) {
                $r = $route;
                foreach ($i['query'] as $key => $value) {
                    $key = Html::getInputName($searchModel, $searchModel->getAutoParam($key));
                    $r[$key] = $value;
                }
                $result[] = Html::a(Html::encode($i['name']), Url::toRoute($r));
            }
        }

        return $result;
    }

    /**
     * По идентификатору возвращает опубликованно объявление.
     *
     * @param integer $id
     */
    public function getDetails($id)
    {
        return Part::findActiveAndPublished()->andWhere(['id' => (int) $id])->one();
    }

    /**
     * Для объявления $advert возвращает похожие объявления.
     * Сравнение объявлений:
     * - по автомобилям;
     * - по категории.
     *
     * @param Part $advert объявление, для которого требуется получить похожие
     * @param int $limit ограничение на вывод
     * @return ActiveDataProvider
     */
    public function getRelated(Part $advert, $limit = 4)
    {
        $partParam = $advert->partParam;
        $query = $this->getSearchQuery($partParam instanceof PartParam, false);;

        $query->andWhere(['<>', Advert::tableName() . '.id', $advert->id]);

        // сформировать запрос по автомобилям
        $this->makeAutoQuery($query,
            $advert->getMarks(),
            $advert->getModels(),
            $advert->getSeries(),
            $advert->getModifications()
        );

        // сформировать запрос по категории
        if ($partParam instanceof PartParam) {
            $this->makeCategoryQuery($query, $partParam->category_id);
        }

        $query->groupBy(Advert::tableName() . '.id');
        $query->orderBy(Advert::tableName() . '.published DESC');

        // установить лимиты
        $query->limit($limit)->offset(0);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
            'sort' => false,
        ]);
    }

    /**
     * Получить массив марок, привязаных к объявлениям
     * @return Mark[]
     */
    public function getDistinctMark()
    {
        return Mark::find()
            ->select('mark.*')
            ->leftJoin(Part::TABLE_MARK . ' advert_mark', 'mark.id = advert_mark.mark_id')
            ->groupBy('mark.id')
            ->orderBy('mark.name ASC')
            ->all();
    }
}