<?php
/**
 * Виджет выбора автомобиля
 */
use app\widgets\Modal;
use auto\widgets\ChooseAutomobile;
use app\widgets\JS;
use yii\web\JsExpression;
use yii\helpers\Html;

/* @var $form yii\bootstrap\ActiveForm */
/* @var $model advert\forms\PartSearch */
/* @var $this yii\base\View */
$modal = Modal::begin([
    'id' => 'topSearchChooseAuto',
    'title' => Yii::t('frontend/advert', 'Choose automobile'),
    'size' => Modal::SIZE_LARGE,
    'closeButton' => [
        'tag' => 'button',
        'class' => 'btn btn-success pull-right',
        'label' => Yii::t('frontend/advert', 'Choose'),
    ]
]);
$chooseAutomobile = ChooseAutomobile::begin([
    'panelClass' => 'col-xs-2 col-sm-2',
    'containerClass' => 'top-search-choose-auto',
    'pluginOptions' => [
        'multipleSelect' => false,
        'markName' => Html::getInputName($model, 'a1'),
        'modelName' => Html::getInputName($model, 'a2'),
        'serieName' => Html::getInputName($model, 'a3'),
        'modificationName' => Html::getInputName($model, 'a4'),
        'markIds' => [$model->a1],
        'modelIds' => [$model->a2],
        'serieIds' => [$model->a3],
        'modificationIds' => [$model->a4],
        'markWrapper' => '.choose-auto-mark',
        'modelWrapper' => '.choose-auto-model',
        'serieWrapper' => '.choose-auto-serie',
        'modificationWrapper' => '.choose-auto-modification',
        'renderItem' => new JsExpression('
            function(type, jsClass, selected, attributeName, id, name, full_name) {
                var obj = {
                    "type"          : type,
                    "jsClass"       : jsClass,
                    "active"       : selected ? "list-group-item-success" : "",
                    "attributeName" : attributeName,
                    "id"            : id,
                    "name"          : name,
                    "full_name"     : full_name
                };
                var tpl = \'<a href="#" class="list-group-item {$jsClass} {$active}" data-type="{$type}" data-id="{$id}" data-full-name="{$full_name}" data-attribute="{$attributeName}">{$name}</a>\';
                $.each(obj, function(k, i) {
                    tpl = tpl.replace(\'{$\' + k + \'}\', i);
                });
                return tpl;
            }
        '),
    ],
]);
    ?>
    <div style="display:none;">
        <?=$form->field($model, 'a1')->hiddenInput(['label' => ''])?>
        <?=$form->field($model, 'a2')->hiddenInput()?>
        <?=$form->field($model, 'a3')->hiddenInput()?>
        <?=$form->field($model, 'a4')->hiddenInput()?>
    </div>
    <div class="col-md-3 col-sm-12">
        <label clas="control-label"><?=Yii::t('advert', 'Choose mark')?></label>
        <div class="list-group choose-auto-mark"></div>
    </div>
    <div class="col-md-3 col-sm-12">
        <label clas="control-label"><?=Yii::t('advert', 'Choose model')?></label>
        <div class="list-group choose-auto-model"></div>
    </div>
    <div class="col-md-3 col-sm-12">
        <label clas="control-label"><?=Yii::t('advert', 'Choose serie')?></label>
        <div class="list-group choose-auto-serie"></div>
    </div>
    <div class="col-md-3 col-sm-12">
        <label clas="control-label"><?=Yii::t('advert', 'Choose modification')?></label>
        <div class="list-group choose-auto-modification"></div>
    </div>
<?php $chooseAutomobile->end(); ?>

<?php JS::begin(); ?>
<script type="text/javascript">
    $(function() {
        var chooser = $('#<?=$chooseAutomobile->id?>');

        var markName = '<?=$chooseAutomobile->pluginOptions['markName']?>';
        var modelName = '<?=$chooseAutomobile->pluginOptions['modelName']?>';
        var serieName = '<?=$chooseAutomobile->pluginOptions['serieName']?>';
        var modificationName = '<?=$chooseAutomobile->pluginOptions['modificationName']?>';

        // клик по автомобилю
        chooser.on('click', 'a.list-group-item', function(e) {
            e.preventDefault();

            if ($(this).is('.list-group-item-success')) {
                return;
            }

            $(this).addClass('list-group-item-success').siblings('a').removeClass('list-group-item-success');

            var type = $(this).data('type');
            var attribute = $(this).data('attribute');
            var id = $(this).data('id');
            if (type !== 'modification' && type) {
                var method = 'choose';
                method += type.charAt(0).toUpperCase() + type.substr(1, type.length - 1);
                chooser.chooseAutomobile(method, id);
            }
            $('.top-search-choose-auto-button').text($(this).data('full-name'));
            $('#topSearch input[name="' + attribute + '"]').val(id);

            if (['mark', 'model', 'serie'].indexOf(type) !== -1) {
                $('#topSearch input[name="' + modificationName + '"]').val('');
            }
            if (['mark', 'model'].indexOf(type) !== -1) {
                $('#topSearch input[name="' + serieName + '"]').val('');
            }
            if (type == 'mark') {
                $('#topSearch input[name="' + modelName + '"]').val('');
            }
        });
    });
</script>
<?php
JS::end();
$modal->end();
?>
<div class="form-group top-search-auto">
    <label class="control-label"><?=Yii::t('frontend/advert', 'Part for')?>:</label>
    <a href="#" class="toggle" data-toggle="modal" data-target="#topSearchChooseAuto"><?php if ($automobile = $model->getAutomobileFullName()):?><?=$automobile?><?php else:?><?=Yii::t('frontend/advert', 'Choose automobile...')?><?php endif;?></a>
</div>
