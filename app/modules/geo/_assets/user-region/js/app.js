/**
 * Определение и установка региона пользователя
 * Параметры:
 * - getNearestRegionUrl - url, по которому можно найти ближайший регион по широте и долготе;
 * - linkSelector - ссылка, в которую запишется идентификатор и название выбранного региона.
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
                    $(params.linkSelector)
                        .data('data-region-id', d.id)
                        .text(d.name);
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