<?php
/**
 * Панель пользователя в шапке сайта
 */

use yii\helpers\Url;
use yii\helpers\Html;
use user\models\User;

/* @var $user \user\models\User */
?>
<div class="user-panel">
    <?php if ($user instanceof User):?>
        <span class="caption"><?=Yii::t('frontend/user', 'You enter as:')?></span>
        <div class="user-dropdown-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                <i class="glyphicon glyphicon-user"></i>
                <?=Html::encode($user->name)?>
                (<?=Html::encode($user->email)?>)
                <i class="caret"></i>
            </a>
            <ul class="dropdown-menu" role="menu">
                <li><a href="<?=Url::toRoute(['/user/profile/index'])?>"><?=Yii::t('frontend/user', 'Profile')?></a></li>
                <li class="divider"></li>
                <?php if ($user->type == User::TYPE_LEGAL_PERSON):?>
                    <li><a href="<?=Url::toRoute(['/partner/partner/index'])?>"><?=Yii::t('frontend/partner', 'Company data')?></a></li>
                    <li class="divider"></li>
                <?php endif;?>
                <li><a href="<?=Url::toRoute(['/advert/user-advert/list'])?>"><?=Yii::t('frontend/user', 'My adverts')?></a></li>
                <li><a href="<?=Url::toRoute(['/advert/user-advert/append'])?>"><?=Yii::t('frontend/advert', 'Append advert')?></a></li>
                <li class="divider"></li>
                <li><a href="<?=Url::toRoute(['/user/user/logout'])?>"><?=Yii::t('frontend/user', 'Exit')?></a></li>
            </ul>
        </div>
    <?php else:?>
        <a href="<?=Url::toRoute(['/user/user/register'])?>"><?=Yii::t('frontend/user', 'Registration')?></a>
        /
        <a href="#" data-dismiss="modal" data-toggle="modal" data-target="#loginModal"><?=Yii::t('frontend/user', 'Enter')?></a>
    <?php endif; ?>
</div>