(function ($) {
    $(function () {

        // Function to handle the table ID change event and hide the table view dropdown for Woo tables
        function handleTableChange(selectedTableId) {
            if (selectedTableId) {
                $.ajax({
                    url: wdt_ajax_object.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'get_table_type_by_id',
                        table_id: selectedTableId
                    },
                    success: function (response) {
                        var tableType = JSON.parse(response).tableType;

                        if (tableType === 'woo_commerce') {
                            $('span.et-fb-form__label:contains("Choose table view")')
                                .closest('.et-fb-form__group')
                                .hide();
                        } else {
                            $('span.et-fb-form__label:contains("Choose table view")')
                                .closest('.et-fb-form__group')
                                .show();
                        }
                    }
                });
            }
        }

        $(document).on('click', '.select-option-item', function () {
            let selectedTableId = $(this).data('value');
            handleTableChange(selectedTableId);
        });

        let initialTableId = $('#et-fb-id .et-fb-selected-item').data('value');
        handleTableChange(initialTableId);
    })
})(jQuery);