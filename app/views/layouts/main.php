<?php
/* @var $this View */
/* @var $content string */

use advert\widgets\TopSearch;
use app\assets\FrontendAssets;
use app\widgets\ServiceMessage;
use user\widgets\LoginModal;
use user\widgets\LostPasswordModal;
use user\widgets\UserPanel;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Menu;

FrontendAssets::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>

<?php $this->beginBody() ?>
    <div class="wrap page-wrap" id="wrapper">
        <div class="logo-head">
            <div class="logo"><a href="/">&nbsp;&nbsp;</a></div>
            <div class="slogan"><i>Автозапчасти на одном сайте</i></div>
            <div class="logo-buttons">
                <div class="top-logo-search">
                    <a href="<?=Url::toRoute(['/advert/catalog/search'])?>" id="toggleTopSearch" class="top-logo-search-btn"><span class="glyphicon glyphicon-search"></span></a>
                </div>
                <div class="top-logo-add-advert">
                    <a href="<?=Url::toRoute(['/advert/advert/append'])?>" class="btn btn-primary btn-top-add-advert">
                        <span class="glyphicon glyphicon-plus"></span>
                        <span class="text">&nbsp;&nbsp;<?=Yii::t('frontend/menu', 'Append advert')?></span>
                    </a>
                </div>
                <div class="top-logo-menu">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle sidebar-toggle">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        NavBar::begin([
            'brandLabel' => false,
            'brandUrl' => '',
            'brandOptions' => [
                'tag' => Url::to() === Yii::$app->homeUrl ? 'span' : 'a',
            ],
            'options' => [
                'class' => 'navbar-default main-navbar',
            ],
            'renderInnerContainer' => false,
        ]);
        print Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-left'],
            'items' => [
                ['label' => Yii::t('frontend/menu', 'Parts catalog'), 'url' => ['/advert/catalog/search']],
                ['label' => Yii::t('frontend/menu', 'About project'), 'url' => '#'],
            ],
        ]);
        ?>
        <div class="pull-right"><?=UserPanel::widget()?></div>
        <?php
        NavBar::end();
        if (Yii::$app->controller->route == 'advert/catalog/search') {
            print TopSearch::widget();
        }
        ?>
        <div class="container content-container" id="page-content-wrapper">
            <?= $content ?>
        </div>
        <div id="sidebar-wrapper">
            <div class="mobile-top-user col-lg-12">
                <?=UserPanel::widget()?>
            </div>
            <?php
            print Nav::widget([
                'options' => ['class' => '', 'id' => ''],
                'items' => [
                    ['label' => Yii::t('frontend/menu', 'Search parts'), 'url' => ['/advert/catalog/search']],
                    ['label' => Yii::t('frontend/menu', 'Append advert'), 'url' => ['/advert/advert/append']],
                    ['label' => Yii::t('frontend/menu', 'About project'), 'url' => '#'],
                ],
            ]);
            ?>
        </div>
    </div>

    <footer class="footer page-wrap">
        <div class="logo">
            <a href="/"></a>
        </div>
        <p class="pull-left">&copy; <?=Yii::$app->params['siteName']?> <?= date('Y') ?></p>
        <?=Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-right bottom-menu'],
            'items' => [
                ['label' => Yii::t('frontend/menu', 'Search parts'), 'url' => ['/advert/catalog/search']],
                ['label' => Yii::t('frontend/menu', 'Append advert'), 'url' => ['/advert/advert/append']],
                ['label' => Yii::t('frontend/menu', 'About project'), 'url' => '#'],
            ],
        ])?>
    </footer>
    <?=LoginModal::widget()?>
    <?=LostPasswordModal::widget()?>
    <?=ServiceMessage::widget()?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
