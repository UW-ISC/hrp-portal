(function ($) {
    $(function () {

        /**
         * Extend wpdatatable_config object with new properties and methods
         */
        $.extend(wpdatatable_config, {
            index_column: 0,
            setIndexColumn: function (indexcolumn) {
                wpdatatable_config.index_column = indexcolumn;
                jQuery('#wdt-index-column').prop('checked', indexcolumn);
                let state = false;
                let indexColumntable;
                for (let column of wpdatatable_config.columns) {
                    if (column.orig_header === 'wdt_indexcolumn') {
                        state = true;
                        indexColumntable = column;
                    }
                }
                if (indexcolumn === 1 && !state) {
                    wpdatatable_config.addColumn(
                        new WDTColumn(
                            {
                                type: 'index',
                                orig_header: 'wdt_indexcolumn',
                                display_header: 'Index',
                                pos: wpdatatable_config.columns.length,
                                parent_table: wpdatatable_config
                            }
                        )
                    );
                } else if (state && indexcolumn == 0) {
                    for (var i = indexColumntable.pos + 1; i <= wpdatatable_config.columns.length - 1; i++) {
                        wpdatatable_config.columns[i].pos = --wpdatatable_config.columns[i].pos;
                    }
                    //remove indexcolumn object from columns_by_headers
                    wpdatatable_config.columns_by_headers = _.omit(
                        wpdatatable_config.columns_by_headers, indexColumntable.orig_header);

                    //remove indexcolumn column from columns
                    wpdatatable_config.columns = _.reject(
                        wpdatatable_config.columns,
                        function (el) {
                            return el.orig_header == indexColumntable.orig_header;
                        });
                }
            },

        });

        /**
         * Load the table for editing
         */
        if (typeof wpdatatable_init_config !== 'undefined' && wpdatatable_init_config.advanced_settings !== '') {

            var advancedSettings = JSON.parse(wpdatatable_init_config.advanced_settings);

            if (advancedSettings !== null) {
                var index_column = advancedSettings.index_column;

                if (typeof index_column !== 'undefined') {
                    wpdatatable_config.setIndexColumn(index_column);
                }

            }

        }
        $('#wdt-index-column').change(function (e) {
            wpdatatable_config.setIndexColumn($(this).is(':checked') ? 1 : 0);
        });

    });

})(jQuery);




