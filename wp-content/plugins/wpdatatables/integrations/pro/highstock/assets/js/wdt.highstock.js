var wpDataTablesHighStock = function () {

    var obj = {
        container: '#wdtHighStockContainer',
        columnIndexes: [],
        numberFormat: 1,
        connectedWPDataTable: null,
        renderCallback: null,
        chart: null,
        setContainer: function (container) {
            this.container = container;
            this.options.chart.renderTo = container.replace('#', '');
        },
        getContainer: function () {
            return this.container;
        },
        setWidth: function (width) {
            if (isNaN(width) || width == null || width == 0) {
                delete this.options.chart.width;
            } else {
                this.options.chart.width = parseInt(width);
            }
        },
        getWidth: function () {
            return this.options.chart.width;
        },
        setHeight: function (height) {
            this.options.chart.height = parseInt(height);
        },
        setLoader: function (loader) {
            this.loader = loader;
        },
        getHeight: function () {
            return this.options.chart.height;
        },
        setRenderCallback: function (callback) {
            this.renderCallback = callback;
        },
        options: {
            chart: {
                backgroundColor: '#FFFFFF',
                borderColor: '#4572A7',
                borderRadius: 0,
                borderWidth: 0,
                height: 400,
                inverted: false,
                panning: false,
                panKey: 'shift',
                plotBackgroundColor: 'undefined',
                plotBackgroundImage: 'undefined',
                plotBorderColor: 'undefined',
                plotBorderWidth: 0,
                type: 'line',
                zoomType: 'undefined'
            },
            credits: {
                enabled: true,
                href: 'https://www.highcharts.com',
                text: 'Highcharts.com'
            },
            exporting: {
                buttons: {
                    contextButton: {
                        align: 'right',
                        symbolStroke: '#666',
                        text: null,
                        verticalAlign: 'top'
                    }
                },
                enabled: true,
                chartOptions: {
                    plotOptions: {
                        series: {
                            dataLabels: {
                                enabled: false
                            }
                        }
                    }
                },
                filename: 'Chart',
                width: 'undefined'
            },
            legend: {
                backgroundColor: '#FFFFFF',
                title: {
                    'text': ''
                },
                layout: 'horizontal',
                align: 'right',
                verticalAlign: 'bottom',
                borderWidth: 0,
                borderColor: '#909090'
            },
            plotOptions: {},
            series: [],
            subtitle: {
                align: 'center',
                floating: false,
                text: 'undefined'
            },
            title: {
                align: 'center',
                floating: false,
                text: ''
            },
            tooltip: {
                backgroundColor: 'rgba(255, 255, 255, 0.85)',
                borderColor: null,
                borderRadius: 3,
                borderWidth: 1,
                enabled: true,
                shared: false,
                valuePrefix: 'undefined',
                valueSuffix: 'undefined'
            },
            xAxis: {
                crosshair: false
            },
            yAxis: {
                crosshair: false,
                gridLineDashStyle: 'Solid',
                title: {
                    align: 'align',
                    text: ''
                },
                plotLines: [
                    {
                        "value": 0,
                        "width": 1,
                        "color": "#808080"
                    }
                ]
            }
        },
        getOptions: function () {
            return this.options;
        },
        setOptions: function (options) {
            for (var property in options) {
                this.options[property] = options[property];
            }
            Highcharts.setOptions({
                lang: {
                    decimalPoint: this.getNumberFormat() === '1' ? ',' : '.',
                    thousandsSep: this.getNumberFormat() === '1' ? '.' : ','
                }
            });
        },
        setNumberFormat: function (numberFormat) {
            this.numberFormat = numberFormat;
        },
        getNumberFormat: function () {
            return this.numberFormat;
        },
        render: function () {
            if (this.renderCallback !== null) {
                this.renderCallback(this);
            }
            var chartID = this.container.replace(/.*_(\d+)/, '$1');
            if (jQuery('#' + this.container).parent().find('.wdt-wrapper-chart-loader').length != 0) {
                jQuery('#' + this.container).parent().find('.wdt-wrapper-chart-loader').each(function() {
                    if (jQuery(this).attr('data-id') === chartID) {
                        jQuery(this).hide();
                    }
                });
            }
            this.chart = new Highcharts.stockChart(this.getContainer(), this.getOptions());
        },
        setType: function (type) {
            switch (type) {
                case 'highstock_line_with_markers_chart':
                    this.options.plotOptions.series = {
                        marker: {
                            enabled: true,
                            radius: 3
                        }
                    };
                    break;
                case 'highstock_spline_chart':
                    this.options.chart.type = 'spline';
                    break;
                case 'highstock_stepline_chart':
                    this.options.plotOptions.series = {
                        step: true
                    };
                    break;
                case 'highstock_line_chart':
                    this.options.chart.type = 'line';
                    break;
                case 'highstock_area_chart':
                    this.options.chart.type = 'area';
                    this.options.plotOptions.series = {
                        threshold: true
                    };
                    break;
                case 'highstock_area_spline_chart':
                    this.options.chart.type = 'areaspline';
                    this.options.plotOptions.series = {
                        threshold: true
                    };
                    break;
                case 'highstock_area_range_chart':
                    this.options.chart.type = 'arearange';
                    break;
                case 'highstock_area_spline_range_chart':
                    this.options.chart.type = 'areasplinerange';
                    break;
                case 'highstock_candlestick_chart':
                    this.options.chart.type = 'candlestick';
                    break;
                case 'highstock_hlc_chart':
                    this.options.chart.type = 'hlc';
                    this.options.series.type = 'hlc';
                    break;
                case 'highstock_ohlc_chart':
                    this.options.chart.type = 'ohlc';
                    this.options.series.type = 'ohlc';
                    break;
                case 'highstock_column_chart':
                    this.options.chart.type = 'column';
                    break;
                case 'highstock_column_range_chart':
                    this.options.chart.type = 'columnrange';
                    break;
                case 'highstock_point_markers_only_chart':
                    this.options.plotOptions.series = {
                        lineWidth: 0,
                        marker: {
                            enabled: true,
                            radius: 2
                        },
                        states: {
                            hover: {
                                lineWidthPlus: 0
                            }
                        }
                    };
                    break;
            }
        },
        sortDataAsc: function () {
            for (var i in this.options.series) {
                this.options.series[i].data = this.options.series[i].data.sort((a, b) => a[0] - b[0]);
            }
        },
        refresh: function () {
            this.chart.redraw();
        },
        setColumnIndexes: function (columnIndexes) {
            this.columnIndexes = columnIndexes;
        },
        setMultipleYaxis: function (chartConfig) {
            if (!['hlc', 'ohlc', 'candlestick', 'arearange','areasplinerange','columnrange'].includes(this.options.chart.type)) {
                var j = 0;
                if (chartConfig.options.yAxis.title) {
                    this.options.yAxis.title = {
                        text: chartConfig.options.yAxis.title.text
                    };
                }
                for (var i in chartConfig.options.series) {
                    this.options.series[j].name = chartConfig.options.series[i].label;
                    this.options.series[j].color = chartConfig.options.series[i].color;
                    j++;
                }
            }
        },
        setChartConfig: function (chartConfig) {
            // Chart
            this.setWidth(chartConfig.width);
            chartConfig.height ? this.options.chart.height = chartConfig.height : null;
            this.options.chart.backgroundColor = chartConfig.background_color;
            chartConfig.border_width ? this.options.chart.borderWidth = chartConfig.border_width : null;
            this.options.chart.borderColor = chartConfig.border_color;
            chartConfig.border_radius ? this.options.chart.borderRadius = chartConfig.border_radius : null;
            chartConfig.zoom_type ? this.options.chart.zoomType = chartConfig.zoom_type : null;
            chartConfig.panning ? this.options.chart.panning = chartConfig.panning : null;
            chartConfig.pan_key ? this.options.chart.panKey = chartConfig.pan_key : null;
            this.options.chart.plotBackgroundColor = chartConfig.plot_background_color;
            this.options.chart.plotBackgroundImage = chartConfig.plot_background_image;
            chartConfig.plot_border_width ? this.options.chart.plotBorderWidth = chartConfig.plot_border_width : null;
            this.options.chart.plotBorderColor = chartConfig.plot_border_color;
            // Series
            var j = 0;
            for (var i in chartConfig.series_data) {
                this.options.series[j].name = chartConfig.series_data[i].label;
                this.options.series[j].color = chartConfig.series_data[i].color;
                j++;
            }
            // Axes
            if (chartConfig.show_grid == 0) {
                this.options.xAxis.lineWidth = 0;
                this.options.xAxis.minorGridLineWidth = 0;
                this.options.xAxis.lineColor = 'transparent';
                this.options.xAxis.minorTickLength = 0;
                this.options.xAxis.tickLength = 0;
                this.options.yAxis.lineWidth = 0;
                this.options.yAxis.gridLineWidth = 0;
                this.options.yAxis.minorGridLineWidth = 0;
                this.options.yAxis.lineColor = 'transparent';
                this.options.yAxis.labels = {
                    enabled: false
                };
                this.options.yAxis.minorTickLength = 0;
                this.options.yAxis.tickLength = 0;
            } else {
                delete this.options.xAxis.lineWidth;
                delete this.options.xAxis.minorGridLineWidth;
                delete this.options.xAxis.lineColor;
                delete this.options.xAxis.minorTickLength;
                delete this.options.xAxis.tickLength;
                delete this.options.yAxis.lineWidth;
                delete this.options.yAxis.gridLineWidth;
                delete this.options.yAxis.minorGridLineWidth;
                delete this.options.yAxis.lineColor;
                this.options.yAxis.labels = {
                    enabled: true
                };
                this.options.yAxis.minorTickLength = 0;
                this.options.yAxis.tickLength = 0;
            }

            chartConfig.highcharts_line_dash_style ? this.options.yAxis.gridLineDashStyle = chartConfig.highcharts_line_dash_style : null;
            chartConfig.vertical_axis_crosshair == 1 ? this.options.yAxis.crosshair = true : this.options.yAxis.crosshair = false;
            this.options.yAxis.title = {
                text: chartConfig.vertical_axis_label ? chartConfig.vertical_axis_label : ""
            };
            chartConfig.vertical_axis_min ? this.options.yAxis.min = Number(chartConfig.vertical_axis_min) : this.options.yAxis.min = undefined;
            chartConfig.vertical_axis_max ? this.options.yAxis.max = Number(chartConfig.vertical_axis_max) : this.options.yAxis.max = undefined;
            this.options.xAxis.title = {
                text: chartConfig.horizontal_axis_label ? chartConfig.horizontal_axis_label : ""
            };
            chartConfig.horizontal_axis_crosshair == 1 ? this.options.xAxis.crosshair = true : this.options.xAxis.crosshair = false;
            this.options.chart.inverted = chartConfig.inverted == 1;
            // Title
            chartConfig.show_title == 1 ? this.options.title.text = chartConfig.title : this.options.title.text = '';
            chartConfig.title_floating == 1 ? this.options.title.floating = true : this.options.title.floating = false;
            chartConfig.title_align ? this.options.title.align = chartConfig.title_align : null;
            chartConfig.subtitle ? this.options.subtitle.text = chartConfig.subtitle : this.options.subtitle.text = null;
            chartConfig.subtitle_align ? this.options.subtitle.align = chartConfig.subtitle_align : null;
            // Tooltip
            chartConfig.tooltip_enabled == 1 ? this.options.tooltip.enabled = true : this.options.tooltip.enabled = false;
            this.options.tooltip.backgroundColor = chartConfig.tooltip_background_color ? chartConfig.tooltip_background_color : 'rgba(247,247,247,0.85)';
            chartConfig.tooltip_border_width ? this.options.tooltip.borderWidth = chartConfig.tooltip_border_width : null;
            this.options.tooltip.borderColor = chartConfig.tooltip_border_color;
            chartConfig.tooltip_border_radius ? this.options.tooltip.borderRadius = chartConfig.tooltip_border_radius : null;
            chartConfig.tooltip_shared == 1 ? this.options.tooltip.shared = true : this.options.tooltip.shared = false;
            this.options.tooltip.valuePrefix = chartConfig.tooltip_value_prefix;
            this.options.tooltip.valueSuffix = chartConfig.tooltip_value_suffix;
            // Legend
            chartConfig.show_legend == 1 ? this.options.legend.enabled = true : this.options.legend.enabled = false;
            this.options.legend.backgroundColor = chartConfig.legend_background_color;
            chartConfig.legend_title ? this.options.legend.title.text = chartConfig.legend_title : null;
            chartConfig.legend_layout ? this.options.legend.layout = chartConfig.legend_layout : null;
            chartConfig.legend_align ? this.options.legend.align = chartConfig.legend_align : null;
            chartConfig.legend_vertical_align ? this.options.legend.verticalAlign = chartConfig.legend_vertical_align : null;
            chartConfig.legend_border_width ? this.options.legend.borderWidth = chartConfig.legend_border_width : null;
            this.options.legend.borderColor = chartConfig.legend_border_color;
            chartConfig.legend_border_radius ? this.options.legend.borderRadius = chartConfig.legend_border_radius : null;
            // Exporting
            chartConfig.exporting == 1 ? this.options.exporting.enabled = true : this.options.exporting.enabled = false;
            chartConfig.exporting_data_labels == 1 ? this.options.exporting.chartOptions.plotOptions.series.dataLabels.enabled = true : this.options.exporting.chartOptions.plotOptions.series.dataLabels.enabled = false;
            chartConfig.exporting_file_name ? this.options.exporting.filename = chartConfig.exporting_file_name : null;
            chartConfig.exporting_width ? this.options.exporting.width = chartConfig.exporting_width : null;
            chartConfig.exporting_button_align ? this.options.exporting.buttons.contextButton.align = chartConfig.exporting_button_align : null;
            chartConfig.exporting_button_vertical_align ? this.options.exporting.buttons.contextButton.verticalAlign = chartConfig.exporting_button_vertical_align : null;
            this.options.exporting.buttons.contextButton.symbolStroke = chartConfig.exporting_button_color ? chartConfig.exporting_button_color : '#666666';
            chartConfig.exporting_button_text ? this.options.exporting.buttons.contextButton.text = chartConfig.exporting_button_text : null;
            // Credits
            chartConfig.credits == 1 ? this.options.credits.enabled = true : this.options.credits.enabled = false;
            chartConfig.credits_href ? this.options.credits.href = chartConfig.credits_href : null;
            chartConfig.credits_text ? this.options.credits.text = chartConfig.credits_text : null;

        },
        getColumnIndexes: function () {
            return this.columnIndexes;
        },
        setConnectedWPDataTable: function (wpDataTable) {
            this.connectedWPDataTable = wpDataTable;
        },
        getConnectedWPDataTable: function () {
            return this.connectedWPDataTable;
        },
        formatDateString: function (dateTimeString, dateFormat = 'dd-mm-yy') {
            // Match date and time components
            const dateRegex = /(\d{1,2})[/-](\d{1,2})[/-](\d{2,4})/;
            const timeRegex = /(\d{1,2}):(\d{1,2})/;

            // Extract date and time components
            const dateMatches = dateTimeString.replaceAll('.', '-').match(dateRegex);
            const timeMatches = dateTimeString.match(timeRegex);

            // Create order of extracting date components
            if (dateFormat.startsWith('d')) {
                var order = [1, 2, 3];
            } else if (dateFormat.startsWith('m')) {
                order = [2, 1, 3];
            } else {
                order = [3, 2, 1];
            }

            if (!dateMatches) {
                return Date.parse("01/01/1970 00:00")
            }

            // Extract day, month, and year from the matches
            if (dateMatches.length === 4) {
                var day = dateMatches[order[0]];
            } else if (dateMatches.length === 3) {
                day = '01';
            }
            var month = dateMatches[order[1]];
            var year = dateMatches[order[2]];

            var hour = '00';
            var minute = '00';

            // Extract time components if available
            if (timeMatches) {
                hour = timeMatches[1];
                minute = timeMatches[2];
            }

            // Adjust the year to four digits if it's in the two-digit format
            if (year.length === 2) {
                const currentYear = new Date().getFullYear();
                const cutoffYear = currentYear - 80;
                year = (Number(year) < cutoffYear ? '20' : '19') + year;
            }

            // Format the date string in "MM/DD/YYYY HH:mm:ss" format
            const formattedDateString = `${month}/${day}/${year} ${hour}:${minute}:00`;

            return Date.parse(formattedDateString);
        },
        enableFollowFiltering: function () {
            if (this.getConnectedWPDataTable() == null) {
                return;
            }
            if (typeof (this.options.plotOptions.series) != "undefined") {
                this.options.plotOptions.series.animation = false;
            } else {
                this.options.plotOptions.series = {animation: false};
            }
            this.numberFormat = JSON.parse(jQuery('#' + this.getConnectedWPDataTable().data('described-by')).val()).number_format;
            this.getConnectedWPDataTable().fnSettings().aoDrawCallback.push({
                sName: 'chart_filter_follow',
                fn: function (oSettings) {
                    var dateAxis = [];
                    var seriesIndex = 0;
                    var filteredData = obj.connectedWPDataTable._('tr', {"filter": "applied"}).toArray();
                    for (var j in obj.columnIndexes) {
                        var seriesDataEntry = [];
                        if ((obj.columnIndexes.length > 0)
                            && (j == 0)) {
                            for (var i in filteredData) {
                                dateAxis.push(filteredData[i][obj.columnIndexes[j]]);
                            }
                        } else {
                            if (['hlc', 'ohlc', 'candlestick'].includes(obj.options.chart.type)) {
                                for (var i in filteredData) {
                                    if (obj.getNumberFormat() === 1) {
                                        seriesDataEntry.push([
                                            obj.formatDateString(dateAxis[i], wpdatatables_settings.wdtDateFormat),
                                            parseFloat(wdtUnformatNumber(filteredData[i][obj.columnIndexes[1]], '.', ',', true)),
                                            parseFloat(wdtUnformatNumber(filteredData[i][obj.columnIndexes[2]], '.', ',', true)),
                                            parseFloat(wdtUnformatNumber(filteredData[i][obj.columnIndexes[3]], '.', ',', true)),
                                            parseFloat(wdtUnformatNumber(filteredData[i][obj.columnIndexes[4]], '.', ',', true))
                                        ]);
                                    } else {
                                        seriesDataEntry.push([
                                            obj.formatDateString(dateAxis[i], wpdatatables_settings.wdtDateFormat),
                                            parseFloat(wdtUnformatNumber(filteredData[i][obj.columnIndexes[1]], ',', '.', true)),
                                            parseFloat(wdtUnformatNumber(filteredData[i][obj.columnIndexes[2]], ',', '.', true)),
                                            parseFloat(wdtUnformatNumber(filteredData[i][obj.columnIndexes[3]], ',', '.', true)),
                                            parseFloat(wdtUnformatNumber(filteredData[i][obj.columnIndexes[4]], ',', '.', true)),
                                        ]);
                                    }
                                }
                                obj.options.series[0].data = seriesDataEntry
                            }else if (['arearange', 'areasplinerange', 'columnrange'].includes(obj.options.chart.type)) {
                                for (var i in filteredData) {
                                    if (obj.getNumberFormat() === 1) {
                                        seriesDataEntry.push([
                                            obj.formatDateString(dateAxis[i], wpdatatables_settings.wdtDateFormat),
                                            parseFloat(wdtUnformatNumber(filteredData[i][obj.columnIndexes[1]], '.', ',', true)),
                                            parseFloat(wdtUnformatNumber(filteredData[i][obj.columnIndexes[2]], '.', ',', true)),
                                        ]);
                                    } else {
                                        seriesDataEntry.push([
                                            obj.formatDateString(dateAxis[i], wpdatatables_settings.wdtDateFormat),
                                            parseFloat(wdtUnformatNumber(filteredData[i][obj.columnIndexes[1]], ',', '.', true)),
                                            parseFloat(wdtUnformatNumber(filteredData[i][obj.columnIndexes[2]], ',', '.', true)),
                                        ]);
                                    }
                                }
                                obj.options.series[0].data = seriesDataEntry
                            } else {
                                for (var i in filteredData) {
                                    var entry = filteredData[i][obj.columnIndexes[j]];
                                    if (obj.getNumberFormat() === 1) {
                                        seriesDataEntry.push([
                                            obj.formatDateString(dateAxis[i], wpdatatables_settings.wdtDateFormat),
                                            parseFloat(wdtUnformatNumber(entry, '.', ',', true))
                                        ]);
                                    } else {
                                        seriesDataEntry.push([
                                            obj.formatDateString(dateAxis[i], wpdatatables_settings.wdtDateFormat),
                                            parseFloat(wdtUnformatNumber(entry, ',', '.', true))
                                        ]);
                                    }
                                }

                                obj.options.series[seriesIndex].data = seriesDataEntry;
                                seriesIndex++;
                            }
                        }
                    }

                    if (obj.chart !== null) {
                        obj.chart.destroy();
                    }
                    if (obj.renderCallback !== null) {
                        obj.renderCallback(obj);
                    }

                    if (!['hlc', 'ohlc', 'candlestick'].includes(obj.options.chart.type)) {
                        obj.sortDataAsc();
                    }
                    var chartID = obj.container.replace(/.*_(\d+)/, '$1');
                    if (jQuery('#' + obj.container).parent().find('.wdt-wrapper-chart-loader').length != 0) {
                        jQuery('#' + obj.container).parent().find('.wdt-wrapper-chart-loader').each(function() {
                            if (jQuery(this).attr('data-id') === chartID) {
                                jQuery(this).hide();
                            }
                        });
                    }
                    obj.chart = new Highcharts.stockChart(obj.getContainer(), obj.getOptions());
                    Highcharts.setOptions({
                        lang: {
                            decimalPoint: obj.getNumberFormat() === 1 ? ',' : '.',
                            thousandsSep: obj.getNumberFormat() === 1 ? '.' : ','
                        }
                    });
                }
            });
        }
    };

    return obj;

};