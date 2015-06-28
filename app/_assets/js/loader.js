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

// смена локации документа
document.changeLocation = function(href) {
    $('body').appendLoader();
    location.href = href;
};

// перезагрузка страницы
document.reloadLocation = function() {
    $('body').appendLoader();
    location.href = href;
};

$(document).ready(function() {
    // по завершению ajax лоадеры удаляем
    // навесить лоадеры на формы
    $(document).ajaxComplete(function() {
        $('.loader').remove();
    });
    $('form').on('submit', function(e) {
        document.appendLoader(this);
    });
    $('form').on('beforeSubmit', function(e) {
        document.appendLoader(this);
    });
});