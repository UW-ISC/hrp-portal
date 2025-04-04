google.charts.load('current', {
    packages: ['corechart', 'bar', 'gauge', 'scatter', 'geochart'],
    mapsApiKey: wpdatatables_mapsapikey.wdtGoogleApiMaps
});

var wpDataTablesGoogleChart = function () {

    var obj = {
        rows: [],
        columns: [],
        type: 'column',
        containerId: 'google-chart-container',
        columnIndexes: [],
        connectedWPDataTable: null,
        chart: null,
        googleDataTable: null,
        renderCallback: null,
        options: {
            animation: {
                duration: 1000,
                easing: 'out',
                startup: true
            },
            backgroundColor: {
                fill: '#FFFFFF',
                strokeWidth: 0,
                stroke: '#666',
                rx: 0
            },
            chartArea: {
                backgroundColor: {}

            },
            crosshair: {},
            curveType: 'none',
            fontName: 'Arial',
            fontSize: null,
            hAxis: {
                direction: 1
            },
            height: 400,
            legend: {
                position: 'right'
            },
            orientation: 'horizontal',
            titlePosition: 'out',
            tooltip: {
                trigger: 'focus'
            },
            vAxis: {
                direction: 1,
                viewWindow: {}
            },
            region: null,
            datalessRegionColor: null,
            colors: null,
            displayMode: '',
        },
        setRows: function (rows) {
            this.rows = rows;
        },
        enableDateTimeAxis: function () {
            this.options.hAxis.gridlines = {
                count: -1,
                units: {
                    days: {format: ['MMM dd']},
                    hours: {format: ['HH:mm', 'ha']}
                }
            }
        },
        detectDates: function () {
            for (var i in this.columns) {
                if (this.columns[i].type == 'date' || this.columns[i].type == 'datetime') {
                    for (var j in this.rows) {
                        var remDate = Date.parse(this.rows[j][i]);
                        if (isNaN(remDate)) {
                            this.rows[j][i] = new Date();
                        } else {
                            this.rows[j][i] = new Date(remDate);
                            if (this.connectedWPDataTable == null) {
                                var timeVal = this.rows[j][i].getTime();
                                if (this.columns[i].type == 'datetime') {
                                    timeVal += this.rows[j][i].getTimezoneOffset() * 60 * 1000;
                                }
                                this.rows[j][i].setTime(timeVal);
                            } else {
                                this.rows[j][i].setTime(this.rows[j][i].getTime());
                            }
                        }
                        if (this.columns[i].type == 'datetime') {
                            this.enableDateTimeAxis();
                        }
                    }
                }
            }
        },
        setColumns: function (columns) {
            this.columns = columns;
        },
        getColumns: function () {
            return this.columns;
        },
        setOptions: function (options) {
            for (var i in options) {
                if (i == 'responsive_width' && options[i] == '1') {
                    obj.options.animation = false;
                    jQuery(window).resize(function () {
                        obj.chart.draw(obj.googleDataTable, obj.options);
                    });
                    continue;
                }
                this.options[i] = options[i];
            }
        },
        getOptions: function () {
            return this.options;
        },
        setType: function (type) {
            this.type = type;
        },
        getType: function () {
            return this.type;
        },
        setGrouping: function (group_chart) {
            this.group_chart = group_chart;
        },
        setLoader: function (loader) {
            this.loader = loader;
        },
        setRegion: function (region_chart) {
            this.region = region_chart;
        },
        setContainer: function (containerId) {
            this.containerId = containerId;
        },
        getContainer: function () {
            return this.containerId;
        },
        setRenderCallback: function (callback) {
            this.renderCallback = callback;
        },
        render: function () {
            if (typeof google.visualization !== 'undefined' && typeof google.visualization.DataTable !== 'undefined') {
                this.googleDataTable = new google.visualization.DataTable();
                for (var i in this.columns) {
                    if (!isNaN(i))
                        this.googleDataTable.addColumn(this.columns[i]);
                }
                this.detectDates();

                this.googleDataTable.addRows(this.rows);
                switch (this.type) {
                    case 'google_column_chart':
                        this.chart = new google.visualization.ColumnChart(document.getElementById(this.containerId));
                        break;
                    case 'google_histogram':
                        this.chart = new google.visualization.Histogram(document.getElementById(this.containerId));
                        break;
                    case 'google_bar_chart':
                        this.options.orientation = 'vertical';
                        this.chart = new google.visualization.BarChart(document.getElementById(this.containerId));
                        break;
                    case 'google_stacked_bar_chart':
                        this.options.orientation = 'vertical';
                        this.options.isStacked = true;
                        this.chart = new google.visualization.BarChart(document.getElementById(this.containerId));
                        break;
                    case 'google_area_chart':
                        this.chart = new google.visualization.AreaChart(document.getElementById(this.containerId));
                        break;
                    case 'google_stepped_area_chart':
                        this.options.isStacked = true;
                        this.chart = new google.visualization.SteppedAreaChart(document.getElementById(this.containerId));
                        break;
                    case 'google_line_chart':
                        this.chart = new google.visualization.LineChart(document.getElementById(this.containerId));
                        break;
                    case 'google_pie_chart':
                        this.chart = new google.visualization.PieChart(document.getElementById(this.containerId));
                        break;
                    case 'google_bubble_chart':
                        this.chart = new google.visualization.BubbleChart(document.getElementById(this.containerId));
                        break;
                    case 'google_donut_chart':
                        this.options.pieHole = 0.4;
                        this.chart = new google.visualization.PieChart(document.getElementById(this.containerId));
                        break;
                    case 'google_gauge_chart':
                        this.options.redFrom = 90;
                        this.options.redTo = 100;
                        this.options.yellowFrom = 75;
                        this.options.yellowTo = 90;
                        this.options.minorTicks = 5;
                        this.chart = new google.visualization.Gauge(document.getElementById(this.containerId));
                        break;
                    case 'google_scatter_chart':
                        this.chart = new google.visualization.ScatterChart(document.getElementById(this.containerId));
                        break;
                    case 'google_candlestick_chart':
                        this.options.legend = 'none';
                        this.chart = new google.visualization.CandlestickChart(document.getElementById(this.containerId));
                        break;
                    case 'google_waterfall_chart':
                        this.options.legend = 'none';
                        this.options.bar = {groupWidth: '100%'};
                        this.options.candlestick = {
                            fallingColor: {strokeWidth: 0, fill: '#a52714'}, // red
                            risingColor: {strokeWidth: 0, fill: '#0f9d58'}   // green
                        };
                        this.chart = new google.visualization.CandlestickChart(document.getElementById(this.containerId));
                        break;
                    case 'google_geo_chart':
                        this.chart = new google.visualization.GeoChart(document.getElementById(this.containerId));
                        this.options.displayMode = '';
                        break;
                    case 'google_marker_geo_chart':
                        this.chart = new google.visualization.GeoChart(document.getElementById(this.containerId));
                        this.options.displayMode = 'markers';
                        break;
                    case 'google_text_geo_chart':
                        this.chart = new google.visualization.GeoChart(document.getElementById(this.containerId));
                        this.options.displayMode = 'text';
                        break;
                }
                if (this.renderCallback !== null) {
                    this.renderCallback(this);
                }
                var chartID = this.chart.container.id.replace(/.*_(\d+)/, '$1');
                if (jQuery(this.chart.container).parent().find('.wdt-wrapper-chart-loader').length != 0) {
                    jQuery(this.chart.container).parent().find('.wdt-wrapper-chart-loader').each(function () {
                        if (jQuery(this).attr('data-id') === chartID) {
                            jQuery(this).hide();
                        }
                    });
                }
                this.chart.draw(this.googleDataTable, this.options);
            }
        },
        refresh: function () {
            if (typeof google.visualization !== 'undefined' && typeof google.visualization.DataTable !== 'undefined' && this.chart != null) {
                this.googleDataTable = new google.visualization.DataTable();
                for (var i in this.columns) {
                    if (!isNaN(i))
                        this.googleDataTable.addColumn(this.columns[i]);
                }
                this.detectDates();
                this.googleDataTable.addRows(this.rows);
                if (this.renderCallback !== null) {
                    this.renderCallback(this);
                }
                this.chart.draw(this.googleDataTable, this.options);
            }
        },
        setConnectedWPDataTable: function (wpDataTable) {
            this.connectedWPDataTable = wpDataTable;
        },
        getConnectedWPDataTable: function () {
            return this.connectedWPDataTable;
        },
        setChartConfig: function (chartConfig) {
            // Chart
            if (chartConfig.responsive_width == 1) {
                this.options.animation = false;
                delete this.options.width;
                jQuery(window).resize(function () {
                    obj.chart.draw(obj.googleDataTable, obj.options);
                });
            } else {
                this.options.width = chartConfig.width;
            }
            chartConfig.height ? this.options.height = chartConfig.height : null;
            this.options.backgroundColor.fill = chartConfig.background_color;
            chartConfig.border_width ? this.options.backgroundColor.strokeWidth = chartConfig.border_width : null;
            this.options.backgroundColor.stroke = chartConfig.border_color;
            chartConfig.border_radius ? this.options.backgroundColor.rx = chartConfig.border_radius : null;
            chartConfig.border_radius ? this.options.backgroundColor.rx = chartConfig.border_radius : null;
            this.options.chartArea.backgroundColor.fill = chartConfig.plot_background_color;
            chartConfig.plot_border_width ? this.options.chartArea.backgroundColor.strokeWidth = chartConfig.plot_border_width : null;
            this.options.chartArea.backgroundColor.stroke = chartConfig.plot_border_color;
            chartConfig.font_size ? this.options.fontSize = chartConfig.font_size : null;
            chartConfig.font_name ? this.options.fontName = chartConfig.font_name : null;
            if (chartConfig.type == 'google_pie_chart') {
                chartConfig.three_d == 1 ? this.options.is3D = true : this.options.is3D = false;
            }
            if (chartConfig.type == 'google_geo_chart' || chartConfig.type == 'google_marker_geo_chart' || chartConfig.type == 'google_text_geo_chart') {
                this.options.region = chartConfig.region;
                this.options.datalessRegionColor = chartConfig.datalessRegionColor;
                this.options.colors = chartConfig.colors;
            }
            // Series
            var j = 0;
            for (var i in chartConfig.series_data) {
                this.columns[j + 1].label = chartConfig.series_data[i].label;
                if (chartConfig.series_data[i].color != '') {
                    this.options.series[j] = {
                        color: chartConfig.series_data[i].color
                    };
                }
                j++;
            }
            if (chartConfig.type == 'google_line_chart') {
                chartConfig.curve_type == 1 ? this.options.curveType = 'function' : this.options.curveType = 'none';
            }
            // Axes
            if (chartConfig.show_grid == 0) {
                this.options.hAxis.gridlines = {
                    color: 'transparent'
                };
                this.options.vAxis.gridlines = {
                    color: 'transparent'
                };
            } else {
                delete this.options.hAxis.gridlines;
                delete this.options.vAxis.gridlines;
            }
            chartConfig.horizontal_axis_label ? this.options.hAxis.title = chartConfig.horizontal_axis_label : null;
            if (chartConfig.horizontal_axis_crosshair == 1 && chartConfig.vertical_axis_crosshair == 0) {
                this.options.crosshair = {
                    trigger: 'both',
                    orientation: 'horizontal'
                }
            } else if (chartConfig.horizontal_axis_crosshair == 0 && chartConfig.vertical_axis_crosshair == 1) {
                this.options.crosshair = {
                    trigger: 'both',
                    orientation: 'vertical'
                }
            } else if (chartConfig.horizontal_axis_crosshair == 1 && chartConfig.vertical_axis_crosshair == 1) {
                this.options.crosshair = {
                    trigger: 'both',
                    orientation: 'both'
                }
            } else {
                this.options.crosshair = {}
            }
            chartConfig.horizontal_axis_direction ? this.options.hAxis.direction = chartConfig.horizontal_axis_direction : null;
            chartConfig.vertical_axis_label ? this.options.vAxis.title = chartConfig.vertical_axis_label : null;
            chartConfig.vertical_axis_direction ? this.options.vAxis.direction = chartConfig.vertical_axis_direction : null;
            this.options.vAxis.viewWindow.min = chartConfig.vertical_axis_min;
            this.options.vAxis.viewWindow.max = chartConfig.vertical_axis_max;
            chartConfig.inverted == 1 ? this.options.orientation = 'vertical' : this.options.orientation = 'horizontal';
            // Title
            chartConfig.show_title == 1 ? this.options.title = chartConfig.title : this.options.title = '';
            chartConfig.title_floating == 1 ? this.options.titlePosition = 'in' : this.options.titlePosition = 'out';
            // Tooltip
            chartConfig.tooltip_enabled == 1 ? this.options.tooltip.trigger = 'focus' : this.options.tooltip.trigger = 'none';
            // Legend
            chartConfig.legend_position ? this.options.legend.position = chartConfig.legend_position : null;
            if (chartConfig.legend_vertical_align == 'bottom') {
                this.options.legend.alignment = 'end';
            } else if (chartConfig.legend_vertical_align == 'middle') {
                this.options.legend.alignment = 'center';
            } else {
                this.options.legend.alignment = 'start';
            }

        },
        enableFollowFiltering: function () {
            if (this.connectedWPDataTable == null) {
                return;
            }

            this.connectedWPDataTable.fnSettings().aoDrawCallback.push({
                sName: 'chart_filter_follow',
                fn: function (oSettings) {
                    var rowsToRender = [];

                    obj.options.animation = false;

                    var filteredData = obj.connectedWPDataTable._('tr', {"filter": "applied"}).toArray();
                    var dateSettingsFormat = JSON.parse(jQuery('#' + obj.connectedWPDataTable.data('described-by')).val()).datepickFormat;
                    var timeSettingsFormat = JSON.parse(jQuery('#' + obj.connectedWPDataTable.data('described-by')).val()).timeFormat;

                    var momentDateFormat = dateSettingsFormat.replace('dd', 'DD').replace('M', 'MMM').replace('mm', 'MM').replace('yy', 'YYYY');
                    var momentTimeFormat = timeSettingsFormat.replace('H', 'H').replace('i', 'mm');
                    var momentDateTimeFormat = momentDateFormat + ' ' + momentTimeFormat;

                    var numberFormat = JSON.parse(jQuery('#' + obj.connectedWPDataTable.data('described-by')).val()).number_format;

                    for (var i in filteredData) {

                        var rowEntry = [];
                        for (var j in obj.columnIndexes) {
                            if (obj.columns[j].type == 'number') {
                                var value = filteredData[i][obj.columnIndexes[j]];
                                if (numberFormat == 1) {
                                    rowEntry.push(parseFloat(wdtUnformatNumber(value, '.', ',', true)));
                                } else {
                                    rowEntry.push(parseFloat(wdtUnformatNumber(value, ',', '.', true)));
                                }
                            } else if (obj.columns[j].type == 'date') {
                                rowEntry.push(moment(filteredData[i][obj.columnIndexes[j]], momentDateFormat).toDate());
                            } else if (obj.columns[j].type == 'datetime') {
                                rowEntry.push(moment(filteredData[i][obj.columnIndexes[j]], momentDateTimeFormat).toDate());
                                obj.enableDateTimeAxis();
                            } else {
                                rowEntry.push(filteredData[i][obj.columnIndexes[j]]);
                            }
                        }
                        rowsToRender.push(rowEntry);
                    }

                    if (obj.group_chart == 1) {
                        var output = [];
                        for (var i in rowsToRender) {
                            if (typeof output !== 'undefined' && output.length > 0) {
                                var value_key = 'none';
                                for (var j in output) {
                                    if (value_key === 'none') {
                                        if (output[j][0].toString() == rowsToRender[i][0].toString()) {
                                            value_key = j;
                                        }
                                    }
                                }
                                if (value_key === 'none') {
                                    output.push(rowsToRender[i]);
                                } else {
                                    for (var n in rowsToRender[i]) {
                                        if (n != 0) {
                                            output[value_key][n] += rowsToRender[i][n];
                                        }
                                    }
                                }
                            } else {
                                output.push(rowsToRender[i]);
                            }
                        }
                        rowsToRender = output;
                    }
                    obj.rows = rowsToRender;
                    obj.refresh();
                    ;
                }
            });
        },
        setColumnIndexes: function (columnIndexes) {
            this.columnIndexes = columnIndexes;
        },
        getColumnIndexes: function () {
            return this.columnIndexes;
        }

    };

    return obj;

};
