(function ($) {
    $(function () {
        $('.wdt-dismiss').on("click", function (e) {
            e.preventDefault();
            $.ajax({
                url: ajaxurl,
                method: "POST",
                data: {
                    'action': 'wdtTempHideRating'
                },
                dataType: "json",
                async: !0,
                success: function (e) {
                    if (e == "success") {
                        $('.wdt-rating-notice').fadeTo(100, 0, function () {
                            $('.wdt-rating-notice').slideUp(100, function () {
                                this.remove();
                            });
                        });
                    }
                }

            });
        });

        $(document).on('click', '.wdt-hide-rating', function (e) {
            e.preventDefault();
            $.ajax({
                url: ajaxurl,
                method: "POST",
                data: {
                    'action': 'wdtHideRating'
                },
                dataType: "json",
                async: !0,
                success: function (e) {
                    if (e == "success") {
                        $('.wdt-rating-notice').slideUp('fast');
                    }
                }
            });
        })

        $('.wpdt-md-news-notice .notice-dismiss').on('click', function (e) {
            e.preventDefault();
            $.ajax({
                url: ajaxurl,
                method: "POST",
                data: {
                    'action': 'wpdtHideMDNewsDiv'
                },
                dataType: "json",
                async: !0,
                success: function (e) {
                    if (e == "success") {
                        $('.wpdt-md-news-notice').slideUp('fast');
                    }
                }
            });
        })

        $('.wpdt-forminator-news-notice .notice-dismiss').on('click', function (e) {
            e.preventDefault();
            $.ajax({
                url: ajaxurl,
                method: "POST",
                data: {
                    'action': 'wdt_remove_forminator_notice'
                },
                dataType: "json",
                async: !0,
                success: function (e) {
                    if (e == "success") {
                        $('.wpdt-forminator-news-notice').slideUp('fast');
                    }
                }
            });
        })
        $('.wpdt-bundles-notice .wpdt-notice-dismiss').on('click', function (e) {
            e.preventDefault();
            $.ajax({
                url: ajaxurl,
                method: "POST",
                data: {
                    'action': 'wdt_remove_bundles_notice'
                },
                dataType: "json",
                async: !0,
                success: function (e) {
                    if (e == "success") {
                        $('.wpdt-bundles-notice').slideUp('fast');
                    }
                }
            });
        })

        $('.wpdt-promote-amelia-notice .notice-dismiss').on('click', function (e) {
            e.preventDefault();
            $.ajax({
                url: ajaxurl,
                method: "POST",
                data: {
                    'action': 'wdt_remove_promo_amelia_notice'
                },
                dataType: "json",
                async: !0,
                success: function (e) {
                    if (e == "success") {
                        $('.wpdt-promote-amelia-notice').slideUp('fast');
                    }
                }
            });
        })

        $('.wpdt-bootstrap-update-notice .notice-dismiss').on('click', function (e) {
            e.preventDefault();
            $.ajax({
                url: ajaxurl,
                method: "POST",
                data: {
                    'action': 'wdt_remove_bootstrap_update_notice'
                },
                dataType: "json",
                async: !0,
                success: function (e) {
                    if (e == "success") {
                        $('.wpdt-bootstrap-update-notice').slideUp('fast');
                    }
                }
            });
        })
    });
})(jQuery);