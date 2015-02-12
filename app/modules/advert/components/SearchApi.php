<?php
namespace advert\components;

use Yii;
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
                ['like', 'name', $q],
                ['like', 'description', $q],
            ]);
        }
    }

    /**
     * Cформировать запрос по автомобилям
     * @param ActiveQuery $query
     * @param int $mark
     * @param int $model
     * @param int $serie
     * @param int $modification
     */
    protected function makeAutoQuery(ActiveQuery $query, $mark, $model, $serie, $modification)
    {
        $or = ['or'];
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

        return new ActiveDataProvider([
            'query' => $query,
        ]);
    }
}