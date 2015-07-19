/**
 * Выбор региона пользователя
 * @param {Integer} id
 * @param {String} name
 */
document.chooseUserRegion = function(id, name) {
    $('.js-selected-region')
        .data('data-region-id', id)
        .text(name);
    $('.js-select-region-dropdown').val(id);
};

/**
 * Определение и установка региона пользователя
 * Параметры:
 * - getNearestRegionUrl - url, по которому можно найти ближайший регион по широте и долготе;
 */
var DetectUserRegion = function(params) {
    /**
     * Получить регион по широте и долготе
     * @param {float} lat
     * @param {float} lng
     */
    var getUserRegion = function(lat, lng) {
        var data = {
            'lat' : lat,
            'lng' : lng
        };
        data[yii.getCsrfParam()] = yii.getCsrfToken();
        $.ajax({
            'type'          : 'post',
            'dataType'      : 'json',
            'url'           : params.getNearestRegionUrl,
            'data'          : data,
            'success'       : function(d) {
                if (d.id && d.name) {
                    document.chooseUserRegion(d.id, d.name);
                }
            }
        });
    };

    if (google.loader.ClientLocation) {
        // определение с помощью google JS API
        getUserRegion(google.loader.ClientLocation.latitude, google.loader.ClientLocation.longitude);
    }
    else if (navigator.geolocation) {
        // определение с помощью Geolocation API
        navigator.geolocation.getCurrentPosition(function(pos) {
            if (pos.coords.latitude && pos.coords.longitude) {
                getUserRegion(pos.coords.latitude, pos.coords.longitude);
            }
        });
    }
};

$(document).ready(function() {
    // выбор региона вручную в модальном окне
    $('.js-select-region-form').on('beforeSubmit', function(e) {
        $.ajax({
            'type'      : 'post',
            'dataType'  : 'json',
            'url'       : $(this).attr('action'),
            'data'      : $(this).serialize(),
            'success'   : function(d) {
                if (d.success) {
                    document.chooseUserRegion(d.id, d.name);
                    document.reloadLocation();
                }
            }
        });
        return false;
    });
});