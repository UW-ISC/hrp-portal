(function ($) {
	"use strict";

	$(document).on("click", ".notice-dismiss[data-megamenu-dismiss]", function (e) {
		e.preventDefault();
		var $btn = $(this);
		var notice = $btn.attr("data-megamenu-dismiss");
		var nonce = $btn.attr("data-megamenu-dismiss-nonce");
		var $notice = $btn.closest(".notice");

		if (!notice || !nonce || !window.megamenuDismissNotices || !window.megamenuDismissNotices.ajaxUrl) {
			return;
		}

		$btn.prop("disabled", true);

		$.post(window.megamenuDismissNotices.ajaxUrl, {
			action: "megamenu_dismiss_admin_notice",
			nonce: nonce,
			notice: notice,
		})
			.done(function (response) {
				if (response && response.success) {
					$notice.fadeTo(100, 0, function () {
						$(this).slideUp(100, function () {
							$(this).remove();
						});
					});
				} else {
					$btn.prop("disabled", false);
				}
			})
			.fail(function () {
				$btn.prop("disabled", false);
			});
	});
})(jQuery);
