(function ($) {
    $(window).on('load', function () {
        // fix for lower versions of jQuery 1 and 2
        $.when($.ready).then(function () {
            // Both ready and loaded

            var wdtGoogleCharts = [];

            if (typeof wpDataCharts !== 'undefined') {

                for (var id in wpDataCharts) {

                    if (wpDataCharts[id].engine == 'chartjs') wdtChart_selector = 'chartJSContainer_' + id;
                    else wdtChart_selector = wpDataCharts[id].container;

                    if (wpDataCharts[id].engine == 'google') {
                        var wdtChart = new wpDataTablesGoogleChart();
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
                        var wdtChart = new wpDataTablesHighchart();
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
                        var wdtChart = new wpDataTablesChartJS();
                        wdtChart.setData(wpDataCharts[id].render_data.options.data);
                        wdtChart.setOptions(wpDataCharts[id].render_data.options.options);
                        wdtChart.setGlobalOptions(wpDataCharts[id].render_data.options.globalOptions);
                        wdtChart.setType(wpDataCharts[id].render_data.configurations.type);
                        wdtChart.setColumnIndexes(wpDataCharts[id].render_data.column_indexes);
                        wdtChart.setGrouping(wpDataCharts[id].group_chart);
                        wdtChart.setLoader(wpDataCharts[id].loader);
                        wdtChart.setContainer(document.getElementById("chartJSContainer_" + id));
                        wdtChart.setCanvas(document.getElementById("chartJSCanvas_" + id));
                        wdtChart.setContainerOptions(wpDataCharts[id].render_data.configurations);
                        if (typeof wpDataChartsCallbacks !== 'undefined' && typeof wpDataChartsCallbacks[id] !== 'undefined') {
                            wdtChart.setRenderCallback(wpDataChartsCallbacks[id]);
                        }
                        if (wpDataCharts[id].follow_filtering != 1) {
                            wdtChart.render();
                        }
                    } else if (wpDataCharts[id].engine == 'apexcharts') {
                        var wdtChart = new wpDataTablesApexChart();
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
                        var wdtChart = new wpDataTablesHighStock();
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

                    if (wpDataCharts[id].follow_filtering == 1) {
                        // Find the wpDataTable object
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
            }

            // Setting the callback for rendering Google Charts
            if (wdtGoogleCharts.length) {
                var wdtGoogleRenderCallback = function () {
                    for (var i in wdtGoogleCharts) {
                        if (!isNaN(i))
                            wdtGoogleCharts[i].render();
                    }
                }
                if (typeof google.charts.setOnLoadCallback !== "undefined") {
                    google.charts.setOnLoadCallback(wdtGoogleRenderCallback);
                } else {
                    for (var i in wdtGoogleCharts) {
                        if (!isNaN(i))
                            wdtGoogleCharts[i].render();
                    }
                }
            }
        })

    })

})(jQuery);
