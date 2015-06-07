<?php
namespace advert\widgets;

use advert\models\Advert;

use advert\assets\AdvertList;

/**
 * Виджет последних объявлений (выводится на главной странице)
 */
class LastAdverts extends \yii\bootstrap\Widget
{
    /**
     * @var int количество выводимых объявлений
     */
    public $limit = 6;

    public function run()
    {
        // получить последние опубликованные объявления
        $lastAdverts = Advert::findActiveAndPublished()->orderBy('published DESC')->limit($this->limit)->all();

        if (!empty($lastAdverts)) {
            AdvertList::register($this->view);
            return $this->render('last-adverts', [
                'list' => $lastAdverts
            ]);
        }

        return '';
    }
}