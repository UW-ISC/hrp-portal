(function ($) {
    $(function () {

        /**
         * Save Google Spreadsheet API settings on Apply button
         */
        $(document).on('click', '#wdt-save-google-settings', function (e) {
            var credentials = $('#wdt-google-sheet-settings').val();

            if (credentials != '') {
                $('.wdt-preload-layer').animateFadeIn();
                saveGoogleAccountSettings(credentials);
            } else {
                $('#wdt-error-modal .modal-body').html("Google service account data can not be empty!");
                $('#wdt-error-modal').modal('show');
                $('.wdt-preload-layer').animateFadeOut();
                return false;
            }
        });

        /**
         * Delete Google settings
         */
        $(document).on('click', '#wdt-delete-google-settings', function (e) {
            $('.wdt-preload-layer').animateFadeIn();
            deleteGoogleAccountSettings();
        });

        function saveGoogleAccountSettings(credentials) {
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'wpdatatables_save_google_settings',
                    settings: credentials,
                    wdtNonce: $('#wdtNonce').val()
                },
                success: function (data) {
                    data = JSON.parse(data);
                    if (data.error) {
                        $('#wdt-error-modal .modal-body').html('There was an error while trying to save google settings!\n ' + data.error);
                        $('#wdt-error-modal').modal('show');
                        $('.wdt-preload-layer').animateFadeOut();
                    } else {
                        window.location = data.link;
                        window.location.reload();
                    }

                },
                error: function () {
                    $('#wdt-error-modal .modal-body').html('There was an error while trying to save google settings!');
                    $('#wdt-error-modal').modal('show');
                    $('.wdt-preload-layer').animateFadeOut();
                }
            });
        }

        function deleteGoogleAccountSettings() {
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'wpdatatables_delete_google_settings',
                    wdtNonce: $('#wdtNonce').val()
                },
                success: function (link) {
                    window.location = link;
                    window.location.reload();
                },
                error: function (data) {
                    $('#wdt-error-modal .modal-body').html('There was an error while trying to delete google settings! ' + data.statusText + ' ' + data.responseText);
                    $('#wdt-error-modal').modal('show');
                    $('.wdt-preload-layer').animateFadeOut();
                }
            });
        }
    });
})(jQuery);

