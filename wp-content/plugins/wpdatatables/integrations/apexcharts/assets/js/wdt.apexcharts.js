var wpDataTablesApexChart = function () {

    var obj = {
        container: 'apex-chart-container',
        columnIndexes: [],
        numberFormat: 1,
        decimalPlaces: 2,
        connectedWPDataTable: null,
        renderCallback: null,
        chart: null,
        responsiveWidth: true,
        setContainer: function (container) {
            this.container = container;
        },
        getContainer: function () {
            return this.container;
        },
        setRenderCallback: function (callback) {
            this.renderCallback = callback;
        },
        setWidth: function (width) {
            if (isNaN(width) || width == null || width == 0) {
                this.options.chart.width = '100%';
            } else {
                this.options.chart.width = parseInt(width);
            }
        },
        getWidth: function () {
            return this.options.chart.width;
        },
        setResponsiveWidth: function (responsiveWidth) {
            this.responsiveWidth = responsiveWidth;
        },
        setHeight: function (height) {
            if (isNaN(height) || height == null || height == 0) {
                this.options.chart.width = '100%';
            } else {
                this.options.chart.height = parseInt(height);
            }

        },
        getHeight: function () {
            return this.options.chart.height;
        },
        options: {
            chart: {
                type: 'line',
                height: 400,
                width: '100%',
                selection: {
                    enabled: true,
                },
                animations: {
                    enabled: false
                },
                dropShadow: {
                    enabled: false,
                },
                toolbar: {
                    show: true,
                    tools: {
                        download: false,
                        selection: false,
                        zoom: false,
                        zoomin: false,
                        zoomout: false,
                        pan: false,
                        customIcons: [],
                        reset: true,
                    },
                    export: {
                        csv: {
                            filename: undefined,
                        },
                        svg: {
                            filename: undefined,
                        },
                        png: {
                            filename: undefined,
                        }
                    },
                },
            },
            markers: {
                size: 0,
                hover: {
                    sizeOffset: 1,
                },
            },
            dataLabels: {
                enabled: false
            },
            plotOptions: {
                bar: {
                    colors: {
                        ranges: [{
                            from: 0,
                            to: 0,
                            color: undefined
                        }],
                        backgroundBarOpacity: 1,
                        backgroundBarRadius: 0,
                        backgroundBarColors: [],
                    },
                    horizontal: false,
                    dataLabels: {
                        position: 'center'
                    },
                },
                radialBar: {
                    offsetY: 0,
                    startAngle: 0,
                    endAngle: 360,
                    hollow: {
                        margin: 5,
                        size: '30%',
                        background: 'transparent',
                        image: undefined,
                    },
                    dataLabels: {
                        name: {
                            show: false,
                        },
                        value: {
                            show: false,
                        }
                    }
                },
                pie: {
                    dataLabels: {
                        minAngleToShowLabel: 0
                    },
                    donut: {},
                }
            },
            title: {
                style: {
                    fontWeight: 'bold',
                }
            },
            subtitle: {},
            stroke: {
                width: 2,
            },
            legend: {
                show: true,
                position: "bottom",
                showForSingleSeries: true,
                labels: {
                    colors: undefined,
                }
            },
            series: [
                // array of [name, data] object for axis charts or values for non-axis charts
            ],
            colors: ['#775DD0', '#008FFB', '#00E396', '#FEB019', '#FF4560', '#B6D05D', '#FB6C00', '#E3004D', '#45FFE4'],
            theme: {
                monochrome: {
                    enabled: false,
                    color: '#255aee',
                    shadeTo: 'light',
                    shadeIntensity: 0.65
                },
                palette: undefined,
            },
            fill: {
                gradient: {},
                type: [],
                image: {
                    src: [],
                    width: undefined,
                    height: undefined
                },
                pattern: {},
            },
            xaxis: {
                type: 'category',
                categories: [],
                crosshairs: {
                    show: false,
                    stroke: {},
                },
                labels: {
                    show: true,
                    rotate: 0,
                    hideOverlappingLabels: true,
                },
                title: {
                    text: undefined,
                },
            },
            yaxis: {},
            grid: {
                show: true,
                borderColor: '#000000',
                strokeDashArray: 1,
                position: 'back',
                xaxis: {
                    lines: {}
                },
                yaxis: {
                    lines: {}
                },
            },
            tooltip: {
                enabled: true,
                followCursor: false,
                shared: false,
                intersect: false,
                fillSeriesColor: false
            },
        },
        setOptions: function (options) {
            for (var property in options) {
                this.options[property] = options[property];
            }
        },
        getOptions: function () {
            return this.options;
        },
        setCustomOptions: function (chartConfig) {
            this.setBackground(chartConfig.chart.background);
            if (this.options.xaxis.title)
                this.options.xaxis.title.position = "bottom";
            if (!(['pie', 'donut', 'radialBar', 'radar', 'bar'].includes(this.options.chart.type))
                || Array.isArray(this.options.yaxis)
                || chartConfig.type === 'apexcharts_column_chart'
                || chartConfig.type === 'apexcharts_stacked_column_chart'
                || chartConfig.type === 'apexcharts_100_stacked_column_chart') {
                this.setSeriesOptions(chartConfig);
                this.setMultiYaxisOptions(chartConfig);
            } else if (this.options.chart.type === 'bar' || this.options.chart.type === 'radar')
                this.setSingleYaxisOptions(chartConfig);
        },
        setBackground: function (background) {
            if (background !== '' && !this.isColorValid(background)) {
                this.options.chart.background = this.formUrlFromSource(background);
            } else {
                this.options.chart.background = background;
            }
        },
        setSeriesOptions: function (chartConfig) {
            var series = chartConfig.series;
            if (chartConfig.fill.type !== 'image') {
                this.options.fill.image.src = [];
                this.options.fill.type = [];
            }
            for (var i in series) {
                if (i !== 0 && typeof series[i].yAxis !== 'undefined') {
                    this.options.yaxis[i].opposite = this.options.yaxis[i].show = series[i].yAxis;
                    this.options.yaxis[i].seriesName = this.options.yaxis[i].title.text = series[i].label;
                }
                if (chartConfig.fill.type !== 'image') {
                    this.options.fill.image.src.push(series[i].chart_image);
                    var fillType = series[i].chart_image == '' ? 'solid' : 'image'
                    this.options.fill.type.push(fillType);
                }
            }
        },
        setSingleSeriesType: function (chartConfig) {
            if (Object.keys(chartConfig.series).length === 1 && chartConfig.series[0].type) {
                this.options.chart.type = chartConfig.series[0].type;
                this.options.stroke.curve = this.options.stroke.curve ? this.options.stroke.curve : 'smooth';
                this.options.plotOptions.bar.horizontal = this.options.plotOptions.bar.horizontal ? this.options.plotOptions.bar.horizontal : false;
            }
        },
        setStartEndAngles: function (chartConfig) {
            if (this.options.chart.type === 'radialBar') {
                this.options.plotOptions.radialBar.startAngle = parseInt(chartConfig.plotOptions.radialBar.startAngle);
                this.options.plotOptions.radialBar.endAngle = parseInt(chartConfig.plotOptions.radialBar.endAngle);
            }
        },
        setMultiYaxisOptions: function (chartConfig) {
            for (const i in chartConfig.yaxis) {
                this.options.yaxis[i].tickAmount = chartConfig.yaxis[0].tickAmount != '0' && chartConfig.yaxis[0].tickAmount != undefined
                    ? parseInt(chartConfig.yaxis[0].tickAmount) : undefined;
                if (chartConfig.yaxis[0].min) {
                    this.options.yaxis[i].min = parseFloat(chartConfig.yaxis[0].min);
                }
                if (chartConfig.yaxis[0].max) {
                    this.options.yaxis[i].max = parseFloat(chartConfig.yaxis[0].max);
                }
                this.options.yaxis[i].opposite = chartConfig.yaxis[i].opposite;
                this.options.yaxis[i].seriesName = this.options.yaxis[i].opposite ? chartConfig.series[i].name : chartConfig.series[0].name;
                if (!this.options.yaxis[i].title.text) this.options.yaxis[i].title.text = this.options.series[i].label;
            }
            this.setDecimalsInFloat();
        },
        setSingleYaxisOptions: function (chartConfig) {
            var series = chartConfig.series;
            if (chartConfig.fill.type !== 'image') {
                this.options.fill.image.src = [];
                this.options.fill.type = [];
            }
            for (var i in series) {
                if (chartConfig.fill.type !== 'image') {
                    this.options.fill.image.src.push(series[i].chart_image);
                    var fillType = series[i].chart_image == '' ? 'solid' : 'image'
                    this.options.fill.type.push(fillType);
                }
                this.options.series[i].name = chartConfig.series[i].label;
            }
            this.setDecimalsInFloat();
        },
        setSeriesAndAxis: function (chartConfig) {
            this.setBackground(chartConfig.chart.background);
            if (this.options.xaxis.title)
                this.options.xaxis.title.position = "bottom";
            if (['line', 'area', 'column'].includes(chartConfig.chart.type)) {
                var j = 0;

                for (var i in chartConfig.series) {
                    this.options.colors[j] = chartConfig.series[i].color ? chartConfig.series[i].color : this.options.colors[j];
                    this.options.fill.image[j] = chartConfig.series[i].chart_image ? chartConfig.series[i].chart_image : '';
                    if (chartConfig.series[i].type)
                        this.options.series[j].type = chartConfig.series[i].type;

                    this.options.yaxis[j] = {
                        title: {
                            text: chartConfig.series[i].name,
                        },
                        label: chartConfig.series[i].label,
                        show: j === 0 || chartConfig.series[i].yAxis,
                        crosshairs: {
                            show: chartConfig.yaxis[0].crosshairs.show,
                        },
                        tooltip: {
                            enabled: chartConfig.yaxis[0].tooltip.enabled
                        },
                        seriesName: chartConfig.series[i].yAxis == 1 ? this.options.series[j].name : this.options.series[0].name,
                        min: chartConfig.yaxis[0].min ? parseFloat(chartConfig.yaxis[0].min) : undefined,
                        max: chartConfig.yaxis[0].max ? parseFloat(chartConfig.yaxis[0].max) : undefined,
                        tickAmount: chartConfig.yaxis[0].tickAmount != '0' && chartConfig.yaxis[0].tickAmount != undefined
                            ? parseInt(chartConfig.yaxis[0].tickAmount) : undefined,
                        opposite: chartConfig.series[i].yAxis == 1,
                        reversed: chartConfig.yaxis[i].reversed,
                    };
                    j++;
                    this.options.series[i].name = this.options.series[i].label;
                }
            }
        },
        setAxisTitles: function () {
            if (!this.options.xaxis.title.text)
                this.options.xaxis.title.text = this.options.series[0].label;
            if (Array.isArray(this.options.yaxis) && !this.options.yaxis[0].title.text)
                this.options.yaxis[0].title.text = this.options.series[0].label;
        },
        setStrokeWidth: function (width) {
            if (width) this.options.stroke.width = parseInt(width);
        },
        isColorValid: function (color) {
            var e = document.getElementById('divValidColor');
            if (!e) {
                e = document.createElement('div');
                e.id = 'divValidColor';
            }
            e.style.borderColor = '';
            e.style.borderColor = color;
            var tmpcolor = e.style.borderColor;
            return tmpcolor.length != 0;

        },
        assesGradientFillType: function (imageSource) {
            return imageSource != '' || (Array.isArray(imageSource) && imageSource.length);
        },
        setType: function (type) {
            switch (type) {
                case 'apexcharts_spline_chart':
                    this.options.chart.type = 'line';
                    this.options.stroke.curve = 'smooth';
                    this.options.plotOptions.bar.horizontal = false;
                    break;
                case 'apexcharts_stepline_chart':
                    this.options.chart.type = 'line';
                    this.options.stroke.curve = 'stepline';
                    this.options.plotOptions.bar.horizontal = false;
                    break;
                case 'apexcharts_area_chart':
                    this.options.chart.type = 'area';
                    this.options.plotOptions.bar.horizontal = false;
                    break;
                case 'apexcharts_spline_area_chart':
                    this.options.chart.type = 'area';
                    this.options.stroke.curve = 'smooth';
                    this.options.plotOptions.bar.horizontal = false;
                    break;
                case 'apexcharts_basic_area_chart':
                    this.options.chart.type = 'area';
                    this.options.stroke.curve = 'straight';
                    this.options.plotOptions.bar.horizontal = false;
                    break;
                case 'apexcharts_stepline_area_chart':
                    this.options.chart.type = 'area';
                    this.options.stroke.curve = 'stepline';
                    this.options.plotOptions.bar.horizontal = false;
                    break;
                case 'apexcharts_grouped_bar_chart':
                    this.options.chart.type = 'bar';
                    this.options.plotOptions.bar.horizontal = true;
                    break;
                case 'apexcharts_stacked_bar_chart':
                    this.options.chart.type = 'bar';
                    this.options.chart.stacked = true;
                    this.options.plotOptions.bar.horizontal = true;
                    break;
                case 'apexcharts_100_stacked_bar_chart':
                    this.options.chart.type = 'bar';
                    this.options.chart.stacked = true;
                    this.options.chart.stackType = '100%'
                    this.options.plotOptions.bar.horizontal = true;
                    break;
                case 'apexcharts_stacked_column_chart':
                    this.options.chart.type = 'bar';
                    this.options.chart.stacked = true;
                    this.options.plotOptions.bar.horizontal = false;
                    break;
                case 'apexcharts_100_stacked_column_chart':
                    this.options.chart.type = 'bar';
                    this.options.chart.stacked = true;
                    this.options.chart.stackType = '100%'
                    this.options.plotOptions.bar.horizontal = false;
                    break;
                case 'apexcharts_column_chart':
                    this.options.chart.type = 'bar';
                    this.options.plotOptions.bar.horizontal = false;
                    break;
                case 'apexcharts_pie_chart':
                    this.options.chart.type = 'pie';
                    this.options.plotOptions.bar.horizontal = false;
                    delete this.options.colors;
                    break;
                case 'apexcharts_donut_chart':
                    this.options.chart.type = 'donut';
                    this.options.plotOptions.bar.horizontal = false;
                    delete this.options.colors;
                    break;
                case 'apexcharts_pie_with_gradient_chart':
                    this.options.chart.type = 'pie';
                    this.options.fill.type = this.assesGradientFillType(this.options.fill.image.src) ? 'image' : 'gradient';
                    delete this.options.colors;
                    this.options.plotOptions.bar.horizontal = false;
                    break;
                case 'apexcharts_donut_with_gradient_chart':
                    this.options.chart.type = 'donut';
                    this.options.fill.type = this.assesGradientFillType(this.options.fill.image.src) ? 'image' : 'gradient';
                    delete this.options.colors;
                    this.options.plotOptions.bar.horizontal = false;
                    break;
                case 'apexcharts_radar_chart':
                    this.options.chart.type = 'radar';
                    this.options.grid.show = false;
                    this.options.plotOptions.bar.horizontal = false;
                    break;
                case 'apexcharts_radialbar_chart':
                    this.options.chart.type = 'radialBar';
                    delete this.options.plotOptions.bar;
                    break;
                case 'apexcharts_radialbar_gauge_chart':
                    this.options.chart.type = 'radialBar';
                    delete this.options.plotOptions.bar;
                    this.options.stroke.dashArray = 4;
                    break;
                case 'apexcharts_polar_area_chart':
                    this.options.chart.type = 'polarArea';
                    this.options.plotOptions.bar.horizontal = false;
                    break;
                case 'apexcharts_bubble_chart':
                    this.options.chart.type = 'bubble';
                    this.options.plotOptions.bar.horizontal = false;
                    break;
                case 'apexcharts_straight_line_chart':
                case 'apexcharts_line_chart':
                default:
                    this.options.chart.type = 'line';
                    this.options.stroke.curve = 'straight';
                    this.options.plotOptions.bar.horizontal = false;
                    break;
            }
        },
        render: function (isTableLoaded = false) {
            if (this.renderCallback !== null) {
                this.renderCallback(this);
            }
            var container = this.getContainer().id ? this.getContainer().id : this.getContainer();

            if (isTableLoaded) {
                this.chart.destroy();
            }

            if (container === '#apex-chart-container' && jQuery(container).length) {
                jQuery(container).empty();
            }

            this.setStrokeWidth(this.options.stroke.width);
            this.setDecimalsInFloat();

            this.chart = new ApexCharts(
                document.querySelector(container),
                this.options
            );
            this.chart.render();

        },
        refresh: function () {
            this.chart.render();
        },
        setConnectedWPDataTable: function (wpDataTable) {
            this.connectedWPDataTable = wpDataTable;
        },
        setNumberFormat: function (numberFormat) {
            this.numberFormat = numberFormat;
        },
        getNumberFormat: function () {
            return this.numberFormat;
        },
        setDecimalPlaces: function (decimalPlaces) {
            this.decimalPlaces = decimalPlaces;
        },
        getDecimalPlaces: function () {
            return this.decimalPlaces;
        },
        setDecimalsInFloat: function () {
            var decimals = this.getDecimalPlaces() ? this.getDecimalPlaces() : 2;
            if (Array.isArray(this.options.yaxis)) {
                for (var i in this.options.yaxis) {
                    this.options.yaxis[i].decimalsInFloat = decimals;
                }
            } else {
                this.options.yaxis.decimalsInFloat = decimals;
            }
        },
        setGrouping: function (group_chart) {
            this.group_chart = group_chart;
        },
        setColumnIndexes: function (columnIndexes) {
            this.columnIndexes = columnIndexes;
        },
        formUrlFromSource: function (sourceUrl) {
            if (sourceUrl && sourceUrl !== '') {
                if (sourceUrl.startsWith('url(')) return sourceUrl;
                return 'url(' + sourceUrl + ') no-repeat center/cover';
            } else return '#FFFFFF';
        },
        setChartConfig: function (chartConfig) {
            // Chart
            if (chartConfig.responsive_width !== 1) {
                this.setWidth(chartConfig.width);
            } else this.setWidth(0);
            this.setResponsiveWidth(chartConfig.responsive_width);
            this.setHeight(chartConfig.height);
            this.options.chart.animations.enabled = chartConfig.enable_animation === 1;
            if (this.options.chart.type === 'pie') {
                if (chartConfig.show_data_labels === 1) {
                    this.options.dataLabels.enabled = true;
                    this.options.plotOptions.pie = {
                        dataLabels: {
                            minAngleToShowLabel: 0
                        }
                    }
                } else {
                    this.options.dataLabels.enabled = false;
                }
            } else if (this.options.chart.type === 'radialBar') {
                this.options.plotOptions.radialBar.dataLabels.show = chartConfig.show_data_labels === 1;
            } else {
                this.options.dataLabels.enabled = chartConfig.show_data_labels === 1;
            }
            this.options.chart.foreColor = chartConfig.text_color ? chartConfig.text_color : undefined;
            this.options.chart.background = chartConfig.background_color;
            if (chartConfig.plot_background_image !== '' && !this.isColorValid(chartConfig.plot_background_image)) {
                this.options.chart.background = this.formUrlFromSource(chartConfig.plot_background_image);
            }
            if (chartConfig.line_background_image != '') {
                this.options.fill.type = 'image';
                this.options.fill.image.src = chartConfig.line_background_image;
            } else {
                this.options.fill.type = ['apexcharts_pie_with_gradient_chart', 'apexcharts_donut_with_gradient_chart'].includes(chartConfig.type) ? 'gradient' : 'solid';
                this.options.fill.image.src = [];
            }
            if (!(['pie', 'donut'].includes(this.options.chart.type))) {
                (chartConfig.zoom_type || chartConfig.zoom_type !== 'None') ? this.options.chart.zoom.type = chartConfig.zoom_type : this.options.chart.zoom.enabled = false;
            } else if (chartConfig.enable_color_palette) {
                this.options.theme.monochrome.enabled = false;
                this.options.theme.palette = chartConfig.color_palette ? chartConfig.color_palette : 'palette1';
            } else {
                this.options.theme.monochrome.enabled = chartConfig.monochrome === 1;
                this.options.theme.monochrome.color = chartConfig.monochrome_color ? chartConfig.monochrome_color : '#255aee';
            }
            this.options.plotOptions.radialBar.startAngle = chartConfig.start_angle ? parseInt(chartConfig.start_angle) : 0;
            this.options.plotOptions.radialBar.endAngle = chartConfig.end_angle ? parseInt(chartConfig.end_angle) : 360;

            if (chartConfig.enable_dropshadow == true) {
                this.options.chart.dropShadow.enabled = true;
                this.options.chart.dropShadow.blur = chartConfig.dropshadow_blur ? chartConfig.dropshadow_blur : 3;
                this.options.chart.dropShadow.opacity = chartConfig.dropshadow_opacity ? chartConfig.dropshadow_opacity : 0.35;
                this.options.chart.dropShadow.color = chartConfig.dropshadow_color ? chartConfig.dropshadow_color : '#000000';
                this.options.chart.dropShadow.left = chartConfig.dropshadow_left ? chartConfig.dropshadow_left : 5;
                this.options.chart.dropShadow.top = chartConfig.dropshadow_top ? chartConfig.dropshadow_top : 5;
            } else {
                this.options.chart.dropShadow.enabled = false;
            }

            // Tooltip
            if (chartConfig.tooltip_enabled == 1) {
                this.options.tooltip.enabled = true;
                this.options.tooltip.shared = this.options.chart.type !== 'radar';
                this.options.tooltip.intersect = this.options.chart.type === 'radar';
                this.options.tooltip.followCursor = chartConfig.follow_cursor == 1 ? true : false;
                this.options.tooltip.fillSeriesColor = chartConfig.fill_series_color == 1 ? true : false;
            } else {
                this.options.tooltip.enabled = false;
            }

            // Series
            if (!(['pie', 'donut', 'radar', 'radialBar', 'bar'].includes(this.options.chart.type))
                || chartConfig.type === 'apexcharts_column_chart'
                || chartConfig.type === 'apexcharts_stacked_column_chart'
                || chartConfig.type === 'apexcharts_100_stacked_column_chart') {
                var j = 0;
                this.options.fill.type = this.options.fill.type === 'image' ? 'image' : [];

                for (var i in chartConfig.series_data) {
                    this.options.series[j].name = chartConfig.series_data[i].label;
                    this.options.colors[j] = this.options.series[j].color = chartConfig.series_data[i].color != null ? chartConfig.series_data[i].color : this.options.colors[j];
                    this.options.fill.image.src[j] = chartConfig.series_data[i].chart_image;
                    this.options.fill.type[j] = chartConfig.series_data[i].chart_image === '' ? 'solid' : 'image';
                    if (chartConfig.series_data[i].type && Object.keys(chartConfig.series_data).length === 1) {
                        var strokeCurve = this.options.stroke.curve ? this.options.stroke.curve : 'smooth';
                        var horizontalBar = this.options.plotOptions.bar.horizontal ? this.options.plotOptions.bar.horizontal : false;
                        this.setType('apexcharts_' + chartConfig.series_data[i].type + '_chart');
                        this.options.stroke.curve = strokeCurve;
                        this.options.plotOptions.bar.horizontal = horizontalBar;
                    } else {
                        switch (chartConfig.series_data[i].type) {
                            case 'area':
                                this.options.series[j].type = 'area';
                                break;
                            case 'bar':
                                this.options.series[j].type = 'bar';
                                this.options.plotOptions.bar.colors = [];
                                this.options.plotOptions.bar.colors.backgroundBarColors = [];
                                this.options.plotOptions.bar.colors.backgroundBarOpacity = 1;
                                this.options.plotOptions.bar.colors.backgroundBarRadius = 0;
                                this.options.plotOptions.bar.colors.ranges = [];
                                this.options.plotOptions.bar.dataLabels = {
                                    'position': 'center'
                                };
                                break;
                            case 'line':
                                this.options.series[j].type = 'line';
                                break;
                            case 'spline':
                                this.options.series[j].type = 'line';
                                this.options.stroke.curve = 'smooth';
                                break;
                            default:
                                this.options.series[j].type = this.options.chart.type ? this.options.chart.type : 'line';
                                break;
                        }
                    }
                    this.options.yaxis[j] = {
                        title: {
                            text: (chartConfig.vertical_axis_label && j === 0) ? chartConfig.vertical_axis_label : chartConfig.series_data[i].label
                        },
                        show: j === 0 ? true : chartConfig.series_data[i].yAxis == 1,
                        seriesName: chartConfig.series_data[i].yAxis == 1 ? this.options.series[j].name : this.options.series[0].name,
                        min: chartConfig.vertical_axis_min ? parseFloat(chartConfig.vertical_axis_min) : undefined,
                        max: chartConfig.vertical_axis_max ? parseFloat(chartConfig.vertical_axis_max) : undefined,
                        tickAmount: chartConfig.tick_amount && chartConfig.tick_amount !== "0" ? parseInt(chartConfig.tick_amount) : undefined,
                        opposite: chartConfig.series_data[i].yAxis == 1,
                        type: chartConfig.series_data[i].type,
                        crosshairs: {
                            show: chartConfig.vertical_axis_crosshair == 1,
                        },
                        tooltip: {
                            enabled: chartConfig.vertical_axis_crosshair == 1,
                        },
                    };
                    this.options.yaxis[j].reversed = chartConfig.reversed != 0;
                    j++;
                }

                // Axes
                if (chartConfig.show_grid == 0) {
                    this.options.grid.show = false;
                } else {
                    this.options.grid.show = true;
                    this.options.grid.borderColor = chartConfig.grid_color ? chartConfig.grid_color : "#000000";
                    this.options.grid.strokeDashArray = chartConfig.grid_stroke ? chartConfig.grid_stroke : 1;
                    this.options.grid.position = chartConfig.grid_position ? chartConfig.grid_position : "back";
                    this.options.grid.xaxis = [];
                    this.options.grid.yaxis = [];
                    this.options.grid.xaxis.lines = [];
                    this.options.grid.yaxis.lines = [];
                    this.options.grid.xaxis.lines.show = !!chartConfig.grid_axes.includes('xaxis');
                    this.options.grid.yaxis.lines.show = !!chartConfig.grid_axes.includes('yaxis');
                }
                this.options.xaxis.crosshairs.show = chartConfig.horizontal_axis_crosshair == 1;
                this.options.xaxis.tooltip.enabled = chartConfig.horizontal_axis_crosshair == 1;
                this.options.markers.size = parseInt(chartConfig.marker_size);
                this.options.stroke.width = chartConfig.stroke_width ? parseInt(chartConfig.stroke_width) : 2;
                this.options.xaxis.title.text = chartConfig.horizontal_axis_label ? chartConfig.horizontal_axis_label : undefined;
            } else if (this.options.chart.type === 'radar') {
                var j = 0;

                this.options.grid.show = this.options.chart.type !== 'radar' && chartConfig.show_grid == 1;
                this.options.fill.type = [];
                for (var i in chartConfig.series_data) {
                    this.options.series[j].name = chartConfig.series_data[i].label;
                    this.options.colors[j] = this.options.series[j].color = chartConfig.series_data[i].color ? chartConfig.series_data[i].color : this.options.colors[j];
                    this.options.fill.image.src[j] = chartConfig.series_data[i].chart_image;
                    this.options.fill.type[j] = this.options.fill.image.src[j] === '' ? 'solid' : 'image';
                    j++;
                }
                this.options.markers.size = parseInt(chartConfig.marker_size);
            } else if (this.options.chart.type === 'bar') {
                var j = 0;
                this.options.fill.type = this.options.fill.type === 'image' ? 'image' : [];

                for (var i in chartConfig.series_data) {
                    if (j === 0) var yAxisTitleTex = chartConfig.series_data[i].label;
                    this.options.series[j].name = chartConfig.series_data[i].label;
                    this.options.colors[j] = this.options.series[j].color = chartConfig.series_data[i].color ? chartConfig.series_data[i].color : this.options.colors[j];
                    this.options.fill.image.src[j] = chartConfig.series_data[i].chart_image;
                    this.options.fill.type[j] = chartConfig.series_data[i].chart_image === '' ? 'solid' : 'image';
                    j++;
                }
                this.options.yaxis = {
                    title: {
                        text: chartConfig.vertical_axis_label ? chartConfig.vertical_axis_label : yAxisTitleTex
                    },
                    min: chartConfig.vertical_axis_min ? parseFloat(chartConfig.vertical_axis_min) : undefined,
                    max: chartConfig.vertical_axis_max ? parseFloat(chartConfig.vertical_axis_max) : undefined,
                    tickAmount: chartConfig.tick_amount && chartConfig.tick_amount !== "0" ? parseInt(chartConfig.tick_amount) : undefined,

                };
                this.options.yaxis.reversed = chartConfig.reversed != 0;

                // Axes
                if (chartConfig.show_grid == 0) {
                    this.options.grid.show = false;
                } else {
                    this.options.grid.show = true;
                    this.options.grid.borderColor = chartConfig.grid_color ? chartConfig.grid_color : "#000000";
                    this.options.grid.strokeDashArray = chartConfig.grid_stroke ? chartConfig.grid_stroke : 1;
                    this.options.grid.position = chartConfig.grid_position ? chartConfig.grid_position : "back";
                    this.options.grid.xaxis = [];
                    this.options.grid.yaxis = [];
                    this.options.grid.xaxis.lines = [];
                    this.options.grid.yaxis.lines = [];
                    this.options.grid.xaxis.lines.show = !!chartConfig.grid_axes.includes('xaxis');
                    this.options.grid.yaxis.lines.show = !!chartConfig.grid_axes.includes('yaxis');
                }
                this.options.xaxis.crosshairs.show = chartConfig.horizontal_axis_crosshair == 1;
                this.options.xaxis.tooltip.enabled = chartConfig.horizontal_axis_crosshair == 1;
                this.options.markers.size = parseInt(chartConfig.marker_size);
                this.options.stroke.width = chartConfig.stroke_width ? parseInt(chartConfig.stroke_width) : 2;
                this.options.xaxis.title.text = chartConfig.horizontal_axis_label ? chartConfig.horizontal_axis_label : undefined;
            }

            // Title
            chartConfig.show_title == 1 ? this.options.title.text = chartConfig.title : this.options.title.text = '';
            chartConfig.title_floating == 1 ? this.options.title.floating = true : this.options.title.floating = false;
            chartConfig.title_align ? this.options.title.align = chartConfig.title_align : this.options.title.align = undefined;
            // chartConfig.title_font_style ? this.options.title.style.fontWeight = chartConfig.title_font_style : this.options.title.style.fontWeight = 'bold';
            chartConfig.subtitle ? this.options.subtitle.text = chartConfig.subtitle : this.options.subtitle.text = undefined;
            chartConfig.subtitle_align ? this.options.subtitle.align = chartConfig.subtitle_align : undefined;

            // Legend
            chartConfig.show_legend == 1 ? this.options.legend.show = true : this.options.legend.show = false;
            this.options.legend.showForSingleSeries = true;
            chartConfig.legend_position_cjs !== '' ? this.options.legend.position = chartConfig.legend_position_cjs : this.options.legend.position = 'bottom';

            // Toolbar
            if (chartConfig.show_toolbar == 0) {
                this.options.chart.toolbar.show = false;
            } else {
                this.options.chart.toolbar.show = true;
                this.options.chart.toolbar.tools.reset = true;
                this.options.chart.toolbar.tools.download = !!chartConfig.toolbar_buttons.includes('download');
                this.options.chart.toolbar.tools.selection = !!chartConfig.toolbar_buttons.includes('selection');
                this.options.chart.selection.enabled = true;
                this.options.chart.toolbar.tools.zoom = !!chartConfig.toolbar_buttons.includes('zoom');
                this.options.chart.toolbar.tools.zoomin = !!chartConfig.toolbar_buttons.includes('zoomin');
                this.options.chart.toolbar.tools.zoomout = !!chartConfig.toolbar_buttons.includes('zoomout');
                this.options.chart.toolbar.tools.pan = !!chartConfig.toolbar_buttons.includes('pan');
            }

            var filename = chartConfig.apex_exporting_file_name ? chartConfig.apex_exporting_file_name : 'Chart';
            this.options.chart.toolbar.export.csv.filename = this.options.chart.toolbar.export.svg.filename = this.options.chart.toolbar.export.png.filename = filename;

        },
        enableFollowFiltering: function () {
            if (this.connectedWPDataTable == null) {
                return;
            }

            this.connectedWPDataTable.fnSettings().aoDrawCallback.push({
                sName: 'chart_filter_follow',
                fn: function (oSettings) {
                    obj.options.xaxis.categories = [];
                    var seriesIndex = 0;
                    var isSingleSeriesType = obj.options.chart.type === 'radialBar' || obj.options.chart.type === 'pie' || obj.options.chart.type === 'donut';
                    var filteredData = obj.connectedWPDataTable._('tr', {
                        "filter": "applied"
                    }).toArray();
                    for (var j in obj.columnIndexes) {
                        var seriesDataEntry = [];
                        var categoriesEntry = [];
                        if ((obj.columnIndexes.length > 0) &&
                            (j == 0)) {
                            for (var i in filteredData) {
                                obj.options.xaxis.categories.push(filteredData[i][obj.columnIndexes[0]]);
                            }
                        } else {
                            for (var i in filteredData) {
                                var entry = filteredData[i][obj.columnIndexes[j]];

                                if (obj.numberFormat == 1) {
                                    seriesDataEntry.push({
                                        data: parseFloat(wdtUnformatNumber(entry, '.', ',', true))
                                    });
                                } else {
                                    seriesDataEntry.push({
                                        data: parseFloat(wdtUnformatNumber(entry, ',', '.', true))
                                    });
                                }
                                categoriesEntry.push(filteredData[i][obj.columnIndexes[0]]);
                            }
                            if (obj.group_chart == 1) {
                                var outputData = [];
                                var outputCategories = [];
                                for (var i in seriesDataEntry) {
                                    if (typeof outputData !== 'undefined' && outputData.length > 0) {
                                        var value_key = 'none';
                                        for (var j in outputData) {
                                            if (value_key === 'none') {
                                                if (outputCategories[j] == categoriesEntry[i]) {
                                                    value_key = j;
                                                }
                                            }
                                        }
                                        if (value_key === 'none') {
                                            outputData.push(seriesDataEntry[i]['data']);
                                            outputCategories.push(categoriesEntry[i]);
                                        } else {
                                            outputData[value_key] += seriesDataEntry[i]['data'];
                                        }
                                    } else {
                                        outputData.push(seriesDataEntry[i]['data']);
                                        outputCategories.push(categoriesEntry[i]);
                                    }
                                }

                                obj.options.xaxis.categories = outputCategories;
                                seriesDataEntry = outputData;
                                if (isSingleSeriesType) {
                                    obj.options.series = seriesDataEntry;
                                } else {
                                    obj.options.series[seriesIndex].data = seriesDataEntry;
                                }
                            } else {
                                if (isSingleSeriesType) {
                                    obj.options.series = [];
                                } else {
                                    obj.options.series[j - 1].data = [];
                                }
                                for (var key in seriesDataEntry) {
                                    if (isSingleSeriesType && obj.options.chart.type !== 'bar') {
                                        obj.options.series.push(seriesDataEntry[key]['data']);
                                    } else if (obj.options.series[j - 1]) {
                                        obj.options.series[j - 1].data.push(seriesDataEntry[key]['data']);
                                    }
                                }
                            }

                            seriesIndex++;
                        }
                    }
                    if (obj.group_chart == 1) {
                        obj.options.xaxis.categories = obj.options.xaxis.categories.filter(function (itm, i, a) {
                            return i == a.indexOf(itm);
                        });
                    }
                    if (obj.chart !== null) {
                        obj.chart.destroy();
                    }
                    if (obj.renderCallback !== null) {
                        obj.renderCallback(obj);
                    }
                    var container = obj.getContainer().id ? obj.getContainer().id : obj.getContainer();
                    obj.chart = new ApexCharts(
                        document.querySelector(container),
                        obj.options
                    );

                    obj.options.labels = obj.options.xaxis.categories;

                    obj.setOptions(obj.options);
                    obj.setWidth(obj.options.chart.width);
                    obj.setHeight(obj.options.chart.height);
                    obj.render();
                }
            });
        }
    }

    return obj;
}