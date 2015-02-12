<?php
/**
 * Вывод объявления в результате поиска
 */

use yii\helpers\Html;

/* @var $this \yii\base\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $model \advert\models\Advert */
?>
<div class="panel panel-default">
    <div class="panel-heading"><?=Html::encode($model->advert_name)?></div>
    <div class="panel-body">
        <?=Html::encode($model->description)?>
    </div>
</div>