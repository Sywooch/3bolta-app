/**
 * Общие скрипты
 */
$(document).ready(function() {
    /**
     * Кастомный селект
     */
    $('select').on('rendered.bs.select', function(e) {
        var id = $(this).attr('id');
        var $button = $(this).siblings('div.btn-group').find('.dropdown-toggle[data-id="' + id + '"]');
        if ($button.find('span').text() == $(this).attr('placeholder')) {
            $button.addClass('selected-default');
        }
        else {
            $button.removeClass('selected-default');
        }
    });
    $('select').each(function() {
        var options = {};
        if ($(this).attr('placeholder')) {
            options.noneSelectedText = $(this).attr('placeholder');
        }
        $(this).selectpicker(options);
        $(this).trigger('rendered.bs.select');
    });

    /**
     * Подгрузка модальных бутстрап-окон в AJAX
     */
    $('.load-modal-ajax').on('show.bs.modal', function(e) {
        var href = $(this).data('ajax-url');
        $(this).find('.modal-body').load(href);
    });
    $('.load-modal-ajax').on('hide.bs.modal', function(e) {
        $(this).find('.modal-body').html('');
    });
});
