if (typeof constructedChartData == 'undefined') {
    var constructedChartData = {};
}

var wdtChartColumnsData = {};
let singleSeriesFromMultipleTypes = ['highstock_area_range_chart', 'highstock_area_spline_range_chart',
    'highstock_column_range_chart', 'highstock_candlestick_chart', 'highstock_hlc_chart', 'highstock_ohlc_chart'];

(function ($) {
    var wdtChartPickerDragStart = 0;
    var wdtChartPickerDragEnd = 0;
    var wdtChartPickerIsDragging = false;
    var wdtChart = null;
    var nextStepButton = $('#wdt-chart-wizard-next-step');
    var previousStepButton = $('#wdt-chart-wizard-previous-step');

    $('.wdt-chart-wizard-chart-selecter-block .card').on('click', function () {
        $('.wdt-chart-wizard-chart-selecter-block .card').removeClass('selected').addClass('not-selected');
        $(this).addClass('selected').removeClass('not-selected');
        nextStepButton.prop('disabled', false);
    });

    $('.chart-container.region').on('click', function (e) {
        if ($('#region-google-charts').val() === 'world') {
            $('#sub-continents-geochart-container button.btn.dropdown-toggle.btn-default').addClass('disabled');
            $('#countries-geochart-container button.btn.dropdown-toggle.btn-default').addClass('disabled');
        } else {
            $('#sub-continents-geochart-container button.btn.dropdown-toggle.btn-default').removeClass('disabled');
            if ($('#sub-continents-geochart').val() === 'world') {
                $('#countries-geochart-container button.btn.dropdown-toggle.btn-default').addClass('disabled');
            }
        }
    });
    /**
     * Steps switcher (Next)
     */
    nextStepButton.on('click', function (e) {
        e.preventDefault();

        var curStep = $('div.chart-wizard-step:visible').data('step');
        $('div.chart-wizard-step').hide();
        $('li.chart_wizard_breadcrumbs_block').removeClass('active');
        $('.wdt-preload-layer').animateFadeIn();

        switch (curStep) {
            case 'step1':
                // Data source
                constructedChartData.type = $('.wdt-chart-wizard-chart-selecter-block .card.selected').data('type');
                constructedChartData.min_columns = parseInt($('.card.selected').data('min_columns'));
                constructedChartData.max_columns = parseInt($('.card.selected').data('max_columns'));
                $('div.chart-wizard-step.step2').show();
                $('li.chart_wizard_breadcrumbs_block.step2').addClass('active');
                constructedChartData.title = $('#chart-name').val();
                constructedChartData.engine = $('#chart-render-engine').val();
                if ($('#chart-render-engine').val() == 'google') {

                    $("#chart-js-container").hide();
                    $("#apexcharts-chart-container").hide();
                    $("#google-chart-container").show();

                    $(".highcharts").hide();
                    $(".highstock").hide();
                    $(".chartjs").hide();
                    $(".apexcharts").hide();
                    $(".google").show();

                    $('.apex-toolbar-container').hide();
                    $('#curve-type-row').hide();
                    $('#three-d-row').hide();
                    $('#background_color_row').show();
                    $('#border_width_row').show();
                    $('#border_color_row').show();
                    $('#border_radius_row').show();
                    $('#plot_background_color_row').show();
                    $('#plot-border-width-row').show();
                    $('#plot_border_color_row').show();
                    $('#font-size-row').show();
                    $('#font-name-row').show();
                    $('.series').show();
                    $('.axes').show();
                    $('#show-grid-row').show();
                    $('#horizontal-axis-crosshair-row').show();
                    $('#vertical-axis-crosshair-row').show();
                    $('.title').show();
                    $('#title-floating-row').show();
                    $('.tooltip').show();
                    $('.legend').show();
                    $('#inverted-row').show();
                    $('.region').hide();

                    switch (constructedChartData.type) {
                        case 'google_column_chart':
                            $('#horizontal-axis-crosshair-row').hide();
                            $('#vertical-axis-crosshair-row').hide();
                            break;
                        case 'google_histogram':
                            $('#horizontal-axis-crosshair-row').hide();
                            $('#vertical-axis-crosshair-row').hide();
                            break;
                        case 'google_bar_chart':
                            $('#horizontal-axis-crosshair-row').hide();
                            $('#vertical-axis-crosshair-row').hide();
                            $('#inverted-row').hide();
                            break;
                        case 'google_stacked_bar_chart':
                            $('#horizontal-axis-crosshair-row').hide();
                            $('#vertical-axis-crosshair-row').hide();
                            $('#inverted-row').hide();
                            break;
                        case 'google_line_chart':
                            $('#curve-type-row').show();
                            break;
                        case 'google_stepped_area_chart':
                            $('#horizontal-axis-crosshair-row').hide();
                            $('#vertical-axis-crosshair-row').hide();
                            break;
                        case 'google_pie_chart':
                            $('#plot_background_color_row').hide();
                            $('#plot-border-width-row').hide();
                            $('#plot_border_color_row').hide();
                            $('#three-d-row').show();
                            $('.axes').hide();
                            $('#title-floating-row').hide();
                            $('.series').hide();
                            break;
                        case 'google_bubble_chart':
                            $('.legend').hide();
                            $('#inverted-row').hide();
                            break;
                        case 'google_donut_chart':
                            $('#plot_background_color_row').hide();
                            $('#plot-border-width-row').hide();
                            $('#plot_border_color_row').hide();
                            $('.axes').hide();
                            $('#title-floating-row').hide();
                            $('.series').hide();
                            break;
                        case 'google_gauge_chart':
                            $('#background_color_row').hide();
                            $('#border_width_row').hide();
                            $('#border_color_row').hide();
                            $('#border_radius_row').hide();
                            $('#plot_background_color_row').hide();
                            $('#plot-border-width-row').hide();
                            $('#plot_border_color_row').hide();
                            $('#font-size-row').hide();
                            $('#font-name-row').hide();
                            $('#show-grid-row').hide();
                            $('.axes').hide();
                            $('.title').hide();
                            $('.tooltip').hide();
                            $('.legend').hide();
                            $('.series').hide();
                            break;
                        case 'google_scatter_chart':
                            $('#inverted-row').hide();
                            break;
                        case 'google_candlestick_chart':
                            $('.series').hide();
                            $('#horizontal-axis-crosshair-row').hide();
                            $('#vertical-axis-crosshair-row').hide();
                            $('.legend').hide();
                            break;
                        case 'google_waterfall_chart':
                            $('.series').hide();
                            $('#horizontal-axis-crosshair-row').hide();
                            $('#vertical-axis-crosshair-row').hide();
                            $('.legend').hide();
                            break;
                        case 'google_geo_chart':
                            $('.region').show();
                            var googleKeyValidate = parseInt(wpdatatables_mapsapikey.wdtGoogleApiMapsValidated) || 0;
                            if (googleKeyValidate != 1) {
                                $('#google-region-geochart-container').hide();
                                $('#sub-continents-geochart-container').hide();
                                $('#countries-geochart-container').hide();
                            }
                            $('#plot_background_color_row').hide();
                            $('#plot-border-width-row').hide();
                            $('#plot_border_color_row').hide();
                            $('.series').hide();
                            $('#plot-background-color-container').hide();
                            $('#plot-border-color-container').hide();
                            $('.axes').hide();
                            $('.title').hide();
                            $('.tooltip').hide();
                            $('#legend_vertical_align_row').hide();
                            $('.legend').hide();
                            $('#group-chart').prop('checked', 'checked');
                            break;
                        case 'google_marker_geo_chart':
                        case 'google_text_geo_chart':
                            $('.region').show();
                            $('#plot_background_color_row').hide();
                            $('#plot-border-width-row').hide();
                            $('#plot_border_color_row').hide();
                            $('.series').hide();
                            $('#plot-background-color-container').hide();
                            $('#plot-border-color-container').hide();
                            $('.axes').hide();
                            $('.title').hide();
                            $('.tooltip').hide();
                            $('#legend_vertical_align_row').hide();
                            $('.legend').hide();
                            $('#group-chart').prop('checked', 'checked');
                            break;
                    }
                } else if ($('#chart-render-engine').val() == 'highcharts') {

                    $("#chart-js-container").hide();
                    $("#apexcharts-chart-container").hide();
                    $("#google-chart-container").show();

                    $(".google").hide();
                    $(".chartjs").hide();
                    $(".apexcharts").hide();
                    $(".highstock").hide();
                    $(".highcharts").show();

                    $('.apex-toolbar-container').hide();
                    $('#border_width_row').show();
                    $('#border_color_row').show();
                    $('#border_radius_row').show();
                    $('#zoom-type-row').show();
                    if ($("#zoom-type")[0].options.length === 2) {
                        $("#zoom-type").append('<option value="y">Y</option>');
                        $("#zoom-type").append('<option value="xy">XY</option>');
                    }

                    $('#panning-row').show();
                    $('#pan-key-row').show();
                    $('#plot-border-width-row').show();
                    $('#plot_border_color_row').show();
                    $('.series').show();
                    $('.axes').show();
                    $('.legend').show();
                    $('.region').hide();

                    if (constructedChartData.type == 'highcharts_basic_bar_chart' ||
                        constructedChartData.type == 'highcharts_stacked_bar_chart') {
                        $('#inverted-row').hide();
                    } else if (constructedChartData.type == 'highcharts_pie_chart' ||
                        constructedChartData.type == 'highcharts_pie_with_gradient_chart' ||
                        constructedChartData.type == 'highcharts_3d_pie_chart' ||
                        constructedChartData.type == 'highcharts_donut_chart' ||
                        constructedChartData.type == 'highcharts_3d_donut_chart' ||
                        constructedChartData.type == 'highcharts_treemap_chart' ||
                        constructedChartData.type == 'highcharts_treemap_level_chart' ||
                        constructedChartData.type == 'highcharts_funnel3d_chart' ||
                        constructedChartData.type == 'highcharts_funnel_chart'
                    ) {
                        $('#border_width_row').hide();
                        $('#border_color_row').hide();
                        $('#border_radius_row').hide();
                        $('#zoom-type-row').hide();
                        $('#panning-row').hide();
                        $('#pan-key-row').hide();
                        $('#plot_border_color_row').hide();
                        $('.axes').hide();
                        $('.legend').hide();
                        $('.series').hide();
                    } else if (constructedChartData.type == 'highcharts_spiderweb_chart' ||
                        constructedChartData.type == 'highcharts_polar_chart') {
                        $('.axes').hide();
                        $('#border_width_row').hide();
                        $('#border_color_row').hide();
                        $('#border_radius_row').hide();
                        $('#zoom-type-row').hide();
                        $('#panning-row').hide();
                        $('#pan-key-row').hide();
                        $('#plot-border-width-row').show();
                        $('#plot_border_color_row').hide();
                    }
                } else if ($('#chart-render-engine').val() == 'chartjs') {

                    $("#google-chart-container").hide();
                    $("#apexcharts-chart-container").hide();
                    $("#chart-js-container").show();

                    $(".google").hide();
                    $(".highcharts").hide();
                    $(".apexcharts").hide();
                    $(".highstock").hide();
                    $(".chartjs").show();
                    $('.region').hide();

                    $('.apex-toolbar-container').hide();
                    $('.series').show();
                    $('#curve-type-row').hide();
                    var legendPositionCjsVal = $('#legend-position-cjs').val() ? $('#legend-position-cjs').val() : 'top';
                    $('#legend-position-cjs').val(legendPositionCjsVal).change();

                    switch (constructedChartData.type) {
                        case 'chartjs_line_chart':
                            $('#curve-type-row').show();
                            break;
                        case 'chartjs_area_chart':
                            $('#curve-type-row').show();
                            break;
                        case 'chartjs_stacked_area_chart':
                            $('#curve-type-row').show();
                            break;
                        case 'chartjs_bar_chart':
                        case 'chartjs_stacked_bar_chart':
                            $('#vertical-axis-min-row').hide();
                            $('#vertical-axis-max-row').hide();
                            break;
                        case 'chartjs_bubble_chart':
                            $('.series').hide();
                            break;
                        case 'chartjs_polar_area_chart':
                            $('.series').hide();
                            break;
                        case 'chartjs_pie_chart':
                            $('.series').hide();
                            break;
                        case 'chartjs_doughnut_chart':
                            $('.series').hide();
                            break;
                    }
                } else if ($('#chart-render-engine').val() == 'apexcharts') {
                    $('#chart-js-container').hide();
                    $('#google-chart-container').hide();
                    $('#apexcharts-chart-container').show();

                    $('.google').hide();
                    $('.highcharts').hide();
                    $('.highstock').hide();
                    $('.chartjs').hide();
                    $('.apexcharts').show();

                    $('.border-settings').hide();

                    $('#zoom-type-row').show();

                    $('.series').show();
                    $('.axes').show();
                    $('.legend').show();
                    $('.region').hide();
                    var legendPositionVal = $('#legend-position-cjs').val() ? $('#legend-position-cjs').val() : 'bottom';
                    $('#legend-position-cjs').val(legendPositionVal).change();

                    if (constructedChartData.type == 'apexcharts_pie_chart' ||
                        constructedChartData.type == 'apexcharts_pie_with_gradient_chart' ||
                        constructedChartData.type == 'apexcharts_donut_chart' ||
                        constructedChartData.type == 'apexcharts_donut_with_gradient_chart'
                    ) {
                        $('.axes').hide();
                        $('.series').hide();
                        $('#zoom-type-row').hide();
                        $('#stroke-width-row').hide();
                        $('#marker-size-row').hide();
                        $('#toolbar-buttons-container').hide();
                    } else if (constructedChartData.type == 'apexcharts_radialbar_chart' ||
                        constructedChartData.type == 'apexcharts_radialbar_gauge_chart') {
                        $('.apexcharts-radialbar').show();
                        $('.apexcharts-pie').hide();
                        $('#zoom-type-row').hide();
                        $('.series').hide();
                        $('.axes').hide();
                        $('#stroke-width-row').hide();
                        $('#marker-size-row').hide();
                        $('#toolbar-buttons-container').hide();
                    } else if (constructedChartData.type == 'apexcharts_radar_chart') {
                        $('#zoom-type-row').hide();
                        $('.axes').hide();
                        $('.apexcharts-pie').hide();
                        $('.chart-series-image').show();
                        $('#stroke-width-row').hide();
                        $('.tooltips').hide();
                        $('#toolbar-buttons-container').hide();
                        $('.chart-show-yaxis').hide();
                    } else if (constructedChartData.type == 'apexcharts_column_chart' ||
                        constructedChartData.type == 'apexcharts_stacked_bar_chart' ||
                        constructedChartData.type == 'apexcharts_grouped_bar_chart' ||
                        constructedChartData.type == 'apexcharts_stacked_column_chart' ||
                        constructedChartData.type == 'apexcharts_100_stacked_column_chart' ||
                        constructedChartData.type == 'apexcharts_100_stacked_bar_chart') {
                        $('#marker-size-row').hide();
                        $('.apexcharts-pie').hide();
                        $('#toolbar-buttons-container').hide();
                        $('.follow-cursor-container').hide();
                        $('#horizontal-axis-crosshair-row').hide();
                        $('#vertical-axis-crosshair-row').hide();
                        $('#zoom-type-row').hide();
                        $('.chart-show-yaxis').hide();
                        if (constructedChartData.type === 'apexcharts_stacked_bar_chart' ||
                            constructedChartData.type === 'apexcharts_grouped_bar_chart' ||
                            constructedChartData.type === 'apexcharts_100_stacked_bar_chart') {
                            $('#vertical-axis-max-row').hide();
                            $('#vertical-axis-min-row').hide();
                            $('#tick-amount-row').hide();
                            $('#reversed-axis').hide();
                        } else if (constructedChartData.type == 'apexcharts_100_stacked_column_chart') {
                            $('#vertical-axis-max-row').hide();
                            $('#vertical-axis-min-row').hide();
                        }
                    } else {
                        $('.apexcharts-pie').hide();
                    }
                } else if ($('#chart-render-engine').val() == 'highstock') {
                    $("#chart-js-container").hide();
                    $("#apexcharts-chart-container").hide();
                    $("#google-chart-container").show();

                    $(".google").hide();
                    $(".chartjs").hide();
                    $(".apexcharts").hide();
                    $(".highcharts").show();
                    $(".highstock").show();

                    $('.group-chart').hide();
                    $('.apex-toolbar-container').hide();
                    $('#border_width_row').show();
                    $('#border_color_row').show();
                    $('#border_radius_row').show();
                    $('#zoom-type-row').show();
                    if ($("#zoom-type")[0].options.length === 2) {
                        $("#zoom-type").append('<option value="y">Y</option>');
                        $("#zoom-type").append('<option value="xy">XY</option>');
                    }
                    $('#panning-row').show();
                    $('#pan-key-row').show();
                    $('#plot-border-width-row').show();
                    $('#plot_border_color_row').show();
                    $('.series').show();
                    $('.chart-show-yaxis').hide();
                    $('.axes').show();
                    $('.legend').show();
                    $('.region').hide();
                }

                previousStepButton.prop('disabled', false);
                previousStepButton.animateFadeIn();
                $('#wpdatatables-chart-source').change();
                $('.wdt-preload-layer').animateFadeOut();
                break;
            case 'step2':
                // Data range
                $('.wdt-preload-layer').animateFadeOut();
                applyDragula();
                nextStepButton.prop('disabled', true);
                nextStepButton.hide();
                constructedChartData.wpdatatable_id = $('#wpdatatables-chart-source').val();
                $('div.chart-wizard-step.step3').show();
                $('li.chart_wizard_breadcrumbs_block.step3').addClass('active');

                $.ajax({
                    url: ajaxurl,
                    type: 'post',
                    dataType: 'json',
                    data: {
                        action: 'wpdatatables_get_columns_data_by_table_id',
                        table_id: constructedChartData.wpdatatable_id,
                        wdtNonce: $('#wdtNonce').val(),
                    },
                    success: function (columns) {
                        wdtChartColumnsData = columns;
                        var columnChartTemplate = $.templates("#wdt-chart-column-block");
                        var columnChartBlockHtml = columnChartTemplate.render({columns: columns});
                        $('div.wdt-chart-column-picker-container div.wdt-chart-wizart-existing-columns-container').html(columnChartBlockHtml);

                        if ((typeof constructedChartData.selected_columns !== 'undefined')
                            || (typeof editing_chart_data !== 'undefined')) {
                            var columns = (typeof editing_chart_data !== 'undefined') ? editing_chart_data.selected_columns : constructedChartData.selected_columns;
                            $('div.wdt-chart-column-picker-container div.wdt-chart-wizard-chosen-columns-container .chart-column-block').remove();
                            for (var i in columns) {
                                $('div.wdt-chart-column-picker-container div.wdt-chart-wizart-existing-columns-container div.chart-column-block[data-orig_header="' + columns[i] + '"]')
                                    .appendTo('div.wdt-chart-column-picker-container div.wdt-chart-wizard-chosen-columns-container');
                            }
                        }
                        $('#wdt-add-chart-columns').click();
                        $('.wdt-chart-column-picker-container .chosen_columns button.deselect-all-columns').addClass('disabled').attr('disabled', 'disabled');
                        ;
                        $('.wdt-chart-column-picker-container .chosen_columns button.select-all-columns').addClass('disabled').attr('disabled', 'disabled');
                        nextStepButton.show();
                    }
                });
                break;
            case 'step3':
                // Formatting
                constructedChartData.follow_filtering = $('#follow-table-filtering').is(':checked') ? 1 : 0;
                if (typeof constructedChartData.selected_columns == 'undefined') {
                    constructedChartData.selected_columns = {};
                }

                // Move string column on first place
                if ($('div.chosen_columns div.chart-column-block.string,' +
                        'div.chosen_columns div.chart-column-block.date,' +
                        'div.chosen_columns div.chart-column-block.datetime,' +
                        'div.chosen_columns div.chart-column-block.time,' +
                        'div.chosen_columns div.chart-column-block.link').length
                    && (!$('div.chosen_columns div.chart-column-block:eq(0)').hasClass('float')
                        || !$('div.chosen_columns div.chart-column-block:eq(0)').hasClass('int'))) {
                    $('div.chosen_columns div.chart-column-block.string,' +
                        'div.chosen_columns div.chart-column-block.date,' +
                        'div.chosen_columns div.chart-column-block.datetime,' +
                        'div.chosen_columns div.chart-column-block.time,' +
                        'div.chosen_columns div.chart-column-block.link')
                        .eq(0)
                        .prependTo('div.wdt-chart-wizard-chosen-columns-container')
                }

                constructedChartData.selected_columns = {};
                constructedChartData.series_data = {};
                $('div.wdt-chart-wizard-chosen-columns-container div.chart-column-block').each(function () {
                    constructedChartData.selected_columns[parseInt($(this).index())] = $(this).data('orig_header');
                });

                if (typeof editing_chart_data !== 'undefined') {
                    if (!_.isEqual(constructedChartData.selected_columns, editing_chart_data.selected_columns)) {
                        editing_chart_data.render_data.series = editing_chart_data.render_data.series.filter(function (editColumns) {
                            return Object.values(constructedChartData.selected_columns).indexOf(editColumns.orig_header) !== -1;
                        });
                    }
                }


                // Set initial width for preview
                if (constructedChartData.width == null) {
                    if (typeof editing_chart_data !== 'undefined') {
                        if (editing_chart_data.render_data.options.responsive_width != 1 && editing_chart_data.render_data.options.width == null) {
                            constructedChartData.width = 400;
                        }
                    } else {
                        constructedChartData.width = 400;
                    }
                }

                $('#wdt-chart-row-range-type').change();

                $('#series-settings-container').empty();

                getInputData();
                nextStepButton.hide();
                // Render chart first time in preview
                $.ajax({
                    url: ajaxurl,
                    data: {
                        action: 'wpdatatable_show_chart_from_data',
                        chart_data: constructedChartData,
                        wdtNonce: $('#wdtNonce').val(),
                    },
                    dataType: 'json',
                    type: 'post',
                    success: function (data) {
                        $('div.chart-wizard-step.step4').show();
                        $('li.chart_wizard_breadcrumbs_block.step4').addClass('active');

                        //Series
                        var seriesBlockTemplate = $.templates("#wdt-chart-series-setting-block");

                        if (constructedChartData.engine == 'google') {
                            if (typeof editing_chart_data != 'undefined') {
                                for (i = 0; i < data.series.length; i++) {
                                    for (j = 0; j < editing_chart_data.render_data.series.length; j++) {
                                        if (data.series[i].orig_header === editing_chart_data.render_data.series[j].orig_header) {
                                            data.series[i].label = data.columns[i + 1].label = editing_chart_data.render_data.series[j].label;
                                        }
                                    }
                                }
                            }
                            seriesBlockTemplateHtml = seriesBlockTemplate.render({series: data.series});

                        } else if (constructedChartData.engine == 'highcharts') {
                            if (typeof editing_chart_data != 'undefined' &&
                                editing_chart_data.highcharts_render_data != null &&
                                constructedChartData.type != 'highcharts_treemap_chart' &&
                                constructedChartData.type != 'highcharts_treemap_level_chart') {
                                for (i = 0; i < data.options.series.length; i++) {
                                    for (j = 0; j < editing_chart_data.highcharts_render_data.options.series.length; j++) {
                                        if (data.options.series[i].orig_header === editing_chart_data.highcharts_render_data.options.series[j].orig_header) {
                                            data.options.series[i].label = editing_chart_data.highcharts_render_data.options.series[j].label;
                                        }
                                    }
                                }
                            }
                            seriesBlockTemplateHtml = seriesBlockTemplate.render({series: data.options.series});

                        } else if (constructedChartData.engine == 'chartjs') {
                            if (typeof editing_chart_data != 'undefined' && editing_chart_data.chartjs_render_data != null && constructedChartData.type !== 'chartjs_bubble_chart') {
                                for (i = 0; i < data.options.data.datasets.length; i++) {
                                    for (j = 0; j < editing_chart_data.chartjs_render_data.options.data.datasets.length; j++) {
                                        if (data.options.data.datasets[i].orig_header === editing_chart_data.chartjs_render_data.options.data.datasets[j].orig_header) {
                                            data.options.data.datasets[i].label = editing_chart_data.chartjs_render_data.options.data.datasets[j].label;
                                        }
                                    }
                                }
                            }
                            seriesBlockTemplateHtml = seriesBlockTemplate.render({series: data.options.data.datasets});
                        } else if (constructedChartData.engine == 'apexcharts') {
                            if (typeof editing_chart_data != 'undefined' && editing_chart_data.apexcharts_render_data != null) {
                                for (i = 0; i < data.options.series.length; i++) {
                                    for (j = 0; j < editing_chart_data.apexcharts_render_data.options.series.length; j++) {
                                        if (data.options.series[i].orig_header === editing_chart_data.apexcharts_render_data.options.series[j].orig_header) {
                                            data.options.series[i].label = editing_chart_data.apexcharts_render_data.options.series[j].label;
                                            var apex_yaxis = editing_chart_data.apexcharts_render_data.options.yaxis;
                                            if (Array.isArray(apex_yaxis)) {
                                                data.options.series[i].name =
                                                    apex_yaxis[j].title && apex_yaxis[j].title.text !== "" ?
                                                        apex_yaxis[j].title.text
                                                        : editing_chart_data.apexcharts_render_data.options.series[j].label;
                                            } else {
                                                data.options.series[i].name = editing_chart_data.apexcharts_render_data.options.series[j].label;
                                            }
                                        }
                                    }
                                }
                            }
                            seriesBlockTemplateHtml = seriesBlockTemplate.render({series: data.options.series});

                        } else if (constructedChartData.engine == 'highstock') {
                            if (typeof editing_chart_data != 'undefined' &&
                                editing_chart_data.highstock_render_data) {
                                for (i = 0; i < data.options.series.length; i++) {
                                    for (j = 0; j < editing_chart_data.highstock_render_data.options.series.length; j++) {
                                        if (data.options.series[i].orig_header === editing_chart_data.highstock_render_data.options.series[j].orig_header) {
                                            data.options.series[i].label = editing_chart_data.highstock_render_data.options.series[j].label;
                                        }
                                    }
                                }
                            }
                            seriesBlockTemplateHtml = seriesBlockTemplate.render({series: data.options.series});
                        }

                        $('#series-settings-container').html(seriesBlockTemplateHtml);

                        if (constructedChartData.engine == 'google') {
                            if (typeof editing_chart_data != 'undefined') {
                                for (i in data.series) {
                                    for (j in editing_chart_data.render_data.series) {
                                        if (data.series[i].orig_header === editing_chart_data.render_data.series[j].orig_header &&
                                            typeof (editing_chart_data.render_data.options.series[j]) !== 'undefined') {
                                            $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-color input').val(editing_chart_data.render_data.options.series[j].color);
                                            data.options.series[i] = {
                                                color: editing_chart_data.render_data.options.series[j].color
                                            }
                                        }
                                    }
                                }
                            } else {
                                for (i in data.series) {
                                    $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-color input').val(data.series[i].color);
                                }
                            }
                        } else if (constructedChartData.engine == 'highcharts') {
                            if (typeof editing_chart_data != 'undefined' && editing_chart_data.highcharts_render_data != null &&
                                constructedChartData.type != 'highcharts_treemap_chart' &&
                                constructedChartData.type != 'highcharts_treemap_level_chart' &&
                                constructedChartData.type != 'highcharts_funnel_chart' &&
                                constructedChartData.type != 'highcharts_funnel3d_chart') {
                                for (i in data.options.series) {
                                    for (j in editing_chart_data.highcharts_render_data.options.series) {
                                        if (data.options.series[i].orig_header === editing_chart_data.highcharts_render_data.options.series[j].orig_header) {
                                            if (constructedChartData.type == 'highcharts_spline_chart' ||
                                                constructedChartData.type == 'highcharts_line_chart' ||
                                                constructedChartData.type == 'highcharts_basic_column_chart' ||
                                                constructedChartData.type == 'highcharts_basic_area_chart' ||
                                                constructedChartData.type == 'highcharts_basic_bar_chart' ||
                                                constructedChartData.type == 'highcharts_polar_chart' ||
                                                constructedChartData.type == 'highcharts_spiderweb_chart') {
                                                $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-color input').val(editing_chart_data.highcharts_render_data.options.series[j].color);
                                                data.options.series[i].color = editing_chart_data.highcharts_render_data.options.series[j].color;
                                                $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-type select').val(editing_chart_data.highcharts_render_data.options.series[j].type);
                                                data.options.series[i].type = editing_chart_data.highcharts_render_data.options.series[j].type;
                                                $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-show-yaxis input').val(editing_chart_data.highcharts_render_data.options.series[j].yAxis);
                                                data.options.series[i].yAxis = editing_chart_data.highcharts_render_data.options.series[j].yAxis
                                            } else {
                                                $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-color input').val(editing_chart_data.highcharts_render_data.options.series[j].color);
                                                data.options.series[i].color = editing_chart_data.highcharts_render_data.options.series[j].color;
                                            }
                                        }
                                    }
                                }
                            } else {
                                for (i in data.options.series) {
                                    $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-color input').val(data.options.series[i].color);
                                    $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-type select').val(data.options.series[i].type);
                                    $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-show-yaxis input').val(data.options.series[i].yAxis);
                                }
                            }
                        } else if (constructedChartData.engine == 'chartjs') {
                            if (typeof editing_chart_data != 'undefined' && editing_chart_data.chartjs_render_data != null && constructedChartData.type !== 'chartjs_bubble_chart') {
                                for (i in data.options.data.datasets) {
                                    for (j in editing_chart_data.chartjs_render_data.options.data.datasets) {
                                        if (data.options.data.datasets[i].orig_header === editing_chart_data.chartjs_render_data.options.data.datasets[j].orig_header) {
                                            $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-color input').val(editing_chart_data.chartjs_render_data.options.data.datasets[j].borderColor);
                                            data.options.data.datasets[i].borderColor = editing_chart_data.chartjs_render_data.options.data.datasets[j].borderColor;
                                            data.options.data.datasets[i].backgroundColor = editing_chart_data.chartjs_render_data.options.data.datasets[j].backgroundColor;
                                        }
                                    }
                                }
                            } else {
                                for (i in data.options.data.datasets) {
                                    $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-color input').val(data.options.data.datasets[i].borderColor);
                                }
                            }
                        } else if (constructedChartData.engine == 'apexcharts') {
                            $.each(data.options.series, function (key, val) {
                                $('#series-image-' + key).on('change', function () {
                                    switchClearButton($('#series-image-' + key).val(), $('#wdt-upload-chart-image-' + key));
                                    toggleBackgroundImageContainer();
                                    renderChart(false);
                                });
                                $('#wdt-upload-chart-image-' + key).on('click', function (e) {
                                    handleMediaUploader(e, jQuery(this).attr("id"), data);
                                    renderChart(false);
                                });

                                $('#apex-series-type-' + key).on('change', function (e) {
                                    if ($(this).val() === 'bar' || $(this).val() === 'area') {
                                        $('#series-image-' + key + '-container').show();
                                    } else {
                                        $('#series-image-' + key).val('');
                                        $('#series-image-' + key + '-container').hide();
                                    }
                                });
                            });
                            if (typeof editing_chart_data != 'undefined' && editing_chart_data.apexcharts_render_data != null) {
                                for (i in data.options.series) {
                                    for (j in editing_chart_data.apexcharts_render_data.options.series) {
                                        if (data.options.series[i].orig_header === editing_chart_data.apexcharts_render_data.options.series[j].orig_header) {
                                            if (constructedChartData.type == 'apexcharts_spline_chart' ||
                                                constructedChartData.type == 'apexcharts_straight_line_chart' ||
                                                constructedChartData.type == 'apexcharts_stepline_chart' ||
                                                constructedChartData.type == 'apexcharts_column_chart' ||
                                                constructedChartData.type == 'apexcharts_basic_area_chart' ||
                                                constructedChartData.type == 'apexcharts_stepline_area_chart' ||
                                                constructedChartData.type == 'apexcharts_spline_area_chart') {
                                                if (editing_chart_data.apexcharts_render_data.options.series[j].type === 'bar' ||
                                                    editing_chart_data.apexcharts_render_data.options.series[j].type === 'area') {
                                                    $('#series-image-' + i + '-container').show();
                                                } else {
                                                    $('#series-image-' + i + '-container').hide();
                                                }
                                                $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-color input').val(editing_chart_data.apexcharts_render_data.options.colors[j]);
                                                data.options.series[i].color = editing_chart_data.apexcharts_render_data.options.colors[j];
                                                $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.apex-series-type-container select').val(editing_chart_data.apexcharts_render_data.options.series[j].type);
                                                data.options.series[i].type = editing_chart_data.apexcharts_render_data.options.series[j].type;
                                                $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-show-yaxis input').prop('checked', editing_chart_data.apexcharts_render_data.options.yaxis[j].opposite);
                                                data.options.series[i].yAxis = editing_chart_data.apexcharts_render_data.options.yaxis[j].opposite;
                                                $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-image input').val(editing_chart_data.apexcharts_render_data.options.series[j].chart_image);
                                                data.options.series[i].chart_image = editing_chart_data.apexcharts_render_data.options.series[j].chart_image;
                                                switchClearButton(editing_chart_data.apexcharts_render_data.options.series[j].chart_image,
                                                    $('#wdt-upload-chart-image-' + i));
                                                toggleBackgroundImageContainer();
                                            } else if (constructedChartData.type == 'apexcharts_stacked_bar_chart' ||
                                                constructedChartData.type == 'apexcharts_100_stacked_bar_chart' ||
                                                constructedChartData.type == 'apexcharts_grouped_bar_chart' ||
                                                constructedChartData.type == 'apexcharts_stacked_column_chart' ||
                                                constructedChartData.type == 'apexcharts_100_stacked_column_chart') {
                                                $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-color input').val(editing_chart_data.apexcharts_render_data.options.colors[j]);
                                                data.options.series[i].color = editing_chart_data.apexcharts_render_data.options.colors[j];
                                                $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-image input').val(editing_chart_data.apexcharts_render_data.options.series[j].chart_image);
                                                data.options.series[i].chart_image = editing_chart_data.apexcharts_render_data.options.series[j].chart_image;
                                                switchClearButton(editing_chart_data.apexcharts_render_data.options.series[j].chart_image,
                                                    $('#wdt-upload-chart-image-' + i));
                                                toggleBackgroundImageContainer();
                                            } else if (constructedChartData.type == 'apexcharts_radar_chart') {
                                                $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-color input').val(editing_chart_data.apexcharts_render_data.options.series[j].color);
                                                data.options.series[i].color = editing_chart_data.apexcharts_render_data.options.series[j].color;
                                            } else {
                                                $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-color input').val(editing_chart_data.apexcharts_render_data.options.series[j].color);
                                                data.options.series[i].color = editing_chart_data.apexcharts_render_data.options.series[j].color;
                                            }
                                        }
                                    }
                                }
                            } else {
                                for (i in data.options.series) {
                                    $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-color input').val(data.options.series[i].color);
                                    $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-type select').val(data.options.series[i].type);
                                    $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-show-yaxis input').val(data.options.series[i].yAxis);
                                }
                            }
                        } else if (constructedChartData.engine == 'highstock') {
                            if (typeof editing_chart_data != 'undefined' && editing_chart_data.highstock_render_data != null) {
                                for (i in data.options.series) {
                                    for (j in editing_chart_data.highstock_render_data.options.series) {
                                        if (singleSeriesFromMultipleTypes.includes(constructedChartData.type)) {
                                            $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-color input').val(editing_chart_data.highstock_render_data.options.series[j].color);
                                            $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-label input').val(editing_chart_data.highstock_render_data.options.series[j].name);
                                            data.options.series[i].color = editing_chart_data.highstock_render_data.options.series[j].color;
                                            data.options.series[i].label = editing_chart_data.highstock_render_data.options.series[j].name;
                                        } else if (data.options.series[i].orig_header === editing_chart_data.highstock_render_data.options.series[j].orig_header) {
                                            $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-color input').val(editing_chart_data.highstock_render_data.options.series[j].color);
                                            data.options.series[i].color = editing_chart_data.highstock_render_data.options.series[j].color;
                                            data.options.series[i].label = editing_chart_data.highstock_render_data.options.series[j].name;
                                        }
                                    }
                                }
                            } else {
                                for (i in data.options.series) {
                                    $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-color input').val(data.options.series[i].color);
                                }
                            }
                        }
                        $(".wdt-chart-wizard .wdt-add-picker").each(function (i) {
                            jQuery(this).addClass('pickr');
                            jQuery(this)
                                .closest('.wdt-color-picker')
                                .find('.wpcolorpicker-icon i')
                                .css("background", this.value);
                        });


                        if (constructedChartData.engine == 'google') {
                            wdtChart = new wpDataTablesGoogleChart();
                            wdtChart.setType(data.type);
                            wdtChart.setColumns(data.columns);
                            wdtChart.setRows(data.rows);
                            wdtChart.setOptions(data.options);
                            wdtChart.setContainer('google-chart-container');
                            wdtChart.setColumnIndexes(data.column_indexes);
                        } else if (constructedChartData.engine == 'highcharts') {
                            wdtChart = new wpDataTablesHighchart();
                            wdtChart.setNumberFormat(data.wdtNumberFormat);
                            wdtChart.setOptions(data.options);
                            wdtChart.setMultiplyYaxis(data);
                            wdtChart.setType(data.type);
                            wdtChart.setWidth(data.width);
                            wdtChart.setHeight(data.height);
                            wdtChart.setColumnIndexes(data.column_indexes);
                            wdtChart.setContainer('#google-chart-container');
                        } else if (constructedChartData.engine == 'chartjs') {
                            if (wdtChart !== null) {
                                wdtChart.chart.destroy();
                            }
                            wdtChart = new wpDataTablesChartJS();
                            var container = document.getElementById("chart-js-container");
                            var canvas = document.getElementById("chart-js-canvas");
                            wdtChart.setData(data.options.data);
                            wdtChart.setOptions(data.options.options);
                            wdtChart.setGlobalOptions(data.options.globalOptions);
                            wdtChart.setType(data.configurations.type);
                            wdtChart.setContainer(container);
                            wdtChart.setCanvas(canvas);
                            wdtChart.setContainerOptions(data.configurations);
                        } else if (constructedChartData.engine == 'apexcharts') {
                            wdtChart = new wpDataTablesApexChart();
                            wdtChart.setOptions(data.options);
                            wdtChart.setType(data.type);
                            wdtChart.setWidth(data.options.chart.width);
                            wdtChart.setHeight(data.options.chart.height);
                            wdtChart.setContainer('#apex-chart-container');
                            wdtChart.setNumberFormat(data.wdtNumberFormat);
                            wdtChart.setDecimalPlaces(data.wdtDecimalPlaces);
                            wdtChart.setSeriesAndAxis(data.options);
                            wdtChart.setStartEndAngles(data.options);
                            wdtChart.setColumnIndexes(data.column_indexes);
                        } else if (constructedChartData.engine === 'highstock') {
                            wdtChart = new wpDataTablesHighStock();
                            wdtChart.setNumberFormat(data.wdtNumberFormat);
                            wdtChart.setOptions(data.options);
                            wdtChart.setMultipleYaxis(data);
                            wdtChart.setType(data.type);
                            wdtChart.setWidth(data.width);
                            wdtChart.setHeight(data.height);
                            wdtChart.setColumnIndexes(data.column_indexes);
                            wdtChart.setContainer('google-chart-container');
                        }
                        wdtChart.render();
                        if (constructedChartData.type == 'google_bubble_chart') {
                            $('.chart-series-color').hide();
                        }
                        $('.selectpicker').selectpicker('refresh');

                        $('div.apex-series-type-container').hide();
                        $('div.chart-series-image').hide();

                        if (constructedChartData.engine == 'google' || constructedChartData.engine == 'chartjs'
                            || constructedChartData.engine == 'highstock'
                            || constructedChartData.type == 'highcharts_stacked_area_chart'
                            || constructedChartData.type == 'highcharts_scatter_plot'
                            || constructedChartData.type == 'highcharts_stacked_bar_chart'
                            || constructedChartData.type == 'highcharts_3d_column_chart'
                            || constructedChartData.type == 'highcharts_stacked_column_chart'
                            || constructedChartData.type == 'highcharts_gauge_chart'
                            || constructedChartData.type == 'apexcharts_radar_chart'
                        ) {
                            $('div.chart-series-type').hide();
                            $('div.chart-show-yaxis').hide();
                        } else if (constructedChartData.type == 'highcharts_polar_chart'
                            || constructedChartData.type == 'highcharts_spiderweb_chart') {
                            $('div.chart-series-label').hide();
                            $('div.chart-show-yaxis').hide();

                        } else if (constructedChartData.engine == 'apexcharts') {
                            $('div.chart-series-type').hide();
                            if (constructedChartData.type == 'apexcharts_basic_area_chart' ||
                                constructedChartData.type == 'apexcharts_spline_area_chart' ||
                                constructedChartData.type == 'apexcharts_stepline_area_chart' ||
                                constructedChartData.type == 'apexcharts_column_chart') {
                                $('div.apex-series-type-container').show();
                                if ($('#line-background-image').val() == '') $('div.chart-series-image').show();
                            } else if (constructedChartData.type == 'apexcharts_grouped_bar_chart' ||
                                constructedChartData.type == 'apexcharts_stacked_bar_chart' ||
                                constructedChartData.type == 'apexcharts_100_stacked_bar_chart' ||
                                constructedChartData.type == 'apexcharts_stacked_column_chart' ||
                                constructedChartData.type == 'apexcharts_100_stacked_column_chart' ||
                                constructedChartData.type == 'apexcharts_column_chart') {
                                if ($('#line-background-image').val() == '') $('div.chart-series-image').show();
                                if (constructedChartData.type !== 'apexcharts_column_chart') $('div.chart-show-yaxis').hide();
                            } else if (constructedChartData.type == 'apexcharts_straight_line_chart' ||
                                constructedChartData.type == 'apexcharts_spline_chart' ||
                                constructedChartData.type == 'apexcharts_stepline_chart') {
                                $('div.apex-series-type-container').show();
                                for (var i in data.options.series) {
                                    if ((data.options.series[i].type === 'bar' || data.options.series[i].type === 'area') &&
                                        $('#line-background-image').val() == '') {
                                        $('#series-image-' + i + '-container').show();
                                    }
                                }
                            }
                        } else {
                            for (var i in data.options.series) {
                                if (data.options.series[i].yAxis) {
                                    $('#show-yaxis-' + i).prop('checked', 'checked');
                                } else {
                                    $('#show-yaxis-' + i).prop('checked', '');
                                }
                            }
                            if (constructedChartData.engine !== 'apexcharts') wdtChart.render();
                        }

                        var eTop = $('.chart-preview-container').offset().top;
                        var eWidth = $('.chart-preview-container').width();

                        $(window).on('scroll', function () {
                            if (eTop - $(window).scrollTop() <= 30) {
                                $('.chart-preview-container').css('position', 'fixed').css('right', 48).css('top', 30).css('width', eWidth);
                            } else {
                                eWidth = $('.chart-preview-container').width();
                                $('.chart-preview-container').css('position', 'relative').css('right', '').css('top', '').css('width', '');
                            }
                        });

                        var isApexEngine = constructedChartData.engine === 'apexcharts';

                        $('#chart-series-color,' +
                            '#background-color,' +
                            '#border_color,' +
                            '#plot-background-color,' +
                            '#plot-border-color,' +
                            '#font-color,' +
                            '#title-font-color,' +
                            '#tooltip-background-color,' +
                            '#tooltip-border-color,' +
                            '#legend_background_color,' +
                            '#legend_border_color,' +
                            '#exporting-button-color'
                        ).on('change', function (e, ui) {
                            e.stopImmediatePropagation()
                            e.preventDefault()

                            if ($('input.background-color').val() != '' && constructedChartData.engine === 'apexcharts') {
                                $('#plot-background-image-container').hide();
                            } else if (constructedChartData.engine === 'apexcharts') {
                                $('#plot-background-image-container').show();
                            }
                            renderChart(false);
                        });

                        // Render chart on changing chart options
                        $('div.step4 input:not(.doNotTriggerChange), div.step4 select')
                            .on('change', function () {
                                renderChart(false);
                            });

                        $('input#group-chart')
                            .on('change', function () {
                                renderChart(true);
                            });

                        $('input#enable-dropshadow')
                            .on('change', function () {
                                if ($('#enable-dropshadow').is(':checked')) {
                                    $('div.dropshadow').show();
                                } else {
                                    $('div.dropshadow').hide();
                                }
                            });

                        $('input#monochrome')
                            .on('change', function () {
                                if ($('#monochrome').is(':checked')) {
                                    $('div#monochrome-color-container').show();
                                    $('#enable-color-palette').prop('checked', '');
                                    $('div#color-palette-row').hide();
                                    $('div#color-palette-container').hide();
                                    $('div#enable-color-palette').attr('disabled', true);
                                } else {
                                    $('div#monochrome-color-container').hide();
                                    $('div#color-palette-row').show();
                                    $('div#enable-color-palette').removeAttr('disabled');
                                }
                            });

                        $('input#enable-color-palette')
                            .on('change', function () {
                                if ($('#enable-color-palette').is(':checked')) {
                                    $('div#enable-monochrome').hide();
                                    $('#monochrome-color-container').prop('checked', '');
                                    $('div#monochrome-color-container').hide();
                                    $('div#color-palette-container').show();
                                    $('div#enable-monochrome').attr('disabled', true);
                                } else {
                                    $('div#enable-monochrome').show();
                                    $('div#color-palette-container').hide();
                                    $('div#enable-monochrome').removeAttr('disabled');
                                }
                            });

                        $('input#show-grid')
                            .on('change', function () {
                                if ($('#show-grid').is(':checked') && isApexEngine) {
                                    $('div.grid-style').show();
                                } else {
                                    $('div.grid-style').hide();
                                }
                            });

                        var backgroundImageInput = $('#line-background-image');
                        backgroundImageInput.on('change', function () {
                            if (backgroundImageInput.val() == '') {
                                $('#wdt-line-image-clear-button').html('<span class="wpdt-icon-image"></span>');
                                $('.chart-series-image').show();
                            } else {
                                $('#wdt-line-image-clear-button').html("Clear");
                                $('.chart-series-image').hide();
                            }
                            renderChart(false);
                        });
                        $('#wdt-line-image-clear-button').on('click', function (e) {
                            handleMediaUploader(e, jQuery(this).attr("id"), data);
                            renderChart(false);
                        });

                        var plotImageInput = $('#plot-background-image');
                        plotImageInput.on('change', function () {
                            if (plotImageInput.val() == '') {
                                $('#wdt-plot-image-clear-button').html('<span class="wpdt-icon-image"></span>');
                                if (isApexEngine)
                                    $('#background-color-container').show();
                            } else {
                                $('#wdt-plot-image-clear-button').html("Clear");
                                if (isApexEngine)
                                    $('#background-color-container').hide();
                            }
                            renderChart(false);
                        });
                        $('#wdt-plot-image-clear-button').on('click', function (e) {
                            handleMediaUploader(e, jQuery(this).attr("id"), data);
                            renderChart(false);
                        });

                        nextStepButton.show().addClass('wdt-save-chart').html('<i class="wpdt-icon-save"></i>' + wpdatatables_chart_wizard_strings.saveChart)
                        $('.wdt-preload-layer').animateFadeOut();
                    }
                });
                break;
            case 'step4':
                getInputData();
                // Save and get shortcode
                $.ajax({
                    url: ajaxurl,
                    data: {
                        action: 'wpdatatable_save_chart_get_shortcode',
                        chart_data: constructedChartData,
                        wdtNonce: $('#wdtNonce').val(),
                    },
                    type: 'post',
                    dataType: 'json',
                    success: function (data) {
                        $('div.chart-wizard-step.step5').show();
                        $('li.chart_wizard_breadcrumbs_block.step5').addClass('active');
                        $('#wdt-chart-shortcode-id').html(data.shortcode);
                        constructedChartData.id = data.id;
                        $('#wp-data-chart-id').val(data.id);
                        $('.wdt-preload-layer').animateFadeOut();
                        nextStepButton.prop('disabled', true);
                        nextStepButton.hide();
                        $('#finishButton').show();
                    }
                });
                break;
        }
    });
    $(document).on('change', 'select#region-google-charts', function (e) {
        $('#sub-continents-geo-chart').selectpicker('val', 'world');
        $('#sub-continents-geo-chart').selectpicker('refresh');
        $('#countries-geo-chart').selectpicker('val', 'world');
        $('#countries-geo-chart').selectpicker('refresh');

        $('#sub-continents-geochart-container .wdt_america,' +
            ' #sub-continents-geochart-container .wdt_asia,' +
            ' #sub-continents-geochart-container .wdt_australia,' +
            ' #sub-continents-geochart-container .wdt_europe,' +
            ' #sub-continents-geochart-container .wdt_africa'
        ).addClass('hidden');
        $('#sub-continents-geochart-container button.btn.dropdown-toggle.btn-default').removeClass('disabled');
        $('#countries-geochart-container button.btn.dropdown-toggle.btn-default').addClass('disabled');

        if ($('#region-google-charts').val() === 'world') {
            $('#sub-continents-geochart-container button.btn.dropdown-toggle.btn-default').addClass('disabled');
            $('#countries-geochart-container').selectpicker('val', 'world');
        }
        if ($('#region-google-charts').val() === '002') {
            $('#sub-continents-geochart-container .wdt_africa').removeClass('hidden');
        }
        if ($('#region-google-charts').val() === '150') {
            $('#sub-continents-geochart-container .wdt_europe').removeClass('hidden');
        }
        if ($('#region-google-charts').val() === '019') {
            $('#sub-continents-geochart-container .wdt_america').removeClass('hidden');
        }
        if ($('#region-google-charts').val() === '142') {
            $('#sub-continents-geochart-container .wdt_asia').removeClass('hidden');
        }
        if ($('#region-google-charts').val() === '009') {
            $('#sub-continents-geochart-container .wdt_australia').removeClass('hidden');
        }
    });
    $(document).on('change', 'select#sub-continents-geo-chart', function (e) {
        $('#countries-geo-chart').selectpicker('val', 'world');
        $('#countries-geo-chart').selectpicker('refresh');

        $('#countries-geochart-container .wdt_africa_c, ' +
            '#countries-geochart-container .wdt_europe_c, ' +
            '#countries-geochart-container .wdt_america_c, ' +
            '#countries-geochart-container .wdt_australia_c, ' +
            '#countries-geochart-container .wdt_asia_c'
        ).addClass('hidden');
        $('#countries-geochart-container button.btn.dropdown-toggle.btn-default').removeClass('disabled');

        if ($('#sub-continents-geo-chart').val() === 'world') {
            $('#countries-geochart-container button.btn.dropdown-toggle.btn-default').addClass('disabled');
        }
        if ($('#sub-continents-geo-chart').val() === '015') {
            $('#countries-geochart-container .wdt_northern_africa').removeClass('hidden');
        }
        if ($('#sub-continents-geo-chart').val() === '011') {
            $('#countries-geochart-container .wdt_western_africa').removeClass('hidden');
        }
        if ($('#sub-continents-geo-chart').val() === '017') {
            $('#countries-geochart-container .wdt_middle_africa.wdt_africa_c').removeClass('hidden');
        }
        if ($('#sub-continents-geo-chart').val() === '014') {
            $('#countries-geochart-container .wdt_eastern_africa').removeClass('hidden');
        }
        if ($('#sub-continents-geo-chart').val() === '018') {
            $('#countries-geochart-container .wdt_southern_africa').removeClass('hidden');
        }
        if ($('#sub-continents-geo-chart').val() === '154') {
            $('#countries-geochart-container .wdt_northern_europe').removeClass('hidden');
        }
        if ($('#sub-continents-geo-chart').val() === '155') {
            $('#countries-geochart-container .wdt_western_europe').removeClass('hidden');
        }
        if ($('#sub-continents-geo-chart').val() === '151') {
            $('#countries-geochart-container .wdt_eastern_europe').removeClass('hidden');
        }
        if ($('#sub-continents-geo-chart').val() === '039') {
            $('#countries-geochart-container .wdt_southern_europe').removeClass('hidden');
        }
        if ($('#sub-continents-geo-chart').val() === '021') {
            $('#countries-geochart-container .wdt_northern_america').removeClass('hidden');
        }
        if ($('#sub-continents-geo-chart').val() === '029') {
            $('#countries-geochart-container .wdt_caribbean').removeClass('hidden');
        }
        if ($('#sub-continents-geo-chart').val() === '013') {
            $('#countries-geochart-container .wdt_central_america').removeClass('hidden');
        }
        if ($('#sub-continents-geo-chart').val() === '005') {
            $('#countries-geochart-container .wdt_south_america').removeClass('hidden');
        }
        if ($('#sub-continents-geo-chart').val() === '143') {
            $('#countries-geochart-container .wdt_central_asia').removeClass('hidden');
        }
        if ($('#sub-continents-geo-chart').val() === '030') {
            $('#countries-geochart-container .wdt_eastern_asia').removeClass('hidden');
        }
        if ($('#sub-continents-geo-chart').val() === '034') {
            $('#countries-geochart-container .wdt_southern_asia').removeClass('hidden');
        }
        if ($('#sub-continents-geo-chart').val() === '035') {
            $('#countries-geochart-container .wdt_southern_eastern_asia').removeClass('hidden');
        }
        if ($('#sub-continents-geo-chart').val() === '145') {
            $('#countries-geochart-container .wdt_western_asia').removeClass('hidden');
        }
        if ($('#sub-continents-geo-chart').val() === '053') {
            $('#countries-geochart-container .wdt_australia_and_new_zealand').removeClass('hidden');
        }
        if ($('#sub-continents-geo-chart').val() === '054') {
            $('#countries-geochart-container .wdt_melanesia').removeClass('hidden');
        }
        if ($('#sub-continents-geo-chart').val() === '057') {
            $('#countries-geochart-container .wdt_micronesia').removeClass('hidden');
        }
        if ($('#sub-continents-geo-chart').val() === '061') {
            $('#countries-geochart-container .wdt_polynesia').removeClass('hidden');
        }
    });

    function renderChart(reloadNeeded) {
        if (typeof reloadNeeded == 'undefined') {
            reloadNeeded = true;
        }

        getInputData();

        if (reloadNeeded) {
            $.ajax({
                url: ajaxurl,
                data: {
                    action: 'wpdatatable_show_chart_from_data',
                    chart_data: constructedChartData,
                    wdtNonce: $('#wdtNonce').val(),
                },
                dataType: 'json',
                type: 'post',
                success: function (data) {
                    if (constructedChartData.engine == 'google') {
                        wdtChart = new wpDataTablesGoogleChart();
                        wdtChart.setType(data.type);
                        wdtChart.setColumns(data.columns);
                        wdtChart.setRows(data.rows);
                        wdtChart.setOptions(data.options);
                        wdtChart.setContainer('google-chart-container');
                        wdtChart.setColumnIndexes(data.column_indexes);
                    } else if (constructedChartData.engine == 'highcharts') {
                        wdtChart = new wpDataTablesHighchart();
                        wdtChart.setNumberFormat(data.wdtNumberFormat);
                        wdtChart.setOptions(data.options);
                        wdtChart.setMultiplyYaxis(data);
                        wdtChart.setType(data.type);
                        wdtChart.setWidth(data.width);
                        wdtChart.setHeight(data.height);
                        wdtChart.setColumnIndexes(data.column_indexes);
                        wdtChart.setContainer('#google-chart-container');
                    } else if (constructedChartData.engine == 'chartjs') {
                        wdtChart.chart.destroy();
                        wdtChart = new wpDataTablesChartJS();
                        var container = document.getElementById("chart-js-container");
                        var canvas = document.getElementById("chart-js-canvas");
                        wdtChart.setData(data.options.data);
                        wdtChart.setGlobalOptions(data.options.globalOptions);
                        wdtChart.setOptions(data.options.options);
                        wdtChart.setType(data.configurations.type);
                        wdtChart.setContainer(container);
                        wdtChart.setCanvas(canvas);
                        wdtChart.setContainerOptions(data.configurations);
                    } else if (constructedChartData.engine == 'apexcharts') {
                        wdtChart = new wpDataTablesApexChart();
                        wdtChart.setOptions(data.options);
                        wdtChart.setType(data.type);
                        wdtChart.setWidth(data.width);
                        wdtChart.setHeight(data.height);
                        wdtChart.setStartEndAngles(data.options);
                        wdtChart.setBackground(data.options.chart.background);
                        wdtChart.setAxisTitles();
                        wdtChart.setColumnIndexes(data.column_indexes);
                        wdtChart.setContainer('#apex-chart-container');
                    } else if (constructedChartData.engine === 'highstock') {
                        wdtChart = new wpDataTablesHighStock();
                        wdtChart.setNumberFormat(data.wdtNumberFormat);
                        wdtChart.setOptions(data.options);
                        wdtChart.setMultipleYaxis(data);
                        wdtChart.setType(data.type);
                        wdtChart.setWidth(data.width);
                        wdtChart.setHeight(data.height);
                        wdtChart.setColumnIndexes(data.column_indexes);
                        wdtChart.setContainer('google-chart-container');
                    }
                    wdtChart.render();

                    $('.wdt-preload-layer').animateFadeOut();

                }
            });
        } else {
            if (constructedChartData.engine == 'chartjs') {
                wdtChart.chart.destroy();
            }
            wdtChart.setChartConfig(constructedChartData);
            if (constructedChartData.engine == 'apexcharts') {
                wdtChart.render(true);
            } else {
                wdtChart.render();
            }
        }

    }

    // Get input fields data
    function getInputData() {
        //Chart
        constructedChartData.width = parseInt($('#chart-width').val());
        constructedChartData.responsive_width = $('#chart-responsive-width').is(':checked') ? 1 : 0;
        constructedChartData.height = parseInt($('#chart-height').val());
        constructedChartData.group_chart = $('#group-chart').is(':checked') ? 1 : 0;
        constructedChartData.enable_animation = $('#enable-animation').is(':checked') ? 1 : 0;
        constructedChartData.show_data_labels = $('#data-labels').is(':checked') ? 1 : 0;
        constructedChartData.start_angle = parseInt($('#start-angle').val());
        constructedChartData.end_angle = parseInt($('#end-angle').val());
        constructedChartData.background_color = $('input.background-color').val();
        constructedChartData.border_width = parseInt($('#border-width').val() ? $('#border-width').val() : 0);
        constructedChartData.border_color = $('input.border_color').val();
        constructedChartData.border_radius = parseInt($('#border-radius').val() ? $('#border-radius').val() : 0);
        constructedChartData.zoom_type = $('#zoom-type').val();
        constructedChartData.panning = $('#panning').is(':checked') ? 1 : 0;
        constructedChartData.pan_key = $('#pan-key').val();
        constructedChartData.plot_background_color = $('input.plot-background-color').val();
        constructedChartData.plot_background_image = $('#plot-background-image').val();
        constructedChartData.line_background_image = $('#line-background-image').val();
        constructedChartData.plot_border_width = $('#plot-border-width').val();
        constructedChartData.plot_border_color = $('input.plot-border-color').val();
        constructedChartData.font_size = $('#font-size').val();
        constructedChartData.font_name = $('#font-name').val();
        constructedChartData.font_style = $('#font-style').val();
        constructedChartData.font_weight = $('#font-weight').val();
        constructedChartData.font_color = $('input.font-color').val();
        constructedChartData.three_d = $('#three-d').is(':checked') ? 1 : 0;
        constructedChartData.monochrome = $('#monochrome').is(':checked') ? 1 : 0;
        constructedChartData.monochrome_color = $('input#monochrome-color').val();
        constructedChartData.enable_color_palette = $('#enable-color-palette').is(':checked') ? 1 : 0;
        constructedChartData.color_palette = $('#color-palette').val();
        constructedChartData.enable_dropshadow = $('#enable-dropshadow').is(':checked') ? 1 : 0;
        constructedChartData.dropshadow_blur = parseInt($('#dropshadow-blur').val());
        constructedChartData.dropshadow_opacity = parseInt($('#dropshadow-opacity').val()) / 100;
        constructedChartData.dropshadow_color = $('input#dropshadow-color').val();
        constructedChartData.dropshadow_top = parseInt($('#dropshadow-top').val());
        constructedChartData.dropshadow_left = parseInt($('#dropshadow-left').val());
        constructedChartData.text_color = $('input#chart-text-color').val();
        constructedChartData.region = $('#sub-continents-geo-chart').val() === 'world' || $('#region-google-charts').val() === 'world' ? $('#region-google-charts').val() : ($('#sub-continents-geo-chart').val() && $('#countries-geo-chart').val() === 'world' ? $('#sub-continents-geo-chart').val() : $('#countries-geo-chart').val());
        constructedChartData.datalessRegionColor = $('input.geochart_color').val();
        constructedChartData.colors = $('input.geochart-color-region').val();
        // Series
        if (typeof constructedChartData.series_data == 'undefined') {
            constructedChartData.series_data = {};
        }
        if (constructedChartData.engine == 'google' || constructedChartData.engine == 'chartjs'
            || constructedChartData.type == 'highcharts_stacked_area_chart'
            || constructedChartData.type == 'highcharts_scatter_plot'
            || constructedChartData.type == 'highcharts_stacked_bar_chart'
            || constructedChartData.type == 'highcharts_3d_column_chart'
            || constructedChartData.type == 'highcharts_stacked_column_chart'
            || constructedChartData.type == 'highcharts_gauge_chart'
        ) {
            $('div.chart-series-block').each(function (e) {
                constructedChartData.series_data[$(this).data('orig_header')] = {
                    label: $(this).find('input.series-label').val(),
                    color: $(this).find('input.series-color').val()
                }
            });
        } else if (constructedChartData.engine === 'apexcharts') {
            $('div.chart-series-block').each(function (e) {

                if (constructedChartData.type == 'apexcharts_spline_chart' ||
                    constructedChartData.type == 'apexcharts_straight_line_chart' ||
                    constructedChartData.type == 'apexcharts_stepline_chart' ||
                    constructedChartData.type == 'apexcharts_basic_line_chart' ||
                    constructedChartData.type == 'apexcharts_basic_area_chart' ||
                    constructedChartData.type == 'apexcharts_spline_area_chart' ||
                    constructedChartData.type == 'apexcharts_stepline_area_chart' ||
                    constructedChartData.type == 'apexcharts_column_chart') {
                    constructedChartData.series_data[$(this).data('orig_header')] = {
                        label: $(this).find('input.series-label').val(),
                        color: $(this).find('input.series-color').val(),
                        type: $(this).find('select.apex-series-type').val() ? $(this).find('select.apex-series-type').val() : getApexChartType(constructedChartData.type),
                        chart_image: $(this).find('input.series-image').val(),
                        yAxis: $('input#show-yaxis-' + e).is(':checked') ? 1 : 0
                    }

                } else {
                    constructedChartData.series_data[$(this).data('orig_header')] = {
                        label: $(this).find('input.series-label').val(),
                        color: $(this).find('input.series-color').val(),
                        chart_image: $(this).find('input.series-image').val(),
                        yAxis: $('input#show-yaxis-' + e).is(':checked') ? 1 : 0
                    }
                }
            });
        } else if (constructedChartData.engine === 'highstock') {
            let chartSeriesBlock = $('div.chart-series-block');
            if (singleSeriesFromMultipleTypes.includes(constructedChartData.type)) {
                constructedChartData.series_data["series_data"] = {
                    label: chartSeriesBlock.find('input.series-label').val(),
                    color: chartSeriesBlock.find('input.series-color').val()
                }
            } else {
                chartSeriesBlock.each(function (e) {
                    constructedChartData.series_data[$(this).data('orig_header')] = {
                        label: $(this).find('input.series-label').val(),
                        color: $(this).find('input.series-color').val()
                    }
                });
            }
        } else {
            $('div.chart-series-block').each(function (e) {
                constructedChartData.series_data[$(this).data('orig_header')] = {
                    label: $(this).find('input.series-label').val(),
                    color: $(this).find('input.series-color').val(),
                    type: $(this).find('select#series-type').val(),
                    yAxis: $('input#show-yaxis-' + e).is(':checked') ? 1 : 0
                }
            });
        }

        constructedChartData.curve_type = $('#curve-type').is(':checked') ? 1 : 0;
        // Axes
        constructedChartData.show_grid = $('#show-grid').is(':checked') ? 1 : 0;
        constructedChartData.grid_color = $('input#grid-color').val();
        constructedChartData.grid_stroke = $('#grid-stroke').val();
        constructedChartData.grid_position = $('#grid-position').val();
        constructedChartData.grid_axes = $('#grid-axes').val();
        constructedChartData.highcharts_line_dash_style = $('#highcharts-line-dash-style').val();
        constructedChartData.horizontal_axis_label = $('#horizontal-axis-label').val();
        constructedChartData.horizontal_axis_crosshair = $('#horizontal-axis-crosshair').is(':checked') ? 1 : 0;
        constructedChartData.horizontal_axis_direction = $('#horizontal-axis-direction').val();
        constructedChartData.vertical_axis_label = $('#vertical-axis-label').val();
        constructedChartData.vertical_axis_crosshair = $('#vertical-axis-crosshair').is(':checked') ? 1 : 0;
        constructedChartData.vertical_axis_direction = $('#vertical-axis-direction').val();
        constructedChartData.marker_size = $('#marker-size').val();
        constructedChartData.stroke_width = $('#stroke-width').val();
        constructedChartData.vertical_axis_min = $('#vertical-axis-min').val();
        constructedChartData.vertical_axis_max = $('#vertical-axis-max').val();
        constructedChartData.tick_amount = $('#tick-amount').val();
        constructedChartData.inverted = $('#inverted').is(':checked') ? 1 : 0;
        constructedChartData.reversed = $('#reversed').is(':checked') ? 1 : 0;
        // Title
        constructedChartData.show_title = $('#show-chart-title').is(':checked') ? 1 : 0;
        constructedChartData.title_floating = $('#title-floating').is(':checked') ? 1 : 0;
        constructedChartData.title_align = $('#title-align').val();
        constructedChartData.title_position = $('#title-position').val();
        constructedChartData.title_font_name = $('#title-font-name').val();
        constructedChartData.title_font_style = $('#title-font-style').val();
        constructedChartData.title_font_weight = $('#title-font-weight').val();
        constructedChartData.title_font_color = $('input#title-font-color').val();
        constructedChartData.subtitle = $('#subtitle').val();
        constructedChartData.subtitle_align = $('#subtitle-align').val();
        // Tooltip
        constructedChartData.tooltip_enabled = $('#tooltip-enabled').is(':checked') ? 1 : 0;
        constructedChartData.tooltip_background_color = $('input.tooltip-background-color').val();
        constructedChartData.tooltip_border_width = $('#tooltip-border-width').val();
        constructedChartData.tooltip_border_color = $('input.tooltip-border-color').val();
        constructedChartData.tooltip_border_radius = $('#tooltip-border-radius').val();
        constructedChartData.tooltip_shared = $('#tooltip-shared').is(':checked') ? 1 : 0;
        constructedChartData.tooltip_value_prefix = $('#tooltip-value-prefix').val();
        constructedChartData.tooltip_value_suffix = $('#tooltip-value-suffix').val();
        constructedChartData.follow_cursor = $('#follow-cursor').is(':checked') ? 1 : 0;
        constructedChartData.fill_series_color = $('#fill-series-color').is(':checked') ? 1 : 0;
        // Legend
        constructedChartData.show_legend = $('#show-legend').is(':checked') ? 1 : 0;
        constructedChartData.legend_position = $('#legend_position').val();
        constructedChartData.legend_background_color = $('input.legend_background_color').val();
        constructedChartData.legend_title = $('#legend_title').val();
        constructedChartData.legend_layout = $('#legend_layout').val();
        constructedChartData.legend_align = $('#legend_align').val();
        constructedChartData.legend_vertical_align = $('#legend_vertical_align').val();
        constructedChartData.legend_border_width = $('#legend_border_width').val();
        constructedChartData.legend_border_color = $('input.legend_border_color').val();
        constructedChartData.legend_border_radius = $('#legend_border_radius').val();
        constructedChartData.legend_position_cjs = $('#legend-position-cjs').val();

        // Exporting
        constructedChartData.exporting = $('#exporting').is(':checked') ? 1 : 0;
        constructedChartData.exporting_data_labels = $('#exporting-data-labels').is(':checked') ? 1 : 0;
        constructedChartData.exporting_file_name = $('#exporting-file-name').val();
        constructedChartData.exporting_width = $('#exporting-width').val();
        constructedChartData.exporting_button_align = $('#exporting-button-align').val();
        constructedChartData.exporting_button_vertical_align = $('#exporting-button-vertical-align').val();
        constructedChartData.exporting_button_color = $('#exporting-button-color').val();
        constructedChartData.exporting_button_text = $('#exporting-button-text').val();
        // Credits
        constructedChartData.credits = $('#credits').is(':checked') ? 1 : 0;
        constructedChartData.credits_href = $('#credits-href').val();
        constructedChartData.credits_text = $('#credits-text').val();

        //Toolbar
        constructedChartData.show_toolbar = $('#show-toolbar').is(':checked') ? 1 : 0;
        constructedChartData.toolbar_buttons = $('#toolbar-buttons').val();
        constructedChartData.apex_exporting_file_name = $('#apex-exporting-file-name').val();

        constructedChartData.datalessRegionColor = $('input.geochart_color').val();
        constructedChartData.colors = $('input.geochart-color-region').val() == '' ? '#267114' : $('input.geochart-color-region').val();
        constructedChartData.region = $('#sub-continents-geo-chart').val() === 'world' || $('#region-google-charts').val() === 'world' ? $('#region-google-charts').val() : ($('#sub-continents-geo-chart').val() && $('#countries-geo-chart').val() === 'world' ? $('#sub-continents-geo-chart').val() : $('#countries-geo-chart').val());
    }

    /**
     * Steps switcher (Prev)
     */
    previousStepButton.on('click', function (e) {
        e.preventDefault();

        $('.wdt-preload-layer').animateFadeIn();
        var curStep = $('div.chart-wizard-step:visible').data('step');

        switch (curStep) {
            case 'step2':
                previousStepButton.prop('disabled', true);
                previousStepButton.hide();
                $('div.chart-wizard-step.step1').show();
                $('div.chart-wizard-step.step2').hide();
                $('li.chart_wizard_breadcrumbs_block.step2').removeClass('active');
                $('li.chart_wizard_breadcrumbs_block.step1').addClass('active');
                $('#chart-render-engine').change();
                $('.wdt-preload-layer').animateFadeOut();
                break;
            case 'step3':
                $('div.chart-wizard-step.step2').show();
                $('div.chart-wizard-step.step3').hide();
                $('li.chart_wizard_breadcrumbs_block.step3').removeClass('active');
                $('li.chart_wizard_breadcrumbs_block.step2').addClass('active');
                $('.wdt-preload-layer').animateFadeOut();
                break;
            case 'step4':
                $('div.chart-wizard-step.step3').show();
                $('div.chart-wizard-step.step4').hide();
                $('li.chart_wizard_breadcrumbs_block.step4').removeClass('active');
                $('li.chart_wizard_breadcrumbs_block.step3').addClass('active');
                $('.wdt-preload-layer').animateFadeOut();
                nextStepButton.removeClass('wdt-save-chart').html('Next ')
                nextStepButton.prop('disabled', false);
                $('#countries-geochart-container').selectpicker();
                break;
            case 'step5':
                $('div.chart-wizard-step.step4').show();
                $('div.chart-wizard-step.step5').hide();
                $('li.chart_wizard_breadcrumbs_block.step5').removeClass('active');
                $('li.chart_wizard_breadcrumbs_block.step4').addClass('active');
                nextStepButton.prop('disabled', false);
                nextStepButton.show();
                $('#finishButton').hide();
                $('.wdt-preload-layer').animateFadeOut();
                break;
        }
    });

    /**
     * Open chart browser on finish
     */
    $('#finishButton').on('click', function (e) {
        e.preventDefault();
        window.location = $('#wdt-browse-charts-url').val();
    });

    /**
     * Pick the chart type
     */
    $('#chart-render-engine').on('change', function (e) {
        e.preventDefault();
        nextStepButton.prop('disabled', true);
        $('.wdt-chart-wizard-chart-selecter-block .card').removeClass('selected').removeClass('not-selected');
        $('div.charts-type').hide();
        if ($(this).val() != '') {
            constructedChartData.engine = $(this).val();
            if (constructedChartData.engine === 'google') {
                $('div.google-charts-type').show();
                var googleKeyValidate = parseInt(wpdatatables_mapsapikey.wdtGoogleApiMapsValidated) || 0;
                if (googleKeyValidate != 1) {
                    $('div[data-type="google_marker_geo_chart"]')[0].style.opacity = 0.4;
                    $('div[data-type="google_marker_geo_chart"]')[0].classList += ' disabled'
                    $('div[data-type="google_text_geo_chart"]')[0].style.opacity = 0.4;
                    $('div[data-type="google_text_geo_chart"]')[0].classList += ' disabled';
                    $('div.alert.alert-info.alert-dismissible').removeClass('hidden');
                }
            } else if (constructedChartData.engine === 'highcharts') {
                $('div.highcharts-charts-type').show();
            } else if (constructedChartData.engine === 'chartjs') {
                $('div.chartjs-charts-type').show();
            } else if (constructedChartData.engine === 'apexcharts') {
                $('div.apexcharts-charts-type').show();
            } else if (constructedChartData.engine === 'highstock') {
                $('div.highstock-charts-type').show();
            }
        }
    });

    /**
     * Pick the data type
     */
    $('#wpdatatables-chart-source').on('change', function (e) {
        e.preventDefault();
        if ($(this).val() == '') {
            nextStepButton.prop('disabled', true);
        } else {
            nextStepButton.prop('disabled', false);
        }
    });

    /**
     * Responsive width checkbox
     */
    $('#chart-responsive-width').on('change', function (e) {
        if ($(this).is(':checked')) {
            $('#chart-width').val('0');
            $('#btn-plus-chart-width').prop('disabled', true);
            $('#btn-minus-chart-width').prop('disabled', true);
            $('#chart-width').prop('readonly', 'readonly');
        } else {
            $('#btn-plus-chart-width').prop('disabled', false);
            $('#btn-minus-chart-width').prop('disabled', false);
            $('#chart-width').prop('readonly', '');
            $('#chart-width').val('400');
        }
    });

    /**
     * Select all columns in the column selecter
     */
    $('button.select-all-columns, button.deselect-all-columns').on('click', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        if ($(this).hasClass('select-all-columns')) {
            $(this).closest('.card').find('div.chart-column-block').addClass('selected');
            $(this).text(wpdatatables_chart_wizard_strings.deselectAll);
        } else {
            $(this).closest('.card').find('div.chart-column-block').removeClass('selected');
            $(this).text(wpdatatables_chart_wizard_strings.selectAll);
        }
        $(this).toggleClass('select-all-columns deselect-all-columns');

    });

    /**
     * Select a column in chart row range picker
     */
    $(document).on('click', 'div.wdt-chart-column-picker-container div.chart-column-block', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
        } else {
            $(this).addClass('selected');
        }
    });

    /**
     * Check for limit of string columns
     */
    function checkColumnsLimit() {
        // 1 - Checking for string columns
        let string_columns = 0;
        let valid = true;
        let date_columns = 0;
        $('div.wdt-chart-wizard-chosen-columns-container div.chart-column-block').each(function () {
            if (
                $(this).hasClass('string')
                || $(this).hasClass('link')
                || $(this).hasClass('email')
                || $(this).hasClass('image')
                || $(this).hasClass('date')
                || $(this).hasClass('datetime')
                || $(this).hasClass('time')
                || $(this).hasClass('masterdetail')
            ) {
                string_columns++;
                if ($(this).hasClass('date') || $(this).hasClass('datetime')) {
                    date_columns++;
                }
            }
        });
        if (this.constructedChartData.engine === 'highstock') {
            // Disable follow filtering for Highstock for certain date formats
            var invalidFormats = ["d Mon Y", "d M Y", "Mon Y", "M Y", "F Y", "F j, Y", "j. F Y.", "Y"];
            if (invalidFormats.includes(wpdatatables_settings.wdtDateFormat)) {
                $('label[for=follow-table-filtering]').addClass('disabled');
                $('input#follow-table-filtering').attr('disabled', 'disabled');
                $('div.datetime-format-error').show();
            } else {
                $('label[for=follow-table-filtering]').removeClass('disabled');
                $('input#follow-table-filtering').removeAttr('disabled');
                $('div.datetime-format-error').hide();
            }

            if (string_columns > date_columns) {
                $('div.chosen_columns div.datetime-only-error').show();
                valid = false;
            } else {
                $('div.chosen_columns div.datetime-only-error').hide();
            }
            if (date_columns > 1) {
                $('div.chosen_columns div.datetime-error').show();
                valid = false;
            } else {
                $('div.chosen_columns div.datetime-error').hide();
            }
            if (date_columns === 0) {
                $('div.chosen_columns div.datetime-needed-error').show();
                valid = false;
            } else {
                $('div.chosen_columns div.datetime-needed-error').hide();
            }
        } else if (string_columns > 1) {
            $('div.chosen_columns div.strings-error').show();
            valid = false;
        } else {
            $('div.chosen_columns div.strings-error').hide();
        }
        // 2 - Checking for min and max columns limit
        var totalColumnCount = $('div.wdt-chart-wizard-chosen-columns-container div.chart-column-block').length;
        if (totalColumnCount < constructedChartData.min_columns) {
            $('div.chosen_columns div.min-columns-error').show();
            $('div.chosen_columns div.min-columns-error span.columns').html(constructedChartData.min_columns);
            valid = false;
        } else {
            $('div.chosen_columns div.min-columns-error').hide();
        }
        if ((constructedChartData.max_columns > 0)
            && (totalColumnCount > constructedChartData.max_columns)) {
            $('div.chosen_columns div.max-columns-error').show();
            $('div.chosen_columns div.max-columns-error span.columns').html(constructedChartData.max_columns);
            valid = false;
        } else {
            $('div.chosen_columns div.max-columns-error').hide();
        }
        if (!valid) {
            nextStepButton.prop('disabled', true);
        } else {
            nextStepButton.prop('disabled', false);
        }
    }

    /**
     * Add columns to chart
     */
    $('#wdt-add-chart-columns').on('click', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        $('div.wdt-chart-column-picker-container div.wdt-chart-wizart-existing-columns-container div.chart-column-block.selected').each(function () {
            $(this).appendTo('div.wdt-chart-column-picker-container div.wdt-chart-wizard-chosen-columns-container').removeClass('selected');
        });

        if ($('.wdt-chart-column-picker-container .existing-columns button').hasClass('deselect-all-columns') ||
            ($('.wdt-chart-wizart-existing-columns-container div.chart-column-block').length === 0
                && $('.wdt-chart-column-picker-container .existing-columns button').hasClass('select-all-columns'))) {
            $('.wdt-chart-wizart-existing-columns-container div.chart-column-block').removeClass('selected');
            $('.wdt-chart-column-picker-container .existing-columns button.deselect-all-columns').text(wpdatatables_chart_wizard_strings.selectAll);
            $('.wdt-chart-column-picker-container .existing-columns button').toggleClass('select-all-columns deselect-all-columns');
            $('.wdt-chart-column-picker-container .existing-columns button.deselect-all-columns').removeClass('disabled').removeAttr('disabled');
            $('.wdt-chart-column-picker-container .existing-columns button.select-all-columns').removeClass('disabled').removeAttr('disabled');

            if ($('.wdt-chart-wizart-existing-columns-container div.chart-column-block').length === 0) {
                $('.wdt-chart-column-picker-container .existing-columns button.deselect-all-columns').addClass('disabled').attr('disabled', 'disabled');
                $('.wdt-chart-column-picker-container .existing-columns button.select-all-columns').addClass('disabled').attr('disabled', 'disabled');
            }
        }
        $('.wdt-chart-column-picker-container .chosen_columns button.deselect-all-columns').removeClass('disabled').removeAttr('disabled');
        $('.wdt-chart-column-picker-container .chosen_columns button.select-all-columns').removeClass('disabled').removeAttr('disabled');

        checkColumnsLimit();
    });

    /**
     * Add all columns to chart
     */
    $('#wdt-add-all-chart-columns').on('click', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        $('div.wdt-chart-column-picker-container div.wdt-chart-wizart-existing-columns-container div.chart-column-block').addClass('selected');
        $('#wdt-add-chart-columns').click();
        $('div.wdt-chart-column-picker-container div.wdt-chart-wizard-chosen-columns-container div.chart-column-block').removeClass('selected');
        $('.wdt-chart-column-picker-container .existing-columns button.deselect-all-columns').addClass('disabled').attr('disabled', 'disabled');
        $('.wdt-chart-column-picker-container .existing-columns button.select-all-columns').addClass('disabled').attr('disabled', 'disabled');
        $('.wdt-chart-column-picker-container .chosen_columns button.deselect-all-columns').removeClass('disabled').removeAttr('disabled');
        $('.wdt-chart-column-picker-container .chosen_columns button.select-all-columns').removeClass('disabled').removeAttr('disabled');
        checkColumnsLimit();
    });

    /**
     * Remove columns from chart series
     */
    $('#wdt-remove-chart-columns').on('click', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        $('div.wdt-chart-column-picker-container div.wdt-chart-wizard-chosen-columns-container div.chart-column-block.selected').each(function () {
            $(this).appendTo('div.wdt-chart-column-picker-container div.wdt-chart-wizart-existing-columns-container ').removeClass('selected');
        });

        if ($('.wdt-chart-column-picker-container .chosen_columns button').hasClass('deselect-all-columns') ||
            ($('.wdt-chart-wizard-chosen-columns-container div.chart-column-block').length === 0 && $('.wdt-chart-column-picker-container .chosen_columns button').hasClass('select-all-columns'))) {
            $('.wdt-chart-wizard-chosen-columns-container div.chart-column-block').removeClass('selected');
            $('.wdt-chart-column-picker-container .chosen_columns button.deselect-all-columns').text(wpdatatables_chart_wizard_strings.selectAll);
            $('.wdt-chart-column-picker-container .chosen_columns button').toggleClass('select-all-columns deselect-all-columns');
            $('.wdt-chart-column-picker-container .chosen_columns button.deselect-all-columns').removeClass('disabled').removeAttr('disabled');
            $('.wdt-chart-column-picker-container .chosen_columns button.select-all-columns').removeClass('disabled').removeAttr('disabled');

            if ($('.wdt-chart-wizard-chosen-columns-container div.chart-column-block').length === 0) {
                $('.wdt-chart-column-picker-container .chosen_columns button.deselect-all-columns').addClass('disabled').attr('disabled', 'disabled');
                $('.wdt-chart-column-picker-container .chosen_columns button.select-all-columns').addClass('disabled').attr('disabled', 'disabled');
            }
        }
        $('.wdt-chart-column-picker-container .existing-columns button.deselect-all-columns').removeClass('disabled').removeAttr('disabled');
        $('.wdt-chart-column-picker-container .existing-columns button.select-all-columns').removeClass('disabled').removeAttr('disabled');

        checkColumnsLimit();
    });

    /**
     * Remove all columns from chart
     */
    $('#wdt-remove-all-chart-columns').on('click', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        $('div.wdt-chart-column-picker-container div.wdt-chart-wizard-chosen-columns-container div.chart-column-block').addClass('selected');
        $('#wdt-remove-chart-columns').click();
        $('div.wdt-chart-column-picker-container div.wdt-chart-wizart-existing-columns-container div.chart-column-block').removeClass('selected');
        $('.wdt-chart-column-picker-container .chosen_columns button.deselect-all-columns').addClass('disabled').attr('disabled', 'disabled');
        $('.wdt-chart-column-picker-container .chosen_columns button.select-all-columns').addClass('disabled').attr('disabled', 'disabled');
        $('.wdt-chart-column-picker-container .existing-columns button.deselect-all-columns').removeClass('disabled').removeAttr('disabled');
        $('.wdt-chart-column-picker-container .existing-columns button.select-all-columns').removeClass('disabled').removeAttr('disabled');
    });

    /**
     * Change the range type
     */
    $('#wdt-chart-row-range-type').on('change', function (e) {
        e.preventDefault();
        //e.stopImmediatePropagation();
        if ($(this).val() == 'all_rows') {
            constructedChartData.range_type = 'all_rows';
            $('#range_picked_info span').html('All');
            $('#open-range-picker-btn').hide();
            $('label[for=follow-table-filtering]').removeClass('disabled');
            $('input#follow-table-filtering').removeAttr('disabled');
        } else {
            constructedChartData.range_type = 'picked_range';
            $('#open-range-picker-btn').show();
            if (typeof constructedChartData.range_data == 'undefined') {
                constructedChartData.range_data = [];
            }
            $('label[for=follow-table-filtering]').addClass('disabled');
            $('input#follow-table-filtering').attr('disabled', 'disabled');
        }
    });

    /**
     * Update the picked range
     */
    var wdtUpdateChartRange = function () {
        $('table.range-picker-table td').removeClass('selected');
        $('table.range-picker-table tbody tr').each(function () {
            if ($(this).find('td.pick-row input.add-row-to-range').is(':checked')) {
                $(this).find('td').not('.pick-row').each(function () {
                    if ($('table.range-picker-table thead th:eq(' + $(this).index() + ') input.pick-column-range:checked').length) {
                        $(this).addClass('selected');
                    } else {
                        $(this).removeClass('selected');
                    }
                });
            }
        });
    };

    /**
     * Open the range picker
     */
    $('#open-range-picker-btn').on('click', function (e) {
        e.preventDefault();
        if (typeof constructedChartData.selected_columns == 'undefined') {
            constructedChartData.selected_columns = {};
        }
        $('.wdt-preload-layer').animateFadeIn();
        $.ajax({
            url: ajaxurl,
            type: 'post',
            dataType: 'json',
            data: {
                action: 'wpdatatables_get_complete_table_json_by_id',
                table_id: constructedChartData.wpdatatable_id,
                wdtNonce: $('#wdtNonce').val(),
            },
            success: function (tableData) {
                // Extract the column headers
                if (tableData.length > 0) {
                    var columnHeaders = [];
                    var selectedRows = constructedChartData.range_data;
                    for (var columnHeader in tableData[0]) {
                        for (var i in wdtChartColumnsData) {
                            if (wdtChartColumnsData[i].orig_header == columnHeader) {
                                var checked = 0;
                                if (typeof constructedChartData.selected_columns[wdtChartColumnsData[i].id] != 'undefined') {
                                    checked = 1;
                                }
                                columnHeaders.push({
                                    header: columnHeader,
                                    id: wdtChartColumnsData[i].id,
                                    checked: checked
                                });
                                break;
                            }
                        }
                    }

                    for (var k = 0; k < tableData.length; k++) {
                        var rowChecked = 0;
                        for (l = 0; l < selectedRows.length; l++) {
                            if (selectedRows[l] == k) {
                                rowChecked = 1;
                                break;
                            }
                        }
                        tableData[k]['rowChecked'] = rowChecked;
                    }

                    var rangePickerTemplate = $.templates("#range-picker-block");
                    var rangePickerHTML = rangePickerTemplate.render({
                        columnHeaders: columnHeaders,
                        tableData: tableData
                    });
                    $('#pick-range-table-container').html(rangePickerHTML);
                    $('.wdt-preload-layer').animateFadeOut();
                    $('#wdt-range-picker').modal('show');
                    wdtUpdateChartRange();
                }
            }
        });
    });

    /**
     * Add/remove row to range
     */
    $(document).on('change', '#wdt-range-picker table input.add-row-to-range, #wdt-range-picker table input.pick-column-range', function (e) {
        e.preventDefault();
        wdtUpdateChartRange();
    });

    $(document).on('click', '#wdt-range-picker table input.add-row-to-range', function (e) {
        e.stopImmediatePropagation();
    });

    function wdtRangePickerMouseDown(e) {
        if (e.target.nodeName == 'INPUT') {
            return;
        }
        if (isRightClick(e)) {
            return false;
        } else {
            var allCells = $("#wdt-range-picker table tbody td");
            wdtChartPickerDragStart = allCells.index($(this));
            wdtChartPickerIsDragging = true;

            if (typeof e.preventDefault != 'undefined') {
                e.preventDefault();
            }
            document.documentElement.onselectstart = function () {
                return false;
            };
        }
    }

    function wdtRangePickerMouseUp(e) {
        if (e.target.nodeName == 'INPUT') {
            wdtUpdateChartRange();
            return;
        }
        if (isRightClick(e)) {
            return false;
        } else {
            var allCells = $("#wdt-range-picker table tbody td");
            wdtChartPickerDragEnd = allCells.index($(this));

            wdtChartPickerIsDragging = false;
            if (wdtChartPickerDragEnd != 0) {
                wdtRangePickerSelectRange();
            }

            document.documentElement.onselectstart = function () {
                return true;
            };
        }
    }

    function wdtRangePickerMouseMove(e) {
        if (wdtChartPickerIsDragging) {
            var allCells = $("#wdt-range-picker table tbody td");
            wdtChartPickerDragEnd = allCells.index($(this));
            wdtRangePickerSelectRange();
        }
    }

    function wdtRangePickerSelectRange() {

        $firstSelected = $("#wdt-range-picker table tbody td").eq(wdtChartPickerDragStart);
        $lastSelected = $("#wdt-range-picker table tbody td").eq(wdtChartPickerDragEnd);
        // Reset all the selected columns and rows
        $('#wdt-range-picker input.pick-column-range').prop('checked', false);
        $('#wdt-range-picker input.add-row-to-range').prop('checked', false);

        // Get the selected columns indexes
        var startColumnIndex = $firstSelected.index();
        var endColumnIndex = $lastSelected.index();

        if (startColumnIndex < endColumnIndex + 1) {
            $('#wdt-range-picker table thead th').slice(startColumnIndex, endColumnIndex + 1).find('input.pick-column-range').prop('checked', true);
        } else {
            $('#wdt-range-picker table thead th').slice(endColumnIndex, startColumnIndex + 1).find('input.pick-column-range').prop('checked', true);
        }

        // Get the selected rows indexes
        var startRowIndex = $firstSelected.parent().index();
        var endRowIndex = $lastSelected.parent().index();

        if (startRowIndex < endRowIndex + 1) {
            $('#wdt-range-picker table tbody tr').slice(startRowIndex, endRowIndex + 1).find('input.add-row-to-range').prop('checked', true);
        } else {
            $('#wdt-range-picker table tbody tr').slice(endRowIndex, startRowIndex + 1).find('input.add-row-to-range').prop('checked', true);
        }

        wdtUpdateChartRange();
    }


    $(document)
        .on('mousedown', '#wdt-range-picker table tbody td', wdtRangePickerMouseDown)
        .on('mouseup', '#wdt-range-picker table tbody td', wdtRangePickerMouseUp)
        .on('mousemove', '#wdt-range-picker table tbody td', wdtRangePickerMouseMove);

    /**
     * Submit the pick range
     */
    $('#submit-pick-range').on('click', function (e) {
        e.preventDefault();
        // First update the picked columns range
        // Remove all columns
        $('#wdt-remove-all-chart-columns').click();
        // Deselect all columns
        $('div.wdt-chart-wizart-existing-columns-container div.chart-column-block').removeClass('selected');
        // Select the columns picked in the range picker
        $('#wdt-range-picker table input.pick-column-range:checked').each(function () {
            var column_id = $(this).closest('th').data('column_id');
            $('div.wdt-chart-wizart-existing-columns-container div.chart-column-block[data-column_id="' + column_id + '"]').addClass('selected');
        });
        // Add the columns
        $('#wdt-add-chart-columns').click();
        // Add the selected row indexes
        var selectedIndexes = [];
        $('#wdt-range-picker table input.add-row-to-range:checked').each(function () {
            selectedIndexes.push($(this).closest('tr').data('index'));
        });
        constructedChartData.range_data = selectedIndexes;
        // Update the counter in the row range data
        $('#range_picked_info span').html(selectedIndexes.length);
        $('#wdt-range-picker').modal('hide');
    });

    /**
     * Load data for editing existing charts
     */
    $(document).ready(function () {
        if ($('#wp-data-chart-id').val() != '') {

            $('#chart-render-engine').change();
            constructedChartData.id = $('#wp-data-chart-id').val();
            constructedChartData.title = editing_chart_data.title;
            // General settings
            $('.charts-type').find("[data-type='" + editing_chart_data.type + "']").click();
            $('#wpdatatables-chart-source').val(editing_chart_data.wpdatatable_id);

            if (editing_chart_data.range_type == 'picked_range') {
                $('#wdt-chart-row-range-type').val('pick_rows').change();
                constructedChartData.range_data = editing_chart_data.row_range;
                constructedChartData.selected_columns = editing_chart_data.selected_columns;
                $('#range_picked_info span').html(constructedChartData.range_data.length);
            }

            if (editing_chart_data.follow_filtering == 1) {
                $('#follow-table-filtering').prop('checked', 'checked');
            } else {
                $('#follow-table-filtering').prop('checked', '');
            }

            // Chart
            if (typeof editing_chart_data.render_data.options.width !== 'undefined') {
                $('#chart-width').val(editing_chart_data.render_data.options.width);
                $('#chart-responsive-width').prop('checked', '');
                $('#btn-plus-chart-width').prop('disabled', false);
                $('#btn-minus-chart-width').prop('disabled', false);
                $('#chart-width').prop('readonly', '');
            } else {
                $('#chart-responsive-width').prop('checked', 'checked');
                $('#chart-width').val(0);
                $('#chart-width').prop('readonly', 'readonly');
            }
            $('#chart-height').val(editing_chart_data.render_data.options.height);

            if (editing_chart_data.render_data.group_chart) {
                $('#group-chart').prop('checked', 'checked');
            } else {
                $('#group-chart').prop('checked', '');
            }

            // Axes
            if (editing_chart_data.render_data.show_grid == null) {
                $('#show-grid').prop('checked', 'checked');
            } else {
                if (editing_chart_data.render_data.show_grid) {
                    $('#show-grid').prop('checked', 'checked');
                } else {
                    $('#show-grid').prop('checked', '');
                }
            }
            $('#horizontal-axis-label').val(editing_chart_data.render_data.options.hAxis.title);
            $('#vertical-axis-label').val(editing_chart_data.render_data.options.vAxis.title);

            // Title
            if (editing_chart_data.render_data.options.title) {
                $('#show-chart-title').prop('checked', 'checked');
            } else {
                $('#show-chart-title').prop('checked', '');
            }

            if (editing_chart_data.engine == 'google') {
                // Chart
                if (editing_chart_data.render_data.options.backgroundColor == null) {
                    $('input.background-color').val('');
                    $('#border-width').val('');
                    $('input.border_color').val('');
                    $('#border-radius').val('');

                } else {
                    if (editing_chart_data.render_data.options.backgroundColor.fill) {
                        $('#background-color').val(editing_chart_data.render_data.options.backgroundColor.fill);
                    }
                    $('#border-width').val(editing_chart_data.render_data.options.backgroundColor.strokeWidth);
                    if (editing_chart_data.render_data.options.backgroundColor.stroke) {
                        $('#border_color').val(editing_chart_data.render_data.options.backgroundColor.stroke);
                    }
                    $('#border-radius').val(editing_chart_data.render_data.options.backgroundColor.rx);
                }
                if ($.inArray(editing_chart_data.type, ['google_text_geo_chart', 'google_marker_geo_chart', 'google_geo_chart']) != -1) {
                    if (editing_chart_data.render_data.options.datalessRegionColor == null) {
                        $('input.geochart_color').val('');
                    } else {
                        $('input.geochart_color').val(editing_chart_data.render_data.options.datalessRegionColor);
                    }
                    if (editing_chart_data.render_data.options.colors == null) {
                        $('input.geochart-color-region').val('');
                    } else {
                        $('input.geochart-color-region').val(editing_chart_data.render_data.options.colors);
                    }

                    $('#sub-continents-geochart-container .wdt_america, ' +
                        '#sub-continents-geochart-container .wdt_asia,' +
                        ' #sub-continents-geochart-container .wdt_australia,' +
                        ' #sub-continents-geochart-container .wdt_europe,' +
                        ' #sub-continents-geochart-container .wdt_africa'
                    ).addClass('hidden');
                    $('#countries-geochart-container .wdt_africa_c,' +
                        ' #countries-geochart-container .wdt_europe_c,' +
                        ' #countries-geochart-container .wdt_america_c,' +
                        ' #countries-geochart-container .wdt_australia_c,' +
                        ' #countries-geochart-container .wdt_asia_c'
                    ).addClass('hidden');

                    if (editing_chart_data.render_data.options.region == 'world') {
                        $('#region-google-charts').val('world');
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['002', '150', '019', '142', '009']) != -1) {
                        $('#region-google-charts').val(editing_chart_data.render_data.options.region);
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['015', '011', '017', '014', '018']) != -1) {
                        $('#region-google-charts').selectpicker('val', '002');
                        $('#sub-continents-geo-chart').val(editing_chart_data.render_data.options.region)
                        $('#sub-continents-geo-chart .wdt_africa').removeClass('hidden');
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['154', '155', '151', '039']) != -1) {
                        $('#region-google-charts').selectpicker('val', '150');
                        $('#region-google-charts').val('150');
                        $('#sub-continents-geo-chart').val(editing_chart_data.render_data.options.region)
                        $('#sub-continents-geo-chart .wdt_europe').removeClass('hidden');
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['021', '029', '013', '005']) != -1) {
                        $('#region-google-charts').selectpicker('val', '019');
                        $('#region-google-charts').val('019');
                        $('#sub-continents-geo-chart').val(editing_chart_data.render_data.options.region)
                        $('#sub-continents-geo-chart .wdt_america').removeClass('hidden');
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['053', '054', '057', '061']) != -1) {
                        $('#region-google-charts').selectpicker('val', '009');
                        $('#region-google-charts').val('009');
                        $('#sub-continents-geo-chart').val(editing_chart_data.render_data.options.region)
                        $('#sub-continents-geo-chart .wdt_australia').removeClass('hidden');
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['143', '030', '034', '035', '145']) != -1) {
                        $('#region-google-charts').selectpicker('val', '142');
                        $('#region-google-charts').val('142');
                        $('#sub-continents-geo-chart').val(editing_chart_data.render_data.options.region)
                        $('#sub-continents-geo-chart .wdt_asia').removeClass('hidden');
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['DZ', 'EG', 'EH', 'LY', 'MA', 'SD', 'SS', 'TN']) != -1) {
                        $('#region-google-charts').selectpicker('val', '002');
                        $('#sub-continents-geo-chart').val('015');
                        $('#countries-geo-chart').val(editing_chart_data.render_data.options.region);
                        $('#countries-geo-chart .wdt_northern_africa').removeClass('hidden')
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['BF', 'BJ', 'CI', 'CV', 'GH', 'GN', 'GW', 'LR', 'ML', 'MR', 'NE', 'NG', 'SH', 'SL', 'SN', 'TG']) != -1) {
                        $('#region-google-charts').selectpicker('val', '002');
                        $('#sub-continents-geo-chart').val('011');
                        $('#countries-geo-chart').val(editing_chart_data.render_data.options.region);
                        $('#countries-geo-chart .wdt_western_africa').removeClass('hidden');
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['AO', 'CD', 'ZR', 'CF', 'CG', 'CM', 'GA', 'GQ', 'ST', 'TD']) != -1) {
                        $('#region-google-charts').selectpicker('val', '002');
                        $('#sub-continents-geo-chart').val('017');
                        $('#countries-geochart-container').val(editing_chart_data.render_data.options.region);
                        $('#countries-geo-chart .wdt_middle_africa').removeClass('hidden');
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['BI', 'DJ', 'ER', 'ET', 'KE', 'KM', 'MG', 'MU', 'MW', 'MZ', 'RE', 'RW', 'SC', 'SO', 'TZ', 'UG', 'YT', 'ZM', 'ZW']) != -1) {
                        $('#region-google-charts').selectpicker('val', '002');
                        $('#sub-continents-geo-chart').val('014');
                        $('#countries-geo-chart').val(editing_chart_data.render_data.options.region);
                        $('#countries-geo-chart .wdt_eastern_africa').removeClass('hidden');
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['BW', 'LS', 'NA', 'SZ', 'ZA']) != -1) {
                        $('#region-google-charts').selectpicker('val', '002');
                        $('#sub-continents-geo-chart').val('018');
                        $('#countries-geo-chart').val(editing_chart_data.render_data.options.region);
                        $('#countries-geo-chart .wdt_southern_africa').removeClass('hidden');
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['GG', 'JE', 'AX', 'DK', 'EE', 'FI', 'FO', 'GB', 'IE', 'IM', 'IS', 'LT', 'LV', 'NO', 'SE', 'SJ']) != -1) {
                        $('#region-google-charts').selectpicker('val', '150');
                        $('#sub-continents-geo-chart').val('154');
                        $('#countries-geo-chart').val(editing_chart_data.render_data.options.region);
                        $('#countries-geo-chart .wdt_northern_europe').removeClass('hidden');
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['AT', 'BE', 'CH', 'DE', 'DD', 'FR', 'FX', 'LI', 'LU', 'MC', 'NL']) != -1) {
                        $('#region-google-charts').selectpicker('val', '150');
                        $('#sub-continents-geo-chart').val('155');
                        $('#countries-geo-chart').val(editing_chart_data.render_data.options.region);
                        $('#countries-geo-chart .wdt_western_europe').removeClass('hidden');
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['BG', 'BY', 'CZ', 'HU', 'MD', 'PL', 'RO', 'RU', 'SU', 'SK', 'UA']) != -1) {
                        $('#region-google-charts').selectpicker('val', '150');
                        $('#sub-continents-geo-chart').val('151');
                        $('#countries-geo-chart').val(editing_chart_data.render_data.options.region);
                        $('#countries-geo-chart .wdt_eastern_europe').removeClass('hidden');
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['AD', 'AL', 'BA', 'ES', 'GI', 'GR', 'HR', 'IT', 'ME', 'MK', 'MT', 'RS', 'PT', 'SI', 'SM', 'VA', 'YU']) != -1) {
                        $('#region-google-charts').selectpicker('val', '150');
                        $('#sub-continents-geo-chart').val('039');
                        $('#countries-geo-chart').val(editing_chart_data.render_data.options.region);
                        $('#countries-geo-chart .wdt_southern_europe').removeClass('hidden');
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['BM', 'CA', 'GL', 'PM', 'US']) != -1) {
                        $('#region-google-charts').selectpicker('val', '019');
                        $('#sub-continents-geo-chart').val('021');
                        $('#countries-geo-chart').val(editing_chart_data.render_data.options.region);
                        $('#countries-geo-chart .wdt_northern_america').removeClass('hidden');
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['AG', 'AI', 'AN', 'AW', 'BB', 'BL', 'BS', 'CU', 'DM', 'DO', 'GD', 'GP', 'HT', 'JM', 'KN', 'KY', 'LC', 'MF', 'MQ', 'MS', 'PR', 'TC', 'TT', 'VC', 'VG', 'VI']) != -1) {
                        $('#region-google-charts').selectpicker('val', '019');
                        $('#sub-continents-geo-chart').val('029');
                        $('#countries-geo-chart').val(editing_chart_data.render_data.options.region);
                        ;
                        $('#countries-geo-chart .wdt_caribbean').removeClass('hidden');
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['BZ', 'CR', 'GT', 'HN', 'MX', 'NI', 'PA', 'SV']) != -1) {
                        $('#region-google-charts').selectpicker('val', '019');
                        $('#sub-continents-geo-chart').val('013');
                        $('#countries-geo-chart').val(editing_chart_data.render_data.options.region);
                        $('#countries-geo-chart .wdt_central_america').removeClass('hidden');
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['AR', 'BO', 'BR', 'CL', 'CO', 'EC', 'FK', 'GF', 'GY', 'PE', 'PY', 'SR', 'UY', 'VE']) != -1) {
                        $('#region-google-charts').selectpicker('val', '019');
                        $('#sub-continents-geo-chart').val('005');
                        $('#countries-geo-chart').val(editing_chart_data.render_data.options.region);
                        $('#countries-geo-chart .wdt_south_america').removeClass('hidden');
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['TM', 'TJ', 'KG', 'KZ', 'UZ']) != -1) {
                        $('#region-google-charts').selectpicker('val', '142');
                        $('#sub-continents-geo-chart').val('143');
                        $('#countries-geo-chart').val(editing_chart_data.render_data.options.region);
                        $('#countries-geo-chart .wdt_central_asia').removeClass('hidden');
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['CN', 'HK', 'JP', 'KP', 'KR', 'MN', 'MO', 'TW']) != -1) {
                        $('#region-google-charts').selectpicker('val', '142');
                        $('#sub-continents-geo-chart').val('030');
                        $('#countries-geo-chart').val(editing_chart_data.render_data.options.region);
                        $('#countries-geo-chart .wdt_eastern_asia').removeClass('hidden');
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['AF', 'BD', 'BT', 'IN', 'IR', 'LK', 'MV', 'NP', 'PK']) != -1) {
                        $('#region-google-charts').selectpicker('val', '142');
                        $('#sub-continents-geo-chart').val('034');
                        $('#countries-geo-chart').val(editing_chart_data.render_data.options.region);
                        $('#countries-geo-chart .wdt_southern_asia').removeClass('hidden');
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['BN', 'ID', 'KH', 'LA', 'MM', 'BU', 'MY', 'PH', 'SG', 'TH', 'TL', 'TP', 'VN']) != -1) {
                        $('#region-google-charts').selectpicker('val', '142');
                        $('#sub-continents-geo-chart').val('035');
                        $('#countries-geo-chart').val(editing_chart_data.render_data.options.region);
                        $('#countries-geo-chart .wdt_southern_eastern_asia').removeClass('hidden');
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['AE', 'AM', 'AZ', 'BH', 'CY', 'GE', 'IL', 'IQ', 'JO', 'KW', 'LB', 'OM', 'PS', 'QA', 'SA', 'NT', 'SY', 'TR', 'YE', 'YD']) != -1) {
                        $('#region-google-charts').selectpicker('val', '142');
                        $('#sub-continents-geo-chart').val('145');
                        $('#countries-geo-chart').val(editing_chart_data.render_data.options.region);
                        $('#countries-geo-chart .wdt_western_asia').removeClass('hidden');
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['AU', 'NF', 'NZ']) != -1) {
                        $('#region-google-charts').selectpicker('val', '009');
                        $('#sub-continents-geo-chart').val('053');
                        $('#countries-geo-chart').val(editing_chart_data.render_data.options.region);
                        $('#countries-geo-chart .wdt_australia_and_new_zealand').removeClass('hidden');
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['FJ', 'NC', 'PG', 'SB', 'VU']) != -1) {
                        $('#region-google-charts').selectpicker('val', '009');
                        $('#sub-continents-geo-chart').val('054');
                        $('#countries-geo-chart').val(editing_chart_data.render_data.options.region);
                        $('#countries-geo-chart .wdt_melanesia').removeClass('hidden');
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['FM', 'GU', 'KI', 'MH', 'MP', 'NR', 'PW']) != -1) {
                        $('#region-google-charts').selectpicker('val', '009');
                        $('#sub-continents-geo-chart').val('057');
                        $('#countries-geo-chart').val(editing_chart_data.render_data.options.region);
                        $('#countries-geo-chart .wdt_micronesia').removeClass('hidden');
                    }
                    if ($.inArray(editing_chart_data.render_data.options.region, ['AS', 'CK', 'NU', 'PF', 'PN', 'TK', 'TO', 'TV', 'WF', 'WS']) != -1) {
                        $('#region-google-charts').selectpicker('val', '009');
                        $('#sub-continents-geo-chart').val('061');
                        $('#countries-geo-chart').val(editing_chart_data.render_data.options.region);
                        $('#countries-geo-chart .wdt_polynesia').removeClass('hidden');
                    }
                }
                if (editing_chart_data.render_data.options.chartArea == null) {
                    $('input.plot-background-color').val('');
                    $('#plot-border-width').val('');
                    $('input.plot-border-color').val('');
                } else {
                    if (editing_chart_data.render_data.options.chartArea.backgroundColor.fill) {
                        $('#plot-background-color').val(editing_chart_data.render_data.options.chartArea.backgroundColor.fill);
                    }
                    $('#plot-border-width').val(editing_chart_data.render_data.options.chartArea.backgroundColor.strokeWidth);
                    if (editing_chart_data.render_data.options.chartArea.backgroundColor.stroke) {
                        $('#plot-border-color').val(editing_chart_data.render_data.options.chartArea.backgroundColor.stroke);
                    }
                }

                if (editing_chart_data.render_data.options.fontSize == null) {
                    $('#font-size').val('');
                } else {
                    $('#font-size').val(editing_chart_data.render_data.options.fontSize);
                }
                if (editing_chart_data.render_data.options.fontName == null) {
                    $('#font-name').val('Arial');
                } else {
                    $('#font-name').val(editing_chart_data.render_data.options.fontName);
                }

                if (editing_chart_data.render_data.options.is3D) {
                    $('#three-d').prop('checked', 'checked');
                } else {
                    $('#three-d').prop('checked', '');
                }

                // Series
                if (editing_chart_data.render_data.options.curveType == 'none') {
                    $('#curve-type').prop('checked', '');
                } else {
                    $('#curve-type').prop('checked', 'checked');
                }

                // Axes
                if (editing_chart_data.render_data.options.crosshair == null) {
                    $('#horizontal-axis-crosshair').prop('checked', '');
                    $('#vertical-axis-crosshair').prop('checked', '');
                } else {
                    if (editing_chart_data.render_data.options.crosshair.orientation == 'both') {
                        $('#horizontal-axis-crosshair').prop('checked', 'checked');
                        $('#vertical-axis-crosshair').prop('checked', 'checked');
                    } else if (editing_chart_data.render_data.options.crosshair.orientation == 'horizontal') {
                        $('#horizontal-axis-crosshair').prop('checked', 'checked');
                        $('#vertical-axis-crosshair').prop('checked', '');
                    } else if (editing_chart_data.render_data.options.crosshair.orientation == 'vertical') {
                        $('#horizontal-axis-crosshair').prop('checked', '');
                        $('#vertical-axis-crosshair').prop('checked', 'checked');
                    }
                }


                if (editing_chart_data.render_data.options.hAxis.direction == null) {
                    $('#horizontal-axis-direction').val(1);
                } else {
                    $('#horizontal-axis-direction').val(editing_chart_data.render_data.options.hAxis.direction);
                }

                if (editing_chart_data.render_data.options.vAxis.direction == null) {
                    $('#vertical-axis-direction').val(1);
                } else {
                    $('#vertical-axis-direction').val(editing_chart_data.render_data.options.vAxis.direction);
                }

                if (editing_chart_data.render_data.options.vAxis.viewWindow == null) {
                    $('#vertical-axis-min').val('');
                    $('#vertical-axis-max').val('');
                } else {
                    $('#vertical-axis-min').val(editing_chart_data.render_data.options.vAxis.viewWindow.min);
                    $('#vertical-axis-max').val(editing_chart_data.render_data.options.vAxis.viewWindow.max);
                }

                if (editing_chart_data.render_data.options.orientation == null) {
                    $('#inverted').prop('checked', '');
                } else {
                    if (editing_chart_data.render_data.options.orientation == 'vertical') {
                        $('#inverted').prop('checked', 'checked');
                    }
                }

                // Title
                if (editing_chart_data.render_data.options.titlePosition == null) {
                    $('#title-floating').prop('checked', '');
                } else {
                    if (editing_chart_data.render_data.options.titlePosition == 'in') {
                        $('#title-floating').prop('checked', 'checked');
                    }
                }

                // Tooltip
                if (editing_chart_data.render_data.options.tooltip == null) {
                    $('#tooltip-enabled').prop('checked', 'checked');
                } else {
                    if (editing_chart_data.render_data.options.tooltip.trigger == 'none') {
                        $('#tooltip-enabled').prop('checked', '');
                    }
                }

                // Legend
                if (editing_chart_data.render_data.options.legend == null) {
                    $('#legend_position').val('right');
                    $('#legend_vertical_align').val("bottom");
                } else {
                    $('#legend_position').val(editing_chart_data.render_data.options.legend.position);
                    if (editing_chart_data.render_data.options.legend.alignment == 'end') {
                        $('#legend_vertical_align').val("bottom");
                    } else if (editing_chart_data.render_data.options.legend.alignment == 'center') {
                        $('#legend_vertical_align').val("middle");
                    } else {
                        $('#legend_vertical_align').val("top");
                    }
                }
            } else if (editing_chart_data.engine == 'highcharts') {

                if (editing_chart_data.highcharts_render_data == null) {
                    // Chart
                    $('input.background-color').val('');
                    $('#border-width').val(0);
                    $('input.border_color').val('');
                    $('#border-radius').val(0);
                    $('#zoom-type').val('none');
                    $('#panning').prop('checked', '');
                    $('#pan-key').val('shift');
                    $('input.plot-background-color').val('');
                    $('#plot-background-image').val('');
                    $('#plot-border-width').val(0);
                    $('input.plot-border-color').val('');

                    // Axes
                    $('#highcharts-line-dash-style').val('solid');
                    $('#horizontal-axis-crosshair').prop('checked', '');
                    $('#vertical-axis-crosshair').prop('checked', '');
                    $('#vertical-axis-min').val('');
                    $('#vertical-axis-max').val('');
                    $('#inverted').prop('checked', '');

                    // Title
                    $('#title-floating').prop('checked', '');
                    $('#title-align').val('center');
                    $('#subtitle').val('');
                    $('#subtitle-align').val('center');

                    // Tooltip
                    $('#tooltip-enabled').prop('checked', 'checked');
                    $('input.tooltip-background-color').val('');
                    $('#tooltip-border-width').val(1);
                    $('input.tooltip-border-color').val('');
                    $('#tooltip-border-radius').val(3);
                    $('#tooltip-shared').prop('checked', '');
                    $('#tooltip-value-prefix').val('');
                    $('#tooltip-value-suffix').val('');

                    // Legend
                    $('#show-legend').prop('checked', 'checked');
                    $('input.legend_background_color').val('');
                    $('#legend_title').val('');
                    $('#legend_layout').val('horizontal');
                    $('#legend_align').val('center');
                    $('#legend_vertical_align').val('bottom');
                    $('#legend_border_width').val(0);
                    $('input.legend_border_color').val('');
                    $('#legend_border_radius').val(0);

                    // Exporting
                    $('#exporting').prop('checked', 'checked');
                    $('#exporting-data-labels').prop('checked', '');
                    $('#exporting-file-name').val('');
                    $('#exporting-width').val('');
                    $('#exporting-button-align').val('right');
                    $('#exporting-button-vertical-align').val('top');
                    $('#exporting-button-color').val('');
                    $('#exporting-button-text').val('');

                    // Credits
                    $('#credits').prop('checked', 'checked');
                    $('#credits-href').val('https://www.highcharts.com');
                    $('#credits-text').val('Highcharts.com');

                } else {
                    // Chart
                    if (editing_chart_data.highcharts_render_data.options.chart.backgroundColor) {
                        $('#background-color').val(editing_chart_data.highcharts_render_data.options.chart.backgroundColor);
                    }
                    $('#border-width').val(editing_chart_data.highcharts_render_data.options.chart.borderWidth);
                    if (editing_chart_data.highcharts_render_data.options.chart.borderColor) {
                        $('#border_color').val(editing_chart_data.highcharts_render_data.options.chart.borderColor);
                    }
                    $('#border-radius').val(editing_chart_data.highcharts_render_data.options.chart.borderRadius);
                    $("#zoom-type").append('<option value="y">Y</option>');
                    $("#zoom-type").append('<option value="xy">XY</option>');
                    $('#zoom-type').val(editing_chart_data.highcharts_render_data.options.chart.zoomType);
                    if (editing_chart_data.highcharts_render_data.options.chart.panning) {
                        $('#panning').prop('checked', 'checked');
                    } else {
                        $('#panning').prop('checked', '');
                    }
                    $('#pan-key').val(editing_chart_data.highcharts_render_data.options.chart.panKey);
                    if (editing_chart_data.highcharts_render_data.options.chart.plotBackgroundColor) {
                        $('#plot-background-color').val(editing_chart_data.highcharts_render_data.options.chart.plotBackgroundColor);
                    }
                    $('#plot-background-image').val(editing_chart_data.highcharts_render_data.options.chart.plotBackgroundImage);
                    if (editing_chart_data.highcharts_render_data.options.chart.plotBackgroundImage) {
                        $('#wdt-plot-image-clear-button').html("Clear");
                    } else {
                        $('#wdt-line-image-clear-button').html('<span class="wpdt-icon-image"></span>');
                    }

                    $('#plot-border-width').val(editing_chart_data.highcharts_render_data.options.chart.plotBorderWidth);
                    if (editing_chart_data.highcharts_render_data.options.chart.plotBorderColor) {
                        $('#plot-border-color').val(editing_chart_data.highcharts_render_data.options.chart.plotBorderColor);
                    }
                    if (editing_chart_data.highcharts_render_data.options.credits.enabled) {
                        $('#credits').prop('checked', 'checked');
                    } else {
                        $('#credits').prop('checked', '');
                    }

                    // Axes
                    if (Array.isArray(editing_chart_data.highcharts_render_data.options.yAxis)) {
                        $('#highcharts-line-dash-style').val(editing_chart_data.highcharts_render_data.options.yAxis[0].gridLineDashStyle);
                    } else {
                        $('#highcharts-line-dash-style').val(editing_chart_data.highcharts_render_data.options.yAxis.gridLineDashStyle);
                    }
                    if (editing_chart_data.highcharts_render_data.options.xAxis.crosshair) {
                        $('#horizontal-axis-crosshair').prop('checked', 'checked');
                    } else {
                        $('#horizontal-axis-crosshair').prop('checked', '');
                    }
                    if (Array.isArray(editing_chart_data.highcharts_render_data.options.yAxis)) {
                        if (editing_chart_data.highcharts_render_data.options.yAxis[0].crosshair) {
                            $('#vertical-axis-crosshair').prop('checked', 'checked');
                        } else {
                            $('#vertical-axis-crosshair').prop('checked', '');
                        }
                        $('#vertical-axis-min').val(editing_chart_data.highcharts_render_data.options.yAxis[0].min);
                        $('#vertical-axis-max').val(editing_chart_data.highcharts_render_data.options.yAxis[0].max);
                    } else {
                        if (editing_chart_data.highcharts_render_data.options.yAxis.crosshair) {
                            $('#vertical-axis-crosshair').prop('checked', 'checked');
                        } else {
                            $('#vertical-axis-crosshair').prop('checked', '');
                        }
                        $('#vertical-axis-min').val(editing_chart_data.highcharts_render_data.options.yAxis.min);
                        $('#vertical-axis-max').val(editing_chart_data.highcharts_render_data.options.yAxis.max);
                    }
                    if (editing_chart_data.highcharts_render_data.options.chart.inverted) {
                        $('#inverted').prop('checked', 'checked');
                    } else {
                        $('#inverted').prop('checked', '');
                    }
                    // Title
                    if (editing_chart_data.highcharts_render_data.options.title.floating) {
                        $('#title-floating').prop('checked', 'checked');
                    } else {
                        $('#title-floating').prop('checked', '');
                    }
                    $('#title-align').val(editing_chart_data.highcharts_render_data.options.title.align);
                    $('#subtitle').val(editing_chart_data.highcharts_render_data.options.subtitle.text);
                    $('#subtitle-align').val(editing_chart_data.highcharts_render_data.options.subtitle.align);

                    // Tooltip
                    if (editing_chart_data.highcharts_render_data.options.tooltip.enabled) {
                        $('#tooltip-enabled').prop('checked', 'checked');
                    } else {
                        $('#tooltip-enabled').prop('checked', '');
                    }
                    if (editing_chart_data.highcharts_render_data.options.tooltip.backgroundColor) {
                        $('#tooltip-background-color').val(editing_chart_data.highcharts_render_data.options.tooltip.backgroundColor);
                    }
                    $('#tooltip-border-width').val(editing_chart_data.highcharts_render_data.options.tooltip.borderWidth);
                    if (editing_chart_data.highcharts_render_data.options.tooltip.borderColor) {
                        $('#tooltip-border-color').val(editing_chart_data.highcharts_render_data.options.tooltip.borderColor);
                    }
                    $('#tooltip-border-radius').val(editing_chart_data.highcharts_render_data.options.tooltip.borderRadius);
                    if (editing_chart_data.highcharts_render_data.options.tooltip.shared) {
                        $('#tooltip-shared').prop('checked', 'checked');
                    } else {
                        $('#tooltip-shared').prop('checked', '');
                    }
                    $('#tooltip-value-prefix').val(editing_chart_data.highcharts_render_data.options.tooltip.valuePrefix);
                    $('#tooltip-value-suffix').val(editing_chart_data.highcharts_render_data.options.tooltip.valueSuffix);

                    // Legend
                    if (editing_chart_data.highcharts_render_data.options.legend.enabled) {
                        $('#show-legend').prop('checked', 'checked');
                    } else {
                        $('#show-legend').prop('checked', '');
                    }
                    $('input.legend_background_color').val(editing_chart_data.highcharts_render_data.options.legend.backgroundColor);
                    $('#legend_title').val(editing_chart_data.highcharts_render_data.options.legend.title.text);
                    $('#legend_layout').val(editing_chart_data.highcharts_render_data.options.legend.layout);
                    $('#legend_align').val(editing_chart_data.highcharts_render_data.options.legend.align);
                    $('#legend_vertical_align').val(editing_chart_data.highcharts_render_data.options.legend.verticalAlign);
                    $('#legend_border_width').val(editing_chart_data.highcharts_render_data.options.legend.borderWidth);
                    $('input.legend_border_color').val(editing_chart_data.highcharts_render_data.options.legend.borderColor);
                    $('#legend_border_radius').val(editing_chart_data.highcharts_render_data.options.legend.borderRadius);

                    // Exporting
                    if (editing_chart_data.highcharts_render_data.options.exporting.enabled) {
                        $('#exporting').prop('checked', 'checked');
                    } else {
                        $('#exporting').prop('checked', '');
                    }
                    if (editing_chart_data.highcharts_render_data.options.exporting.chartOptions.plotOptions.series.dataLabels.enabled) {
                        $('#exporting-data-labels').prop('checked', 'checked');
                    } else {
                        $('#exporting-data-labels').prop('checked', '');
                    }
                    $('#exporting-file-name').val(editing_chart_data.highcharts_render_data.options.exporting.filename);
                    $('#exporting-width').val(editing_chart_data.highcharts_render_data.options.exporting.width);
                    $('#exporting-button-align').val(editing_chart_data.highcharts_render_data.options.exporting.buttons.contextButton.align);
                    $('#exporting-button-vertical-align').val(editing_chart_data.highcharts_render_data.options.exporting.buttons.contextButton.verticalAlign);
                    $('#exporting-button-color').val(editing_chart_data.highcharts_render_data.options.exporting.buttons.contextButton.symbolStroke);
                    $('#exporting-button-text').val(editing_chart_data.highcharts_render_data.options.exporting.buttons.contextButton.text);

                    // Credits
                    if (editing_chart_data.highcharts_render_data.options.credits.enabled) {
                        $('#credits').prop('checked', 'checked');
                    } else {
                        $('#credits').prop('checked', '');
                    }
                    $('#credits-href').val(editing_chart_data.highcharts_render_data.options.credits.href);
                    $('#credits-text').val(editing_chart_data.highcharts_render_data.options.credits.text);
                }

            } else if (editing_chart_data.engine == 'chartjs') {
                // Chart
                if (editing_chart_data.chartjs_render_data.configurations.canvas.backgroundColor) {
                    $('#background-color').val(editing_chart_data.chartjs_render_data.configurations.canvas.backgroundColor);
                }
                $('#border-width').val(editing_chart_data.chartjs_render_data.configurations.canvas.borderWidth);
                if (editing_chart_data.chartjs_render_data.configurations.canvas.borderColor) {
                    $('#border_color').val(editing_chart_data.chartjs_render_data.configurations.canvas.borderColor);
                }
                $('#border-radius').val(editing_chart_data.chartjs_render_data.configurations.canvas.borderRadius);

                if (typeof editing_chart_data.chartjs_render_data.options.globalOptions.font !== 'undefined') {
                    if (editing_chart_data.chartjs_render_data.options.globalOptions.font.size == null) {
                        $('#font-size').val('');
                    } else {
                        $('#font-size').val(editing_chart_data.chartjs_render_data.options.globalOptions.font.size);
                    }
                }
                if (typeof editing_chart_data.chartjs_render_data.options.globalOptions.font !== 'undefined') {
                    $('#font-name').val(editing_chart_data.chartjs_render_data.options.globalOptions.font.family);
                    $('#font-style').val(editing_chart_data.chartjs_render_data.options.globalOptions.font.style);
                    $('#font-weight').val(editing_chart_data.chartjs_render_data.options.globalOptions.font.weight);
                }
                if (typeof editing_chart_data.chartjs_render_data.options.globalOptions.color !== 'undefined') {
                    $('#font-color').val(editing_chart_data.chartjs_render_data.options.globalOptions.color);
                }

                // Series
                if (editing_chart_data.type !== 'chartjs_bubble_chart' && editing_chart_data.chartjs_render_data.options.data.datasets[0].lineTension == 0.4) {
                    $('#curve-type').prop('checked', 'checked');
                } else {
                    $('#curve-type').prop('checked', '');
                }

                // Axes version 4.0.2
                if (typeof editing_chart_data.chartjs_render_data.options.options.scales.x !== 'undefined') {
                    $('#horizontal-axis-label').val(editing_chart_data.chartjs_render_data.options.options.scales.x.title.text);
                }
                if (typeof editing_chart_data.chartjs_render_data.options.options.scales.y !== 'undefined') {
                    $('#vertical-axis-label').val(editing_chart_data.chartjs_render_data.options.options.scales.y.title.text);
                    editing_chart_data.chartjs_render_data.options.options.scales.y.beginAtZero ?
                        $('#vertical-axis-min').val(0) : $('#vertical-axis-min').val(editing_chart_data.chartjs_render_data.options.options.scales.y.min);
                    $('#vertical-axis-max').val(editing_chart_data.chartjs_render_data.options.options.scales.y.max);
                }
                // Axes
                if (typeof editing_chart_data.chartjs_render_data.options.options.scales.xAxes !== 'undefined') {
                    $('#horizontal-axis-label').val(editing_chart_data.chartjs_render_data.options.options.scales.xAxes[0].scaleLabel.labelString);
                }
                if (typeof editing_chart_data.chartjs_render_data.options.options.scales.yAxes !== 'undefined') {
                    $('#vertical-axis-label').val(editing_chart_data.chartjs_render_data.options.options.scales.yAxes[0].scaleLabel.labelString);
                    editing_chart_data.chartjs_render_data.options.options.scales.yAxes[0].ticks.beginAtZero ?
                        $('#vertical-axis-min').val(0) : $('#vertical-axis-min').val(editing_chart_data.chartjs_render_data.options.options.scales.yAxes[0].ticks.min);
                    $('#vertical-axis-max').val(editing_chart_data.chartjs_render_data.options.options.scales.yAxes[0].ticks.max);
                }

                if (typeof editing_chart_data.chartjs_render_data.options.options.plugins !== 'undefined') {
                    // Title version 4.0.2
                    $('#title-position').val(editing_chart_data.chartjs_render_data.options.options.plugins.title.position);
                    $('#title-font-name').val(editing_chart_data.chartjs_render_data.options.options.plugins.title.font.family);
                    $('#title-font-style').val(editing_chart_data.chartjs_render_data.options.options.plugins.title.font.style);
                    $('#title-font-weight').val(editing_chart_data.chartjs_render_data.options.options.plugins.title.font.weight);
                    if (editing_chart_data.chartjs_render_data.options.options.plugins.title.color) {
                        $('#title-font-color').val(editing_chart_data.chartjs_render_data.options.options.plugins.title.color);
                    }

                    // Tooltip version 4.0.2
                    if (editing_chart_data.chartjs_render_data.options.options.plugins.tooltip.enabled) {
                        $('#tooltip-enabled').prop('checked', 'checked');
                    } else {
                        $('#tooltip-enabled').prop('checked', '');
                    }
                    if (editing_chart_data.chartjs_render_data.options.options.plugins.tooltip.backgroundColor) {
                        $('#tooltip-background-color').val(editing_chart_data.chartjs_render_data.options.options.plugins.tooltip.backgroundColor);
                    }
                    $('#tooltip-border-radius').val(editing_chart_data.chartjs_render_data.options.options.plugins.tooltip.cornerRadius);
                    if (editing_chart_data.chartjs_render_data.options.options.plugins.tooltip.mode == 'index') {
                        $('#tooltip-shared').prop('checked', 'checked');
                    } else {
                        $('#tooltip-shared').prop('checked', '');
                    }

                    // Legend version 4.0.2
                    if (editing_chart_data.chartjs_render_data.options.options.plugins.legend.display) {
                        $('#show-legend').prop('checked', 'checked');
                    } else {
                        $('#show-legend').prop('checked', '');
                    }
                    $('#legend-position-cjs').val(editing_chart_data.chartjs_render_data.options.options.plugins.legend.position);
                } else {
                    // Title
                    $('#title-position').val(editing_chart_data.chartjs_render_data.options.options.title.position);
                    $('#title-font-name').val(editing_chart_data.chartjs_render_data.options.options.title.fontFamily);
                    $('#title-font-style').val(editing_chart_data.chartjs_render_data.options.options.title.fontStyle);
                    if (editing_chart_data.chartjs_render_data.options.options.title.fontColor) {
                        $('#title-font-color-container').colorpicker('setValue', editing_chart_data.chartjs_render_data.options.options.title.fontColor);
                    }

                    // Tooltip
                    if (editing_chart_data.chartjs_render_data.options.options.tooltips.enabled) {
                        $('#tooltip-enabled').prop('checked', 'checked');
                    } else {
                        $('#tooltip-enabled').prop('checked', '');
                    }
                    if (editing_chart_data.chartjs_render_data.options.options.title.fontColor) {
                        $('#tooltip-background-color-container').colorpicker('setValue', editing_chart_data.chartjs_render_data.options.options.tooltips.backgroundColor);
                    }
                    $('#tooltip-border-radius').val(editing_chart_data.chartjs_render_data.options.options.tooltips.cornerRadius);
                    if (editing_chart_data.chartjs_render_data.options.options.tooltips.mode == 'label') {
                        $('#tooltip-shared').prop('checked', 'checked');
                    } else {
                        $('#tooltip-shared').prop('checked', '');
                    }

                    // Legend
                    if (editing_chart_data.chartjs_render_data.options.options.legend.display) {
                        $('#show-legend').prop('checked', 'checked');
                    } else {
                        $('#show-legend').prop('checked', '');
                    }
                    $('#legend-position-cjs').val(editing_chart_data.chartjs_render_data.options.options.legend.position);
                }
            } else if (editing_chart_data.engine == 'apexcharts') {
                if (editing_chart_data.apexcharts_render_data == null) {
                    // Chart
                    $('#enable-animation').prop('checked', '');
                    $('#data-labels').prop('checked', '');
                    $('input.background-color').val('');
                    $('#zoom-type').val('none');
                    $('input.plot-background-color').val('');
                    $('#plot-background-image').val('');
                    $('input#chart-text-color').val('');
                    $('#line-background-image').val('');

                    // Axes
                    $('#horizontal-axis-crosshair').prop('checked', '');
                    $('#vertical-axis-crosshair').prop('checked', '');
                    $('#vertical-axis-min').val('');
                    $('#vertical-axis-max').val('');
                    $('#tick-amount').val('');
                    $('#inverted').prop('checked', '');

                    // Title
                    $('#title-floating').prop('checked', '');
                    $('#title-align').val('center');
                    $('#subtitle').val('');
                    $('#subtitle-align').val('center');

                    // Tooltip
                    $('#tooltip-enabled').prop('checked', 'checked');
                    $('#follow-cursor').prop('checked', '');
                    $('#fill-series-color').prop('checked', '');

                    // Legend
                    $('#show-legend').prop('checked', 'checked');
                    $('input.legend_background_color').val('');
                    $('#legend_title').val('');
                    $('#legend_layout').val('horizontal');
                    $('#legend_align').val('center');
                    $('#legend_vertical_align').val('bottom');
                    $('#legend_border_width').val(0);
                    $('input.legend_border_color').val('');
                    $('#legend_border_radius').val(0);

                    //Toolbar
                    $('#show-toolbar').prop('checked', '');

                    $('#apex-exporting-file-name').val('');

                } else {
                    // Chart
                    if (editing_chart_data.apexcharts_render_data.options.chart.animations.enabled) {
                        $('#enable-animation').prop('checked', 'checked');
                    } else {
                        $('#enable-animation').prop('checked', '');
                    }
                    if (editing_chart_data.apexcharts_render_data.options.dataLabels.enabled) {
                        $('#data-labels').prop('checked', 'checked');
                    } else {
                        $('#data-labels').prop('checked', '');
                    }
                    $('#zoom-type').val(editing_chart_data.apexcharts_render_data.options.chart.zoom.type);
                    $('#start-angle').val(editing_chart_data.apexcharts_render_data.options.plotOptions.radialBar.startAngle);
                    $('#end-angle').val(editing_chart_data.apexcharts_render_data.options.plotOptions.radialBar.endAngle);
                    if (['apexcharts_pie_with_gradient_chart', 'apexcharts_pie_chart', 'apexcharts_donut_with_gradient_chart', 'apexcharts_donut_chart'].includes(editing_chart_data.apexcharts_render_data.type)) {
                        if (editing_chart_data.apexcharts_render_data.options.theme.monochrome.enabled) {
                            $('#monochrome').prop('checked', 'checked');
                            $('#monochrome-color-container').show();
                            $('#enable-monochrome').show();
                            $('#monochrome-color').val(editing_chart_data.apexcharts_render_data.options.theme.monochrome.color);
                            $('#color-palette-row').removeClass('apexcharts-pie apexcharts');
                            $('#color-palette-row').hide();
                        } else {
                            $('#monochrome').prop('checked', '');
                        }
                        if (!editing_chart_data.apexcharts_render_data.options.theme.monochrome.enabled && editing_chart_data.apexcharts_render_data.options.theme.palette) {
                            $('#enable-color-palette').prop('checked', 'checked');
                            $('#color-palette-row').show();
                            $('#color-palette-container').show();
                            $('#color-palette').val(editing_chart_data.apexcharts_render_data.options.theme.palette).change();
                            $('#enable-monochrome').removeClass('apexcharts-pie apexcharts');
                            $('#enable-monochrome').hide();
                        } else {
                            $('#enable-color-palette').prop('checked', '');
                        }
                    } else if (!['apexcharts_radialbar_chart', 'apexcharts_radialbar_gauge_chart', 'apexcharts_radar_chart'].includes(editing_chart_data.apexcharts_render_data.type)) {
                        //Axes
                        if (editing_chart_data.apexcharts_render_data.options.grid.borderColor) {
                            $('#grid-color').val(editing_chart_data.apexcharts_render_data.options.grid.borderColor);
                        }
                        $('#grid-stroke').val(editing_chart_data.apexcharts_render_data.options.grid.strokeDashArray);
                        $('#grid-position').val(editing_chart_data.apexcharts_render_data.options.grid.position);
                        if (editing_chart_data.apexcharts_render_data.options.grid.xaxis.lines.show) {
                            $("#grid-axes  option[value='xaxis']").attr('selected', 'selected');
                        } else {
                            $("#grid-axes  option[value='xaxis']").removeAttr('selected');
                        }
                        if (editing_chart_data.apexcharts_render_data.options.grid.yaxis.lines.show) {
                            $("#grid-axes  option[value='yaxis']").attr('selected', 'selected');
                        } else {
                            $("#grid-axes  option[value='yaxis']").removeAttr('selected');
                        }
                        if (editing_chart_data.apexcharts_render_data.options.xaxis.crosshairs.show) {
                            $('#horizontal-axis-crosshair').prop('checked', 'checked');
                        } else {
                            $('#horizontal-axis-crosshair').prop('checked', '');
                        }
                        $('#marker-size').val(editing_chart_data.apexcharts_render_data.options.markers.size);
                        $('#stroke-width').val(editing_chart_data.apexcharts_render_data.options.stroke.width);
                        if (!Array.isArray(editing_chart_data.apexcharts_render_data.options.yaxis)) {
                            $('#vertical-axis-min').val(editing_chart_data.apexcharts_render_data.options.yaxis.min);
                            $('#vertical-axis-max').val(editing_chart_data.apexcharts_render_data.options.yaxis.max);
                            $('#tick-amount').val(editing_chart_data.apexcharts_render_data.options.yaxis.tickAmount);
                            if (editing_chart_data.apexcharts_render_data.options.yaxis.reversed) {
                                $('#reversed').prop('checked', 'checked');
                            } else {
                                $('#reversed').prop('checked', '');
                            }
                        } else if (editing_chart_data.apexcharts_render_data.options.yaxis[0]) {
                            if (editing_chart_data.apexcharts_render_data.options.yaxis[0].crosshairs.show) {
                                $('#vertical-axis-crosshair').prop('checked', 'checked');
                            } else {
                                $('#vertical-axis-crosshair').prop('checked', '');
                            }
                            $('#vertical-axis-min').val(editing_chart_data.apexcharts_render_data.options.yaxis[0].min);
                            $('#vertical-axis-max').val(editing_chart_data.apexcharts_render_data.options.yaxis[0].max);
                            $('#tick-amount').val(editing_chart_data.apexcharts_render_data.options.yaxis[0].tickAmount);
                            if (editing_chart_data.apexcharts_render_data.options.yaxis[0].reversed) {
                                $('#reversed').prop('checked', 'checked');
                            } else {
                                $('#reversed').prop('checked', '');
                            }
                        }
                    } else if (editing_chart_data.apexcharts_render_data.type === 'apexcharts_radar_chart') {
                        $('#marker-size').val(editing_chart_data.apexcharts_render_data.options.markers.size);
                    }
                    if (editing_chart_data.apexcharts_render_data.options.chart.background && isColorValid(editing_chart_data.apexcharts_render_data.options.chart.background)) {
                        $('#background-color').val(editing_chart_data.apexcharts_render_data.options.chart.background);
                        $('#plot-background-image-container').removeClass('apexcharts');
                    } else if (editing_chart_data.apexcharts_render_data.options.chart.background) {
                        $('#plot-background-image')
                            .val(editing_chart_data.apexcharts_render_data.options.chart.background
                                .replace('url(', '').replace(') no-repeat center/cover', ''));
                        $('#wdt-plot-image-clear-button').html("Clear");
                        $('#background-color-container').hide();
                    }
                    if (editing_chart_data.apexcharts_render_data.options.chart.foreColor) {
                        $('#chart-text-color').val(editing_chart_data.apexcharts_render_data.options.chart.foreColor);
                    }
                    if (editing_chart_data.apexcharts_render_data.options.fill.type === 'image' && typeof editing_chart_data.apexcharts_render_data.options.fill.image.src === 'string') {
                        $('#line-background-image').val(editing_chart_data.apexcharts_render_data.options.fill.image.src);
                        $('#wdt-line-image-clear-button').html("Clear");
                    }
                    if (editing_chart_data.apexcharts_render_data.options.chart.dropShadow.enabled) {
                        $('#enable-dropshadow').prop('checked', 'checked');
                        $('div.dropshadow').show();
                        $('#dropshadow-blur').val(editing_chart_data.apexcharts_render_data.options.chart.dropShadow.blur);
                        $('#dropshadow-opacity').val(editing_chart_data.apexcharts_render_data.options.chart.dropShadow.opacity * 100);
                        $('#dropshadow-color').val(editing_chart_data.apexcharts_render_data.options.chart.dropShadow.color);
                        $('#dropshadow-top').val(editing_chart_data.apexcharts_render_data.options.chart.dropShadow.top);
                        $('#dropshadow-left').val(editing_chart_data.apexcharts_render_data.options.chart.dropShadow.left);
                    } else {
                        $('#enable-dropshadow').prop('checked', '');
                    }

                    //Series
                    for (const i in editing_chart_data.apexcharts_render_data.options.series) {
                        if (editing_chart_data.apexcharts_render_data.options.series[i].type === 'bar' || editing_chart_data.apexcharts_render_data.options.series[i].type === 'area') {
                            $('#series-image-' + i + '-container').show();

                        } else {
                            $('#series-image-' + i + '-container').hide();
                        }
                    }

                    // Title
                    if (editing_chart_data.apexcharts_render_data.options.title.floating) {
                        $('#title-floating').prop('checked', 'checked');
                    } else {
                        $('#title-floating').prop('checked', '');
                    }
                    $('#title-align').val(editing_chart_data.apexcharts_render_data.options.title.align);
                    $('#subtitle').val(editing_chart_data.apexcharts_render_data.options.subtitle.text);
                    $('#subtitle-align').val(editing_chart_data.apexcharts_render_data.options.subtitle.align);

                    // Tooltip
                    if (editing_chart_data.apexcharts_render_data.options.tooltip.enabled) {
                        $('#tooltip-enabled').prop('checked', 'checked');
                    } else {
                        $('#tooltip-enabled').prop('checked', '');
                    }
                    if (editing_chart_data.apexcharts_render_data.options.tooltip.followCursor) {
                        $('#follow-cursor').prop('checked', 'checked');
                    } else {
                        $('#follow-cursor').prop('checked', '');
                    }
                    if (editing_chart_data.apexcharts_render_data.options.tooltip.fillSeriesColor) {
                        $('#fill-series-color').prop('checked', 'checked');
                    } else {
                        $('#fill-series-color').prop('checked', '');
                    }

                    // Legend
                    if (editing_chart_data.apexcharts_render_data.options.legend.show) {
                        $('#show-legend').prop('checked', 'checked');
                    } else {
                        $('#show-legend').prop('checked', '');
                    }
                    $('select[name=legend-position-cjs]').val(editing_chart_data.apexcharts_render_data.options.legend.position);
                    $('.selectpicker').selectpicker('refresh');

                    // Toolbar
                    if (editing_chart_data.apexcharts_render_data.options.chart.toolbar.show) {
                        $('#show-toolbar').prop('checked', 'checked');
                    } else {
                        $('#show-toolbar').prop('checked', '');
                    }
                    for (const tool in editing_chart_data.apexcharts_render_data.options.chart.toolbar.tools) {
                        if (tool !== 'customIcons') {
                            if (editing_chart_data.apexcharts_render_data.options.chart.toolbar.tools[tool]) {
                                $("#toolbar-buttons  option[value='" + tool + "']").attr('selected', 'selected');
                            } else {
                                $("#toolbar-buttons  option[value='" + tool + "']").removeAttr('selected');
                            }
                        }
                    }
                    $('#apex-exporting-file-name').val(editing_chart_data.apexcharts_render_data.options.chart.toolbar.export.png.filename);
                }

            } else if (editing_chart_data.engine == 'highstock') {

                if (editing_chart_data.highstock_render_data == null) {
                    // Chart
                    $('input.background-color').val('');
                    $('#border-width').val(0);
                    $('input.border_color').val('');
                    $('#border-radius').val(0);
                    $('#zoom-type').val('none');
                    $('#panning').prop('checked', '');
                    $('#pan-key').val('shift');
                    $('input.plot-background-color').val('');
                    $('#plot-background-image').val('');
                    $('#plot-border-width').val(0);
                    $('input.plot-border-color').val('');


                    // Axes
                    $('#highcharts-line-dash-style').val('solid');
                    $('#horizontal-axis-crosshair').prop('checked', '');
                    $('#vertical-axis-crosshair').prop('checked', '');
                    $('#vertical-axis-min').val('');
                    $('#vertical-axis-max').val('');
                    $('#inverted').prop('checked', '');


                    // Title
                    $('#title-floating').prop('checked', '');
                    $('#title-align').val('center');
                    $('#subtitle').val('');
                    $('#subtitle-align').val('center');


                    // Tooltip
                    $('#tooltip-enabled').prop('checked', 'checked');
                    $('input.tooltip-background-color').val('');
                    $('#tooltip-border-width').val(1);
                    $('input.tooltip-border-color').val('');
                    $('#tooltip-border-radius').val(3);
                    $('#tooltip-shared').prop('checked', '');
                    $('#tooltip-value-prefix').val('');
                    $('#tooltip-value-suffix').val('');


                    // Legend
                    $('#show-legend').prop('checked', 'checked');
                    $('input.legend_background_color').val('');
                    $('#legend_title').val('');
                    $('#legend_layout').val('horizontal');
                    $('#legend_align').val('center');
                    $('#legend_vertical_align').val('bottom');
                    $('#legend_border_width').val(0);
                    $('input.legend_border_color').val('');
                    $('#legend_border_radius').val(0);


                    // Exporting
                    $('#exporting').prop('checked', 'checked');
                    $('#exporting-data-labels').prop('checked', '');
                    $('#exporting-file-name').val('');
                    $('#exporting-width').val('');
                    $('#exporting-button-align').val('right');
                    $('#exporting-button-vertical-align').val('top');
                    $('#exporting-button-color').val('');
                    $('#exporting-button-text').val('');


                    // Credits
                    $('#credits').prop('checked', 'checked');
                    $('#credits-href').val('https://www.highcharts.com');
                    $('#credits-text').val('Highcharts.com');


                } else {
                    // Chart
                    if (editing_chart_data.highstock_render_data.options.chart.backgroundColor) {
                        $('#background-color').val(editing_chart_data.highstock_render_data.options.chart.backgroundColor);
                    }
                    $('#border-width').val(editing_chart_data.highstock_render_data.options.chart.borderWidth);
                    if (editing_chart_data.highstock_render_data.options.chart.borderColor) {
                        $('#border_color').val(editing_chart_data.highstock_render_data.options.chart.borderColor);
                    }
                    $('#border-radius').val(editing_chart_data.highstock_render_data.options.chart.borderRadius);
                    $("#zoom-type").append('<option value="y">Y</option>');
                    $("#zoom-type").append('<option value="xy">XY</option>');
                    $('#zoom-type').val(editing_chart_data.highstock_render_data.options.chart.zoomType);
                    if (editing_chart_data.highstock_render_data.options.chart.panning) {
                        $('#panning').prop('checked', 'checked');
                    } else {
                        $('#panning').prop('checked', '');
                    }
                    $('#pan-key').val(editing_chart_data.highstock_render_data.options.chart.panKey);
                    if (editing_chart_data.highstock_render_data.options.chart.plotBackgroundColor) {
                        $('#plot-background-color').val(editing_chart_data.highstock_render_data.options.chart.plotBackgroundColor);
                    }
                    $('#plot-background-image').val(editing_chart_data.highstock_render_data.options.chart.plotBackgroundImage);
                    if (editing_chart_data.highstock_render_data.options.chart.plotBackgroundImage) {
                        $('#wdt-plot-image-clear-button').html("Clear");
                    } else {
                        $('#wdt-line-image-clear-button').html('<span class="wpdt-icon-image"></span>');
                    }

                    $('#plot-border-width').val(editing_chart_data.highstock_render_data.options.chart.plotBorderWidth);
                    if (editing_chart_data.highstock_render_data.options.chart.plotBorderColor) {
                        $('#plot-border-color').val(editing_chart_data.highstock_render_data.options.chart.plotBorderColor);
                    }
                    if (editing_chart_data.highstock_render_data.options.credits.enabled) {
                        $('#credits').prop('checked', 'checked');
                    } else {
                        $('#credits').prop('checked', '');
                    }

                    // Axes
                    if (Array.isArray(editing_chart_data.highstock_render_data.options.yAxis)) {
                        $('#highcharts-line-dash-style').val(editing_chart_data.highstock_render_data.options.yAxis[0].gridLineDashStyle);
                    } else {
                        $('#highcharts-line-dash-style').val(editing_chart_data.highstock_render_data.options.yAxis.gridLineDashStyle);
                    }
                    if (editing_chart_data.highstock_render_data.options.xAxis.crosshair) {
                        $('#horizontal-axis-crosshair').prop('checked', 'checked');
                    } else {
                        $('#horizontal-axis-crosshair').prop('checked', '');
                    }
                    if (Array.isArray(editing_chart_data.highstock_render_data.options.yAxis)) {
                        if (editing_chart_data.highstock_render_data.options.yAxis[0].crosshair) {
                            $('#vertical-axis-crosshair').prop('checked', 'checked');
                        } else {
                            $('#vertical-axis-crosshair').prop('checked', '');
                        }
                        $('#vertical-axis-min').val(editing_chart_data.highstock_render_data.options.yAxis[0].min);
                        $('#vertical-axis-max').val(editing_chart_data.highstock_render_data.options.yAxis[0].max);
                    } else {
                        if (editing_chart_data.highstock_render_data.options.yAxis.crosshair) {
                            $('#vertical-axis-crosshair').prop('checked', 'checked');
                        } else {
                            $('#vertical-axis-crosshair').prop('checked', '');
                        }
                        $('#vertical-axis-min').val(editing_chart_data.highstock_render_data.options.yAxis.min);
                        $('#vertical-axis-max').val(editing_chart_data.highstock_render_data.options.yAxis.max);
                    }
                    if (editing_chart_data.highstock_render_data.options.chart.inverted) {
                        $('#inverted').prop('checked', 'checked');
                    } else {
                        $('#inverted').prop('checked', '');
                    }
                    // Title
                    if (editing_chart_data.highstock_render_data.options.title.floating) {
                        $('#title-floating').prop('checked', 'checked');
                    } else {
                        $('#title-floating').prop('checked', '');
                    }
                    $('#title-align').val(editing_chart_data.highstock_render_data.options.title.align);
                    $('#subtitle').val(editing_chart_data.highstock_render_data.options.subtitle.text);
                    $('#subtitle-align').val(editing_chart_data.highstock_render_data.options.subtitle.align);

                    // Tooltip
                    if (editing_chart_data.highstock_render_data.options.tooltip.enabled) {
                        $('#tooltip-enabled').prop('checked', 'checked');
                    } else {
                        $('#tooltip-enabled').prop('checked', '');
                    }
                    if (editing_chart_data.highstock_render_data.options.tooltip.backgroundColor) {
                        $('#tooltip-background-color').val(editing_chart_data.highstock_render_data.options.tooltip.backgroundColor);
                    }
                    $('#tooltip-border-width').val(editing_chart_data.highstock_render_data.options.tooltip.borderWidth);
                    if (editing_chart_data.highstock_render_data.options.tooltip.borderColor) {
                        $('#tooltip-border-color').val(editing_chart_data.highstock_render_data.options.tooltip.borderColor);
                    }
                    $('#tooltip-border-radius').val(editing_chart_data.highstock_render_data.options.tooltip.borderRadius);
                    if (editing_chart_data.highstock_render_data.options.tooltip.shared) {
                        $('#tooltip-shared').prop('checked', 'checked');
                    } else {
                        $('#tooltip-shared').prop('checked', '');
                    }
                    $('#tooltip-value-prefix').val(editing_chart_data.highstock_render_data.options.tooltip.valuePrefix);
                    $('#tooltip-value-suffix').val(editing_chart_data.highstock_render_data.options.tooltip.valueSuffix);


                    // Legend
                    if (editing_chart_data.highstock_render_data.options.legend.enabled) {
                        $('#show-legend').prop('checked', 'checked');
                    } else {
                        $('#show-legend').prop('checked', '');
                    }
                    $('input.legend_background_color').val(editing_chart_data.highstock_render_data.options.legend.backgroundColor);
                    $('#legend_title').val(editing_chart_data.highstock_render_data.options.legend.title.text);
                    $('#legend_layout').val(editing_chart_data.highstock_render_data.options.legend.layout);
                    $('#legend_align').val(editing_chart_data.highstock_render_data.options.legend.align);
                    $('#legend_vertical_align').val(editing_chart_data.highstock_render_data.options.legend.verticalAlign);
                    $('#legend_border_width').val(editing_chart_data.highstock_render_data.options.legend.borderWidth);
                    $('input.legend_border_color').val(editing_chart_data.highstock_render_data.options.legend.borderColor);
                    $('#legend_border_radius').val(editing_chart_data.highstock_render_data.options.legend.borderRadius);


                    // Exporting
                    if (editing_chart_data.highstock_render_data.options.exporting.enabled) {
                        $('#exporting').prop('checked', 'checked');
                    } else {
                        $('#exporting').prop('checked', '');
                    }
                    if (editing_chart_data.highstock_render_data.options.exporting.chartOptions.plotOptions.series.dataLabels.enabled) {
                        $('#exporting-data-labels').prop('checked', 'checked');
                    } else {
                        $('#exporting-data-labels').prop('checked', '');
                    }
                    $('#exporting-file-name').val(editing_chart_data.highstock_render_data.options.exporting.filename);
                    $('#exporting-width').val(editing_chart_data.highstock_render_data.options.exporting.width);
                    $('#exporting-button-align').val(editing_chart_data.highstock_render_data.options.exporting.buttons.contextButton.align);
                    $('#exporting-button-vertical-align').val(editing_chart_data.highstock_render_data.options.exporting.buttons.contextButton.verticalAlign);
                    $('#exporting-button-color').val(editing_chart_data.highstock_render_data.options.exporting.buttons.contextButton.symbolStroke);
                    $('#exporting-button-text').val(editing_chart_data.highstock_render_data.options.exporting.buttons.contextButton.text);


                    // Credits
                    if (editing_chart_data.highstock_render_data.options.credits.enabled) {
                        $('#credits').prop('checked', 'checked');
                    } else {
                        $('#credits').prop('checked', '');
                    }
                    $('#credits-href').val(editing_chart_data.highstock_render_data.options.credits.href);
                    $('#credits-text').val(editing_chart_data.highstock_render_data.options.credits.text);
                }
            }
        }
    });

    function applyDragula() {
        var drake = dragula([document.querySelector('.wdt-chart-wizart-existing-columns-container'), document.querySelector('.wdt-chart-wizard-chosen-columns-container')], {
            invalid: function (el, target) {
                if (el.classList.contains('alert')) {
                    return true;
                }
            }
        });
        drake.on('drop', function (el, container) {
            checkColumnsLimit();
            $(container).parent().find('button').removeClass('disabled').removeAttr('disabled');
            if ($(container).parent().find('div.chart-column-block').length === 0) {
                $(container).parent().find('button').addClass('disabled').attr('disabled', 'disabled');
            }
        });
        drake.on('drag', function (el, container) {
            checkColumnsLimit();
            $(container).parent().find('button').removeClass('disabled').removeAttr('disabled');
            if ($(container).parent().find('div.chart-column-block').length - 1 === 0) {
                $(container).parent().find('button').addClass('disabled').attr('disabled', 'disabled');
            }
        });

        drake.on('dragend', function (el, container) {
            checkColumnsLimit();
            $(el).parent().parent().find('button').removeClass('disabled').removeAttr('disabled');
            if ($(el).parent().parent().find('div.chart-column-block').length === 0) {
                $(el).parent().parent().find('button').addClass('disabled').attr('disabled', 'disabled');
            }
        });
    }
})(jQuery);


