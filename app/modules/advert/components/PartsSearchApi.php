<?php
namespace advert\components;

use advert\forms\PartSearch;
use advert\models\Advert;
use advert\models\Part;
use advert\models\PartIndex;
use advert\models\PartParam;
use auto\models\Mark;
use geo\components\GeoApi;
use geo\models\Region;
use sammaye\solr\SolrDataProvider;
use Solarium\QueryType\Select\Query\Query as SelectQuery;
use Yii;
use yii\base\Component;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
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
     * Добавить к существующему запросу новое условие.
     * Новое условие формируется из частей $pieces, склеиваемых оператором $piecesOperator (OR или AND).
     * Добавляется условие к существующему по логике $operator (OR или AND)
     *
     * @param SelectQuery $select
     * @param array $pieces
     * @param string $piecesOperator
     * @param string $operator
     */
    protected function appendQuery(SelectQuery $select, $pieces, $piecesOperator = 'OR', $operator = 'AND')
    {
        $operator = strtoupper($operator);
        $piecesOperator = strtoupper($piecesOperator);

        $pieces = (array) $pieces;

        if ($operator != 'AND') {
            $operator = 'OR';
        }

        $query = $select->getQuery();
        if ($query == '*:*') {
            $query = '';
        }
        if (!empty($query)) {
            $query .= " $operator ";
        }
        $query .= '(' . implode(" $piecesOperator ", $pieces) . ')';
        $select->setQuery($query);
    }

    /**
     * Сформировать запрос по ключевому слову
     *
     * @param SelectQuery $select
     * @param string $q
     */
    protected function makeKeywordQuery(SelectQuery $select, $q)
    {
        if ($q) {
            $term = str_replace('*', '', $q);
            $phrase = '*' . $term . '*';
            $pieces = [];
            $pieces[] = 'name:' . $select->getHelper()->escapePhrase($phrase);
            $pieces[] = 'catalogue_number:' . $select->getHelper()->escapeTerm($term);
            $this->appendQuery($select, $pieces, 'OR');
        }
    }

    /**
     * Cформировать запрос по автомобилям
     *
     * @param SelectQuery $select
     * @param mixed $mark массив идентификаторов или идентификатор
     * @param mixed $model массив идентификаторов или идентификатор
     * @param mixed $serie массив идентификаторов или идентификатор
     * @param mixed $modification массив идентификаторов или идентификатор
     * @param boolean $any искать совпадение по любому автомобилю
     */
    protected function makeAutoQuery(SelectQuery $select, $mark, $model, $serie, $modification, $any = false)
    {
        $pieces = [];
        $or = [$any ? 'or' : 'and'];
        if ($mark) {
            $mark = (array) $mark;
            $pieces[] = 'mark_id:(' . implode(' OR ', $mark) . ')';
        }
        if ($model) {
            $model = (array) $model;
            $pieces[] = 'model_id:(' . implode(' OR ', $model) . ')';
        }
        if ($serie) {
            $serie = (array) $serie;
            $pieces[] = 'serie_id:(' . implode(' OR ', $serie) . ')';
        }
        if ($modification) {
            $modification = (array) $modification;
            $pieces[] = 'modification_id:(' . implode(' OR ', $modification) . ')';
        }
        if (!empty($pieces)) {
            $this->appendQuery($select, $pieces, $any ? 'OR' : 'AND', 'AND');
        }
    }

    /**
     * Сформировать запрос по категории
     * @param SelectQuery $select
     * @param int $category
     */
    protected function makeCategoryQuery(SelectQuery $select, $category)
    {
        if ($category) {
            $this->appendQuery($select, 'category_id:' . (int) $category);
        }
    }

    /**
     * Сформировать запрос по состоянию
     * @param SelectQuery $select
     * @param int $condition
     */
    protected function makeConditionQuery(SelectQuery $select, $condition)
    {
        if ($condition) {
            $this->appendQuery($select, 'condition_id:' . (int) $condition);
        }
    }

    /**
     * Установить запрос по региону (только если не установлен параметр "Искать в других регионах")
     *
     * @param SelectQuery $select
     * @param Region|null $region модель региона или null
     * @param boolean $searchOtherRegion
     */
    protected function makeRegionQuery(SelectQuery $select, $region, $searchOtherRegion)
    {
        if (!$searchOtherRegion && $region instanceof Region) {
            $this->appendQuery($select, 'region_id:' . (int) $region->id);
        }
    }

    /**
     * Установить запрос по цене (от и до)
     *
     * @param SelectQuery $select
     * @param float $priceFrom
     * @param float $priceTo
     */
    protected function makePriceQuery(SelectQuery $select, $priceFrom, $priceTo)
    {
        $priceFromQuery = '*';
        $priceToQuery = '*';

        if ((float) $priceFrom) {
            $priceFromQuery = (float) $priceFrom;
        }
        if ((float) $priceTo > 0 && (float) $priceTo > (float) $priceFrom) {
            $priceToQuery = (float) $priceTo;
        }

        if ($priceFromQuery != '*' || $priceToQuery != '*') {
            $this->appendQuery($select, 'price:[' . $priceFromQuery . ' TO ' . $priceToQuery . ']');
        }
    }

    /**
     * Запрос по типу продавца:
     * - пустая строка - нет поиска по продавцу;
     * - 0 - всегда частное лицо;
     * - остальные цифры - это всегда тип юр. лица.
     *
     * @param SelectQuery $select
     * @param mixed $sellerType
     */
    protected function makeSellerQuery(SelectQuery $select, $sellerType)
    {
        if ($sellerType == '' || is_null($sellerType)) {
            // отсутствует запрос по типу продавца
            return;
        }

        $this->appendQuery($select, 'seller_type:' . (int) $sellerType);
    }

    /**
     * Установить сортировку.
     * Если выбран регион и поиск в других регионах, значит сортирует результат по ближайшим регионам.
     *
     * @param SelectQuery $select
     * @param Region|null $region модель региона или null
     * @param boolean $searchOtherRegion
     */
    public function makeSort(SelectQuery $select, $region, $searchOtherRegion)
    {
        $sort = [];
        if ($searchOtherRegion && $region instanceof Region) {
            // искать в других регионах, значит сортируем по ближайшим регионам
            /* @var $region Region */
            /* @var $geoApi GeoApi */
            $geoApi = Yii::$app->getModule('geo')->api;
            // сортировка по регионам
            $regionIds = $geoApi->getNearestRegionsIds($region);
            if (!empty($regionIds)) {
                foreach ($regionIds as $regionId) {
                    $sort["map(region_id,$regionId,$regionId,1,0)"] = 'desc';
                }
            }
        }
        // сортировка по дате
        $sort['published_from'] = 'desc';
        $select->setSorts($sort);
    }

    /**
     * Создать базовый запрос в Solr по активным на данный момент объявлениям
     *
     * @return SelectQuery
     */
    public function createSelectQuery()
    {
        /* @var $partsIndex PartsIndex */
        $partsIndex = \Yii::$app->getModule('advert')->partsIndex;
        /* @var $select SelectQuery */
        $select = $partsIndex->createSelect();
        $select->setQuery('active:1 AND published_to:[' . date('Y-m-d') . 'T' . date('H:i:s') . 'Z TO *]');
        return $select;
    }

    /**
     * Получить результат поиска
     * @param array $queryParams массив из $_REQUEST
     * @return SolrDataProvider
     */
    public function searchItems($queryParams = [])
    {
        $form = new PartSearch();

        /* @var $geoApi GeoApi */
        $geoApi = \Yii::$app->getModule('geo')->api;

        $region = $geoApi->getUserRegion(true);

        /* @var $partsIndex PartsIndex */
        $partsIndex = \Yii::$app->getModule('advert')->partsIndex;
        /* @var $select SelectQuery */
        $select = $this->createSelectQuery();

        if ($form->load($queryParams) && $form->validate()) {
            if ($form->r) {
                // указан регион
                $region = Region::find()->andWhere(['id' => (int) $form->r])->one();
            }
            // сформировать запрос по ключевому слову
            $this->makeKeywordQuery($select, $form->q);
            // сформировать запрос по автомобилям
            $this->makeAutoQuery($select, $form->a1, $form->a2, $form->a3, $form->a4);
            // сформировать запрос по категории
            $this->makeCategoryQuery($select, $form->cat);
            // сформировать запрос по состоянию
            $this->makeConditionQuery($select, $form->con);
            // запрос по региону
            $this->makeRegionQuery($select, $region, (boolean) $form->sor);
            // запрос по типу продавца
            $this->makeSellerQuery($select, $form->st);
            // запрос по цене
            $this->makePriceQuery($select, $form->p1, $form->p2);
            // сортировка
            $this->makeSort($select, $region, (boolean) $form->sor);
        }
        else {
            // обычная сортировка по дате и по региону по умолчанию
            $this->makeSort($select, $region, true);
        }

        return new SolrDataProvider([
            'query' => $select,
            'solr' => $partsIndex->solr,
            'modelClass' => PartIndex::className(),
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
    public function getAdvertAutomobileTree(Part $advert)
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
     * @param array $data дерево автомобилей, результат работы метода getAdvertAutomobileTree
     * @return array
     */
    public function getAutomobilesLink($route, $data)
    {
        $result = [];

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
     * Для объявления $advert возвращает похожие объявления из индекса Solr.
     * Сравнение объявлений:
     * - по автомобилям;
     * - по категории.
     *
     * @param Part $advert объявление, для которого требуется получить похожие
     * @param int $limit ограничение на вывод
     * @return SolrDataProvider
     */
    public function getRelated(Part $advert, $limit = 4)
    {
        /* @var $partsIndex PartsIndex */
        $partsIndex = \Yii::$app->getModule('advert')->partsIndex;
        /* @var $select SelectQuery */
        $select = $this->createSelectQuery();

        $partParam = $advert->partParam;

        // сформировать запрос по автомобилям
        $this->makeAutoQuery($select,
            $advert->getMarks(),
            $advert->getModels(),
            $advert->getSeries(),
            $advert->getModifications()
        );

        // сформировать запрос по категории
        if ($partParam instanceof PartParam) {
            $this->makeCategoryQuery($select, $partParam->category_id);
        }

        // установить лимиты
        $select->setStart(0)->setRows($limit);

        return new SolrDataProvider([
            'modelClass' => PartIndex::className(),
            'query' => $select,
            'pagination' => false,
            'sort' => false,
            'solr' => $partsIndex->solr,
        ]);
    }

    /**
     * Получить последние добавленные объявления
     *
     * @param integer $limit ограничение на выдаваемое количество объявлений
     * @return SolrDataProvider
     */
    public function getLastAdverts($limit)
    {
        /* @var $partsIndex PartsIndex */
        $partsIndex = \Yii::$app->getModule('advert')->partsIndex;
        /* @var $select SelectQuery */
        $select = $this->createSelectQuery();

        $select->setSorts(['published_from' => 'desc']);
        $select->setStart(0)->setRows($limit);

        return new SolrDataProvider([
            'modelClass' => PartIndex::className(),
            'query' => $select,
            'pagination' => false,
            'sort' => false,
            'solr' => $partsIndex->solr,
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