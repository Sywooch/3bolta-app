/**
 * Поиск запчастей
 */

$(document).ready(function() {
    /**
     * Собрать параметры в расширенном поиске в виде массива
     */
    var collectExtendedParams = function() {
        var params = [];
        $('.js-extended-search .js-extended-param').each(function() {
            var title= $(this).data('title');
            var from = null;
            var to = null;
            var param = '';
            if ($(this).is('.js-top-search-price')) {
                // фильтр по цене
                from = $(this).data('from');
                to = $(this).data('to');

                var valFrom = parseFloat($(this).find('input[type="text"]:first').val());
                var valTo = parseFloat($(this).find('input[type="text"]:last').val());

                if (!isNaN(valFrom) && valFrom > 0) {
                    param += from + ' ' + valFrom;
                }

                if (!isNaN(valTo) && valTo > 0) {
                    param += (param.length ? ' ' : '') + to + ' ' + valTo;
                }

                if (param.length > 0) {
                    param = title + ': ' + param;
                }
            }
            else if ($(this).is('.js-top-search-region')) {
                // выбор региона
                var text = $(this).find('select option:selected').text();
                var val = $(this).find('select option:selected').val();
                if (val && val != $('.js-selected-region').data('region-id')) {
                    param = title + ': ' + text;
                }
            }
            else if ($(this).is('.js-top-search-seller')) {
                // выбор типа продавца
                var text = $(this).find('select option:selected').text();
                if (text.length) {
                    param = title + ': ' + text;
                }
            }

            if (param.length) {
                params.push(param);
            }
        });

        if (params.length) {
            params = params.join(', ');
            $('.js-extended-search-toggle span').text(params);
        }
        else {
            $('.js-extended-search-toggle span').text($('.js-extended-search-toggle').data('default-text'));
        }
    };
    $('.js-extended-search').on('click', '.js-extended-search-toggle, .js-extended-search-toggled', function(e) {
        // схлопнуть/развернуть дополнительные параметры
        $('.js-extended-search').toggleClass('toggled');
        collectExtendedParams();
        e.preventDefault();
    });

    collectExtendedParams();
});