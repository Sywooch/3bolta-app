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
