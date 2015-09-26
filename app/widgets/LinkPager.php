<?php
namespace app\widgets;

use Yii;
use yii\helpers\Html;
use yii\widgets\LinkPager as LinkPagerBase;

/**
 * Обертка для постраничной навигации
 */
class LinkPager extends LinkPagerBase
{
    /**
     * @var boolean показывать только следующую и предыдущую кнопку
     */
    public $renderOnlyNextPrev = true;

    /**
     * @var string|boolean будет переопределено автоматиески в инит
     */
    public $prevPageLabel = false;

    /**
     * @var string|boolean будет переопределено автоматиески в инит
     */
    public $nextPageLabel = false;

    /**
     * Инициализация
     */
    public function init()
    {
        parent::init();

        if ($this->renderOnlyNextPrev && !$this->prevPageLabel) {
            $this->prevPageLabel = Yii::t('frontend/main', '&laquo; Preview page');
        }

        if ($this->renderOnlyNextPrev && !$this->nextPageLabel) {
            $this->nextPageLabel = Yii::t('frontend/main', 'Next page &raquo;');
        }
    }

    /**
     * @inheritdoc
     */
    protected function renderPageButtons()
    {
        if (!$this->renderOnlyNextPrev) {
            return parent::renderPageButtons();
        }

        $pageCount = $this->pagination->getPageCount();
        if ($pageCount < 2 && $this->hideOnSinglePage) {
            return '';
        }

        $buttons = [];
        $currentPage = $this->pagination->getPage();

        // prev page
        if ($this->prevPageLabel !== false) {
            if (($page = $currentPage - 1) < 0) {
                $page = 0;
            }
            $buttons[] = $this->renderPageButton($this->prevPageLabel, $page, $this->prevPageCssClass, $currentPage <= 0, false);
        }

        // next page
        if ($this->nextPageLabel !== false) {
            if (($page = $currentPage + 1) >= $pageCount - 1) {
                $page = $pageCount - 1;
            }
            $buttons[] = $this->renderPageButton($this->nextPageLabel, $page, $this->nextPageCssClass, $currentPage >= $pageCount - 1, false);
        }

        return Html::tag('ul', implode("\n", $buttons), $this->options);
    }
}