/**
 * Поиск запчастей
 */

$(document).ready(function() {
    $('.js-extended-search').on('click', '.js-extended-search-toggle, .js-extended-search-toggled', function(e) {
        // схлопнуть/развернуть дополнительные параметры
        $('.js-extended-search').toggleClass('toggled');
        // TODO: отображение данных в схлопнутом виде
        e.preventDefault();
    });
});