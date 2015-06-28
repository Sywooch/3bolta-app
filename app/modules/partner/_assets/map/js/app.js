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
    var $map = $('.js-trade-point-map');
    var $mapWrapper = $map.parents('div:first');
    // список
    var $list = $('.js-trade-point-list');
    // форма
    var $form = $('.js-trade-point-map-form');
    // поле для ввода адреса
    var $addressInput = $('.js-trade-point-address');
    // поле для ввода координат
    var $coordinatesInput = $('.js-trade-point-map-coordinates');
    // флаг, означающий, что список активирован
    var listEnabled = false;

    // шаблон для бабла
    var bubleTemplate = '<div class="trade-point-map-infowindow">';
    bubleTemplate += '<h4><%- name %></h4>';
    bubleTemplate += '<span class="location"><i class="icon-location"></i> <%- address %></span><br />';
    bubleTemplate += '<span class="phone"><i class="icon-phone"></i> <%- phone %></span>';
    bubleTemplate += '</div>';
    bubleTemplate = _.template(bubleTemplate);

    // шаблон для списка
    var listTemplate = '<div class="trade-point-list-item <%- itemClass %> js-trade-point-list-item" data-trade-point-id="<%- id %>">';
    listTemplate += '<div class="trade-point-list-item-unactive"></div>'
    listTemplate += '<h4><%- name %></h4>';
    listTemplate += '<span class="mark"><i class="icon-cab"></i> <% _.forEach(marks, function(mark) { %> <%- mark %> <% }); %></span>';
    listTemplate += '<span class="location"><i class="icon-location"></i> <%- address %></span><br />';
    listTemplate += '<span class="phone"><i class="icon-phone"></i> <%- phone %></span><br />';
    listTemplate += '</div>';
    listTemplate = _.template(listTemplate);

    // массив сгенерированных торговых точек (они же - маркеры на карте)
    var tradePoints = [];

    // активировать список
    var enableList = function() {
        $mapWrapper.addClass('toggled');
        listEnabled = $list.is(':visible');
    };

    // деактивировать список
    var disableList = function() {
        $mapWrapper.removeClass('toggled');
        listEnabled = false;
    };

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
    var map = new google.maps.Map($map.get(0), mapOptions);

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

    // очистить торговые точки на карте, которых нет в новой выборке
    var clearTradePoints = function(newIds) {
        for (var i in tradePoints) {
            var id = tradePoints[i].data.id;
            if ($.inArray(id, newIds) === -1) {
                tradePoints[i].remove();
            }
        }
        tradePoints.slice(0, 1);
    };

    // обновить торговые точки
    var renewTradePoints = function(newItems) {
        var newIds = [];
        var oldIds = [];
        for (var i in tradePoints) {
            oldIds.push(tradePoints[i].data.id);
        }
        // сортировать по активности
        newItems = _.sortByOrder(newItems, ['active'], [false], _.values);
        if (newItems) {
            for (var i in newItems) {
                newIds.push(newItems[i].id);
            }
        }
        clearTradePoints(newIds);
        for (var i in newItems) {
            if ($.inArray(newItems[i].id, oldIds) === -1) {
                createTradePoint(newItems[i]);
            }
        }
        // обновить список
        $list.empty();
        for (var i in tradePoints) {
            tradePoints[i].listItem = createListItem(tradePoints[i].data);
        }
    };

    // создать элемент списка
    var createListItem = function(data) {
        var $listItem = $(listTemplate(data));
        $list.append($listItem);
        $listItem.on('trade.point.listOpen', function(e, doNotScroll) {
            if (!doNotScroll) {
                $list.scrollTo(this);
            }
            $(this).addClass('active').siblings('.js-trade-point-list-item').removeClass('active');
        });
        return $listItem;
    };

    // создать бабл для карты на основе данных data для маркера tradePoint
    var createBuble = function(data, tradePoint) {
        var infowindow = new google.maps.InfoWindow({
            'content'           : bubleTemplate(data)
        });
        infowindow.tradePoint = tradePoint;
        infowindow.remove = function() {
            google.maps.event.clearInstanceListeners(infowindow);
            infowindow.setMap(null);
        };
        infowindow.hide = function() {
            this.tradePoint.setAnimation(null);
            this.close();
        };
        infowindow.show = function(doNotScroll) {
            this.tradePoint.setAnimation(google.maps.Animation.BOUNCE);
            if (!listEnabled) {
                // если список неактивирован - показываем бабл
                this.open(map, this.tradePoint);
            }
            else {
                // иначе - открываем в списке
                this.tradePoint.listItem.trigger('trade.point.listOpen', [doNotScroll]);
            }
        };
        google.maps.event.addListener(infowindow, 'closeclick', function() {
            this.hide();
        });
        return infowindow;
    };

    // создать торговую точку на карте на основе данных data
    var createTradePoint = function(data) {
        data.itemClass = data.active ? '' : 'disabled';
        var tradePoint = new google.maps.Marker({
            'position'          : new google.maps.LatLng(data.latitude, data.longitude),
            'icon'              : getMarkerIcon(data.active),
            'map'               : map
        });
        tradePoint.data = data;
        tradePoint.infowindow = createBuble(data, tradePoint);

        // удаление маркера
        tradePoint.remove = function() {
            this.infowindow.remove();
            google.maps.event.clearInstanceListeners(this);
            this.setMap(null);
        };

        // клик по маркеру
        google.maps.event.addListener(tradePoint, 'click', function() {
            $.each(tradePoints, function(k, v) {
                // close all infowindow
                v.infowindow.hide();
            });
            this.infowindow.show();
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
                    if (d.items) {
                        renewTradePoints(d.items);
                    }
                    if (tradePoints.length > 10) {
                        enableList();
                    }
                    else {
                        disableList();
                    }
                }
            });
        }, 500);
    };

    // открыть элемент списка
    $list.on('click', '.js-trade-point-list-item', function(e) {
        var id = $(this).data('trade-point-id');
        for (var i in tradePoints) {
            if (tradePoints[i].data.id == id) {
                tradePoints[i].infowindow.show(true);
            }
            else {
                tradePoints[i].infowindow.hide();
            }
        }
    });

    // субмит формы
    $('.js-trade-point-map-form').on('submit', function() {
        searchTradePoints();
        return false;
    });
});