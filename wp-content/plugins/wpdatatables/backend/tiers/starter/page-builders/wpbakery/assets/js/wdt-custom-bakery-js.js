(function ($) {
    $(document).on('change', 'select.wpb_vc_param_value.id', function () {
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
                        $('div[data-vc-shortcode-param-name="table_view"]').hide();
                    } else {
                        $('div[data-vc-shortcode-param-name="table_view"]').hide();
                    }
                }
            });
        }
    });
})(jQuery);
