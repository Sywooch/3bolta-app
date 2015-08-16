<?php
/**
 * Задать вопрос по объявлению
 */

use advert\forms\QuestionForm;
use advert\models\PartAdvert;
use app\widgets\Modal;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model PartAdvert */
/* @var $questionForm QuestionForm */
?>

<a href="#" data-dismiss="modal" data-toggle="modal" data-target="#advertMessage<?=$model->id?>"><i class="icon icon-comment"></i><?=Yii::t('frontend/advert', 'Ask a question for part')?></a>

<?php
$modal = Modal::begin([
    'id' => 'advertMessage' . $model->id,
    'title' => Yii::t('frontend/advert', 'Ask a question for part'),
]);
    $form = ActiveForm::begin([
        'action' => ['question', 'id' => $model->id],
        'enableClientValidation' => true,
        'enableAjaxValidation' => true,
    ]);
    if (!$questionForm->getUser_id()) {
        print $form->field($questionForm, 'user_name');
        print $form->field($questionForm, 'user_email');
    }
    print $form->field($questionForm, 'question')->textarea([
        'cols' => 50,
        'rows' => 5,
    ]);
    ActiveForm::end();
Modal::end(); ?>