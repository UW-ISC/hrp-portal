/* global jQuery, ajaxurl, megamenu_location_dialog */
(function ($) {
    "use strict";

    var locationTitleBlurTimer = null;

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
        var previewOpen = $("#megamenu-preview-dialog").hasClass("is-open");
        var locOpen = $("#megamenu-location-settings-dialog").hasClass("is-open");
        var scssOpen = $("#megamenu-scss-variables-dialog").hasClass("is-open");
        $("body").toggleClass("megamenu-dialog-open", previewOpen || locOpen || scssOpen);
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
        var tpl = megamenuLocationDialogI18n("dialog_title_tpl");
        var prefix = "Location Settings:";
        if (tpl && tpl.indexOf("%s") !== -1) {
            prefix = tpl.split("%s")[0];
        }
        prefix = String(prefix).replace(/\s+$/, "");
        $tt
            .find(".megamenu-location-settings-title-prefix")
            .text(prefix ? prefix + " " : "");
        $tt.find(".megamenu-location-title").text(plainLabel || "");
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
        var $host = $dialog.find(".megamenu-admin-modal__loading-host").first();
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

    function megamenuCloseLocationSettingsDialog() {
        var $dialog = $("#megamenu-location-settings-dialog");
        if (!$dialog.length) {
            return;
        }
        if (
            window.MegamenuAdminModalExpand &&
            typeof window.MegamenuAdminModalExpand.collapseOnClose === "function"
        ) {
            window.MegamenuAdminModalExpand.collapseOnClose($dialog);
        }
        megamenuLocationDialogSetLoading($dialog, false);
        megamenuLocationDialogClearTitleBlurTimer();
        $dialog.prop("hidden", true).removeClass("is-open");
        $("#megamenu-location-settings-dialog-body").empty();
        $("#megamenu-location-settings-dialog-subtitle").text("").prop("hidden", true);
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
        $r.find(".mega-location-settings-open").attr(
            "data-location-label",
            plainLabel
        );
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

    function megamenuBindDialogFieldBehaviours($root) {
        $root
            .find('select[name$="[effect_mobile]"]')
            .off("change.mmmLocDlg")
            .on("change.mmmLocDlg", function () {
                var $row = $(this).closest("tr.mega-effect_mobile");
                if (
                    this.value === "slide_left" ||
                    this.value === "slide_right"
                ) {
                    $row.addClass("mega-is-offcanvas");
                } else {
                    $row.removeClass("mega-is-offcanvas");
                }
            })
            .trigger("change.mmmLocDlg");
    }

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
            $row.addClass("mega-location-disabled mega-location-disabled-assign-menu");
        } else if (enabled) {
            $row.addClass("mega-location-enabled");
        } else {
            $row.addClass("mega-location-disabled");
        }

        $row.find(".mega-location-settings-open").each(function () {
            var req = $(this).attr("data-requires-menu") === "1";
            $(this).prop("disabled", req);
        });
        var $prev = $row.find(".megamenu-preview-open");
        if ($prev.length) {
            var previewUrl = $prev.attr("data-preview-url") || "";
            var can =
                hasMenu && previewUrl.length > 0 && !!enabled;
            $prev.prop("disabled", !can);
        }
    }

    function megamenuOpenLocationSettingsDialog($trigger) {
        var dlg = window.megamenu_location_dialog || {};
        var location = $trigger.attr("data-location") || "";
        var label = $trigger.attr("data-location-label") || location;
        var requiresMenu = $trigger.attr("data-requires-menu") === "1";

        if (requiresMenu) {
            window.alert(
                megamenuLocationDialogI18n("assign_menu") ||
                    "Assign a menu first."
            );
            return;
        }

        megamenuMountLocationSettingsDialogFromTemplate();

        var $dialog = $("#megamenu-location-settings-dialog");
        var $body = $("#megamenu-location-settings-dialog-body");
        if (!$dialog.length || !$body.length) {
            return;
        }

        megamenuLocationDialogApplyHeadingLocationName($dialog, label);

        $dialog.data("mmmLocation", location);
        $dialog.data("mmmPlainLabel", label);

        var assignedPrefix = megamenuLocationDialogI18n("assigned_menu_prefix");
        var assignedMenu = $trigger.attr("data-assigned-menu");
        var $sub = $("#megamenu-location-settings-dialog-subtitle");
        var $panel = $dialog.find(".megamenu-admin-modal__panel");
        if (assignedMenu && assignedPrefix) {
            $sub.text(assignedPrefix + " " + assignedMenu).prop("hidden", false);
            $panel.attr(
                "aria-labelledby",
                "megamenu-location-settings-dialog-title megamenu-location-settings-dialog-subtitle"
            );
        } else {
            $sub.text("").prop("hidden", true);
            $panel.attr("aria-labelledby", "megamenu-location-settings-dialog-title");
        }

        $body.empty();
        megamenuLocationDialogSetLoading($dialog, true);

        $dialog.prop("hidden", false).addClass("is-open");
        if (
            window.MegamenuAdminModalExpand &&
            typeof window.MegamenuAdminModalExpand.restoreOnOpen === "function"
        ) {
            window.MegamenuAdminModalExpand.restoreOnOpen($dialog);
        }
        megamenuSyncBodyDialogOpenClass();

        $.post(
            megamenuLocationAjaxUrl(),
            {
                action: "megamenu_get_location_settings_html",
                nonce: dlg.nonce,
                location: location,
            }
        )
            .done(function (res) {
                if (!res || !res.success || !res.data || !res.data.html) {
                    window.alert(megamenuLocationDialogI18n("load_error"));
                    megamenuCloseLocationSettingsDialog();
                    return;
                }
                $body.html(res.data.html);
                megamenuInitLocationDialogTabs($body);
                megamenuBindDialogFieldBehaviours($body);
                megamenuSyncComponentsToggleWrappers($body);
            })
            .fail(function () {
                window.alert(megamenuLocationDialogI18n("load_error"));
                megamenuCloseLocationSettingsDialog();
            })
            .always(function () {
                megamenuLocationDialogSetLoading($dialog, false);
            });

        var $close = $dialog.find(".megamenu-modal-close");
        if ($close.length) {
            $close.trigger("focus");
        }
    }

    function megamenuSaveLocationSettingsDialog() {
        var $dialog = $("#megamenu-location-settings-dialog");
        var $form = $dialog.find("form.megamenu-location-settings-dialog-form");
        if (!$form.length) {
            return;
        }

        var location = $form.attr("data-location") || "";
        megamenuLocationDialogSetLoading($dialog, true);

        var done = function (ok) {
            megamenuLocationDialogSetLoading($dialog, false);
            if (ok) {
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
        ".megamenu-location-settings-dialog-save",
        function (e) {
            e.preventDefault();
            megamenuSaveLocationSettingsDialog();
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
