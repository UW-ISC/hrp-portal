/**
 * wpDataTables AI — Chart Type Suggester.
 *
 * Suggest → review (engine + type + axes) → Apply maps onto the chart wizard.
 *
 * Result-panel selectpickers are initialized only after the panel is visible —
 * bootstrap-select breaks when inited inside `.hidden`.
 *
 * @copyright © Melograno Venture Studio. All rights reserved.
 */
(function (window, $) {
    'use strict';

    var TYPE_MAP = {
        google: {
            line: 'google_line_chart',
            bar: 'google_bar_chart',
            column: 'google_column_chart',
            area: 'google_area_chart',
            pie: 'google_pie_chart',
            donut: 'google_donut_chart',
            scatter: 'google_scatter_chart',
            mixed: 'google_column_chart'
        },
        chartjs: {
            line: 'chartjs_line_chart',
            bar: 'chartjs_bar_chart',
            column: 'chartjs_column_chart',
            area: 'chartjs_area_chart',
            pie: 'chartjs_pie_chart',
            donut: 'chartjs_doughnut_chart',
            scatter: 'chartjs_line_chart',
            mixed: 'chartjs_column_chart'
        },
        highcharts: {
            line: 'highcharts_line_chart',
            bar: 'highcharts_basic_bar_chart',
            column: 'highcharts_basic_column_chart',
            area: 'highcharts_basic_area_chart',
            pie: 'highcharts_pie_chart',
            donut: 'highcharts_donut_chart',
            scatter: 'highcharts_scatter_plot',
            mixed: 'highcharts_basic_column_chart'
        },
        apexcharts: {
            line: 'apexcharts_straight_line_chart',
            bar: 'apexcharts_grouped_bar_chart',
            column: 'apexcharts_column_chart',
            area: 'apexcharts_basic_area_chart',
            pie: 'apexcharts_pie_chart',
            donut: 'apexcharts_donut_chart',
            scatter: 'apexcharts_straight_line_chart',
            mixed: 'apexcharts_column_chart'
        },
        highstock: {
            line: 'highstock_line_chart',
            bar: 'highstock_column_chart',
            column: 'highstock_column_chart',
            area: 'highstock_area_chart',
            pie: 'highstock_line_chart',
            donut: 'highstock_line_chart',
            scatter: 'highstock_line_chart',
            mixed: 'highstock_column_chart'
        }
    };

    var ENGINE_LABELS = {
        google: 'Google Charts',
        chartjs: 'Chart.js',
        highcharts: 'HighCharts',
        apexcharts: 'ApexCharts',
        highstock: 'HighCharts Stock'
    };

    /** Matches chart wizard label column types (first series column). */
    var LABEL_TYPES = ['string', 'date', 'datetime', 'time', 'link', 'select'];

    /** Matches chart wizard series column types (int / float blocks). */
    var SERIES_TYPES = ['int', 'float', 'formula'];

    function esc(value) {
        return $('<div/>').text(value == null ? '' : value).html();
    }

    function getPickerValue($select) {
        if (!$select.length) {
            return '';
        }
        if ($select.parent().hasClass('bootstrap-select') && typeof $select.selectpicker === 'function') {
            return $select.selectpicker('val') || '';
        }
        return $select.val() || '';
    }

    function setPickerValue($select, value) {
        if (!$select.length) {
            return;
        }
        if ($select.parent().hasClass('bootstrap-select') && typeof $select.selectpicker === 'function') {
            $select.selectpicker('val', value);
            return;
        }
        $select.val(value);
        if (typeof $select.selectpicker === 'function' && $select.hasClass('selectpicker')) {
            $select.selectpicker('refresh');
        }
    }

    function pickerOpts(extra) {
        // No container:'body' on result pickers — rebuilds orphan .bs-container menus.
        return $.extend({ width: '100%' }, extra || {});
    }

    function destroySelectpicker($el) {
        if (!$el.length || typeof $.fn.selectpicker !== 'function') {
            return;
        }
        if ($el.parent().hasClass('bootstrap-select') || $el.data('selectpicker')) {
            $el.selectpicker('destroy');
        }
        // destroy() can leave listbox sizing that makes the bare <select> look "open".
        $el.removeAttr('size')
            .removeClass('bs-select-hidden selectpicker')
            .addClass('wdt-ai-cs-pending-picker')
            .css({
                display: '',
                position: '',
                top: '',
                bottom: '',
                left: '',
                width: '',
                height: '',
                opacity: '',
                padding: '',
                border: '',
                zIndex: ''
            });
    }

    function initSelectpicker($el, extra) {
        if (!$el.length || typeof $.fn.selectpicker !== 'function') {
            return;
        }
        if ($el.parent().hasClass('bootstrap-select') || $el.data('selectpicker')) {
            $el.selectpicker('refresh');
            return;
        }
        // Add class only now — common.js skips pending pickers on page load.
        $el.removeClass('wdt-ai-cs-pending-picker').addClass('selectpicker');
        $el.selectpicker(pickerOpts(extra));
    }

    /**
     * Replace options by destroy → html → fresh selectpicker() (table-generator pattern).
     * Never html()+refresh on a live widget — that leaves native listboxes / open menus.
     */
    function remountSelectpicker($el, optionsHtml, value, extra) {
        if (!$el.length || typeof $.fn.selectpicker !== 'function') {
            return;
        }
        destroySelectpicker($el);
        if (typeof optionsHtml === 'string') {
            $el.html(optionsHtml);
        }
        if (typeof value !== 'undefined' && value !== null && value !== '') {
            $el.val(value);
        } else {
            $el.val($el.prop('multiple') ? [] : '');
        }
        $el.removeClass('wdt-ai-cs-pending-picker').addClass('selectpicker');
        $el.selectpicker(pickerOpts(extra));
    }

    function initControlPickers($root) {
        // Always-visible controls only — result pickers wait until the panel is shown.
        $root.find('.wdt-ai-cs-controls select.selectpicker').each(function () {
            initSelectpicker($(this));
        });
        if (typeof $.fn.wdtBootstrapTooltip === 'function') {
            $root.find('[data-toggle="tooltip"]').wdtBootstrapTooltip();
        }
    }

    function listWizardEngines() {
        var engines = [];
        $('#chart-render-engine option').each(function () {
            var v = $(this).val();
            if (v && TYPE_MAP[v]) {
                engines.push(v);
            }
        });
        return engines.length ? engines : Object.keys(TYPE_MAP);
    }

    function columnType(column) {
        return String((column && column.type) || 'string').toLowerCase();
    }

    function isLabelColumn(column) {
        return LABEL_TYPES.indexOf(columnType(column)) !== -1;
    }

    function isSeriesColumn(column) {
        return SERIES_TYPES.indexOf(columnType(column)) !== -1;
    }

    function isInternalColumn(column) {
        var name = (column && column.name) || '';
        return name === 'wdt_ID'
            || name === 'wdt_created_by'
            || name === 'wdt_created_at'
            || name === 'wdt_last_edited_by'
            || name === 'wdt_last_edited_at';
    }

    function optionsHtml(columns, selected, multiSelected) {
        var selectedSet = {};
        if (multiSelected) {
            (multiSelected || []).forEach(function (name) {
                selectedSet[name] = true;
            });
        }
        if (!columns || !columns.length) {
            return '<option value="">—</option>';
        }
        return columns.map(function (column) {
            var name = column.name || '';
            var sel = multiSelected
                ? (selectedSet[name] ? ' selected' : '')
                : (name === selected ? ' selected' : '');
            var label = name + (column.type ? ' (' + column.type + ')' : '');
            return '<option value="' + esc(name) + '"' + sel + '>' + esc(label) + '</option>';
        }).join('');
    }

    function syncEngineSelect($panel, selectedEngine) {
        var engines = listWizardEngines();
        var value = selectedEngine && engines.indexOf(selectedEngine) !== -1
            ? selectedEngine
            : (engines.indexOf('google') !== -1 ? 'google' : engines[0]);
        var html = engines.map(function (engine) {
            return '<option value="' + esc(engine) + '">' +
                esc(ENGINE_LABELS[engine] || engine) + '</option>';
        }).join('');
        remountSelectpicker($panel.find('.wdt-ai-cs-engine'), html, value);
    }

    function fillAxisSelects($panel, columns, xAxis, yAxis) {
        var labelCols = (columns || []).filter(function (col) {
            return col.name && isLabelColumn(col) && !isInternalColumn(col);
        });
        var seriesCols = (columns || []).filter(function (col) {
            return col.name && isSeriesColumn(col) && !isInternalColumn(col);
        });

        // Fall back to non-internal labels if the table has no typed label columns.
        if (!labelCols.length) {
            labelCols = (columns || []).filter(function (col) {
                return col.name && !isSeriesColumn(col) && !isInternalColumn(col);
            });
        }

        if (xAxis && !labelCols.some(function (c) { return c.name === xAxis; })) {
            xAxis = labelCols.length ? labelCols[0].name : '';
        }
        yAxis = (yAxis || []).filter(function (name) {
            return seriesCols.some(function (c) { return c.name === name; });
        });
        if (!yAxis.length && seriesCols.length) {
            yAxis = [seriesCols[0].name];
        }

        remountSelectpicker(
            $panel.find('.wdt-ai-cs-x-axis'),
            optionsHtml(labelCols, xAxis, null),
            xAxis || ''
        );
        remountSelectpicker(
            $panel.find('.wdt-ai-cs-y-axis'),
            optionsHtml(seriesCols, null, yAxis),
            yAxis.length ? yAxis : null,
            { selectedTextFormat: 'count > 2' }
        );

        var warnings = [];
        if (!labelCols.length) {
            warnings.push('No string/date label columns found for the X-axis.');
        }
        if (!seriesCols.length) {
            warnings.push('No numeric (int/float/formula) columns found for series. Set salary etc. to Integer or Float in the table.');
        }
        return warnings;
    }

    function showWarnings($panel, warnings) {
        var $box = $panel.find('.wdt-ai-cs-warnings');
        if (!warnings || !warnings.length) {
            $box.addClass('hidden').empty();
            return;
        }
        $box.removeClass('hidden').html(warnings.map(function (w) {
            return '<div>' + esc(w) + '</div>';
        }).join(''));
    }

    function showAlternatives($panel, alternatives) {
        var $box = $panel.find('.wdt-ai-cs-alternatives');
        if (!alternatives || !alternatives.length) {
            $box.addClass('hidden').empty();
            return;
        }
        var heading = $panel.find('.wdt-ai-chart-card').data('alternativesLabel') || 'Alternatives';
        var html = '<div class="wdt-ai-cs-alt-label">' + esc(heading) + '</div>';
        alternatives.forEach(function (alt) {
            html += '<button type="button" class="btn btn-default btn-sm wdt-ai-cs-alt-btn" data-chart-type="' +
                esc(alt.chart_type) + '">' + esc(alt.chart_type);
            if (alt.reason) {
                html += ' — ' + esc(alt.reason);
            }
            html += '</button> ';
        });
        $box.removeClass('hidden').html(html);
    }

    function resolveEngineCardType(engine, abstractType) {
        var map = TYPE_MAP[engine] || TYPE_MAP.google;
        return map[abstractType] || map.column || map.line;
    }

    function applyToWizard($panel) {
        var tableId = getPickerValue($panel.find('.wdt-ai-cs-table'));
        var title = $.trim($panel.find('.wdt-ai-cs-title').val() || '');
        var chartType = getPickerValue($panel.find('.wdt-ai-cs-chart-type')) || 'column';
        var engine = getPickerValue($panel.find('.wdt-ai-cs-engine')) || 'google';
        var xAxis = getPickerValue($panel.find('.wdt-ai-cs-x-axis')) || '';
        var yAxis = getPickerValue($panel.find('.wdt-ai-cs-y-axis'));

        if (!tableId) {
            $panel.find('.wdt-ai-cs-status').text('Pick a data table first.');
            return;
        }

        if (!TYPE_MAP[engine]) {
            engine = 'google';
        }
        if (!Array.isArray(yAxis)) {
            yAxis = yAxis ? [yAxis] : [];
        }

        if (title) {
            $('#chart-name').val(title).trigger('change');
        }

        var $engineSelect = $('#chart-render-engine');
        setPickerValue($engineSelect, engine);
        $engineSelect.trigger('change');

        window.setTimeout(function () {
            var cardType = resolveEngineCardType(engine, chartType);
            var $card = $('.wdt-chart-wizard-chart-selecter-block .card[data-type="' + cardType + '"]');
            if (!$card.length) {
                $card = $('div.' + engine + '-charts-type .wdt-chart-wizard-chart-selecter-block .card:not(.disabled)').first();
            }
            if ($card.length) {
                $('.wdt-chart-wizard-chart-selecter-block .card').removeClass('selected').addClass('not-selected');
                $card.removeClass('not-selected').addClass('selected');
                $card.trigger('click');
            }

            var $source = $('#wpdatatables-chart-source');
            setPickerValue($source, String(tableId));

            var selectedColumns = [];
            if (xAxis) {
                selectedColumns.push(xAxis);
            }
            yAxis.forEach(function (col) {
                if (col && selectedColumns.indexOf(col) === -1) {
                    selectedColumns.push(col);
                }
            });

            if (typeof window.constructedChartData === 'undefined') {
                window.constructedChartData = {};
            }
            window.constructedChartData.selected_columns = selectedColumns;
            window.constructedChartData.wpdatatable_id = parseInt(tableId, 10) || tableId;
            window.constructedChartData.title = title;
            window.constructedChartData.engine = engine;
            if ($card.length) {
                window.constructedChartData.type = $card.data('type');
                window.constructedChartData.min_columns = parseInt($card.data('min_columns'), 10) || 0;
                window.constructedChartData.max_columns = parseInt($card.data('max_columns'), 10) || 0;
            }

            window.wdtAiChartSuggestion = {
                table_id: tableId,
                chart_type: chartType,
                engine: engine,
                title: title,
                x_axis: xAxis,
                y_axis: yAxis,
                selected_columns: selectedColumns
            };
            $(document).trigger('wdt_ai_chart_suggestion_ready', [window.wdtAiChartSuggestion]);

            $panel.find('.wdt-ai-cs-status').text('Applied — continue with Next.');
            $('#wdt-chart-wizard-next-step').prop('disabled', false);
        }, 50);
    }

    function uniqueWarnings(warnings) {
        var seen = {};
        return (warnings || []).filter(function (w) {
            if (!w || seen[w]) {
                return false;
            }
            seen[w] = true;
            return true;
        });
    }

    function bind($panel) {
        initControlPickers($panel);

        $panel.on('click', '.wdt-ai-cs-generate', function () {
            var tableId = parseInt(getPickerValue($panel.find('.wdt-ai-cs-table')), 10) || 0;
            if (!tableId) {
                $panel.find('.wdt-ai-cs-status').text('Pick a data table first.');
                return;
            }

            var $btn = $(this);
            var description = $.trim($panel.find('.wdt-ai-cs-description').val() || '');
            var model = getPickerValue($panel.find('.wdt-ai-cs-model'));

            $btn.prop('disabled', true);
            $panel.find('.wdt-ai-cs-status').text('Suggesting…');
            $panel.find('.wdt-ai-cs-result').addClass('hidden');

            window.wdtAi.post('suggest-chart', {
                table_id: tableId,
                description: description,
                available_engines: listWizardEngines(),
                model: model
            }).then(function (data) {
                var engine = data.engine || 'google';
                if (!TYPE_MAP[engine]) {
                    engine = 'google';
                }

                // Show first so bootstrap-select measures a visible parent.
                $panel.find('.wdt-ai-cs-result').removeClass('hidden');

                $panel.find('.wdt-ai-cs-title').val(data.title || '');

                syncEngineSelect($panel, engine);
                remountSelectpicker(
                    $panel.find('.wdt-ai-cs-chart-type'),
                    null,
                    data.chart_type || 'column'
                );

                var axisWarnings = fillAxisSelects(
                    $panel,
                    data.columns || [],
                    data.x_axis || '',
                    data.y_axis || []
                );

                $panel.find('.wdt-ai-cs-reason').text(data.reason || '');
                showWarnings($panel, uniqueWarnings(
                    (data.warnings || [])
                        .filter(function (w) {
                            // Prefer the actionable FE copy when both mention missing numerics.
                            return !axisWarnings.length
                                || String(w).toLowerCase().indexOf('no numeric') === -1;
                        })
                        .concat(axisWarnings)
                ));
                showAlternatives($panel, data.alternatives || []);
                $panel.find('.wdt-ai-cs-status').text('');
                $panel.data('wdtAiColumns', data.columns || []);
                $panel.data('wdtAiSuggestion', data);
            }).catch(function (message) {
                $panel.find('.wdt-ai-cs-status').text(message || 'AI request failed.');
            }).then(function () {
                $btn.prop('disabled', false);
            });
        });

        $panel.on('click', '.wdt-ai-cs-alt-btn', function () {
            var type = $(this).data('chart-type');
            if (type) {
                setPickerValue($panel.find('.wdt-ai-cs-chart-type'), type);
            }
        });

        $panel.on('click', '.wdt-ai-cs-apply', function () {
            applyToWizard($panel);
        });
    }

    window.wdtAi.ready(function () {
        var $panel = $('.wdt-ai-chart-suggester-section');
        if (!$panel.length) {
            return;
        }
        bind($panel);
    });
})(window, jQuery);
