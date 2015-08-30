/**
 * Форма отправки ответа на вопрос по объявлению
 * @param {String} modalId идентификатор модального окна
 * @param {String} captchaParam нвазвание инпута, в котором передается капча
 * @param {String} captchaValue значение captcha
 */
var advertAnswerForm = function(modalId) {
    var $modal = $('#' + modalId);
    var $form = $modal.find('form:first');

    $form.on('beforeSubmit', function(e) {
        e.preventDefault();
        $.ajax({
            'type'          : 'post',
            'url'           : $(this).attr('action'),
            'data'          : $(this).serialize(),
            'dataType'      : 'json',
            'success'       : function(d) {
                document.removeLoader();
                if (d.success) {
                    $form.remove();
                    $modal.find('.js-success').show();
                }
                else {
                    $form.remove();
                    $modal.find('.js-error').show();
                }
            }
        });
        return false;
    });
};
