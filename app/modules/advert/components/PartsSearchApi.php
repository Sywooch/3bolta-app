<?php
namespace advert\components;

use advert\forms\PartSearch;
use advert\models\PartAdvert;
use auto\models\Mark;
use geo\components\GeoApi;
use geo\models\Region;
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
     * Сформировать запрос по ключевому слову
     * @param ActiveQuery $query
     */
    protected function makeKeywordQuery(ActiveQuery $query, $q)
    {
        if ($q) {
            $query->andFilterWhere(['or',
                ['like', 'partadvert.advert_name', $q],
                ['like', 'partadvert.description', $q],
            ]);
        }
    }

    /**
     * Cформировать запрос по автомобилям
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
            $query->andWhere(['partadvert.category_id' => $category]);
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
            $query->andWhere(['condition_id' => $condition]);
        }
    }

    /**
     * Установить запрос по региону (только если не установлен параметр "Искать в других регионах")
     *
     * @param ActiveQuery $query
     * @param PartSearch $form
     */
    protected function makeRegionQuery(ActiveQuery $query, PartSearch $form)
    {
        if ($form->r && !$form->sor) {
            /* @var $region Region */
            $region = Region::find()->andWhere(['id' => (int) $form->r])->one();
            if ($region instanceof Region) {
                $query->andWhere(['partadvert.region_id' => $region->id]);
            }
        }
    }

    /**
     * Установить запрос по цене (от и до)
     *
     * @param ActiveQuery $query
     * @param PartSearch $form
     */
    protected function makePriceQuery(ActiveQuery $query, PartSearch $form)
    {
        $priceFrom = (float) $form->p1;
        $priceTo = (float) $form->p2;

        if ($priceFrom > 0) {
            $query->andWhere(['>=', 'partadvert.price', $priceFrom]);
        }

        if ($priceTo > 0 && $priceTo >= $priceFrom) {
            $query->andWhere(['<=', 'partadvert.price', $priceTo]);
        }
    }

    /**
     * Запрос по типу продавца:
     * - пустая строка - нет поиска по продавцу;
     * - 0 - всегда частное лицо;
     * - остальные цифры - это всегда тип юр. лица.
     *
     * @param ActiveQuery $query
     * @param PartSearch $form
     */
    protected function makeSellerQuery(ActiveQuery $query, PartSearch $form)
    {
        if ($form->st != '' && !is_null($form->st)) {
            $st = (int) $form->st;

            if ($st == 0) {
                // частное лицо, не должно быть привязки к торговой точке
                $query->andWhere(['trade_point_id' => null]);
            }
            else {
                // в остальном - это тип продавца
                $query->andWhere(['not', ['trade_point_id' => null]]);
                $query->joinWith(['tradePoint', 'tradePoint.partner'], true, 'INNER JOIN');
                $query->andWhere([
                    \partner\models\Partner::tableName() . '.company_type' => $st
                ]);
            }
        }
    }

    /**
     * Установить сортировку
     *
     * @param ActiveQuery $query
     * @param PartSearch $form
     */
    public function makeSort(ActiveQuery $query, PartSearch $form)
    {
        $sort = [];
        if ($form->sor && $form->r) {
            // искать в других регионах, значит сортируем по ближайшим регионам
            /* @var $geoApi GeoApi */
            $geoApi = Yii::$app->getModule('geo')->api;
            /* @var $region Region */
            $region = Region::find()->andWhere(['id' => (int) $form->r])->one();
            if ($region instanceof Region) {
                // сортировка по регионам
                $regionIds = $geoApi->getNearestRegionsIds($region);
                if (!empty($regionIds)) {
                    $s = 'CASE partadvert.region_id ' . "\n";
                    $x = 0;
                    foreach ($regionIds as $regionId) {
                        $s .= 'WHEN ' . $regionId . ' THEN ' . ++$x;
                    }
                    $s .= 'END' . "\n";
                    $sort[] = new Expression($s);
                }
            }
        }

        // сортировка по дате
        $sort[] = new Expression('partadvert.published DESC');

        $query->orderBy($sort);
    }

    /**
     * Получить результат поиска
     * @param array $queryParams массив из $_REQUEST
     * @return ActiveDataProvider
     */
    public function searchItems($queryParams = [])
    {
        $form = new PartSearch();

        $query = PartAdvert::findActiveAndPublished();

        if ($form->load($queryParams) && $form->validate()) {
            // сформировать запрос по ключевому слову
            $this->makeKeywordQuery($query, $form->q);
            // сформировать запрос по автомобилям
            $this->makeAutoQuery($query, $form->a1, $form->a2, $form->a3, $form->a4);
            // сформировать запрос по категории
            $this->makeCategoryQuery($query, $form->cat);
            // сформировать запрос по состоянию
            $this->makeConditionQuery($query, $form->con);
            // запрос по региону
            $this->makeRegionQuery($query, $form);
            // запрос по типу продавца
            $this->makeSellerQuery($query, $form);
            // запрос по цене
            $this->makePriceQuery($query, $form);
        }

        // своя сортировка
        $query->groupBy('partadvert.id');
        $this->makeSort($query, $form);

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
     * @param PartAdvert $advert
     * @return []
     */
    protected function getAdvertAutomobileTree(PartAdvert $advert)
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
     * @param [] путь к поиску
     * @param PartAdvert $advert
     * @return []
     */
    public function getAutomobilesLink($route, PartAdvert $advert)
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
     * @param type $id
     */
    public function getDetails($id)
    {
        return PartAdvert::findActiveAndPublished()->andWhere(['id' => $id])->one();
    }

    /**
     * Для объявления $advert возвращает похожие объявления.
     * Сравнение объявлений:
     * - по автомобилям;
     * - по категории.
     *
     * @param PartAdvert $advert объявление, для которого требуется получить похожие
     * @param int $limit ограничение на вывод
     * @return ActiveDataProvider
     */
    public function getRelated(PartAdvert $advert, $limit = 4)
    {
        $query = PartAdvert::findActiveAndPublished();

        $query->andWhere(['<>', 'partadvert.id', $advert->id]);

        // сформировать запрос по автомобилям
        $this->makeAutoQuery($query,
            $advert->getMarks(),
            $advert->getModels(),
            $advert->getSeries(),
            $advert->getModifications()
        );

        // сформировать запрос по категории
        $this->makeCategoryQuery($query, $advert->category_id);

        $query->groupBy('partadvert.id');
        $query->orderBy('partadvert.published DESC');

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
            ->leftJoin(PartAdvert::TABLE_MARK . ' advert_mark', 'mark.id = advert_mark.mark_id')
            ->groupBy('mark.id')
            ->orderBy('mark.name ASC')
            ->all();
    }
}