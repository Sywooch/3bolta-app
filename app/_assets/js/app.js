/**
 * Общие скрипты
 */
$(document).ready(function() {
    $('#toggleTopSearch').click(function(e) {
        e.preventDefault();
        $('#topSearchWrap').slideToggle();
        $(this).parents('li:first').toggleClass('active');
    });
});
