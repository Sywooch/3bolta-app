/**
 * Общие скрипты
 */

// анимация дивов до потомков
jQuery.fn.scrollTo = function(elem, speed) {
    $(this).animate({
        scrollTop:  $(this).scrollTop() - $(this).offset().top + $(elem).offset().top
    }, speed == undefined ? 1000 : speed);
    return this;
};

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

    /**
     * переключение мобильного меню
     */
    $('.sidebar-toggle').on('click', function(e) {
        $('#wrapper').toggleClass('toggled');
    });

    /**
     * включение и выключение верхнего поиска
     */
    $('.js-top-search-toggle').click(function(e) {
        e.preventDefault();
        if (!$(this).is('.js-expand')) {
            $(this).addClass('js-expand');
            $(this).find('i').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
            $('.js-top-search').show();
        }
        else {
            $(this).removeClass('js-expand');
            $(this).find('i').addClass('glyphicon-chevron-down').removeClass('glyphicon-chevron-up');
            $('.js-top-search').hide();
        }
        $(document).trigger('top-search-toggled');
    });
});
