<?php
/**
 * Ответить на вопрос по объявлению
 */

use advert\forms\AnswerForm;
use advert\models\Advert;
use app\widgets\Modal;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Advert */
/* @var $answerForm AnswerForm */

$question = $answerForm->getQuestion();
?>

<?php
$modal = Modal::begin([
    'id' => 'advertAnswer' . $model->id,
    'title' => Yii::t('frontend/advert', 'Reply a question for part'),
    'clientOptions' => [
        'show' => true,
    ],
]);
    print Html::beginTag('div', [
        'class' => 'alert alert-success js-success',
        'style' => 'display:none;'
    ]);
        print Yii::t('frontend/advert', 'Your reply is successfully sended to user.');
    print Html::endTag('div');
    print Html::beginTag('div', [
        'class' => 'alert alert-danger js-error',
        'style' => 'display:none;'
    ]);
        print Yii::t('frontend/advert', 'An error occurred while sending the reply.');
        print Html::tag('br');
        print Yii::t('frontend/advert', 'Please contact support.');
    print Html::endTag('div');
    $form = ActiveForm::begin([
        'id' => $answerForm->formName(),
        'action' => ['answer', 'id' => $model->id, 'hash' => $answerForm->getQuestion_uid()],
        'enableClientValidation' => true,
        'enableAjaxValidation' => true,
    ]);
    if ($question instanceof \advert\models\AdvertQuestion) {
        print '<strong>' . Yii::t('frontend/advert', 'User name') . ':</strong> ' . Html::encode($question->from_user_name) . '<br />';
        print '<strong>' . Yii::t('frontend/advert', 'Question') . ':</strong> <br />';
        print Html::tag('div', Html::encode($question->question), [
            'style' => 'width:100%;height:50px;overflow-y:scroll;'
        ]);
    }
    print $form->field($answerForm, 'answer')->textarea([
        'cols' => 50,
        'rows' => 5,
    ]);
    print Html::submitButton(Yii::t('frontend/advert', 'Reply a question'), ['class' => 'btn btn-primary']);
    ActiveForm::end();
Modal::end();

app\widgets\JS::begin();
?>
<script type="text/javascript">
    $(document).ready(function() {
        new advertAnswerForm('advertAnswer<?=$model->id?>');
    });
</script>
<?php
app\widgets\JS::end();
?>