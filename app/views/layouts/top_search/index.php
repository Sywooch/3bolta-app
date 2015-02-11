<?php
/**
 * Поиск в верхней части
 */
/* @var $this yii\web\View */

print $this->render('_choose_auto');
?>

<p>
    <?=Yii::t('frontend/advert', 'Part for')?>:
    <a href="#" class="top-search-choose-auto-button" data-toggle="modal" data-target="#topSearchChooseAuto"><?=Yii::t('frontend/advert', 'Choose automobile...')?></a>
</p>
