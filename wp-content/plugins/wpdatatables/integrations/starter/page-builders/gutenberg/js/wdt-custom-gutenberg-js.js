(function ($) {
    $(document).on('change', 'select#wpdatatables-js-select-table', function (e) {
        e.stopImmediatePropagation();
        let selectedTableID = $(this).val();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_table_type_by_id',
                table_id: selectedTableID
            },
            success: function (response) {
                var tableType = JSON.parse(response).tableType;

                // Conditionally hide or show the Table view dropdown
                if (tableType === 'woo_commerce') {
                    $('select#wpdatatables-js-select-view').closest('.components-base-control').hide();
                } else {
                    $('select#wpdatatables-js-select-view').closest('.components-base-control').show();
                }
            }
        });
    });
})(jQuery);
