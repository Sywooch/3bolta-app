/**
 * Скрипты на странице списка объявлений
 */
$(document).ready(function() {
    $('.list-item-internal').on('click', function(e) {
        e.preventDefault();

        location.href = $(this).find('a:first').attr('href');
    });
});