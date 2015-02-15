/**
   * Детальная страница объявления
    */
$(document).ready(function() {
    // переключение картинок
    $('.item-details-images-list').on('click', '.thumbnail', function(e) {
        e.preventDefault();
        $('.item-details-images-list a.thumbnail').removeClass('active');
        $(this).addClass('active');
        $('.item-details-images-full img').attr('src', $(this).attr('href'));
    }); 
});
