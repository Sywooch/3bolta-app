/**
 * Скрипты на странице списка объявлений
 */
$(document).ready(function() {
    $('.list-item').on('click', function(e) {
        e.preventDefault();

        document.changeLocation($(this).find('a:first').attr('href'));
    });
});
