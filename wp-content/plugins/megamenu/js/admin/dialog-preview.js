/* global jQuery, window, MegamenuAdminModalExpand */
/**
 * Max Mega Menu — location preview modal (Menu Locations + Appearance > Menus).
 */
(function ($) {
    "use strict";

    function megamenuExpandApi() {
        return window.MegamenuAdminModalExpand;
    }

    function megamenuMountPreviewDialogFromTemplate() {
        if (document.getElementById("megamenu-preview-dialog")) {
            return;
        }
        var tpl = document.getElementById("megamenu-preview-dialog-template");
        if (!tpl || !tpl.textContent) {
            return;
        }
        var wrap = document.createElement("div");
        wrap.innerHTML = tpl.textContent.trim();
        var dlg = wrap.querySelector("#megamenu-preview-dialog");
        if (dlg) {
            document.body.appendChild(dlg);
        }
    }

    function megamenuSyncBodyDialogOpenClass() {
        var previewOpen = $("#megamenu-preview-dialog").hasClass("is-open");
        var locOpen = $("#megamenu-location-settings-dialog").hasClass("is-open");
        var scssOpen = $("#megamenu-scss-variables-dialog").hasClass("is-open");
        $("body").toggleClass("megamenu-dialog-open", previewOpen || locOpen || scssOpen);
    }

    function megamenuPreviewDialogHeading($dialog, locationLabel) {
        var tpl = ($dialog && $dialog.attr("data-i18n-location-preview-title-tpl")) || "";
        var name = locationLabel || "";
        if (tpl && tpl.indexOf("%s") !== -1) {
            return tpl.replace("%s", name);
        }
        return "Location Preview: " + name;
    }

    function megamenuResetPreviewViewport($dialog) {
        var $wrap = $dialog.find(".megamenu-preview-dialog__viewport-toggle");
        var $desktop = $dialog.find(".megamenu-preview-dialog__viewport-btn--desktop");
        var $mobile = $dialog.find(".megamenu-preview-dialog__viewport-btn--mobile");
        $dialog.removeClass("megamenu-preview-dialog--mobile-preview");
        $dialog.css("--megamenu-preview-mobile-width", "");
        $wrap.removeClass("megamenu-preview-dialog__viewport-toggle--mobile-disabled");
        $mobile
            .prop("disabled", false)
            .removeAttr("aria-disabled")
            .removeClass("megamenu-preview-dialog__viewport-btn--unavailable")
            .removeAttr("data-mega-tooltip")
            .removeAttr("data-mega-tooltip-position");
        $desktop.addClass("megamenu-preview-dialog__viewport-btn--active").attr("aria-pressed", "true");
        $mobile.removeClass("megamenu-preview-dialog__viewport-btn--active").attr("aria-pressed", "false");
    }

    function megamenuSetPreviewViewportDesktop($dialog) {
        var $desktop = $dialog.find(".megamenu-preview-dialog__viewport-btn--desktop");
        var $mobile = $dialog.find(".megamenu-preview-dialog__viewport-btn--mobile");
        $dialog.removeClass("megamenu-preview-dialog--mobile-preview");
        $desktop.addClass("megamenu-preview-dialog__viewport-btn--active").attr("aria-pressed", "true");
        $mobile.removeClass("megamenu-preview-dialog__viewport-btn--active").attr("aria-pressed", "false");
    }

    function megamenuSetPreviewViewportMobile($dialog) {
        var $desktop = $dialog.find(".megamenu-preview-dialog__viewport-btn--desktop");
        var $mobile = $dialog.find(".megamenu-preview-dialog__viewport-btn--mobile");
        if ($mobile.prop("disabled") || $mobile.attr("aria-disabled") === "true") {
            return;
        }
        $dialog.addClass("megamenu-preview-dialog--mobile-preview");
        $mobile.addClass("megamenu-preview-dialog__viewport-btn--active").attr("aria-pressed", "true");
        $desktop.removeClass("megamenu-preview-dialog__viewport-btn--active").attr("aria-pressed", "false");
    }

    function megamenuPreviewIframeAttachLoadHandler($dialog) {
        var $iframe = $dialog.find(".megamenu-preview-dialog__iframe");
        var $shell = $dialog.find(".megamenu-preview-dialog__iframe-shell");
        $iframe.off("load.megamenuPreview").on("load.megamenuPreview", function () {
            var href = "";
            try {
                href = String(this.contentWindow && this.contentWindow.location
                    ? this.contentWindow.location.href
                    : "");
            } catch (ignore) {
                href = "";
            }
            if (href === "about:blank" || href === "about:srcdoc") {
                return;
            }
            $shell.removeClass("megamenu-preview-dialog__iframe-shell--loading");
            $shell.attr("aria-busy", "false");
        });
    }

    function megamenuPreviewIframeStartLoading($dialog) {
        var $shell = $dialog.find(".megamenu-preview-dialog__iframe-shell");
        $shell.addClass("megamenu-preview-dialog__iframe-shell--loading");
        $shell.attr("aria-busy", "true");
        megamenuPreviewIframeAttachLoadHandler($dialog);
    }

    function megamenuPreviewIframeStopLoading($dialog) {
        var $shell = $dialog.find(".megamenu-preview-dialog__iframe-shell");
        var $iframe = $dialog.find(".megamenu-preview-dialog__iframe");
        $shell.removeClass("megamenu-preview-dialog__iframe-shell--loading");
        $shell.attr("aria-busy", "false");
        $iframe.off("load.megamenuPreview");
    }

    function megamenuClosePreviewDialog() {
        var $dialog = $("#megamenu-preview-dialog");
        if (!$dialog.length) {
            return;
        }
        megamenuResetPreviewViewport($dialog);
        var api = megamenuExpandApi();
        if (api && typeof api.collapseOnClose === "function") {
            api.collapseOnClose($dialog);
        }
        megamenuPreviewIframeStopLoading($dialog);
        $dialog.prop("hidden", true).removeClass("is-open");
        $dialog.removeAttr("data-active-preview-url");
        $dialog.removeAttr("data-active-preview-location");
        $dialog.find(".megamenu-preview-dialog__iframe").attr("src", "about:blank");
        $("#megamenu-preview-dialog-subtitle").text("").prop("hidden", true);
        $("#megamenu-preview-dialog-title .megamenu-admin-modal__title-text").text("");
        $dialog
            .find(".megamenu-admin-modal__panel")
            .attr("aria-labelledby", "megamenu-preview-dialog-title");
        megamenuSyncBodyDialogOpenClass();
    }

    /**
     * Open the location preview modal from a trigger button (shared with theme editor save-then-preview).
     *
     * @param {JQuery} $btn Trigger with data-preview-url, titles, breakpoint, etc.
     */
    function megamenuOpenLocationPreview($btn) {
        var url = $btn.attr("data-preview-url");
        megamenuMountPreviewDialogFromTemplate();
        var $dialog = $("#megamenu-preview-dialog");
        if (!$dialog.length || !url) {
            return;
        }
        var assignedPrefix = $dialog.attr("data-i18n-assigned-menu-prefix") || "";
        var locationLabel = $btn.attr("data-preview-location-label") || "";
        $("#megamenu-preview-dialog-title .megamenu-admin-modal__title-text").text(
            megamenuPreviewDialogHeading($dialog, locationLabel)
        );
        var assignedMenu = $btn.attr("data-preview-assigned-menu");
        var $sub = $("#megamenu-preview-dialog-subtitle");
        var $panel = $dialog.find(".megamenu-admin-modal__panel");
        if (assignedMenu && assignedPrefix) {
            $sub.text(assignedPrefix + " " + assignedMenu).prop("hidden", false);
            $panel.attr(
                "aria-labelledby",
                "megamenu-preview-dialog-title megamenu-preview-dialog-subtitle"
            );
        } else {
            $sub.text("").prop("hidden", true);
            $panel.attr("aria-labelledby", "megamenu-preview-dialog-title");
        }
        var previewTitle = $btn.attr("data-preview-title");
        if (previewTitle) {
            $dialog.find(".megamenu-preview-dialog__iframe").attr("title", previewTitle);
        }
        megamenuResetPreviewViewport($dialog);
        var api = megamenuExpandApi();
        if (api && typeof api.restoreOnOpen === "function") {
            api.restoreOnOpen($dialog);
        }
        var bpRaw = $btn.attr("data-responsive-breakpoint");
        var bp = parseInt(bpRaw, 10);
        if (isNaN(bp) || bp < 0) {
            bp = 0;
        }
        var $toggleWrap = $dialog.find(".megamenu-preview-dialog__viewport-toggle");
        var $mobileBtn = $dialog.find(".megamenu-preview-dialog__viewport-btn--mobile");
        if (bp === 0) {
            $mobileBtn
                .attr("aria-disabled", "true")
                .addClass("megamenu-preview-dialog__viewport-btn--unavailable");
            $toggleWrap.addClass("megamenu-preview-dialog__viewport-toggle--mobile-disabled");
            var disabledTip = $dialog.attr("data-i18n-mobile-preview-disabled") || "";
            if (disabledTip) {
                $mobileBtn.attr("data-mega-tooltip", disabledTip).attr("data-mega-tooltip-position", "right");
            }
        } else {
            $dialog.css("--megamenu-preview-mobile-width", bp + "px");
        }
        $dialog.attr("data-active-preview-url", url);
        megamenuPreviewIframeStartLoading($dialog);
        $dialog.find(".megamenu-preview-dialog__iframe").attr("src", url);
        $dialog.prop("hidden", false).addClass("is-open");
        megamenuSyncBodyDialogOpenClass();
        var $close = $dialog.find(".megamenu-modal-close");
        if ($close.length) {
            $close.trigger("focus");
        }
    }

    window.megamenuOpenLocationPreview = megamenuOpenLocationPreview;

    $(document).on("click", "button.megamenu-preview-open:not([disabled])", function (e) {
        var $btn = $(this);
        if ($btn.attr("data-megamenu-save-theme-then-preview") === "1") {
            e.preventDefault();
            if (typeof window.megamenuSaveThemeEditorThenPreview === "function") {
                window.megamenuSaveThemeEditorThenPreview($btn);
            } else {
                megamenuOpenLocationPreview($btn);
            }
            return;
        }
        e.preventDefault();
        megamenuOpenLocationPreview($btn);
    });

    $(document).on(
        "click",
        "#megamenu-preview-dialog .megamenu-preview-dialog__viewport-btn--desktop",
        function (e) {
            e.preventDefault();
            var $dialog = $("#megamenu-preview-dialog");
            if (!$dialog.length || !$dialog.hasClass("is-open")) {
                return;
            }
            megamenuSetPreviewViewportDesktop($dialog);
        }
    );

    $(document).on(
        "click",
        "#megamenu-preview-dialog .megamenu-preview-dialog__viewport-btn--mobile",
        function (e) {
            e.preventDefault();
            if ($(this).attr("aria-disabled") === "true") {
                return;
            }
            var $dialog = $("#megamenu-preview-dialog");
            if (!$dialog.length || !$dialog.hasClass("is-open")) {
                return;
            }
            megamenuSetPreviewViewportMobile($dialog);
        }
    );

    $(document).on("click", "#megamenu-preview-dialog .megamenu-preview-dialog__refresh-btn", function (e) {
        e.preventDefault();
        var $dialog = $("#megamenu-preview-dialog");
        if (!$dialog.length || !$dialog.hasClass("is-open")) {
            return;
        }
        var reloadUrl = $dialog.attr("data-active-preview-url");
        var $iframe = $dialog.find(".megamenu-preview-dialog__iframe");
        if (!reloadUrl || !$iframe.length) {
            return;
        }
        megamenuPreviewIframeStartLoading($dialog);
        $iframe.attr("src", "about:blank");
        window.setTimeout(function () {
            $iframe.attr("src", reloadUrl);
        }, 0);
    });

    $(document).on(
        "click",
        "#megamenu-preview-dialog .megamenu-admin-modal__backdrop, #megamenu-preview-dialog .megamenu-modal-close",
        function (e) {
            e.preventDefault();
            megamenuClosePreviewDialog();
        }
    );

    $(document).on("keydown.megamenuPreview", function (e) {
        if (e.key !== "Escape") {
            return;
        }
        var $dlg = $("#megamenu-preview-dialog");
        if (!$dlg.hasClass("is-open")) {
            return;
        }
        if (
            window.MegamenuAdminModalExpand &&
            typeof window.MegamenuAdminModalExpand.handleEscapeCollapseIfExpanded === "function" &&
            window.MegamenuAdminModalExpand.handleEscapeCollapseIfExpanded($dlg, e)
        ) {
            return;
        }
        megamenuClosePreviewDialog();
    });
})(jQuery);
