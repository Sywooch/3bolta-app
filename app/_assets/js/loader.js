/**
 * Лоадер
 */

// добавить лоадер к элементу to
document.appendLoader = function(to) {
    if ($('.loader').length) {
        return;
    }
    var tagName = $(to).length ? $(to).get(0).tagName.toLowerCase() : '';
    var html = '<div class="loader"></div>';
    if (tagName == 'form' && $(to).parents('.modal-content').length) {
        // если форма в модальном окне
        $(to).parents('.modal-content:first').prepend(html);
    }
    else if (tagName == 'form' || !tagName) {
        // если просто форма или напрямую в боди
        $('body').prepend(html);
    }
    else if (tagName) {
        $(to).prepend(html);
    }
};

// удалить лоадеры
document.removeLoader = function() {
    var $loader = $('.loader');
    $loader.remove();
};

// смена локации документа
document.changeLocation = function(href) {
    document.appendLoader('body');
    location.href = href;
};

// перезагрузка страницы
document.reloadLocation = function() {
    document.appendLoader('body');
    location.reload();
};

$(document).ready(function() {
    // по завершению ajax лоадеры удаляем
    // навесить лоадеры на формы
    $('form').on('afterValidate', function(e) {
        $('.loader').remove();
    });
    $('form').on('submit', function(e) {
        document.removeLoader();
        document.appendLoader(this);
    });
});