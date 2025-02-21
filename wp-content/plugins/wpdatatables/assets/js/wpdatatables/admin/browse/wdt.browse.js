var duplicate_table_id = '';

(function ($) {
    $(document).ready(function () {

        /**
         * Delete item action alert
         */
        $(document).on('click', '.wdt-submit-delete', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            $('#wdt-delete-modal').modal('show');

            var href = $(this).attr('href');

            $('#wdt-browse-delete-button').click(function () {
                window.location = href;
            });
        });

        /**
         * Search tables and charts in backend
         */
        $(document).on("keyup input", "input#search_id-search-input", _.debounce(function () {
                $("button#search-submit").click();
            }, 800)
        );

        /**
         * Bulk action alert
         */
        $(document).on('click', '#doaction, #doaction2', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            var select_box = $(this).siblings('div.wpdt-bulk-select').find('select.wpdt-bulk-select').val();

            if (select_box == -1) {
                return;
            }

            if ($('#wdt-datatables-browse-table table.widefat input[type="checkbox"]:checked').length == 0) {
                return;
            }

            $('#wdt-delete-modal').modal('show');

            $('#wdt-browse-delete-button').click(function () {
                $('#wdt-datatables-browse-table').submit();
            });
        });

        /**
         * Display a duplicate table modal
         */
        $(document).on('click', '.wdt-duplicate-table', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            duplicate_table_id = $(this).data('table_id');
            if ($(this).data('table_type') === 'manual') {
                $('.wdt-duplicate-manual-table').show();
            } else {
                $('.wdt-duplicate-manual-table').hide();
            }

            $('input.wdt-duplicate-table-name').val($(this).data('table_name') + '_' + wpdatatables_browse_strings.copyBrowser.toLowerCase());

            $('#wdt-duplicate-table-modal').modal('show');
        });

        /**
         * A duplicate table action
         */
        $(document).on('click', 'button.duplicate-table-button', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            $('#wdt-preload-layer').show();
            var new_table_name = $(this).closest('.modal-content').find('input.wdt-duplicate-table-name').val();
            var manual_duplicate_input = ($('input[name=wdt-duplicate-database]').is(':checked')) ? 1 : 0;
            var wdtNonce = $('#wdt-duplicate-table-modal #wdtNonce').val();

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpdatatables_duplicate_table',
                    table_id: duplicate_table_id,
                    new_table_name: new_table_name,
                    manual_duplicate_input: manual_duplicate_input,
                    wdtNonce: wdtNonce
                },
                success: function () {
                    window.location.reload();
                }
            });

            $('#wdt-duplicate-table-modal').modal('hide');

        });

        /**
         * Display a duplicate chart modal
         */
        $(document).on('click', '.wdt-duplicate-chart', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            duplicate_chart_id = $(this).data('chart_id');

            $('input.wdt-duplicate-chart-name').val($(this).data('chart_name') + '_' + wpdatatables_browse_strings.copyBrowser.toLowerCase());

            $('#wdt-duplicate-chart-modal').modal('show');
        });

        /**
         * A duplicate chart action
         */
        $(document).on('click', 'button.duplicate-chart-button', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            $('#wdt-preload-layer').show();
            var new_chart_name = $(this).closest('.modal-content').find('input.wdt-duplicate-chart-name').val();
            var wdtNonce = $('#wdt-duplicate-chart-modal #wdtNonce').val();

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpdatatables_duplicate_chart',
                    chart_id: duplicate_chart_id,
                    new_chart_name: new_chart_name,
                    wdtNonce: wdtNonce
                },
                success: function () {
                    window.location.reload();
                }
            });

            $('#wdt-duplicate-table-modal').modal('hide');

        });

        /**
         * Highlight a row when checkbox is active
         */
        $(document).on('click', '.wdt-datatables-admin-wrap .card.wdt-browse-table table tbody :checkbox', function () {
            var parent_tr = $(this).closest('tr');
            var btnDelete = $('#doaction');
            if ($(this).is(':checked')) {
                parent_tr.addClass('checked-row');
            } else {
                parent_tr.removeClass('checked-row');
            }
            var numOfCheckedTR = $('#wdt-datatables-browse-table table tr.checked-row');
            if (numOfCheckedTR.length) {
                btnDelete.removeClass('disabled').html('<i class="wpdt-icon-trash-reg"></i>' + wpdatatables_browse_strings.deleteSelectedBrowser);
            } else {
                btnDelete.addClass('disabled').html('<i class="wpdt-icon-trash-reg"></i>' + wpdatatables_browse_strings.deleteBrowser);
            }
        });

        /**
         * Highlight all rows when the select all checkbox is active
         */
        $(document).on('click', '.wdt-datatables-admin-wrap .card.wdt-browse-table table thead :checkbox, .wdt-datatables-admin-wrap .card.wdt-browse-table table tfoot :checkbox', function () {
            var all_tr = $(this).closest('table').find('tbody tr');
            var btnDelete = $('#doaction');

            if ($(this).is(':checked')) {
                all_tr.addClass('checked-row');
                btnDelete.removeClass('disabled').html('<i class="wpdt-icon-trash-reg"></i>' + wpdatatables_browse_strings.deleteSelectedBrowser);
            } else {
                all_tr.removeClass('checked-row');
                btnDelete.addClass('disabled').html('<i class="wpdt-icon-trash-reg"></i>' + wpdatatables_browse_strings.deleteBrowser);
            }
        });

        /**
         * Hide folders upgrade notice
         */
        $(document).on('click', '.wdt-dismiss-folders-btn', function (e) {
            e.preventDefault();
            $.ajax({
                url: ajaxurl,
                method: "POST",
                data: {
                    'action': 'wdtDismissFoldersNotice'
                },
                dataType: "json",
                success: function (e) {
                    if (e == "success") {
                        $('.wdt-folders-upgrade-notice').slideUp('fast');
                    }
                }
            });
        })
    });
})(jQuery);
