<?php
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
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
                'options' => [
                    'class' => 'navbar-default main-navbar',
                ],
                'renderInnerContainer' => false,
            ]);
            echo Nav::widget([
                'options' => ['class' => 'navbar-nav navbar-right'],
                'items' => [
                    ['label' => '', 'linkOptions' => [
                        'class' => 'glyphicon glyphicon-search',
                        'id' => 'toggleTopSearch'
                    ], 'url' => '#'],
                    ['label' => Yii::t('frontend/menu', 'Parts catalogue'), 'url' => '#'],
                    ['label' => Yii::t('frontend/menu', 'About project'), 'url' => '#'],
                ],
            ]);
            NavBar::end();
            ?>
            <div class="panel panel-default" id="topSearch">
                <div class="panel-body">
                    <?=$this->render('top_search/index')?>
                </div>
            </div>

        <div class="container">
            <?= Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]) ?>
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
