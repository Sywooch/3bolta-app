<?php

use app\widgets\JS;
use geo\assets\UserRegion;
use geo\models\Region;
use yii\helpers\Html;
use yii\helpers\Url;
/**
 * Виджет ссылки для выбора региона пользователя
 */

/* @var $userRegion Region */
/* @var $needToSetRegion boolean */

print Html::a(
    $userRegion instanceof Region ?
        $userRegion->site_name :
        Yii::t('frontend/geo', 'Select your region...'),
    '#', [
        'class' => 'js-data-region',
        'data-region-id' => $userRegion instanceof Region ? $userRegion->id : null,
    ]
);

if ($needToSetRegion):
    UserRegion::register($this);
?>
    <?php JS::begin(['key' => 'detect-user-region']); ?>
    <script type="text/javascript">
        $(document).ready(function() {
            DetectUserRegion({
                'linkSelector' : '.js-data-region',
                'getNearestRegionUrl' : '<?=Url::toRoute(['/geo/geo/detect-user-region'])?>'
            });
        });
    </script>
    <?php JS::end(); ?>
<?php endif;?>