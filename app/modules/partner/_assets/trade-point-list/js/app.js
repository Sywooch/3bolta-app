/**
 * Скрипты на странице списка торговых точек пользователя
 */

// объект сгенерированной карты
document.tradePointMap = null;
// список торговых точек
document.tradePoints = [];

// генерация карты
document.onLoadTradePointMap = function(map) {
    document.tradePointMap = map;

    var bounds = new google.maps.LatLngBounds();

    // поместить торговые точки на карту
    $(document.tradePoints).each(function(k, tradePoint) {
        var latLng = new google.maps.LatLng(tradePoint.latitude, tradePoint.longitude);
        var marker = new google.maps.Marker({
            'position'          : latLng,
            'map'               : map
        });
        bounds.extend(latLng);
        var content = '<p><b>Адрес: </b> ' + tradePoint.address + '<br />';
        content += '<b>Телефон: </b>' + tradePoint.phone + '<br /></p>';
        content += '<b>';
        content += '<a href="#" data-toggle="modal" data-target="#tradePointModal' + tradePoint.id + '" class="btn btn-sm btn-success">';
        content += '<span class="glyphicon glyphicon-pencil">';
        content += '</span>&nbsp;Редактировать</a></b>&nbsp;&nbsp;'
        content += '<b><a href="' + tradePoint.removeUrl + '" data-confirm="Вы уверены, что хотите удалить торговую точку?" data-method="post" class="btn btn-sm btn-warning"><span class="glyphicon glyphicon-remove">';
        content += '</span>&nbsp;Удалить</a></b>'
        var infowindow = new google.maps.InfoWindow({
            'content'           : content
        });
        marker.remove = function() {
            google.maps.event.clearInstanceListeners(this);
            this.setMap(null);
        };
        google.maps.event.addListener(marker, 'click', function() {
            infowindow.open(map,marker);
        });
        map.setCenter(latLng);
    });

    if (document.tradePoints.length > 1) {
        document.tradePointMap.fitBounds(bounds);
    }
};
$(document).ready(function() {
});