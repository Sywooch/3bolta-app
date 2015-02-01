<?php
/* @var $this yii\web\View */
/* @var $model \app\modules\storage\forms\UploadFile */
/* @var $form yii\bootstrap\ActiveForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Tabs;
$this->title = Yii::t('backend', 'Create {modelClass}', [
    'modelClass' => Yii::t('backend/storage', 'File'),
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'File list'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-form">

    <?php
    $form = ActiveForm::begin([
        'options' => [
            'enctype' => 'multipart/form-data',
        ],
    ]);
    print $form->field($model, 'storage')->dropDownList(Yii::$app->getModule('storage')->repository);
    print $form->field($model, 'file')->fileInput();
    ?>

        <div class="form-group">
            <?=Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-success', 'name' => 'save'])?>
        </div>
    <?php ActiveForm::end(); ?>
</div>
