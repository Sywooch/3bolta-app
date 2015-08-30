<?php
/**
 * Задать вопрос по объявлению
 */

use advert\forms\QuestionForm;
use advert\models\PartAdvert;
use app\widgets\Modal;
use yii\helpers\Html;
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
    print Html::beginTag('div', [
        'class' => 'alert alert-success js-success',
        'style' => 'display:none;'
    ]);
        print Yii::t('frontend/advert', 'Your message is successfully sended to advert owner on a e-mail.');
        print Html::tag('br');
        print Yii::t('frontend/advert', 'You will take the answer to your question on your e-mail address.');
    print Html::endTag('div');
    print Html::beginTag('div', [
        'class' => 'alert alert-danger js-error',
        'style' => 'display:none;'
    ]);
        print Yii::t('frontend/advert', 'An error occurred while sending the message.');
        print Html::tag('br');
        print Yii::t('frontend/advert', 'Please contact support.');
    print Html::endTag('div');
    $form = ActiveForm::begin([
        'id' => $questionForm->formName(),
        'action' => ['question', 'id' => $model->id],
        'enableClientValidation' => false,
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
    print Html::submitButton(Yii::t('frontend/advert', 'Ask a question'), ['class' => 'btn btn-primary']);
    ActiveForm::end();
Modal::end();

app\widgets\JS::begin();
?>
<script type="text/javascript">
    $(document).ready(function() {
        new advertQuestionForm(
            'advertMessage<?=$model->id?>',
            '<?=Html::getInputName($questionForm, 'captcha')?>',
            '<?=\Yii::$app->request->getCsrfToken()?>'
        );
    });
</script>
<?php
app\widgets\JS::end();
?>