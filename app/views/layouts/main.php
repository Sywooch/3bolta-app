<?php
/* @var $this View */
/* @var $content string */

use app\assets\FrontendAssets;
use app\widgets\ServiceMessage;
use geo\widgets\SelectRegionModal;
use geo\widgets\UserRegion;
use user\widgets\LoginModal;
use user\widgets\LostPasswordModal;
use user\widgets\UserPanel;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

FrontendAssets::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport" />
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>

<?php $this->beginBody() ?>
    <div class="wrap page-wrap" id="wrapper">
        <div class="logo-head">
            <div class="logo"><a href="/">&nbsp;&nbsp;</a></div>
            <?=UserRegion::widget()?>
            <div class="logo-buttons">
                <div class="top-logo-button">
                    <a href="<?=Url::toRoute(['/advert/part-catalog/search'])?>" id="toggleTopSearch" class="btn btn-primary">
                        <span class="glyphicon glyphicon-search"></span>
                        <span class="text">&nbsp;&nbsp;<?=Yii::t('frontend/menu', 'Search parts')?></span>
                    </a>
                </div>
                <div class="top-logo-button">
                    <a href="<?=Url::toRoute(['/advert/part-advert/append'])?>" class="btn btn-primary">
                        <span class="glyphicon glyphicon-plus"></span>
                        <span class="text">&nbsp;&nbsp;<?=Yii::t('frontend/menu', 'Place an advert')?></span>
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
                ['label' => Yii::t('frontend/menu', 'Parts catalog'), 'url' => ['/advert/part-catalog/search']],
                ['label' => Yii::t('frontend/menu', 'Organizations catalog'), 'url' => ['/partner/search/index']],
                ['label' => Yii::t('frontend/menu', 'About project'), 'url' => '#'],
            ],
        ]);
        ?>
        <div class="pull-right"><?=UserPanel::widget()?></div>
        <?php
        NavBar::end();
        ?>
        <div class="container content-container" id="page-content-wrapper">
            <?= $content ?>
        </div>
        <div id="sidebar-wrapper">
            <div class="mobile-region col-lg-12">
                <?=UserRegion::widget()?>
            </div>
            <div class="mobile-top-user col-lg-12">
                <?=UserPanel::widget()?>
            </div>
            <?php
            print Nav::widget([
                'options' => ['class' => '', 'id' => ''],
                'items' => [
                    ['label' => Yii::t('frontend/menu', 'Search parts'), 'url' => ['/advert/part-catalog/search']],
                    ['label' => Yii::t('frontend/menu', 'Search organization'), 'url' => ['/partner/search/index']],
                    ['label' => Yii::t('frontend/menu', 'Place an advert'), 'url' => ['/advert/part-advert/append']],
                    ['label' => Yii::t('frontend/menu', 'About project'), 'url' => '#'],
                ],
            ]);
            ?>
        </div>
    </div>

    <footer class="footer page-wrap">
        <p class="pull-left">&copy; <?=Yii::$app->params['siteName']?> <?= date('Y') ?></p>
        <?=Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-right bottom-menu'],
            'items' => [
                ['label' => Yii::t('frontend/menu', 'Search parts'), 'url' => ['/advert/part-catalog/search']],
                ['label' => Yii::t('frontend/menu', 'Search organization'), 'url' => ['/partner/search/index']],
                ['label' => Yii::t('frontend/menu', 'Place an advert'), 'url' => ['/advert/part-advert/append']],
                ['label' => Yii::t('frontend/menu', 'About project'), 'url' => '#'],
            ],
        ])?>
    </footer>
    <?=LoginModal::widget()?>
    <?=LostPasswordModal::widget()?>
    <?=ServiceMessage::widget()?>
    <?=SelectRegionModal::widget()?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
