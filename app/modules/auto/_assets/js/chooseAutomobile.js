
/**
 * Выбор автомобилей - виджет
 */

var chooseAutomobile = function(params) {
    var wrap = function(selector) {
        return $(params.wrapper).find(selector);
    };

    /**
     * Добавить html в враппер wrapper
     * @param {String} wrapper selector
     * @param {String} html
     */
    var appendWrapItems = function(wrapper, html) {
        wrap(wrapper).append(html);
    };

    /**
     * Сгенерировать html
     * @param {Array} data данные
     * @param {String} name
     * @param {Array} exists
     * @returns {String}
     */
    var getDataHtml = function(data, attributeName, exists) {
        var html = '';
        $.each(data, function(i, item) {
            item['checked'] = '';
            if (exists && exists.inArray(item.id) != -1) {
                item['checked'] = 'checked="checked"';
            }
            item['attributeName'] = attributeName;
            html += applyTemplate(item);
        });
        return html;
    };

    /**
     * ПРименить шаблон к единице
     * @param {object} item
     * @param {string} name
     * @returns {string}
     */
    var applyTemplate = function(item, name) {
        var ret = params.template;
        $.each(item, function(key, value) {
            ret = ret.replace('{$' + key + '}', value);
        });
        return ret;
    };

    /**
     * Выполнить запрос
     * @param {string} url
     * @param {object} params
     * @param {object} callback
     */
    var query = function(url, params, callback) {
        $.ajax({
            'url'           : url,
            'type'          : 'post',
            'dataType'      : 'json',
            'data'          : params,
            'success'       : callback
        });
    };

    /**
     * Выбор серии
     * @param {integer} serieId
     */
    var chooseSerie = function(serieId) {
        if (params.serieIds.indexOf(serieId) == -1) {
            params.serieIds.push(serieId);
        }
        query(params.modificationUrl, {'serieId': serieId}, function(d) {
            if (d.cnt && d.data) {
                appendWrapItems('.choose-auto-modification', getDataHtml(d.data, params.modificationName));
            }
        });
    };

    /**
     * Снять выбор серии
     * @param {integer} serieId
     */
    var unchooseSerie = function(serieId) {
        wrap('.js-serie-' + serieId).remove();
        var index = params.serieIds.indexOf(serieId);
        if (index >= 0) {
            params.serieIds.remove(index);
        }
    };

    /**
     * Выбор модели
     * @param {integer} modelId
     */
    var chooseModel = function(modelId) {
        if (params.modelIds.indexOf(modelId) == -1) {
            params.modelIds.push(modelId);
        }
        query(params.serieUrl, {'modelId': modelId}, function(d) {
            if (d.cnt && d.data) {
                appendWrapItems('.choose-auto-serie', getDataHtml(d.data, params.serieName));
            }
        });
    };

    /**
     * Снять выбор модели
     * @param {integer} modelId
     */
    var unchooseModel = function(modelId) {
        wrap('.js-model-' + modelId).remove();
        var index = params.modelIds.indexOf(modelId);
        if (index >= 0) {
            params.modelIds.remove(index);
        }
    };

    /**
     * Выбор марки
     * @param {integer} markId
     */
    var chooseMark = function(markId) {
        if (params.markIds.indexOf(markId) == -1) {
            params.markIds.push(markId);
        }
        query(params.modelUrl, {'markId': markId}, function(d) {
            if (d.cnt && d.data) {
                appendWrapItems('.choose-auto-model', getDataHtml(d.data, params.modelName));
            }
        });
    };

    /**
     * Снять выбор марки
     * @param {integer} markId
     */
    var unchooseMark = function(markId) {
        wrap('.js-mark-' + markId).remove();
        var index = params.markIds.indexOf(markId);
        if (index >= 0) {
            params.markIds.remove(index);
        }
    };

    // клик по марке
    wrap('.choose-auto-mark').on('change', 'input[type="checkbox"]', function(e) {
        if ($(this).is(':checked')) {
            chooseMark($(this).val());
        }
        else {
            unchooseMark($(this).val());
        }
    });

    // клик по модели
    wrap('.choose-auto-model').on('change', 'input[type="checkbox"]', function(e) {
        if ($(this).is(':checked')) {
            chooseModel($(this).val());
        }
        else {
            unchooseModel($(this).val());
        }
    });

    // клик по серии
    wrap('.choose-auto-serie').on('change', 'input[type="checkbox"]', function(e) {
        if ($(this).is(':checked')) {
            chooseSerie($(this).val());
        }
        else {
            unchooseSerie($(this).val());
        }
    });

    // предзагрузка марок
    query(params.markUrl, {}, function(d) {
        if (d.cnt && d.data) {
            appendWrapItems('.choose-auto-mark', getDataHtml(d.data, params.markName));
        }
    });
};

