<?php
namespace advert\components;

use yii\helpers\StringHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Html;
use advert\forms\Search;
use yii\data\ActiveDataProvider;
use advert\models\Advert;
use yii\db\ActiveQuery;

/**
 * API для поиска запчастей
 */
class SearchApi extends \yii\base\Component
{
    /**
     * Сформировать запрос по ключевому слову
     * @param ActiveQuery $query
     */
    protected function makeKeywordQuery(ActiveQuery $query, $q)
    {
        if ($q) {
            $query->andFilterWhere(['or',
                ['like', 'advert.advert_name', $q],
                ['like', 'advert.description', $q],
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
            $query->andWhere(['advert.category_id' => $category]);
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
     * Получить результат поиска
     * @param [] $queryParams массив из $_REQUEST
     * @return \yii\data\ActiveDataProvider
     */
    public function searchItems($queryParams = [])
    {
        $form = new Search();

        $query = Advert::findActiveAndPublished();

        if ($form->load($queryParams) && $form->validate()) {
            // сформировать запрос по ключевому слову
            $this->makeKeywordQuery($query, $form->q);
            // сформировать запрос по автомобилям
            $this->makeAutoQuery($query, $form->a1, $form->a2, $form->a3, $form->a4);
            // сформировать запрос по категории
            $this->makeCategoryQuery($query, $form->cat);
            // сформировать запрос по состоянию
            $this->makeConditionQuery($query, $form->con);
        }

        // своя сортировка
        $query->groupBy('advert.id');
        $query->orderBy('advert.published DESC');

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
     * @param Advert $advert
     * @return []
     */
    protected function getAdvertAutomobileTree(Advert $advert)
    {
        $r = ArrayHelper::map(array_merge(
            $advert->mark, $advert->model,
            $advert->serie, $advert->modification
        ), 'id', function($data) {
            /* @var $data \yii\db\ActiveRecord */
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
            /* @var $data \yii\db\ActiveRecord */
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
     * @param Advert $advert
     * @return []
     */
    public function getAutomobilesLink($route, Advert $advert)
    {
        $result = [];

        $data = $this->getAdvertAutomobileTree($advert);

        $searchModel = new Search();
        foreach ($data as $k1 => $items) {
            foreach ($items as $k2 => $i) {
                $r = $route;
                foreach ($i['query'] as $key => $value) {
                    switch ($key) {
                        case 'mark':
                            $key = 'a1';
                            break;
                        case 'model':
                            $key = 'a2';
                            break;
                        case 'serie':
                            $key = 'a3';
                            break;
                        case 'modification':
                            $key = 'a4';
                            break;
                    }
                    $key = Html::getInputName($searchModel, $key);
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
        return Advert::findActiveAndPublished()->andWhere(['id' => $id])->one();
    }

    /**
     * Для объявления $advert возвращает похожие объявления.
     * Сравнение объявлений:
     * - по автомобилям;
     * - по категории.
     *
     * @param Advert $advert объявление, для которого требуется получить похожие
     * @param int $limit ограничение на вывод
     * @return ActiveDataProvider
     */
    public function getRelated(Advert $advert, $limit = 4)
    {
        $query = Advert::findActiveAndPublished();

        $query->andWhere(['<>', 'advert.id', $advert->id]);

        // сформировать запрос по автомобилям
        $this->makeAutoQuery($query,
            $advert->getMarks(),
            $advert->getModels(),
            $advert->getSeries(),
            $advert->getModifications()
        );

        // сформировать запрос по категории
        $this->makeCategoryQuery($query, $advert->category_id);

        $query->groupBy('advert.id');
        $query->orderBy('advert.published DESC');

        // установить лимиты
        $query->limit($limit)->offset(0);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
            'sort' => false,
        ]);
    }
}