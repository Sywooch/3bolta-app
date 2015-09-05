<?php
namespace advert\widgets;

use advert\models\Part;

use advert\assets\AdvertList;

/**
 * Виджет последних объявлений (выводится на главной странице)
 */
class LastPartAdverts extends \yii\bootstrap\Widget
{
    /**
     * @var int количество выводимых объявлений
     */
    public $limit = 6;

    public function run()
    {
        // получить последние опубликованные объявления
        $lastAdverts = Part::findActiveAndPublished()->orderBy('published DESC')->limit($this->limit)->all();

        if (!empty($lastAdverts)) {
            AdvertList::register($this->view);
            return $this->render('last-part-adverts', [
                'list' => $lastAdverts
            ]);
        }

        return '';
    }
}