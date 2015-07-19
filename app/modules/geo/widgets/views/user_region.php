<?php
/**
 * Виджет ссылки для выбора региона пользователя
 */

use app\widgets\JS;
use app\widgets\Modal;
use geo\assets\UserRegion;
use geo\forms\SelectRegion;
use geo\models\Region;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;

UserRegion::register($this);

/* @var $selectRegion SelectRegion */
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
            'data-target' => '#select-region-modal',
            'data-region-id' => $userRegion instanceof Region ? $userRegion->id : null,
        ]
    );
print Html::endTag('div');

Modal::begin([
    'id' => 'select-region-modal',
    'title' => Yii::t('frontend/geo', 'Select your region...'),
]);
    $form = ActiveForm::begin([
        'options' => ['class' => 'js-select-region-form'],
        'action' => Url::toRoute(['/geo/geo/select-region']),
        'enableClientValidation' => false,
        'enableAjaxValidation' => false,
    ]);
        print $form->field($selectRegion, 'regionId', [
            'template' => '{input}{hint}{error}',
        ])->dropDownList($selectRegion->getRegionDropDown(), [
            'class' => 'form-control js-select-region-dropdown',
            'data-live-search' => 'true',
        ]);
        print Html::submitButton(Yii::t('frontend/geo', 'Select'), [
            'class' => 'btn btn-primary',
        ]);
    ActiveForm::end();
Modal::end();

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