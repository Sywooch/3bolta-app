<?php
namespace advert\widgets;

use Yii;

use advert\forms\PartSearch as Form;

/**
 * Виджет поиска по автозапчастям
 */
class PartSearch extends \yii\bootstrap\Widget
{
    /**
     * @var Form форма поиска
     */
    public $model;

    public function getViewPath()
    {
        return parent::getViewPath() . DIRECTORY_SEPARATOR . 'part-search';
    }

    public function init()
    {
        parent::init();
        if (!($this->model instanceof PartForm)) {
            $this->model = new Form();
            if ($this->model->load(Yii::$app->request->getQueryParams())) {
                $this->model->validate();
            }
        }
    }

    public function run()
    {
        return $this->render('index', [
            'model' => $this->model,
        ]);
    }
}