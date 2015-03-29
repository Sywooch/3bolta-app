/**
 * Выбор местоположения - виджет.
 * Виджет запоминает координаты при вводе адреса в инпут.
 */
(function($) {
    $.fn.selectLocation = function(options) {
        var self = this;
        var map;

        google.maps.event.addDomListener(window, 'load', function() {
            var mapOptions = {
                center: new google.maps.LatLng(55.997778, 37.190278),
                zoom: 12,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                panControl: true
            };
            map = new google.maps.Map($(self).get(0), mapOptions);

            // маркер найденной точки
            var marker = null;

            /**
             * Создать маркер на карте
             * Передается объект типа google.maps.LatLng
             * @param {Object} latLng
             */
            var createMarker = function(latLng) {
                // удалить маркер если уже был
                if (marker) {
                    marker.remove();
                }
                marker = marker = new google.maps.Marker({
                    'position'          : latLng,
                    'map'               : map
                });
                marker.remove = function() {
                    google.maps.event.clearInstanceListeners(this);
                    this.setMap(null);
                };
            };

            /**
             * Установить координаты точки
             * @param {Object} point объект типа google.maps.LatLng
             */
            var setLatLngAttributes = function(point) {
                if (typeof(options.setLatitude) == 'function') {
                    options.setLatitude(point.lat());
                }
                else {
                    $(options.setLatitude).val(point.lat());
                }
                if (typeof(options.setLongitude) == 'function') {
                    options.setLongitude(point.lng());
                }
                else {
                    $(options.setLongitude).val(point.lng());
                }
            };

            /**
             * Выбрать местоположение, на входе объект у которго есть geometry
             * @param {Object} item
             */
            var selectLocation = function(item) {
                if (!item.geometry) {
                    return;
                }
                var bounds = item.geometry.viewport ? item.geometry.viewport : item.geometry.bounds;
                var center = null;
                if (bounds) {
                    map.fitBounds(new google.maps.LatLngBounds(bounds.getSouthWest(), bounds.getNorthEast()));
                }
                if (item.geometry.location) {
                    center = item.geometry.location;
                }
                else if (bounds) {
                    var lat = bounds.getSouthWest().lat() + ((bounds.getNorthEast().lat() - bounds.getSouthWest().lat()) / 2);
                    var lng = bounds.getSouthWest().lng() + ((bounds.getNorthEast().lng() - bounds.getSouthWest().lng()) / 2);
                    center = new google.maps.LatLng(lat, lng);
                }
                if (center) {
                    map.setCenter(center);
                    createMarker(center);
                    setLatLngAttributes(center);
                }
            };

            // автокомплит для поиска местонахождения
            var autocomplete = new google.maps.places.Autocomplete($(options.address).get(0));

            google.maps.event.addListener(autocomplete, 'place_changed', function() {
                var place = autocomplete.getPlace();
                if (!place) {
                    return;
                }
                selectLocation(place);
            });

            var defaults = {
                'lat'       : typeof(options.getLatitude) == 'function' ? options.getLatitude() : $(options.getLatitude).val(),
                'lng'       : typeof(options.getLongitude) == 'function' ? options.getLongitude() : $(options.getLongitude).val()
            };
            if (defaults.lat && defaults.lng) {
                var center = new google.maps.LatLng(defaults.lat, defaults.lng);
                map.setCenter(center);
                createMarker(center);
                setLatLngAttributes(center);
            }
        });
    };
})(jQuery);