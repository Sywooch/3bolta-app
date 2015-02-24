<?php
use user\widgets\UserPanel;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use user\widgets\LoginModal;
use app\assets\FrontendAssets;
use advert\widgets\TopSearch;
/* @var $this \yii\web\View */
/* @var $content string */

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
    <div class="wrap">
        <?php
        NavBar::begin([
            'brandLabel' => Yii::$app->params['siteName'],
            'brandUrl' => Yii::$app->homeUrl,
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
                ['label' => Yii::t('frontend/menu', 'Search parts'), 'url' => ['/advert/catalog/search']],
                ['label' => Yii::t('frontend/menu', 'Append advert'), 'url' => ['/advert/advert/append']],
                ['label' => Yii::t('frontend/menu', 'About project'), 'url' => '#'],
                [
                    'label' => '',
                    'linkOptions' => [
                        'class' => 'glyphicon glyphicon-search',
                        'id' => 'toggleTopSearch',
                        'style' => Url::to() === Url::toRoute(['/advert/catalog/search']) ? 'display:none;' : '',
                    ],
                    'url' => '#',
                ]
            ],
        ]);
        print UserPanel::widget();
        NavBar::end();
        print TopSearch::widget();
        ?>
        <div class="container content-container">
            <?= $content ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p class="pull-left">&copy; <?=Yii::$app->params['siteName']?> <?= date('Y') ?></p>
            <p class="pull-right"><?= Yii::powered() ?></p>
        </div>
    </footer>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
