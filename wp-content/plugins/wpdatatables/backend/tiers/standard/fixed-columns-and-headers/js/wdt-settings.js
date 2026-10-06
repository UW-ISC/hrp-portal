(function ($) {
    $(function () {

        /**
         * Extend wpdatatable_config object with new properties and methods
         */
        $.extend(wpdatatable_config, {
            fixed_header: 0,
            fixed_header_offset: 0,
            fixed_columns: 0,
            fixed_left_columns_number: 0,
            fixed_right_columns_number: 0,
            setFixedHeader: function (fixedheader) {
                wpdatatable_config.fixed_header = fixedheader;
                jQuery('#wdt-fixed-header').prop('checked', fixedheader);

                if (fixedheader === 1) {
                    jQuery('.advanced-table-settings-block-fixed-header').removeClass('hidden');
                } else {
                    jQuery('.advanced-table-settings-block-fixed-header').addClass('hidden');
                    wpdatatable_config.setFixedHeaderOffset(0);
                }
            },
            setFixedHeaderOffset: function (fixheaderoffset) {
                wpdatatable_config.fixed_header_offset = fixheaderoffset;
                if (jQuery('#wdt-fixed-header-offset').val() != wpdatatable_config.fixed_header_offset) {
                    jQuery('#wdt-fixed-header-offset').val(wpdatatable_config.fixed_header_offset);
                }
            },
            setFixedColumns: function (fixedcolumn) {
                wpdatatable_config.fixed_columns = fixedcolumn;
                jQuery('#wdt-fixed-columns').prop('checked', fixedcolumn);

                if (fixedcolumn === 1) {
                    jQuery('.advanced-table-settings-block-fixed-columns').removeClass('hidden');
                    wpdatatable_config.setScrollable(1);
                    wpdatatable_config.setLimitLayout(0);
                    wpdatatable_config.setWordWrap(0);
                } else {
                    jQuery('.advanced-table-settings-block-fixed-columns').addClass('hidden');
                    wpdatatable_config.setLeftFixedColumnNumber(0);
                    wpdatatable_config.setRightFixedColumnNumber(0);
                }
            },
            setLeftFixedColumnNumber: function (fixed_left_columns_number) {
                if (jQuery('#wdt-fixed-columns-left-number').val() == '') jQuery('#wdt-fixed-columns-left-number').val(0);
                if (fixed_left_columns_number === 0 && wpdatatable_config.fixed_right_columns_number === 0 || fixed_left_columns_number < 0 && wpdatatable_config.fixed_right_columns_number === 0) fixed_left_columns_number = 1;
                if (fixed_left_columns_number < 0 && wpdatatable_config.fixed_right_columns_number !== 0) fixed_left_columns_number = 0;
                wpdatatable_config.fixed_left_columns_number = fixed_left_columns_number;
                if (jQuery('#wdt-fixed-columns-left-number').val() != wpdatatable_config.fixed_left_columns_number) {
                    jQuery('#wdt-fixed-columns-left-number').val(wpdatatable_config.fixed_left_columns_number);
                }
            },

            setRightFixedColumnNumber: function (fixed_right_columns_number) {
                if (jQuery('#wdt-fixed-columns-right-number').val() == '') jQuery('#wdt-fixed-columns-right-number').val(0);
                if (fixed_right_columns_number < 0) fixed_right_columns_number = 0;
                wpdatatable_config.fixed_right_columns_number = fixed_right_columns_number;
                if (fixed_right_columns_number === 0 && wpdatatable_config.fixed_left_columns_number === 0) wpdatatable_config.setLeftFixedColumnNumber(1);
                if (jQuery('#wdt-fixed-columns-right-number').val() != wpdatatable_config.fixed_right_columns_number) {
                    jQuery('#wdt-fixed-columns-right-number').val(wpdatatable_config.fixed_right_columns_number);
                }
            }

        });

        /**
         * Load the table for editing
         */
        if (typeof wpdatatable_init_config !== 'undefined' && wpdatatable_init_config.advanced_settings !== '') {

            var advancedSettings = JSON.parse(wpdatatable_init_config.advanced_settings);

            if (advancedSettings !== null) {

                var fixed_header = advancedSettings.fixed_header;
                var fixed_header_offset = advancedSettings.fixed_header_offset;
                var fixed_columns = advancedSettings.fixed_columns;
                var fixed_right_columns_number = advancedSettings.fixed_right_columns_number;
                var fixed_left_columns_number = advancedSettings.fixed_left_columns_number;


                if (typeof fixed_header !== 'undefined') {
                    wpdatatable_config.setFixedHeader(fixed_header);
                }

                if (typeof fixed_header_offset !== 'undefined') {
                    wpdatatable_config.setFixedHeaderOffset(fixed_header_offset);
                }

                if (typeof fixed_columns !== 'undefined') {
                    wpdatatable_config.setFixedColumns(fixed_columns);
                }

                if (typeof fixed_right_columns_number !== 'undefined') {
                    wpdatatable_config.setRightFixedColumnNumber(fixed_right_columns_number);
                }

                if (typeof fixed_left_columns_number !== 'undefined') {
                    wpdatatable_config.setLeftFixedColumnNumber(fixed_left_columns_number);
                }

            }

        }

        $('#wdt-fixed-header').change(function (e) {
            wpdatatable_config.setFixedHeader($(this).is(':checked') ? 1 : 0);
        });
        $('#wdt-fixed-header-offset').change(function (e) {
            wpdatatable_config.setFixedHeaderOffset($(this).val());
        });

        $('#wdt-fixed-columns').change(function (e) {
            wpdatatable_config.setFixedColumns($(this).is(':checked') ? 1 : 0);
        });
        $('#wdt-fixed-columns-left-number').change(function (e) {
            wpdatatable_config.setLeftFixedColumnNumber($(this).val());
        });
        $('#wdt-fixed-columns-right-number').change(function (e) {
            wpdatatable_config.setRightFixedColumnNumber($(this).val());
        });

    });

})(jQuery);




