
/**
 * Выбор автомобилей - виджет
 */
(function($) {
    var defaults = {
        'multipleSelect'        : false,
        'markWrapper'           : false,
        'modelWrapper'          : false,
        'serieWrapper'          : false,
        'modificationWrapper'   : false,
        'markUrl'               : false,
        'modelUrl'              : false,
        'serieUrl'              : false,
        'modificationUrl'       : false,
        'markIds'               : [],
        'modelIds'              : [],
        'serieIds'              : [],
        'modificationIds'       : [],
        'markName'              : '',
        'modelName'             : '',
        'serieName'             : '',
        'modificationName'      : '',
        'renderItem'            : function(type, jsClass, selected, attributeName, id, name) {}
    };




    $.fn.chooseAutomobile = function(method) {
        var options = $(this).data('chooseAutomobileOptions') ? $(this).data('chooseAutomobileOptions') : {};

        var self = this;

        /**
         * Сгенерировать html
         * @param {Array} data данные
         * @param {String} name
         * @param {Array} exists
         * @returns {String}
         */
        var getDataHtml = function(type, data, attributeName, exists) {
            var html = '';
            $.each(data, function(i, item) {
                html += options.renderItem(
                    type, item.jsClass,
                    exists && (exists.indexOf(item.id) !== -1 || exists.indexOf(item.id.toString()) !== -1),
                    attributeName, item.id, item.name, item.full_name
                );
            });
            return html;
        };

        var queries = 0;
        /**
         * Выполнить запрос
         * @param {String} url
         * @param {Object} params
         * @param {Object} callback
         */
        var query = function(url, params, callback) {
            queries++;
            $(self).find('.choose-auto-loader').show();
            $.ajax({
                'url'           : url,
                'type'          : 'post',
                'dataType'      : 'json',
                'data'          : params,
                'success'       : function(d) {
                    queries--;
                    if (queries < 1) {
                        $(self).find('.choose-auto-loader').hide();
                    }
                    callback(d);
                }
            });
        };

        /**
         * Очистить выбор
         * @param {String} key
         */
        var clearSelected = function(key) {
            if (!options.multipleSelect) {
                $.each(options[key], function(i, id) {
                    switch (key) {
                        case 'markIds':
                            unchooseMark(id);
                            break;
                        case 'modelIds':
                            unchooseModel(id);
                            break;
                        case 'serieIds':
                            unchooseSerie(id);
                            break;
                    }
                });
                options[key] = [];
            }
        };

        /**
         * Выбор серии
         * @param {integer} serieId
         */
        var chooseSerie = function(serieId) {
            clearSelected('serieIds');
            if (!serieId) {
                return;
            }
            if (options.serieIds.indexOf(serieId) === -1) {
                options.serieIds.push(serieId);
            }
            query(options.modificationUrl, {'serieId': serieId}, function(d) {
                if (d.cnt && d.data) {
                    $(self).find(options.modificationWrapper).append(getDataHtml(
                        'modification', d.data, options.modificationName, options.modificationIds
                    ));
                }
            });
        };

        /**
         * Снять выбор серии
         * @param {integer} serieId
         */
        var unchooseSerie = function(serieId) {
            $(self).find('.js-serie-' + serieId).remove();
            var index = options.serieIds.indexOf(serieId);
            if (index >= 0) {
                options.serieIds.splice(index, 1);
            }
        };

        /**
         * Выбор модели
         * @param {integer} modelId
         */
        var chooseModel = function(modelId) {
            clearSelected('modelIds');
            if (!modelId) {
                return;
            }
            if (options.modelIds.indexOf(modelId) === -1) {
                options.modelIds.push(modelId);
            }
            query(options.serieUrl, {'modelId': modelId}, function(d) {
                if (d.cnt && d.data) {
                    $(self).find(options.serieWrapper).append(getDataHtml(
                        'serie', d.data, options.serieName, options.serieIds
                    ));
                }
            });
        };

        /**
         * Снять выбор модели
         * @param {integer} modelId
         */
        var unchooseModel = function(modelId) {
            $(self).find('.js-model-' + modelId).remove();
            var index = options.modelIds.indexOf(modelId);
            if (index >= 0) {
                options.modelIds.splice(index, 1);
            }
        };

        /**
         * Выбор марки
         * @param {integer} markId
         */
        var chooseMark = function(markId) {
            clearSelected('markIds');
            if (!markId) {
                return;
            }
            if (options.markIds.indexOf(markId) === -1) {
                options.markIds.push(markId);
            }
            query(options.modelUrl, {'markId': markId}, function(d) {
                if (d.cnt && d.data) {
                    $(self).find(options.modelWrapper).append(getDataHtml(
                        'model', d.data, options.modelName, options.modelIds
                    ));
                }
            });
        };

        /**
         * Снять выбор марки
         * @param {integer} markId
         */
        var unchooseMark = function(markId) {
            $(self).find('.js-mark-' + markId).remove();
            var index = options.markIds.indexOf(markId);
            if (index >= 0) {
                options.markIds.splice(index, 1);
            }
        };

        /**
         * Получить опцию
         * @param {String} key название опции
         * @returns {mixed}
         */
        var getOption = function(key) {
            return options[key];
        };

        var methods = {
            'chooseMark'        : chooseMark,
            'unchooseMark'      : unchooseMark,
            'chooseModel'       : chooseModel,
            'unchooseModel'     : unchooseModel,
            'chooseSerie'       : chooseSerie,
            'unchooseSerie'     : unchooseSerie,
            'getOption'         : getOption
        };

        var init = function(params) {
            options = $.extend({}, defaults, options, params);
            $(self).data('chooseAutomobileOptions', options);

            if ($(self).find('.choose-auto-loader').length == 0) {
                $(self).prepend('<div class="choose-auto-loader"></div>');
            }
            // предзагрузка автомобилей
            query(options.markUrl, {}, function(d) {
                if (d.cnt && d.data) {
                    $(self).find(options.markWrapper).append(getDataHtml(
                        'mark', d.data, options.markName, options.markIds
                    ));
                }
            });
            $.each(options.markIds, function(key, id) {
                chooseMark(id);
            });
            $.each(options.modelIds, function(key, id) {
                chooseModel(id);
            });
            $.each(options.serieIds, function(key, id) {
                chooseSerie(id);
            });
        };

        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        }
        else if (typeof method === 'object' || !method) {
            return init.apply(this, arguments);
        }
    };
})(jQuery);