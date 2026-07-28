(function ($) {
    /**
     * Core chart bootstrap (google, highcharts, chartjs, apex, highstock).
     * @param {jQuery} $ jQuery
     * @param {number[]|null} allowedIds If set, only these chart IDs are processed (Divi VB preview).
     */
    function wpdtChartsRenderBody($, allowedIds) {
        var wdtGoogleCharts = [];

        if (typeof wpDataCharts === 'undefined') {
            return;
        }

        for (var id in wpDataCharts) {
            if (!Object.prototype.hasOwnProperty.call(wpDataCharts, id)) {
                continue;
            }
            if (allowedIds && allowedIds.length) {
                var nid = parseInt(id, 10);
                if (allowedIds.indexOf(nid) === -1) {
                    continue;
                }
            }

            var wdtChart;

            if (wpDataCharts[id].engine == 'google') {
                wdtChart = new wpDataTablesGoogleChart();
                wdtChart.setType(wpDataCharts[id].render_data.type);
                wdtChart.setColumns(wpDataCharts[id].render_data.columns);
                wdtChart.setRows(wpDataCharts[id].render_data.rows);
                wdtChart.setOptions(wpDataCharts[id].render_data.options);
                wdtChart.setGrouping(wpDataCharts[id].group_chart);
                wdtChart.setLoader(wpDataCharts[id].loader);
                wdtChart.setContainer(wpDataCharts[id].container);
                wdtChart.setColumnIndexes(wpDataCharts[id].render_data.column_indexes);
                if (typeof wpDataChartsCallbacks !== 'undefined' && typeof wpDataChartsCallbacks[id] !== 'undefined') {
                    wdtChart.setRenderCallback(wpDataChartsCallbacks[id]);
                }
                wdtGoogleCharts.push(wdtChart);
            } else if (wpDataCharts[id].engine == 'highcharts') {
                wdtChart = new wpDataTablesHighchart();
                wdtChart.setNumberFormat(wpDataCharts[id].render_data.wdtNumberFormat);
                wdtChart.setOptions(wpDataCharts[id].render_data.options);
                wdtChart.setMultiplyYaxis(wpDataCharts[id].render_data);
                wdtChart.setType(wpDataCharts[id].render_data.type);
                wdtChart.setWidth(wpDataCharts[id].render_data.width);
                wdtChart.setHeight(wpDataCharts[id].render_data.height);
                wdtChart.setColumnIndexes(wpDataCharts[id].render_data.column_indexes);
                wdtChart.setGrouping(wpDataCharts[id].group_chart);
                wdtChart.setLoader(wpDataCharts[id].loader);
                wdtChart.setContainer('#' + wpDataCharts[id].container);
                if (typeof wpDataChartsCallbacks !== 'undefined' && typeof wpDataChartsCallbacks[id] !== 'undefined') {
                    wdtChart.setRenderCallback(wpDataChartsCallbacks[id]);
                }
                if (wpDataCharts[id].follow_filtering != 1) {
                    wdtChart.render();
                }
            } else if (wpDataCharts[id].engine == 'chartjs') {
                wdtChart = new wpDataTablesChartJS();
                wdtChart.setData(wpDataCharts[id].render_data.options.data);
                wdtChart.setOptions(wpDataCharts[id].render_data.options.options);
                wdtChart.setGlobalOptions(wpDataCharts[id].render_data.options.globalOptions);
                wdtChart.setType(wpDataCharts[id].render_data.configurations.type);
                wdtChart.setColumnIndexes(wpDataCharts[id].render_data.column_indexes);
                wdtChart.setGrouping(wpDataCharts[id].group_chart);
                wdtChart.setLoader(wpDataCharts[id].loader);
                wdtChart.setContainer(document.getElementById('chartJSContainer_' + id));
                wdtChart.setCanvas(document.getElementById('chartJSCanvas_' + id));
                wdtChart.setContainerOptions(wpDataCharts[id].render_data.configurations);
                if (typeof wpDataChartsCallbacks !== 'undefined' && typeof wpDataChartsCallbacks[id] !== 'undefined') {
                    wdtChart.setRenderCallback(wpDataChartsCallbacks[id]);
                }
                if (wpDataCharts[id].follow_filtering != 1) {
                    wdtChart.render();
                }
            } else if (wpDataCharts[id].engine == 'apexcharts') {
                wdtChart = new wpDataTablesApexChart();
                wdtChart.setOptions(wpDataCharts[id].render_data.options);
                wdtChart.setType(wpDataCharts[id].render_data.type);
                wdtChart.setSingleSeriesType(wpDataCharts[id].render_data.options);
                wdtChart.setStartEndAngles(wpDataCharts[id].render_data.options);
                wdtChart.setContainer('#' + wpDataCharts[id].container);
                wdtChart.setCustomOptions(wpDataCharts[id].render_data.options);
                wdtChart.setColumnIndexes(wpDataCharts[id].render_data.column_indexes);
                wdtChart.setNumberFormat(wpDataCharts[id].render_data.wdtNumberFormat);
                wdtChart.setDecimalPlaces(wpDataCharts[id].render_data.wdtDecimalPlaces);
                wdtChart.setGrouping(wpDataCharts[id].group_chart);
                wdtChart.setLoader(wpDataCharts[id].loader);
                if (typeof wpDataChartsCallbacks !== 'undefined' && typeof wpDataChartsCallbacks[id] !== 'undefined') {
                    wdtChart.setRenderCallback(wpDataChartsCallbacks[id]);
                }

                if (wpDataCharts[id].follow_filtering != 1) {
                    wdtChart.render();
                }
            } else if (wpDataCharts[id].engine === 'highstock') {
                wdtChart = new wpDataTablesHighStock();
                wdtChart.setNumberFormat(wpDataCharts[id].render_data.wdtNumberFormat);
                wdtChart.setOptions(wpDataCharts[id].render_data.options);
                wdtChart.setType(wpDataCharts[id].render_data.type);
                wdtChart.setMultipleYaxis(wpDataCharts[id].render_data);
                wdtChart.setWidth(wpDataCharts[id].render_data.width);
                wdtChart.setHeight(wpDataCharts[id].render_data.height);
                wdtChart.setColumnIndexes(wpDataCharts[id].render_data.column_indexes);
                wdtChart.setContainer(wpDataCharts[id].container);
                if (typeof wpDataChartsCallbacks !== 'undefined' && typeof wpDataChartsCallbacks[id] !== 'undefined') {
                    wdtChart.setRenderCallback(wpDataChartsCallbacks[id]);
                }
                if (wpDataCharts[id].follow_filtering != 1) {
                    wdtChart.render();
                }
            }

            if (wpDataCharts[id].follow_filtering == 1 && typeof wdtChart !== 'undefined') {
                var $wdtable = $('table.wpDataTable[data-wpdatatable_id=' + wpDataCharts[id].wpdatatable_id + ']');
                if ($wdtable.length > 0) {
                    var wdtObj = wpDataTables[$wdtable.get(0).id];
                    wdtChart.setConnectedWPDataTable(wdtObj);
                    wdtChart.enableFollowFiltering();
                    wdtObj.fnDraw();
                } else {
                    wdtChart.render();
                }
            }
        }

        if (wdtGoogleCharts.length) {
            var wdtGoogleRenderCallback = function () {
                for (var i in wdtGoogleCharts) {
                    if (!isNaN(i)) {
                        wdtGoogleCharts[i].render();
                    }
                }
            };
            if (typeof google.charts.setOnLoadCallback !== 'undefined') {
                google.charts.setOnLoadCallback(wdtGoogleRenderCallback);
            } else {
                for (var j in wdtGoogleCharts) {
                    if (!isNaN(j)) {
                        wdtGoogleCharts[j].render();
                    }
                }
            }
        }
    }

    $(window).on('load', function () {
        $.when($.ready).then(function () {
            wpdtChartsRenderBody($, null);
        });
    });

    /**
     * Re-run chart render after late-injected markup (e.g. Divi 5 Visual Builder REST preview).
     * @param {number[]|null} allowedIds Chart database IDs to render, or null for all keys in wpDataCharts.
     */
    window.wpdtChartsRenderForPreview = function (allowedIds) {
        var $jq = window.jQuery;
        if (!$jq) {
            return;
        }
        $jq.when($jq.ready).then(function () {
            wpdtChartsRenderBody($jq, allowedIds || null);
        });
    };
})(jQuery);
