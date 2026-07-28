/* global jQuery, ajaxurl, megamenu_location_dialog, MegamenuAdminModalExpand */
(function ($) {
    "use strict";

    var PREVIEW_VIEWPORT_STORAGE_KEY = "megamenu_admin_preview_viewport";

    var locationTitleBlurTimer = null;

    function megamenuExpandApi() {
        return window.MegamenuAdminModalExpand;
    }

    function megamenuPreviewViewportStorageRead() {
        try {
            var v = window.localStorage.getItem(PREVIEW_VIEWPORT_STORAGE_KEY);
            if (v === "mobile" || v === "desktop") {
                return v;
            }
        } catch (ignore) {}
        return null;
    }

    function megamenuPreviewViewportStorageWrite(mode) {
        try {
            if (mode === "mobile" || mode === "desktop") {
                window.localStorage.setItem(PREVIEW_VIEWPORT_STORAGE_KEY, mode);
            }
        } catch (ignore) {}
    }

    function megamenuLocationDialogClearTitleBlurTimer() {
        if (locationTitleBlurTimer) {
            clearTimeout(locationTitleBlurTimer);
            locationTitleBlurTimer = null;
        }
    }

    function megamenuMountLocationSettingsDialogFromTemplate() {
        if (document.getElementById("megamenu-location-settings-dialog")) {
            return;
        }
        var tpl = document.getElementById(
            "megamenu-location-settings-dialog-template"
        );
        if (!tpl || !tpl.textContent) {
            return;
        }
        var wrap = document.createElement("div");
        wrap.innerHTML = tpl.textContent.trim();
        var dlg = wrap.querySelector("#megamenu-location-settings-dialog");
        if (dlg) {
            document.body.appendChild(dlg);
        }
    }

    function megamenuSyncBodyDialogOpenClass() {
        var locOpen = $("#megamenu-location-settings-dialog").hasClass("is-open");
        var scssOpen = $("#megamenu-scss-variables-dialog").hasClass("is-open");
        $("body").toggleClass("megamenu-dialog-open", locOpen || scssOpen);
    }

    /** Prefer localized URL (root-relative) so admin-ajax matches the current host:port. */
    function megamenuLocationAjaxUrl() {
        var dlg = window.megamenu_location_dialog || {};
        if (dlg.ajaxurl) {
            return dlg.ajaxurl;
        }
        if (typeof window.ajaxurl !== "undefined" && window.ajaxurl) {
            return window.ajaxurl;
        }
        return "/wp-admin/admin-ajax.php";
    }

    function megamenuLocationDialogI18n(key) {
        var d = window.megamenu_location_dialog || {};
        var i = d.i18n || {};
        return i[key] || "";
    }

    function megamenuLocationDialogApplyHeadingLocationName(
        $dialog,
        plainLabel
    ) {
        var $tt = $dialog.find(".megamenu-admin-modal__title-text").first();
        if (!$tt.length) {
            return;
        }
        $tt.find(".megamenu-location-title").text(plainLabel || "");
    }

    function megamenuLocationDialogClearAssignedSubheading($dialog) {
        $dialog.find(".megamenu-location-settings-dialog__assigned").first().empty();
    }

    /**
     * Keep body.megamenu_enabled in sync with toggles. Do not trigger a real
     * change event on every .megamenu_enabled input — that re-runs the pill's
     * delegated handler once per location and floods admin-ajax.
     */
    function megamenuSyncEnabledBodyClassFromToggles() {
        if (typeof window.megamenuApplyEnabledBodyClass === "function") {
            window.megamenuApplyEnabledBodyClass();
            return;
        }
        if ($("input.megamenu_enabled:checked").length) {
            $("body").addClass("megamenu_enabled");
        } else {
            $("body").removeClass("megamenu_enabled");
        }
    }

    function megamenuLocationDialogSetLoading($dialog, loading) {
        var $host = $dialog
            .find(".megamenu-location-settings-dialog__settings-view.megamenu-admin-modal__loading-host")
            .first();
        if (!$host.length) {
            return;
        }
        $host.toggleClass("megamenu-admin-modal__loading-host--loading", !!loading);
        if (loading) {
            $host.attr("aria-busy", "true");
        } else {
            $host.removeAttr("aria-busy");
        }
    }

    function megamenuLocationDialogSwapExpandI18n($dialog, mode) {
        var isPreview = mode === "preview";
        var exp = isPreview
            ? $dialog.attr("data-i18n-preview-expand")
            : $dialog.attr("data-i18n-settings-expand");
        var coll = isPreview
            ? $dialog.attr("data-i18n-preview-collapse")
            : $dialog.attr("data-i18n-settings-collapse");
        $dialog.attr("data-i18n-modal-expand", exp || "");
        $dialog.attr("data-i18n-modal-collapse", coll || "");
        var api = megamenuExpandApi();
        if (api && typeof api.applyExpanded === "function") {
            api.applyExpanded(
                $dialog,
                $dialog.hasClass(api.EXPANDED_CLASS)
            );
        }
    }

    function megamenuResetPreviewViewport($dialog) {
        var $wrap = $dialog.find(".megamenu-preview-dialog__viewport-toggle");
        var $desktop = $dialog.find(
            ".megamenu-preview-dialog__viewport-btn--desktop"
        );
        var $mobile = $dialog.find(
            ".megamenu-preview-dialog__viewport-btn--mobile"
        );
        $dialog.removeClass("megamenu-preview-dialog--mobile-preview");
        $dialog.css("--megamenu-preview-mobile-width", "");
        $wrap.removeClass(
            "megamenu-preview-dialog__viewport-toggle--mobile-disabled"
        );
        $mobile
            .prop("disabled", false)
            .removeAttr("aria-disabled")
            .removeClass("megamenu-preview-dialog__viewport-btn--unavailable")
            .removeAttr("data-mega-tooltip")
            .removeAttr("data-mega-tooltip-position");
        $desktop
            .addClass("is-active")
            .attr("aria-pressed", "true");
        $mobile
            .removeClass("is-active")
            .attr("aria-pressed", "false");
    }

    function megamenuSetPreviewViewportDesktop($dialog) {
        var $desktop = $dialog.find(
            ".megamenu-preview-dialog__viewport-btn--desktop"
        );
        var $mobile = $dialog.find(
            ".megamenu-preview-dialog__viewport-btn--mobile"
        );
        $dialog.removeClass("megamenu-preview-dialog--mobile-preview");
        $desktop
            .addClass("is-active")
            .attr("aria-pressed", "true");
        $mobile
            .removeClass("is-active")
            .attr("aria-pressed", "false");
        megamenuPreviewViewportStorageWrite("desktop");
    }

    function megamenuSetPreviewViewportMobile($dialog) {
        var $desktop = $dialog.find(
            ".megamenu-preview-dialog__viewport-btn--desktop"
        );
        var $mobile = $dialog.find(
            ".megamenu-preview-dialog__viewport-btn--mobile"
        );
        if ($mobile.prop("disabled") || $mobile.attr("aria-disabled") === "true") {
            return;
        }
        $dialog.addClass("megamenu-preview-dialog--mobile-preview");
        $mobile
            .addClass("is-active")
            .attr("aria-pressed", "true");
        $desktop
            .removeClass("is-active")
            .attr("aria-pressed", "false");
        megamenuPreviewViewportStorageWrite("mobile");
    }

    function megamenuPreviewIframeAttachLoadHandler($dialog) {
        var $iframe = $dialog.find(".megamenu-preview-dialog__iframe");
        var $shell = $dialog.find(".megamenu-preview-dialog__iframe-shell");
        $iframe.off("load.megamenuPreview").on("load.megamenuPreview", function () {
            var href = "";
            try {
                href = String(
                    this.contentWindow && this.contentWindow.location
                        ? this.contentWindow.location.href
                        : ""
                );
            } catch (ignore) {
                href = "";
            }
            if (href === "about:blank" || href === "about:srcdoc") {
                return;
            }
            $shell.removeClass("megamenu-admin-modal__loading-host--loading");
            $shell.attr("aria-busy", "false");
            var locAfterLoad = megamenuLocationFromDialog($dialog);
            var storedAfterLoad = locAfterLoad
                ? megamenuPreviewShellBgRead(locAfterLoad)
                : "custom:transparent";
            megamenuApplyPreviewShellBackground($dialog, storedAfterLoad);
        });
    }

    function megamenuPreviewIframeStartLoading($dialog) {
        var $shell = $dialog.find(".megamenu-preview-dialog__iframe-shell");
        $shell.addClass("megamenu-admin-modal__loading-host--loading");
        $shell.attr("aria-busy", "true");
        megamenuPreviewIframeAttachLoadHandler($dialog);
    }

    function megamenuPreviewIframeStopLoading($dialog) {
        var $shell = $dialog.find(".megamenu-preview-dialog__iframe-shell");
        var $iframe = $dialog.find(".megamenu-preview-dialog__iframe");
        $shell.removeClass("megamenu-admin-modal__loading-host--loading");
        $shell.attr("aria-busy", "false");
        $iframe.off("load.megamenuPreview");
    }

    var MMM_PREVIEW_SHELL_BG_PREFIX = "megamenu_preview_shell_bg_v1_";

    function megamenuLocationDialogSlugForStorage(location) {
        return encodeURIComponent(String(location || "").replace(/\s+/g, ""));
    }

    function megamenuPreviewShellBgStorageKey(location) {
        return MMM_PREVIEW_SHELL_BG_PREFIX + megamenuLocationDialogSlugForStorage(location);
    }

    function megamenuPreviewShellBgRead(location) {
        try {
            var v = window.localStorage.getItem(
                megamenuPreviewShellBgStorageKey(location)
            );
            if (
                v &&
                v.indexOf("custom:") === 0 &&
                v.length < 500 &&
                v.length > 7
            ) {
                return v;
            }
        } catch (ignore) {}
        return "custom:rgba(67,67,67,0.5)";
    }

    function megamenuPreviewShellBgWrite(location, value) {
        try {
            window.localStorage.setItem(
                megamenuPreviewShellBgStorageKey(location),
                value
            );
        } catch (ignore) {}
    }

    function megamenuLocationFromDialog($dialog) {
        var loc =
            ($dialog && $dialog.data && $dialog.data("mmmLocation")) || "";
        if (!loc && $dialog && $dialog.length) {
            loc =
                $dialog
                    .find("form.megamenu-location-settings-dialog-form")
                    .attr("data-location") || "";
        }
        return String(loc || "");
    }

    function megamenuPreviewShellCustomColorFromStored(value) {
        if (!value || value.indexOf("custom:") !== 0) {
            return "";
        }
        return value.slice(7);
    }

    function megamenuUpdatePreviewBgSwatch($dialog, cssColor) {
        var $sw = $dialog.find(
            ".megamenu-preview-dialog__bg-swatch--preview"
        );
        if (!$sw.length) {
            return;
        }
        var t = String(cssColor || "")
            .toLowerCase()
            .trim();
        $sw.removeClass(
            "megamenu-preview-dialog__bg-swatch--preview-transparent"
        );
        if (
            t === "transparent" ||
            t === "rgba(0, 0, 0, 0)" ||
            t === "rgba(0,0,0,0)"
        ) {
            $sw.addClass(
                "megamenu-preview-dialog__bg-swatch--preview-transparent"
            );
            $sw.css({ "background-color": "", "background-image": "" });
            return;
        }
        $sw.css({
            "background-color": cssColor,
            "background-image": "none",
        });
    }

    function megamenuPreviewBgEnsurePickerBound($dialog) {
        if (!$dialog || !$dialog.length) {
            return;
        }
        if ($dialog.data("mmmPreviewBgPickerInit")) {
            return;
        }
        var $inp = $dialog.find(
            ".megamenu-preview-dialog__bg-custom-color-input"
        );
        if (
            !$inp.length ||
            typeof $.fn.customColorPicker !== "function"
        ) {
            return;
        }
        $inp.customColorPicker({
            defaultColor: "#50575e",
            showCssVarPalette: false,
            palette: ["transparent", "#ffffff", "#50575e"],
            onColorChange: function (finalColorString) {
                var $in = $(this);
                var $dlg = $in.closest(
                    "#megamenu-location-settings-dialog"
                );
                if (!$dlg.length) {
                    return;
                }
                var stored = "custom:" + finalColorString;
                megamenuApplyPreviewShellBackground($dlg, stored);
                megamenuUpdatePreviewBgSwatch($dlg, finalColorString);
                var loc = megamenuLocationFromDialog($dlg);
                if (loc) {
                    megamenuPreviewShellBgWrite(loc, stored);
                }
            },
        });
        var $wrap = $inp.next(".mega-custom-color-input-wrapper");
        $wrap.on("click.mmmPreviewBgSync", function () {
            var $dlg = $inp.closest(
                "#megamenu-location-settings-dialog"
            );
            megamenuPreviewBgOpenPicker($dlg, false);
        });
        $dialog.data("mmmPreviewBgPickerInit", true);
    }

    function megamenuPreviewBgOpenPicker($dialog, forceShowPicker) {
        megamenuPreviewBgEnsurePickerBound($dialog);
        var $inp = $dialog.find(
            ".megamenu-preview-dialog__bg-custom-color-input"
        );
        if (!$inp.length || !$inp.data("customColorPickerApi")) {
            return;
        }
        var current = $inp.customColorPicker("get");
        var stored = "custom:" + current;
        megamenuApplyPreviewShellBackground($dialog, stored);
        megamenuUpdatePreviewBgSwatch($dialog, current);
        var loc = megamenuLocationFromDialog($dialog);
        if (loc) {
            megamenuPreviewShellBgWrite(loc, stored);
        }
        if (
            forceShowPicker &&
            typeof $.fn.customColorPicker === "function" &&
            $inp.length
        ) {
            $inp.customColorPicker("open");
        }
    }

    function megamenuClosePreviewBgColorPickerIfOpen() {
        var $dlg = $("#megamenu-location-settings-dialog");
        if (!$dlg.length || !$dlg.hasClass("is-open")) {
            return;
        }
        if (typeof $.fn.customColorPicker !== "function") {
            return;
        }
        $dlg.find(".megamenu-preview-dialog__bg-custom-color-input").each(function () {
            var $inp = $(this);
            var $pc = $inp.data("picker-container-ref");
            if ($pc && $pc.length && $pc.is(":visible")) {
                $inp.customColorPicker("close");
            }
        });
    }

    function megamenuApplyPreviewIframeDocumentBodyBackground($iframe, color, isTransparent) {
        var el = $iframe && $iframe.length ? $iframe[0] : null;
        if (!el) {
            return;
        }
        try {
            var doc = el.contentDocument;
            if (!doc || !doc.body) {
                return;
            }
            var bodyEl = doc.body;
            if (isTransparent) {
                bodyEl.style.removeProperty("background-color");
                bodyEl.style.removeProperty("background-image");
            } else {
                bodyEl.style.backgroundColor = color;
                bodyEl.style.backgroundImage = "none";
            }
        } catch (ignore) {}
    }

    function megamenuApplyPreviewShellBackground($dialog, value) {
        if (!$dialog || !$dialog.length) {
            return;
        }
        var v = value;
        if (!v || v.indexOf("custom:") !== 0) {
            v = "custom:transparent";
        }
        var color = v.slice(7);
        var $shell = $dialog.find(".megamenu-preview-dialog__iframe-shell");
        var $iframe = $dialog.find(".megamenu-preview-dialog__iframe");

        $shell.removeClass("megamenu-preview-dialog__iframe-shell--bg-custom");
        $shell.css({
            "background-color": "",
            "background-image": "",
            "background-size": "",
            "background-position": "",
        });

        $iframe.css({
            "background-color": "",
            "background-image": "",
            "background-size": "",
            "background-position": "",
        });

        var t = String(color).trim().toLowerCase();
        var isTransparent =
            t === "transparent" ||
            t === "rgba(0, 0, 0, 0)" ||
            t === "rgba(0,0,0,0)";

        megamenuApplyPreviewIframeDocumentBodyBackground(
            $iframe,
            color,
            isTransparent
        );
    }

    function megamenuSyncPreviewBgSwatch($dialog, storedValue) {
        if (!storedValue || storedValue.indexOf("custom:") !== 0) {
            return;
        }
        megamenuUpdatePreviewBgSwatch(
            $dialog,
            megamenuPreviewShellCustomColorFromStored(storedValue)
        );
    }

    function megamenuRestorePreviewShellBgForDialog($dialog) {
        megamenuPreviewBgEnsurePickerBound($dialog);
        var loc = megamenuLocationFromDialog($dialog);
        if (!loc) {
            megamenuApplyPreviewShellBackground(
                $dialog,
                "custom:transparent"
            );
            megamenuSyncPreviewBgSwatch($dialog, "custom:transparent");
            return;
        }
        var v = megamenuPreviewShellBgRead(loc);
        megamenuApplyPreviewShellBackground($dialog, v);
        megamenuSyncPreviewBgSwatch($dialog, v);
        if (v.indexOf("custom:") === 0) {
            var $inp = $dialog.find(
                ".megamenu-preview-dialog__bg-custom-color-input"
            );
            var part = megamenuPreviewShellCustomColorFromStored(v);
            if (
                $inp.length &&
                $inp.data("customColorPickerApi") &&
                part
            ) {
                $inp.customColorPicker("set", part);
            }
        }
    }

    function megamenuLocationDialogSyncModePill($dialog, previewActive) {
        var $s = $dialog.find(
            ".megamenu-location-settings-dialog__mode-btn--settings"
        );
        var $p = $dialog.find(
            ".megamenu-location-settings-dialog__mode-btn--preview"
        );
        if (!$s.length || !$p.length) {
            return;
        }
        var settingsOn = !previewActive;
        $s
            .toggleClass("is-active", settingsOn)
            .addClass("button-secondary")
            .attr("aria-pressed", settingsOn ? "true" : "false");
        $p
            .toggleClass("is-active", !!previewActive)
            .addClass("button-secondary")
            .attr("aria-pressed", previewActive ? "true" : "false");
    }

    function megamenuLocationDialogTogglePreviewViews($dialog, previewOn) {
        $dialog
            .find(".megamenu-location-settings-dialog__settings-view")
            .prop("hidden", !!previewOn);
        $dialog
            .find(".megamenu-location-settings-dialog__preview-view")
            .prop("hidden", !previewOn);
    }

    function megamenuLocationDialogEnsurePreviewSourceButton($dialog) {
        var $existing = $dialog.data("mmmPreviewSourceBtn");
        if ($existing && $existing.length) {
            return;
        }
        var loc =
            $dialog.find("form.megamenu-location-settings-dialog-form").attr("data-location") ||
            "";
        if (!loc) {
            return;
        }
        var $all = $(
            '.megamenu-preview-open[data-location="' + loc + '"]'
        );
        var $cardBtn = $all.filter(":not([disabled])").first();
        if (!$cardBtn.length) {
            $cardBtn = $all.filter(function () {
                return !!($(this).attr("data-preview-url") || "").length;
            }).first();
        }
        if ($cardBtn.length) {
            $dialog.data("mmmPreviewSourceBtn", $cardBtn);
            megamenuPreparePreviewFromButton($cardBtn);
        }
    }

    function megamenuLocationDialogEnterPreviewChrome($dialog) {
        megamenuLocationDialogEnsurePreviewSourceButton($dialog);
        $dialog.addClass("megamenu-location-settings-dialog--preview-visible");
        megamenuLocationDialogTogglePreviewViews($dialog, true);
        $dialog.find(".megamenu-location-settings-dialog__footer-settings").prop("hidden", true);
        $dialog.find(".megamenu-location-settings-dialog__footer-preview").prop("hidden", false);
        megamenuLocationDialogSwapExpandI18n($dialog, "preview");
        megamenuLocationDialogSyncModePill($dialog, true);
        megamenuRestorePreviewShellBgForDialog($dialog);
    }

    function megamenuLocationDialogEnterSettingsChrome($dialog) {
        $dialog.removeClass("megamenu-location-settings-dialog--preview-visible");
        megamenuLocationDialogTogglePreviewViews($dialog, false);
        $dialog.find(".megamenu-location-settings-dialog__footer-settings").prop("hidden", false);
        $dialog.find(".megamenu-location-settings-dialog__footer-preview").prop("hidden", true);
        megamenuLocationDialogSwapExpandI18n($dialog, "settings");
        megamenuLocationDialogSyncModePill($dialog, false);
        var $btn = $dialog.find(".megamenu-location-settings-dialog-save");
        var origLabel = $btn.data("mmm-save-label");
        if (origLabel) $btn.html(origLabel);
        $btn.prop("disabled", false);
    }

    function megamenuLocationDialogClearPreviewStateForNewLoad($dialog) {
        megamenuResetPreviewViewport($dialog);
        megamenuPreviewIframeStopLoading($dialog);
        $dialog.find(".megamenu-preview-dialog__iframe").attr("src", "about:blank");
        $dialog.removeAttr("data-active-preview-url");
        $dialog.removeClass(
            "megamenu-location-settings-dialog--preview-visible megamenu-preview-dialog--mobile-preview"
        );
        megamenuLocationDialogTogglePreviewViews($dialog, false);
        megamenuLocationDialogSyncModePill($dialog, false);
        $dialog.removeData("mmmPreviewSourceBtn");
        $dialog.find(".megamenu-location-settings-dialog__footer-settings").prop("hidden", false);
        $dialog.find(".megamenu-location-settings-dialog__footer-preview").prop("hidden", true);
    }

    function megamenuLocationDialogResetOnClose($dialog) {
        if (!$dialog.length) {
            return;
        }
        megamenuResetPreviewViewport($dialog);
        megamenuPreviewIframeStopLoading($dialog);
        $dialog.find(".megamenu-preview-dialog__iframe").attr("src", "about:blank");
        $dialog.removeAttr("data-active-preview-url");
        $dialog.removeClass(
            "megamenu-location-settings-dialog--preview-visible megamenu-preview-dialog--mobile-preview"
        );
        megamenuLocationDialogTogglePreviewViews($dialog, false);
        megamenuLocationDialogSyncModePill($dialog, false);
        $dialog.removeData("mmmPreviewSourceBtn");
        $dialog.find(".megamenu-location-settings-dialog__footer-settings").prop("hidden", false);
        $dialog.find(".megamenu-location-settings-dialog__footer-preview").prop("hidden", true);
        megamenuLocationDialogClearAssignedSubheading($dialog);
    }

    function megamenuPreparePreviewFromButton($btn) {
        var $dialog = $("#megamenu-location-settings-dialog");
        if (!$dialog.length) {
            return;
        }
        var url = $btn.attr("data-preview-url");
        if (!url) {
            return;
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
        var $mobileBtn = $dialog.find(
            ".megamenu-preview-dialog__viewport-btn--mobile"
        );
        if (bp === 0) {
            $mobileBtn
                .attr("aria-disabled", "true")
                .addClass("megamenu-preview-dialog__viewport-btn--unavailable");
            $toggleWrap.addClass(
                "megamenu-preview-dialog__viewport-toggle--mobile-disabled"
            );
            var disabledTip = $dialog.attr("data-i18n-mobile-preview-disabled") || "";
            if (disabledTip) {
                $mobileBtn
                    .attr("data-mega-tooltip", disabledTip)
                    .attr("data-mega-tooltip-position", "right");
            }
        } else {
            $dialog.css("--megamenu-preview-mobile-width", bp + "px");
        }
        if (bp !== 0 && megamenuPreviewViewportStorageRead() === "mobile") {
            megamenuSetPreviewViewportMobile($dialog);
        }
        $dialog.attr("data-active-preview-url", url);
        megamenuPreviewIframeStartLoading($dialog);
        $dialog.find(".megamenu-preview-dialog__iframe").attr("src", url);
    }

    function megamenuOpenLocationPreview($btn) {
        var location = $btn.attr("data-location");
        if (!location) {
            return;
        }
        var menuId = $btn.attr("data-preview-menu-id") || "";
        var $fake = $('<button type="button" />');
        var attrs = {
            "data-location": location,
            "data-requires-menu": "0",
        };
        if (menuId) {
            attrs["data-preview-menu-id"] = menuId;
        }
        $fake.attr(attrs);
        megamenuOpenLocationSettingsDialog($fake, {
            openPreviewAfterLoad: true,
            previewButton: $btn,
        });
    }

    window.megamenuOpenLocationPreview = megamenuOpenLocationPreview;

    function megamenuCloseLocationSettingsDialog() {
        var $dialog = $("#megamenu-location-settings-dialog");
        if (!$dialog.length) {
            return;
        }
        megamenuLocationDialogResetOnClose($dialog);
        megamenuLocationDialogSetLoading($dialog, false);
        megamenuLocationDialogClearTitleBlurTimer();
        $dialog.off("click.mmmLocHintTab");
        var locScrollHintRO = $dialog.data("mmmLocScrollHintRO");
        if (locScrollHintRO) { locScrollHintRO.disconnect(); $dialog.removeData("mmmLocScrollHintRO"); }
        $dialog.find(".mmm-scroll-hint").prop("hidden", true).removeClass("is-visible");
        $dialog.prop("hidden", true).removeClass("is-open");
        $("#megamenu-location-settings-dialog-body").empty();
        $dialog
            .find(".megamenu-admin-modal__panel")
            .attr("aria-labelledby", "megamenu-location-settings-dialog-title");
        megamenuSyncBodyDialogOpenClass();
    }

    function megamenuLocationDialogUpdateCardsLocationLabel(
        location,
        plainLabel,
        previewTitle
    ) {
        var $r = $(
            '.mega-location[data-mega-location="' + location + '"]'
        ).first();
        if (!$r.length) {
            return;
        }
        $r.attr("data-mmm-plain-label", plainLabel);
        $r.find(".megamenu-preview-open").each(function () {
            var $p = $(this);
            $p.attr("data-preview-location-label", plainLabel);
            if (previewTitle) {
                $p.attr("data-preview-title", previewTitle);
            }
        });
        var $titleText = $r.find(".mega-location__title-text").first();
        if ($titleText.length) {
            $titleText.text(plainLabel);
        }
        $r.find(".mega-location-card-title-input").val(plainLabel);
    }

    function megamenuSyncDialogHeadingIfSameLocation(location, plainLabel) {
        var $dialog = $("#megamenu-location-settings-dialog");
        if (!$dialog.hasClass("is-open")) {
            return;
        }
        if (($dialog.data("mmmLocation") || "") !== location) {
            return;
        }
        if ($dialog.hasClass("megamenu-location-settings-dialog--preview-visible")) {
            return;
        }
        $dialog.data("mmmPlainLabel", plainLabel);
        megamenuLocationDialogApplyHeadingLocationName($dialog, plainLabel);
    }

    function locationCardTitleEls($row) {
        return {
            field: $row.find(".mega-location__title-edit-field"),
            text: $row.find(".mega-location__title-text").first(),
            btn: $row.find(".mega-location__title-edit"),
            input: $row.find(".mega-location-card-title-input")
        };
    }

    function locationCardTitleSaveFailed($input) {
        window.alert(
            megamenuLocationDialogI18n("title_save_error") ||
                "Could not save the location name."
        );
        $input.trigger("focus");
    }

    function megamenuLocationCardResetTitleEdit($row) {
        megamenuLocationDialogClearTitleBlurTimer();
        if (!$row || !$row.length) {
            return;
        }
        var d = locationCardTitleEls($row);
        var plain = $row.attr("data-mmm-plain-label") || "";
        d.field.prop("hidden", true);
        d.text.prop("hidden", false);
        d.btn.prop("hidden", false);
        d.input.val(plain);
    }

    function megamenuLocationCardEnterTitleEdit($from) {
        megamenuLocationDialogClearTitleBlurTimer();
        var $row = $from.closest(".mega-location");
        if (!$row.length || !$row.find("h2.mega-location__title--editable").length) {
            return;
        }
        var d = locationCardTitleEls($row);
        if (!d.field.prop("hidden")) {
            d.input.trigger("focus");
            return;
        }
        d.text.prop("hidden", true);
        d.field.prop("hidden", false);
        d.btn.prop("hidden", true);
        d.input
            .val(
                $.trim(
                    $row.attr("data-mmm-plain-label") || d.text.text() || ""
                )
            )
            .trigger("focus");
    }

    function megamenuLocationCardCommitFromInput($input) {
        megamenuLocationDialogClearTitleBlurTimer();
        var $row = $input.closest(".mega-location");
        var loc =
            $input.attr("data-mega-location") ||
            $row.attr("data-mega-location") ||
            "";
        var prev = $.trim($row.attr("data-mmm-plain-label") || "");
        var next = $.trim($input.val());
        if (
            !next ||
            next === prev ||
            loc.indexOf("max_mega_menu_") !== 0
        ) {
            megamenuLocationCardResetTitleEdit($row);
            return;
        }

        var dlg = window.megamenu_location_dialog || {};
        $.ajax({
            url: megamenuLocationAjaxUrl(),
            type: "POST",
            dataType: "json",
            data: {
                action: "megamenu_save_custom_location_title",
                nonce: dlg.nonce,
                location: loc,
                title: next,
            },
        })
            .done(function (res) {
                if (!(res && res.success && res.data)) {
                    locationCardTitleSaveFailed($input);
                    return;
                }
                var t = res.data.title || next;
                megamenuLocationDialogUpdateCardsLocationLabel(
                    loc,
                    t,
                    res.data.preview_title || ""
                );
                megamenuSyncDialogHeadingIfSameLocation(loc, t);
                megamenuLocationCardResetTitleEdit($row);
            })
            .fail(function () {
                locationCardTitleSaveFailed($input);
            });
    }

    function megamenuInitLocationDialogTabs($root) {
        if (
            !window.megamenuDialogTabs ||
            typeof window.megamenuDialogTabs.bindVerticalRail !== "function"
        ) {
            return;
        }
        var loc =
            ($root.find("form.megamenu-location-settings-dialog-form").attr("data-location") ||
                "location").replace(/[^a-zA-Z0-9_-]/g, "-");
        var $dialog = $root.closest("#megamenu-location-settings-dialog");
        $root.find(".megamenu-dialog-tablist").each(function () {
            var $nav = $(this);
            var $tabsRoot = $nav.closest(".megamenu-dialog-rail");
            if (!$tabsRoot.length) {
                $tabsRoot = $nav.parent();
            }
            window.megamenuDialogTabs.bindVerticalRail({
                tablist: $nav[0],
                panelsRoot: $tabsRoot[0],
                tabSelector: "button.megamenu-dialog-tab",
                panelsSelector: ".mega-tab-content",
                idPrefix: "mega-location-" + loc,
                getPanelKey: function (btn) {
                    return btn.getAttribute("data-tab");
                },
                panelMatches: function (panel, key) {
                    return panel.classList.contains(key);
                },
            });
        });
    }

    /**
     * Keep Gutenberg toggle visuals in sync (is-checked / is-disabled on the wrapper span).
     *
     * @param {JQuery} [$root] Root to search under; defaults to document.
     */
    function megamenuSyncComponentsToggleWrappers($root) {
        var $scope = $root && $root.length ? $root : $(document);
        $scope
            .find(".components-form-toggle__input[type=\"checkbox\"]")
            .each(function () {
                var $input = $(this);
                var $wrap = $input.closest(".components-form-toggle");
                if (!$wrap.length) {
                    return;
                }
                $wrap.toggleClass("is-checked", $input.prop("checked"));
                $wrap.toggleClass(
                    "is-disabled",
                    !!$input.prop("disabled") ||
                        $input.attr("aria-disabled") === "true"
                );
            });
    }

    window.megamenuSyncComponentsToggleWrappers = megamenuSyncComponentsToggleWrappers;

    function megamenuApplyMmmRowState($row, enabled) {
        if (!$row || !$row.length) {
            return;
        }
        $row.toggleClass("mega-location-mmm-on", !!enabled);
        $row.toggleClass("mega-location-mmm-off", !enabled);
        var hasMenu = $row.attr("data-has-nav-menu") === "1";

        // Keep mega-location-enabled / mega-location-disabled in sync with the toggle.
        // Initial HTML sets these from max_mega_menu_is_enabled(); without this, turning MMM on
        // only flips mmm-on/off while mega-location-disabled stays set, so footer CSS still
        // greys out Settings / Preview (see admin.scss: .mega-location-mmm-off | .mega-location-disabled).
        $row.removeClass(
            "mega-location-enabled mega-location-disabled mega-location-disabled-assign-menu"
        );
        if (!hasMenu) {
            if (enabled) {
                $row.addClass("mega-location-enabled");
            } else {
                $row.addClass("mega-location-disabled");
            }
        } else if (enabled) {
            $row.addClass("mega-location-enabled");
        } else {
            $row.addClass("mega-location-disabled");
        }

        var $prev = $row.find(".megamenu-preview-open");
        if ($prev.length) {
            var previewUrl = $prev.attr("data-preview-url") || "";
            var can =
                hasMenu && previewUrl.length > 0 && !!enabled;
            $prev.prop("disabled", !can);
        }

        var $dlg = $("#megamenu-location-settings-dialog");
        if (
            $dlg.hasClass("is-open") &&
            ($row.attr("data-mega-location") || "") ===
                ($dlg.data("mmmLocation") || "")
        ) {
            megamenuLocationDialogSyncNoticesAndPreview($dlg);
        }
    }

    function megamenuLocationDialogSyncNoticesAndPreview($dialog) {
        if (!$dialog || !$dialog.length) {
            return;
        }
        var loc = $dialog.data("mmmLocation") || "";
        if (!loc) {
            return;
        }
        var $row = $(".mega-location").filter(function () {
            return $(this).attr("data-mega-location") === loc;
        }).first();
        var hasMenu = $dialog.data("mmmHasNavMenu") === "1";
        var mmmOn = $row.length
            ? $row.hasClass("mega-location-mmm-on")
            : $dialog.data("mmmLocationMmmOn") === "1";
        var previewOk = hasMenu && mmmOn;
        $dialog
            .find(".megamenu-location-settings-dialog__notice--no-menu")
            .prop("hidden", !!hasMenu);
        $dialog
            .find(".megamenu-location-settings-dialog__notice--mmm-off")
            .prop("hidden", !!mmmOn);
        $dialog
            .find(".megamenu-location-settings-dialog__mode-btn--preview")
            .prop("disabled", !previewOk);
        if (!previewOk && $dialog.hasClass("megamenu-location-settings-dialog--preview-visible")) {
            megamenuLocationDialogEnterSettingsChrome($dialog);
        }
    }

    function megamenuOpenLocationSettingsDialog($trigger, opts) {
        opts = opts || {};
        var previewFirst =
            opts.openPreviewAfterLoad &&
            opts.previewButton &&
            opts.previewButton.length;
        var dlg = window.megamenu_location_dialog || {};
        var location = $trigger.attr("data-location") || "";

        megamenuMountLocationSettingsDialogFromTemplate();

        var $dialog = $("#megamenu-location-settings-dialog");
        var $body = $("#megamenu-location-settings-dialog-body");
        if (!$dialog.length || !$body.length) {
            return;
        }

        megamenuLocationDialogClearPreviewStateForNewLoad($dialog);

        megamenuLocationDialogApplyHeadingLocationName($dialog, "");
        megamenuLocationDialogClearAssignedSubheading($dialog);

        $dialog.data("mmmLocation", location);
        $dialog.data("mmmPlainLabel", "");

        megamenuRestorePreviewShellBgForDialog($dialog);

        var $cardRow = $trigger.closest(".mega-location");
        var editingMenuId = 0;
        if ($cardRow.length) {
            var em = parseInt($cardRow.attr("data-mmm-editing-menu-id") || "0", 10);
            if (!isNaN(em) && em > 0) {
                editingMenuId = em;
            }
        }
        if (!editingMenuId) {
            editingMenuId =
                parseInt(
                    $trigger.attr("data-preview-menu-id") ||
                        $trigger.attr("data-menu-id") ||
                        "0",
                    10
                ) || 0;
        }

        if ($cardRow.length) {
            $dialog.data(
                "mmmHasNavMenu",
                $cardRow.attr("data-has-nav-menu") === "1" ? "1" : "0"
            );
        } else {
            var menuIdForState = editingMenuId;
            var assignedName =
                $trigger.attr("data-assigned-menu") ||
                $trigger.attr("data-preview-assigned-menu") ||
                "";
            if (menuIdForState > 0 || assignedName) {
                $dialog.data("mmmHasNavMenu", "1");
            }
        }

        megamenuLocationDialogSyncNoticesAndPreview($dialog);

        $body.empty();
        megamenuLocationDialogSetLoading($dialog, true);

        $dialog.prop("hidden", false).addClass("is-open");
        if (
            window.MegamenuAdminModalExpand &&
            typeof window.MegamenuAdminModalExpand.restoreOnOpen === "function"
        ) {
            window.MegamenuAdminModalExpand.restoreOnOpen($dialog);
        }
        if (previewFirst) {
            $dialog.data("mmmPreviewSourceBtn", opts.previewButton);
            megamenuPreparePreviewFromButton(opts.previewButton);
            megamenuLocationDialogEnterPreviewChrome($dialog);
        } else {
            megamenuLocationDialogSwapExpandI18n($dialog, "settings");
        }
        megamenuSyncBodyDialogOpenClass();

        var cardsContext = dlg.cards_context || "page";

        $.post(
            megamenuLocationAjaxUrl(),
            {
                action: "megamenu_get_location_settings_html",
                nonce: dlg.nonce,
                location: location,
                cards_context: cardsContext,
                editing_menu_id: editingMenuId,
            }
        )
            .done(function (res) {
                if (!res || !res.success || !res.data || !res.data.html) {
                    window.alert(megamenuLocationDialogI18n("load_error"));
                    megamenuCloseLocationSettingsDialog();
                    return;
                }
                $body.html(res.data.html);
                if (res.data && typeof res.data.location_label === "string") {
                    megamenuLocationDialogApplyHeadingLocationName(
                        $dialog,
                        res.data.location_label
                    );
                    $dialog.data("mmmPlainLabel", res.data.location_label);
                }
                if (
                    res.data &&
                    typeof res.data.assigned_summary_html === "string"
                ) {
                    $dialog
                        .find(".megamenu-location-settings-dialog__assigned")
                        .first()
                        .html(res.data.assigned_summary_html);
                }
                if (
                    res.data &&
                    typeof res.data.has_nav_menu !== "undefined"
                ) {
                    $dialog.data(
                        "mmmHasNavMenu",
                        res.data.has_nav_menu ? "1" : "0"
                    );
                }
                if (
                    res.data &&
                    typeof res.data.mmm_enabled !== "undefined"
                ) {
                    $dialog.data(
                        "mmmLocationMmmOn",
                        res.data.mmm_enabled ? "1" : "0"
                    );
                }
                megamenuLocationDialogSyncNoticesAndPreview($dialog);
                megamenuInitLocationDialogTabs($body);
                megamenuSyncComponentsToggleWrappers($body);
                megamenuRestorePreviewShellBgForDialog($dialog);

                // Scroll-down hint: show when panels content overflows.
                (function() {
                    var $panels = $body.find(".megamenu-dialog-panels");
                    var $hint = $dialog.find(".mmm-scroll-hint");

                    function updateScrollHint() {
                        if (!$panels.length || !$hint.length) { return; }
                        var el = $panels[0];
                        var hasMore = el.scrollHeight > el.clientHeight + el.scrollTop + 2;
                        $hint.toggleClass("is-visible", hasMore);
                        $hint.attr("aria-hidden", hasMore ? "false" : "true");
                    }

                    // Remove [hidden] so opacity/visibility transitions can fire.
                    $hint.removeAttr("hidden");

                    $panels.off("scroll.mmmLocHint").on("scroll.mmmLocHint", updateScrollHint);

                    $hint.off("click.mmmLocHint").on("click.mmmLocHint", function() {
                        $panels[0].scrollBy({ top: 200, behavior: "smooth" });
                    });

                    $dialog.off("click.mmmLocHintTab").on("click.mmmLocHintTab", ".megamenu-dialog-tab", function() {
                        window.setTimeout(updateScrollHint, 50);
                    });

                    // ResizeObserver re-checks on window resize and modal expand/collapse transitions.
                    if (typeof ResizeObserver !== "undefined") {
                        var ro = new ResizeObserver(updateScrollHint);
                        ro.observe($panels[0]);
                        $dialog.data("mmmLocScrollHintRO", ro);
                    } else {
                        window.requestAnimationFrame(function() {
                            window.requestAnimationFrame(updateScrollHint);
                        });
                    }
                }());
                if (previewFirst) {
                    window.setTimeout(function () {
                        $dialog
                            .find(
                                ".megamenu-location-settings-dialog__mode-btn--preview"
                            )
                            .trigger("focus");
                    }, 0);
                }
            })
            .fail(function () {
                window.alert(megamenuLocationDialogI18n("load_error"));
                megamenuCloseLocationSettingsDialog();
            })
            .always(function () {
                megamenuLocationDialogSetLoading($dialog, false);
            });

        var $focusBtn = previewFirst
            ? null
            : $dialog.find(".megamenu-modal-close").first();
        if ($focusBtn && $focusBtn.length) {
            $focusBtn.trigger("focus");
        }
    }

    function megamenuSaveLocationSettingsDialog() {
        var $dialog = $("#megamenu-location-settings-dialog");
        if ($dialog.hasClass("megamenu-location-settings-dialog--preview-visible")) {
            return;
        }
        var $form = $dialog.find("form.megamenu-location-settings-dialog-form");
        if (!$form.length) {
            return;
        }

        var $btn = $dialog.find(".megamenu-location-settings-dialog-save");
        var origLabel = $btn.text();
        if (!$btn.data("mmm-save-label")) {
            $btn.data("mmm-save-label", origLabel);
        }
        var location = $form.attr("data-location") || "";
        var succeeded = false;

        $btn.prop("disabled", true).text(megamenuLocationDialogI18n("saving") + "…");

        var done = function (ok) {
            if (ok) {
                succeeded = true;
                var $r = $(
                    '.mega-location[data-mega-location="' + location + '"]'
                ).first();
                var $toggle = $r
                    .find("input.megamenu_enabled[data-mega-location]")
                    .first();
                var enabled = $toggle.length ? $toggle.is(":checked") : true;
                if ($r.length) {
                    megamenuApplyMmmRowState($r, enabled);
                    megamenuSyncEnabledBodyClassFromToggles();
                }
                $btn.html('<span class="dashicons dashicons-yes" aria-hidden="true"></span> ' + (megamenuLocationDialogI18n("saved_button") || "Saved"));
            } else {
                $btn.text(origLabel);
                $btn.prop("disabled", false);
            }
        };

        $.ajax({
            url: megamenuLocationAjaxUrl(),
            type: "POST",
            data:
                $form.serialize() +
                "&action=megamenu_save_location_settings",
            dataType: "json",
        })
            .done(function (res) {
                if (res && res.success) {
                    done(true);
                } else {
                    window.alert(megamenuLocationDialogI18n("save_error"));
                    done(false);
                }
            })
            .fail(function () {
                window.alert(megamenuLocationDialogI18n("save_error"));
                done(false);
            });
    }

    $(document).on(
        "click",
        "h2.mega-location__title--editable .mega-location__title-cluster",
        function (e) {
            var $tgt = $(e.target);
            if ($tgt.closest(".mega-location-card-title-input").length) {
                return;
            }
            var $row = $(this).closest(".mega-location");
            if (
                !$row.find(".mega-location__title-edit-field").prop("hidden") &&
                $tgt.closest(".mega-location__title-edit-field").length
            ) {
                return;
            }
            e.preventDefault();
            megamenuLocationCardEnterTitleEdit($(this));
        }
    );

    $(document).on(
        "focusout keydown",
        ".mega-location-card-title-input",
        function (e) {
            var $input = $(this);
            if (e.type === "keydown") {
                if (e.key !== "Enter") {
                    return;
                }
                e.preventDefault();
                megamenuLocationCardCommitFromInput($input);
                return;
            }
            var $row = $input.closest(".mega-location");
            locationTitleBlurTimer = setTimeout(function () {
                locationTitleBlurTimer = null;
                if (
                    $row.find(".mega-location__title-edit-field").prop("hidden")
                ) {
                    return;
                }
                megamenuLocationCardCommitFromInput($input);
            }, 200);
        }
    );

    $(document).on(
        "click",
        ".mega-location-settings-open:not(:disabled)",
        function (e) {
            e.preventDefault();
            megamenuOpenLocationSettingsDialog($(this));
        }
    );

    $(document).on(
        "click",
        ".menu_settings.menu_settings_menu_locations.mega-location-cards-root .mega-location.postbox",
        function (e) {
            if (
                $(e.target).closest(
                    "a, button, input, select, textarea, label, " +
                        ".components-form-toggle, " +
                        ".mega-mmm-enable-toggle, " +
                        ".mega-location-settings-open, " +
                        ".megamenu-preview-open, " +
                        ".mega-location__title-cluster, " +
                        ".mega-location-card-title-input, " +
                        ".mega-location__title-edit-field, " +
                        ".mega-location__delete, " +
                        ".mega-location-delete-link"
                ).length
            ) {
                return;
            }
            e.preventDefault();
            var $btn = $(this).find(".mega-location-settings-open").first();
            if (!$btn.length) {
                return;
            }
            megamenuOpenLocationSettingsDialog($btn);
        }
    );

    $(document).on("change", "input.megamenu_enabled[data-mega-location]", function () {
        var dlg = window.megamenu_location_dialog || {};
        var $cb = $(this);
        var location = $cb.attr("data-mega-location") || "";
        var enabled = $cb.is(":checked");
        var $row = $cb.closest(".mega-location");

        if ($cb.prop("disabled")) {
            return;
        }

        if (!location || !megamenuLocationAjaxUrl()) {
            return;
        }

        $.ajax({
            url: megamenuLocationAjaxUrl(),
            type: "POST",
            dataType: "json",
            data: {
                action: dlg.toggle_location_action || "megamenu_toggle_location_mmm",
                nonce: dlg.nonce,
                location: location,
                enabled: enabled ? "1" : "0",
            },
        })
            .done(function (res) {
                if (res && res.success) {
                    megamenuApplyMmmRowState($row, enabled);
                    megamenuSyncEnabledBodyClassFromToggles();
                } else {
                    $cb.prop("checked", !enabled);
                    megamenuSyncComponentsToggleWrappers($cb.closest(".mega-mmm-enable-toggle"));
                    window.alert(
                        megamenuLocationDialogI18n("toggle_error") ||
                            "Could not update this location."
                    );
                }
            })
            .fail(function () {
                $cb.prop("checked", !enabled);
                megamenuSyncComponentsToggleWrappers($cb.closest(".mega-mmm-enable-toggle"));
                window.alert(
                    megamenuLocationDialogI18n("toggle_error") ||
                        "Could not update this location."
                );
            });
    });

    $(document).on("click", "button.mega-location-delete-link", function () {
        var dlg = window.megamenu_location_dialog || {};
        var $btn = $(this);
        if ($btn.attr("aria-disabled") === "true") {
            return;
        }
        var location =
            $btn.data("location") || $btn.attr("data-location") || "";
        if (!location || !megamenuLocationAjaxUrl() || !dlg.nonce) {
            return;
        }
        var confirmMsg =
            megamenuLocationDialogI18n("delete_confirm") ||
            "Delete this menu location?";
        if (!window.confirm(confirmMsg)) {
            return;
        }
        $btn.attr("aria-disabled", "true");
        $.ajax({
            url: megamenuLocationAjaxUrl(),
            type: "POST",
            dataType: "json",
            data: {
                action:
                    dlg.delete_location_action || "megamenu_delete_menu_location",
                nonce: dlg.nonce,
                location: location,
            },
        })
            .done(function (res) {
                if (res && res.success) {
                    var $dlg = $("#megamenu-location-settings-dialog");
                    var $form = $dlg.find(
                        "form.megamenu-location-settings-dialog-form"
                    );
                    if (
                        $dlg.hasClass("is-open") &&
                        ($form.attr("data-location") || "") === location
                    ) {
                        megamenuCloseLocationSettingsDialog();
                    }
                    var $row = $(
                        '.mega-location.postbox[data-mega-location="' +
                            location +
                            '"]'
                    ).first();
                    if ($row.length) {
                        $row.remove();
                    }
                    megamenuSyncEnabledBodyClassFromToggles();
                } else {
                    window.alert(
                        megamenuLocationDialogI18n("delete_error") ||
                            "Could not delete this menu location."
                    );
                }
            })
            .fail(function () {
                window.alert(
                    megamenuLocationDialogI18n("delete_error") ||
                        "Could not delete this menu location."
                );
            })
            .always(function () {
                $btn.removeAttr("aria-disabled");
            });
    });

    $(document).on(
        "change",
        "body.maxmegamenu-admin .components-form-toggle__input[type=\"checkbox\"]",
        function () {
            var $input = $(this);
            var $wrap = $input.closest(".components-form-toggle");
            if (!$wrap.length) {
                return;
            }
            $wrap.toggleClass("is-checked", $input.prop("checked"));
            $wrap.toggleClass(
                "is-disabled",
                !!$input.prop("disabled") ||
                    $input.attr("aria-disabled") === "true"
            );
        }
    );

    $(document).on(
        "click",
        "#megamenu-location-settings-dialog .megamenu-admin-modal__backdrop, #megamenu-location-settings-dialog .megamenu-modal-close",
        function (e) {
            e.preventDefault();
            megamenuCloseLocationSettingsDialog();
        }
    );

    $(document).on(
        "click",
        "#megamenu-location-settings-dialog .megamenu-location-settings-dialog__mode-btn--settings",
        function (e) {
            e.preventDefault();
            var $dialog = $("#megamenu-location-settings-dialog");
            if (!$dialog.hasClass("is-open")) {
                return;
            }
            megamenuLocationDialogEnterSettingsChrome($dialog);
        }
    );

    $(document).on(
        "click",
        "#megamenu-location-settings-dialog .megamenu-location-settings-dialog__mode-btn--preview",
        function (e) {
            e.preventDefault();
            var $dialog = $("#megamenu-location-settings-dialog");
            if (!$dialog.hasClass("is-open")) {
                return;
            }
            if (
                $dialog
                    .find(".megamenu-location-settings-dialog__mode-btn--preview")
                    .prop("disabled")
            ) {
                return;
            }
            megamenuLocationDialogEnsurePreviewSourceButton($dialog);
            var $src = $dialog.data("mmmPreviewSourceBtn");
            if ($src && $src.length) {
                megamenuPreparePreviewFromButton($src);
            }
            megamenuLocationDialogEnterPreviewChrome($dialog);
        }
    );

    $(document).on(
        "click",
        ".megamenu-location-settings-dialog-save",
        function (e) {
            e.preventDefault();
            megamenuSaveLocationSettingsDialog();
        }
    );

    $(document).on(
        "change input",
        "#megamenu-location-settings-dialog-body",
        function () {
            var $btn = $("#megamenu-location-settings-dialog").find(".megamenu-location-settings-dialog-save");
            var origLabel = $btn.data("mmm-save-label");
            if (origLabel) $btn.text(origLabel);
            $btn.prop("disabled", false);
        }
    );

    $(document).on(
        "click",
        "#megamenu-location-settings-dialog .megamenu-location-settings-dialog-edit-theme",
        function (e) {
            e.preventDefault();
            var $wrap = $(this).closest(
                ".megamenu-location-settings-dialog-theme-selector"
            );
            var $sel = $wrap.find("select").first();
            var url = "";
            if ($sel.length) {
                url =
                    $sel.find("option:selected").attr("data-theme-editor-url") ||
                    "";
            }
            if (url) {
                window.location.href = url;
            }
        }
    );

    $(document).on("click", "button.megamenu-preview-open:not([disabled])", function (e) {
        e.preventDefault();
        megamenuOpenLocationPreview($(this));
    });

    $(document).on(
        "click",
        "#megamenu-location-settings-dialog .megamenu-preview-dialog__viewport-btn--desktop",
        function (e) {
            e.preventDefault();
            var $dialog = $("#megamenu-location-settings-dialog");
            if (
                !$dialog.length ||
                !$dialog.hasClass("is-open") ||
                !$dialog.hasClass("megamenu-location-settings-dialog--preview-visible")
            ) {
                return;
            }
            megamenuSetPreviewViewportDesktop($dialog);
        }
    );

    $(document).on(
        "click",
        "#megamenu-location-settings-dialog .megamenu-preview-dialog__viewport-btn--mobile",
        function (e) {
            e.preventDefault();
            if ($(this).attr("aria-disabled") === "true") {
                return;
            }
            var $dialog = $("#megamenu-location-settings-dialog");
            if (
                !$dialog.length ||
                !$dialog.hasClass("is-open") ||
                !$dialog.hasClass("megamenu-location-settings-dialog--preview-visible")
            ) {
                return;
            }
            megamenuSetPreviewViewportMobile($dialog);
        }
    );

    $(document).on(
        "click",
        "#megamenu-location-settings-dialog .megamenu-preview-dialog__bg-open-picker",
        function (e) {
            e.preventDefault();
            var $dialog = $("#megamenu-location-settings-dialog");
            if (!$dialog.length || !$dialog.hasClass("is-open")) {
                return;
            }
            megamenuPreviewBgOpenPicker($dialog, true);
        }
    );

    $(document).on(
        "focusin.megamenuPreviewBgPicker",
        "#megamenu-location-settings-dialog",
        function (e) {
            var el = e.target;
            if (!el || el.tagName !== "IFRAME") {
                return;
            }
            if (!$(el).hasClass("megamenu-preview-dialog__iframe")) {
                return;
            }
            megamenuClosePreviewBgColorPickerIfOpen();
        }
    );

    $(window).on("blur.megamenuPreviewBgPicker", function () {
        megamenuClosePreviewBgColorPickerIfOpen();
    });

    $(document).on("keydown.megamenuLocationDlg", function (e) {
        if (e.key !== "Escape") {
            return;
        }
        var $t = $(e.target);
        var $cardRow = $t.closest(".mega-location");
        if (
            $t.hasClass("mega-location-card-title-input") &&
            $cardRow.length &&
            !$cardRow.find(".mega-location__title-edit-field").prop("hidden")
        ) {
            e.preventDefault();
            e.stopImmediatePropagation();
            megamenuLocationCardResetTitleEdit($cardRow);
            $cardRow.find(".mega-location__title-edit").trigger("focus");
            return;
        }
        var $dlg = $("#megamenu-location-settings-dialog");
        if (!$dlg.hasClass("is-open")) {
            return;
        }
        var closedMegaPicker = false;
        $dlg.find(".mega-color-picker-input").each(function () {
            var $inp = $(this);
            var $pc = $inp.data("picker-container-ref");
            if ($pc && $pc.length && $pc.is(":visible")) {
                if (typeof $.fn.customColorPicker === "function") {
                    $inp.customColorPicker("close");
                }
                closedMegaPicker = true;
            }
        });
        if (closedMegaPicker) {
            e.preventDefault();
            e.stopImmediatePropagation();
            return;
        }
        e.preventDefault();
        if (
            window.MegamenuAdminModalExpand &&
            typeof window.MegamenuAdminModalExpand.handleEscapeCollapseIfExpanded === "function" &&
            window.MegamenuAdminModalExpand.handleEscapeCollapseIfExpanded($dlg, e)
        ) {
            return;
        }
        megamenuCloseLocationSettingsDialog();
    });

    $(function () {
        if ($("body").hasClass("maxmegamenu-admin")) {
            megamenuSyncComponentsToggleWrappers($("body"));
        }

        $(".mega-location").each(function () {
            var $row = $(this);
            var on = $row.hasClass("mega-location-mmm-on");
            megamenuApplyMmmRowState($row, on);
        });

        var dlg = window.megamenu_location_dialog || {};
        var initial = dlg.initial_open_location;
        if (initial) {
            var $btn = $(
                '.mega-location-settings-open[data-location="' +
                    initial +
                    '"]'
            ).first();
            if ($btn.length && !$btn.is(":disabled")) {
                megamenuOpenLocationSettingsDialog($btn);
            }
        }

        var highlightLoc = dlg.highlight_new_location;
        if (highlightLoc) {
            var stripNewLocationParams = function () {
                try {
                    var url = new URL(window.location.href);
                    url.searchParams.delete("location_added");
                    url.searchParams.delete("location");
                    var next =
                        url.pathname + (url.search || "") + (url.hash || "");
                    if (
                        next !==
                        window.location.pathname +
                            window.location.search +
                            window.location.hash
                    ) {
                        window.history.replaceState({}, "", next);
                    }
                } catch (e) {
                    // IE or restricted environments: leave URL unchanged.
                }
            };

            var $card = $(
                '.mega-location.postbox[data-mega-location="' +
                    highlightLoc +
                    '"]'
            ).first();
            if ($card.length) {
                $card.addClass("mega-location--new-highlight");
                var clearHighlight = function () {
                    $card.removeClass("mega-location--new-highlight");
                    stripNewLocationParams();
                };
                $card.one(
                    "mouseenter.mmmNewLocHighlight touchstart.mmmNewLocHighlight",
                    clearHighlight
                );
            } else {
                stripNewLocationParams();
            }
        }
    });
})(jQuery);
