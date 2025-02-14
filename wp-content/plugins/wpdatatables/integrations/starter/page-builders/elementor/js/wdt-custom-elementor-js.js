(function ($) {
    if (typeof elementor !== 'undefined' && elementor.hasOwnProperty('hooks')) {
        elementor.hooks.addAction('panel/open_editor/widget', function (panel, model, view) {
            // Listen for changes on the wpdt-table-id select dropdown
            panel.$el.on('change', 'select[data-setting="wpdt-table-id"]', function () {
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

                            // Conditionally hide or show the "Table view" dropdown
                            if (tableType === 'woo_commerce') {
                                panel.$el.find('.elementor-control-wpdt-view').hide();
                            } else {
                                panel.$el.find('.elementor-control-wpdt-view').show();
                            }
                        }
                    });
                }
            });
        });
    }
})(jQuery);