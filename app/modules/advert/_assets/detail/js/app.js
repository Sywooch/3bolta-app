/**
   * Детальная страница объявления
    */
$(document).ready(function() {
    // переключение картинок
    $('.js-item-image-list').on('click', 'a', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        $(this).addClass('active').parents('div:first').siblings('div').find('a').removeClass('active');
        document.appendLoader('.js-item-image-full');
        var img = new Image();
        img.onload = function() {
            document.removeLoader();
            $('.js-item-image-full img').attr('src', href);
        };
        img.src = href;
    });
    $('.js-item-image-full .prev, .js-item-image-full .next').on('click', function(e) {
        var index = -1;
        var $links = $('.js-item-image-list a');
        var iter = $(this).is('.prev') ? -1 : 1;
        $links.each(function(n) {
            if ($(this).is('.active')) {
                index = n + iter;
            }
        });
        if (index >= $links.length) {
            index = 0;
        }
        else if (index < 0) {
            index = $links.length - 1;
        }
        $links.eq(index).trigger('click');
    });
});

/**
 * Форма отправки e-mail
 * @param {String} modalId идентификатор модального окна
 * @param {String} captchaParam нвазвание инпута, в котором передается капча
 * @param {String} captchaValue значение captcha
 */
var advertQuestionForm = function(modalId, captchaParam, captchaValue) {
    var $modal = $('#' + modalId);
    var $form = $modal.find('form:first');
    $form.prepend('<input type="hidden" name="' + captchaParam + '" value="' + captchaValue + '" />');

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
