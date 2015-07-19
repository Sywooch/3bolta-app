<?php
/**
 * Виджет ссылки для выбора региона пользователя
 */

use app\widgets\JS;
use geo\models\Region;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $userRegion Region */
/* @var $needToSetRegion boolean */


print Html::beginTag('div', ['class' => 'region']);
    print Html::tag('i', '', ['class' => 'icon icon-location']);
    print Html::a(
        $userRegion instanceof Region ?
            $userRegion->site_name :
            Yii::t('frontend/geo', 'Select your region...'),
        '#', [
            'class' => 'js-selected-region',
            'data-dismiss' => 'modal',
            'data-toggle' => 'modal',
            'data-target' => '#selectRegionModal',
            'data-region-id' => $userRegion instanceof Region ? $userRegion->id : null,
        ]
    );
print Html::endTag('div');

if ($needToSetRegion):
?>
    <?php JS::begin(['key' => 'detect-user-region']); ?>
    <script type="text/javascript">
        $(document).ready(function() {
            DetectUserRegion({
                'getNearestRegionUrl' : '<?=Url::toRoute(['/geo/geo/detect-user-region'])?>'
            });
        });
    </script>
    <?php JS::end(); ?>
<?php endif;?>