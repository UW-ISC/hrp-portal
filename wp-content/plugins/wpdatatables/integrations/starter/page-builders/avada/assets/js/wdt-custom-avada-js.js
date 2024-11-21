(function ($) {
    $(document).on('fusion-builder-settings-render', function () {
        $('select[name="id"]').on('change', function () {
            let selectedTableID = $(this).val();

            if (selectedTableID) {
                $.ajax({
                    url: wdt_ajax_object.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'get_table_type_by_id',
                        table_id: selectedTableID
                    },
                    success: function (response) {
                        var tableType = JSON.parse(response).tableType;

                        if (tableType === 'woo_commerce') {
                            $('select[name="table_view"]').closest('.fusion-builder-option').hide();
                        } else {
                            $('select[name="table_view"]').closest('.fusion-builder-option').show();
                        }
                    }
                });
            }
        });
    });

    $(document).on('change', '.wpdatatable select[name="id"]', function () {
        let selectedTableID = $(this).val();

        if (selectedTableID) {
            $.ajax({
                url: wdt_ajax_object.ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_table_type_by_id',
                    table_id: selectedTableID
                },
                success: function (response) {
                    var tableType = JSON.parse(response).tableType;

                    if (tableType === 'woo_commerce') {
                        $('li[data-option-id="table_view"]').hide();
                    } else {
                        $('li[data-option-id="table_view"]').hide();
                    }
                }
            });
        }
    });
    $(document).on('change', 'ul[data-element="wpdatatable"] input#id', function () {
        let selectedTableID = $(this).val();

        if (selectedTableID) {
            $.ajax({
                url: wdt_ajax_object.ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_table_type_by_id',
                    table_id: selectedTableID
                },
                success: function (response) {
                    var tableType = JSON.parse(response).tableType;

                    if (tableType === 'woo_commerce') {
                        $('li[data-option-id="table_view"]').hide();
                    } else {
                        $('li[data-option-id="table_view"]').hide();
                    }
                }
            });
        }
    });
})(jQuery);