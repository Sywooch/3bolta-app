<?php
namespace advert\widgets;

use advert\assets\AdvertList;
use advert\components\PartsSearchApi;
use advert\models\PartIndex;
use sammaye\solr\SolrDataProvider;
use Yii;
use yii\bootstrap\Widget;

/**
 * Виджет последних объявлений (выводится на главной странице)
 */
class LastPartAdverts extends Widget
{
    /**
     * @var int количество выводимых объявлений
     */
    public $limit = 6;

    public function run()
    {
        // получить последние опубликованные объявления
        /* @var $searchApi PartsSearchApi */
        $searchApi = Yii::$app->getModule('advert')->partsSearch;
        /* @var $dataProvider SolrDataProvider */
        $dataProvider = $searchApi->getLastAdverts($this->limit);
        /* @var $lastAdverts PartIndex[] */
        $lastAdverts = $dataProvider->getModels();

        if (!empty($lastAdverts)) {
            AdvertList::register($this->view);
            return $this->render('last-part-adverts', [
                'list' => $lastAdverts
            ]);
        }

        return '';
    }
}