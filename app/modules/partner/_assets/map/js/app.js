/**
 * Скрипты на странице карты торговых точек
 */

document.tradePointMapParams = {
    'mapMarkerSvg' : null,
    'mapMarkerUnactiveSvg' : null,
    'mapMarkerPng' : null,
    'mapMarkerUnactivePng' : null
};

$(document).ready(function() {
    // враппер карты
    var $mapWrapper = $('.js-trade-point-map');
    // форма
    var $form = $('.js-trade-point-map-form');
    // поле для ввода адреса
    var $addressInput = $('.js-trade-point-address');
    // поле для ввода координат
    var $coordinatesInput = $('.js-trade-point-map-coordinates');

    // массив сгенерированных торговых точек (они же - маркеры на карте)
    var tradePoints = [];

    // получить иконку в зависимости от условия активности
    var getMarkerIcon = function(getActive) {
        var markerIcon = getActive ? document.tradePointMapParams['mapMarkerSvg'] : document.tradePointMapParams['mapMarkerUnactiveSvg'];
        if (!!navigator.userAgent.match(/Trident.*rv\:11\./)) {
            markerIcon = getActive ? document.tradePointMapParams['mapMarkerPng'] : document.tradePointMapParams['mapMarkerUnactivePng'];
        }
        return markerIcon;
    };

    // определить высоту враппера
    var resizeMapWrapper = function() {
        $mapWrapper.height($('footer').offset().top - $mapWrapper.offset().top);
    };
    resizeMapWrapper();
    $(window).resize(resizeMapWrapper);

    // сгенерировать карту
    var mapOptions = {
        center: new google.maps.LatLng(55.997778, 37.190278),
        zoom: 12,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        panControl: true
    };
    var map = new google.maps.Map($mapWrapper.get(0), mapOptions);

    // сгенерировать автокомплит по адресу
    var addressAutocomplete = new google.maps.places.Autocomplete($addressInput.get(0));
    // смена местоположения
    google.maps.event.addListener(addressAutocomplete, 'place_changed', function() {
        var place = addressAutocomplete.getPlace();
        if (!place) {
            return;
        }
        var bounds = null;
        if (place.geometry.viewport) {
            bounds = place.geometry.viewport;
        }
        else if (place.geometry.bounds) {
            bounds = place.geometry.bounds;
        }
        else if (place.geometry.location) {
            map.setCenter(place.geometry.location);
        }
        if (bounds) {
            map.fitBounds(new google.maps.LatLngBounds(bounds.getSouthWest(), bounds.getNorthEast()));
        }
    });

    // при изменении точек карты - отправляем форму
    google.maps.event.addListener(map, 'idle', function() {
        var bounds = map.getBounds();
        $coordinatesInput.val(JSON.stringify({
            'sw' : {
                'lat' : bounds.getSouthWest().lat(),
                'lng' : bounds.getSouthWest().lng()
            },
            'ne' : {
                'lat' : bounds.getNorthEast().lat(),
                'lng' : bounds.getNorthEast().lng()
            }
        }));
        $form.submit();
    });

    // выбор имени торговой точки
    $('.js-trade-point-map-name').on('input', function() {
        $form.submit();
    })
    $('.js-trade-point-map-name').on('autocompleteselect', function() {
        $form.submit();
    });

    // выбор марки
    $('.js-trade-point-map-mark').on('input', function() {
        $form.submit();
    })
    $('.js-trade-point-map-mark').on('autocompleteselect', function() {
        $form.submit();
    });

    // очистить торговые точки на карте
    var clearTradePoints = function() {
        for (var i in tradePoints) {
            tradePoints[i].remove();
        }
        tradePoints.slice(0, 1);
    };

    // создать торговую точку на карте на основе данных data
    var createTradePoint = function(data) {
        var tradePoint = new google.maps.Marker({
            'position'          : new google.maps.LatLng(data.latitude, data.longitude),
            'icon'              : getMarkerIcon(data.active),
            'map'               : map
        });
        tradePoint.data = data;
        tradePoint.remove = function() {
            google.maps.event.clearInstanceListeners(this);
            this.setMap(null);
        };
        var content = '<h4>' + data.name + '</h4>';
        content += '<p><b>Адрес: </b> ' + data.address + '<br />';
        content += '<b>Телефон: </b>' + data.phone + '<br /></p>';
        var infowindow = new google.maps.InfoWindow({
            'content'           : content
        });
        tradePoint.infowindow = infowindow;
        google.maps.event.addListener(tradePoint, 'click', function() {
            infowindow.open(map, tradePoint);
        });
        tradePoints.push(tradePoint);
    };

    // поиск торговых точек
    var searchTradePointsLocked = false;
    var searchTradePoints = function() {
        if (searchTradePointsLocked) {
            clearTimeout(searchTradePointsLocked);
        }
        searchTradePointsLocked = setTimeout(function() {
            $.ajax({
                'type'      : 'post',
                'dataType'  : 'json',
                'data'      : $form.serialize() + '&' + yii.getCsrfParam() + '=' + yii.getCsrfToken(),
                'url'       : $form.attr('action'),
                'success'   : function(d) {
                    searchTradePointsLocked = false;
                    clearTradePoints();
                    if (d.items) {
                        for (var i in d.items) {
                            createTradePoint(d.items[i]);
                        }
                    }
                }
            });
        }, 500);
    };

    $('.js-trade-point-map-form').on('submit', function() {
        searchTradePoints();
        return false;
    });
});