/**
 * Helper func to check if right mousebutton was clicked
 */
function isRightClick(e) {
    if (e.which) {
        return (e.which == 3);
    } else if (e.button) {
        return (e.button == 2);
    }
    return false;
}

/**
 * Helper func to convert hex to rgb color
 */
function hexToRgb(hex) {
    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
}

function isColorValid(color) {
    var e = document.getElementById('divValidColor');
    if (!e) {
        e = document.createElement('div');
        e.id = 'divValidColor';
    }
    e.style.borderColor = '';
    e.style.borderColor = color;
    var tmpcolor = e.style.borderColor;
    return tmpcolor.length != 0;
}

function getApexChartType(type) {
    var apexChartType = '';
    switch (type) {
        case 'apexcharts_spline_area_chart':
        case 'apexcharts_stepline_area_chart':
        case 'apexcharts_basic_area_chart':
            apexChartType = 'area';
            break;
        case 'apexcharts_column_chart':
        case 'apexcharts_grouped_bar_chart':
        case 'apexcharts_stacked_bar_chart':
        case 'apexcharts_100_stacked_bar_chart':
        case 'apexcharts_stacked_column_chart':
        case 'apexcharts_100_stacked_column_chart':
            apexChartType = 'bar';
            break;
        case 'apexcharts_straight_line_chart':
        case 'apexcharts_spline_chart':
        case 'apexcharts_stepline_chart':
        default:
            apexChartType = 'line';
            break;
    }
    return apexChartType;
}

