/**
 * Скрипты в форме редактирования торговой точки
 */
$(document).ready(function() {
    /**
     * Переключение телефона
     */
    $('#trade-point-form input[type="checkbox"]').change(function() {
        $('.js-trade-point-phone').toggle();
    });

    /**
     * Субмит формы
     */
    $('#trade-point-form').off('beforeSubmit').on('beforeSubmit', function(e) {
        e.preventDefault();

        $(this).find('.js-trade-point-error').hide();
        $.ajax({
            'url'       : $(this).attr('action'),
            'type'      : 'post',
            'dataType'  : 'json',
            'data'      : $(this).serialize(),
            'success'   : function(d) {
                if (d.success) {
                    document.reloadLocation();
                }
                else {
                    $(this).find('.js-trade-point-error').show();
                }
            }
        });
        return false;
    });
});