(function ($) {
    $(function () {
        $(document).on('click', '.wpdt-ivyforms-promo-close', function (e) {
            e.preventDefault();
            var nonce = $('#wpdt-ivyforms-promo-user-dismiss-nonce').val();
            if (!nonce) {
                return;
            }
            $('.wpdt-ivyforms-promo-notice').remove();
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'wdt_dismiss_ivyforms_promo_user',
                    nonce: nonce
                }
            });
        });

        $(document).on('click', '.wpdt-ivyforms-promo-never', function (e) {
            e.preventDefault();
            var nonce = $('#wpdt-ivyforms-promo-dismiss-nonce').val();
            if (!nonce) {
                return;
            }
            $('.wpdt-ivyforms-promo-notice').remove();
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'wdt_remove_ivyforms_promo_notice',
                    nonce: nonce
                }
            });
        });

        $(document).on('click', '#wpdt-ivyforms-install-btn', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var $status = $('#wpdt-ivyforms-install-status');
            var nonce = $('#wpdt-ivyforms-install-nonce').val();
            if (!nonce) {
                return;
            }
            var installFailedMsg = (typeof wdtIvyformsPromo !== 'undefined' && wdtIvyformsPromo.install_failed)
                ? wdtIvyformsPromo.install_failed
                : '';
            $btn.prop('disabled', true);
            $status.text('');
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'ivyforms_one_click_install',
                    nonce: nonce
                }
            }).done(function (res) {
                if (res && res.success) {
                    window.location.reload();
                } else {
                    $btn.prop('disabled', false);
                    var msg = '';
                    if (res && res.data) {
                        if (typeof res.data === 'string') {
                            msg = res.data;
                        } else if (res.data.message) {
                            msg = res.data.message;
                        }
                    }
                    $status.text(msg || installFailedMsg);
                }
            }).fail(function () {
                $btn.prop('disabled', false);
                $status.text(installFailedMsg);
            });
        });
    });
})(jQuery);