function handleMediaUploader(e, id, data) {
    jQuery(function ($) {
        var imageInput = '';
        var key = id;
        var clearButton = '';
        var toggledContainer = '';

        switch (id.replace(/\d+/g, '')) {
            case 'wdt-plot-image-clear-button':
                imageInput = $('#plot-background-image');
                clearButton = $('#wdt-plot-image-clear-button');
                toggledContainer = $('#background-color-container');
                break;
            case 'wdt-line-image-clear-button':
                imageInput = $('#line-background-image');
                clearButton = $('#wdt-line-image-clear-button');
                break;
            case 'wdt-upload-chart-image-':
                key = parseInt(key.replace(/[^0-9]/g, ''));
                imageInput = $('#series-image-' + key);
                clearButton = $('#wdt-upload-chart-image-' + key);
                break;

        }

        if (imageInput.val() == '') {
            e.preventDefault();
            e.stopImmediatePropagation();
            e.preventDefault();

            var image = wp.media.frames.items = (wp.media({
                title: 'Choose image',
                button: {
                    text: 'Select'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            })).open()
                .on('select', function (e) {
                    var uploaded_image = image.state().get('selection').first();
                    var image_url = uploaded_image.toJSON().url;
                    imageInput.val(image_url).change();
                });
        } else {
            imageInput.val('').change();
            clearButton.html('<span class="wpdt-icon-image"></span>');
            if (id.replace(/\d+/g, '') === 'wdt-upload-chart-image-') {
                toggleBackgroundImageContainer();
            } else if (id === 'wdt-line-image-clear-button') {
                for (var i in data.options.series) {
                    if (data.options.series[i].type === 'bar' || data.options.series[i].type === 'area') {
                        $('#series-image-' + i + '-container').show();
                    }
                }
            } else {
                toggledContainer.show();
            }
        }
    });
}

function toggleBackgroundImageContainer() {
    jQuery(function ($) {
        var seriesImageLen = $('.chart-series-image :input').filter(function () {
            return this.value !== ""
        });

        if (seriesImageLen.length > 0) {
            $('#line-background-image-container').hide();
        } else {
            $('#line-background-image-container').show();
        }
    });
}

function switchClearButton(image, button) {
    jQuery(function ($) {
        if (image == '') {
            button.html('<span class="wpdt-icon-image"></span>');
        } else {
            button.html("Clear");
        }
    });
}