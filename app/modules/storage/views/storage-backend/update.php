<?php
/* @var $this yii\web\View */
/* @var $model \app\modules\storage\model\File */
/* @var $form yii\bootstrap\ActiveForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Tabs;
use yii\widgets\DetailView;
$this->title = Yii::t('backend', 'Update {modelClass}', [
    'modelClass' => Yii::t('backend/storage', 'File'),
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'File list'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-form">
    <?php
    print DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id', 'repository', 'real_name',
            'file_path' => [
                'attribute' => 'file_path',
                'value' => Html::a(
                    Html::encode($model->file_path),
                    $model->getUrl()
                ),
                'format' => 'html',
            ],
            'mime_type', 'uploader_addr', 'size',
            'is_image', 'width', 'height',
        ]
    ]);
    ?>
</div>
