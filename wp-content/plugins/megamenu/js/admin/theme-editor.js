/*global ajaxurl, $, jQuery, megamenu_settings, wp, cm_settings*/

/**
 * Max Mega Menu theme editor and related admin screens (maxmegamenu_*):
 * theme form (AJAX save, validation), horizontal mega-tabs + underline slider,
 * toggle bar designer (sortable blocks, AJAX block insert), and shared settings UI.
 */
jQuery(function ($) {
    "use strict";

    /** Delay so CodeMirror can measure layout after the custom styling tab is shown. */
    const CODE_MIRROR_TAB_REFRESH_MS = 160;

    /** Scroll handler: blur focused theme inputs only after scrolling settles. */
    const THEME_EDITOR_SCROLL_BLUR_MS = 200;

    /**
     * Replacement for deprecated jQuery.isNumeric() (removed in jQuery 4).
     *
     * @param {*} value
     * @returns {boolean}
     */
    function megamenuIsNumeric(value) {
        const type = typeof value;
        if (type === "number") {
            return Number.isFinite(value);
        }
        if (type === "string") {
            const trimmed = value.trim();
            if (trimmed === "") {
                return false;
            }
            const num = Number(trimmed);
            return !Number.isNaN(num) && Number.isFinite(num);
        }
        return false;
    }

    function debounce(fn, waitMs) {
        let t = null;
        return function () {
            const ctx = this;
            const args = arguments;
            window.clearTimeout(t);
            t = window.setTimeout(function () {
                fn.apply(ctx, args);
            }, waitMs);
        };
    }

    /**
     * @param {*} response
     * @returns {boolean} True if this looks like wp_send_json / admin-ajax JSON shape.
     */
    function isStructuredThemeSaveResponse(response) {
        return response !== null && typeof response === "object" && "success" in response;
    }

    /**
     * Theme editor "px" field rules (matches previous inline validation).
     *
     * @param {string} value
     * @returns {boolean} True if value is acceptable for a px-type field.
     */
    function isMegaThemePxValueValid(value) {
        if (value == 0 || value === "normal" || value === "inherit") {
            return true;
        }
        const s = String(value);
        const L = s.length;
        const last2 = L >= 2 ? s.substr(L - 2) : "";
        const last3 = L >= 3 ? s.substr(L - 3) : "";
        const last1 = L >= 1 ? s.substr(L - 1) : "";
        if (
            last2 === "px" ||
            last2 === "em" ||
            last2 === "vh" ||
            last2 === "vw" ||
            last2 === "pt" ||
            s === "max-content" ||
            last3 === "rem" ||
            last1 === "%"
        ) {
            return true;
        }
        return false;
    }

    /**
     * @param {string} value
     * @returns {boolean} True if value is a finite integer string/number.
     */
    function isMegaThemeIntValueValid(value) {
        return Math.floor(Number(value)) === Number(value);
    }

    /**
     * @param {string} validation
     * @param {string} value
     * @returns {boolean} True if the value fails validation for that rule.
     */
    function megaThemeFieldValidationFails(validation, value) {
        if (validation === "int") {
            return !isMegaThemeIntValueValid(value);
        }
        if (validation === "px") {
            return !isMegaThemePxValueValid(value);
        }
        if (validation === "float") {
            return !megamenuIsNumeric(value);
        }
        return false;
    }

    function getMemoryLimitLink(settings) {
        return $("<a>")
            .attr("href", settings.increase_memory_limit_url)
            .html(settings.increase_memory_limit_anchor_text);
    }

    function appendSubmitAfterFailMessage($p) {
        const $wrap = $("<div>")
            .addClass("notice notice-error is-dismissible theme_result_message")
            .append($p);
        $(".megamenu_submit").after($wrap);
    }

    function initDestructiveConfirm() {
        $(document).on("click", ".megamenu-destructive-confirm", function (e) {
            const settings = window.megamenu_settings || {};
            const message =
                settings.confirm_destructive_action ||
                settings.confirm ||
                "Are you sure?";
            if (!window.confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    }

    function syncMobileMenuTabFromBreakpoint() {
        const v = $("input.mega-setting-responsive_breakpoint").val();
        const disabled = v === "0px" || v === "0";
        $(".mega-tab-content-mobile_menu").toggleClass("mega-mobile-disabled", disabled);
    }

    function initMobileTabSync() {
        syncMobileMenuTabFromBreakpoint();
        $("input.mega-setting-responsive_breakpoint").on("keyup", syncMobileMenuTabFromBreakpoint);
    }

    function syncMobileToggleTabDisabled() {
        const on = $('input[name="settings[disable_mobile_toggle]"]').is(":checked");
        $(".mega-tab-content-mobile_menu").toggleClass("mega-toggle-disabled", on);
    }

    function initMobileToggleSync() {
        syncMobileToggleTabDisabled();
        $('input[name="settings[disable_mobile_toggle]"]').on("change", syncMobileToggleTabDisabled);
    }

    function syncEffectMobileOffcanvasRow($select) {
        const v = $select.val();
        const offcanvas = v === "slide_left" || v === "slide_right";
        $select.closest("tr.mega-effect_mobile").toggleClass("mega-is-offcanvas", offcanvas);
    }

    function initEffectMobileOffcanvas() {
        $('select[name$="[effect_mobile]"]')
            .each(function () {
                syncEffectMobileOffcanvasRow($(this));
            })
            .on("change", function () {
                syncEffectMobileOffcanvasRow($(this));
            });
    }

    function initCodeMirrorTab() {
        if (typeof wp === "undefined" || typeof wp.codeEditor === "undefined" || typeof cm_settings === "undefined") {
            return;
        }
        const $themeTextarea = $("#megamenu-theme-textarea-custom_css");
        if ($themeTextarea.length) {
            wp.codeEditor.initialize($themeTextarea, cm_settings);
        }

        $('[data-tab="mega-tab-content-custom_styling"]').on("click", function () {
            window.setTimeout(function () {
                $(".mega-tab-content-custom_styling")
                    .find(".CodeMirror")
                    .each(function (key, value) {
                        value.CodeMirror.refresh();
                    });
            }, CODE_MIRROR_TAB_REFRESH_MS);
        });
    }

    /**
     * Sync custom styling CodeMirror content into the underlying textarea before serializing the theme form.
     */
    function megamenuSaveCustomCssFromCodeMirror() {
        const $cm = $(".mega-tab-content-custom_styling .CodeMirror");
        if ($cm.length && $cm[0].CodeMirror) {
            $cm[0].CodeMirror.save();
        }
    }

    function megamenuSyncThemeEditorDialogBodyClass() {
        const previewOpen = $("#megamenu-preview-dialog").hasClass("is-open");
        const locOpen = $("#megamenu-location-settings-dialog").hasClass("is-open");
        const scssOpen = $("#megamenu-scss-variables-dialog").hasClass("is-open");
        $("body").toggleClass("megamenu-dialog-open", previewOpen || locOpen || scssOpen);
    }

    function megamenuMountScssVariablesDialogFromTemplate() {
        if (document.getElementById("megamenu-scss-variables-dialog")) {
            return;
        }
        const tpl = document.getElementById("megamenu-scss-variables-dialog-template");
        if (!tpl || !tpl.textContent) {
            return;
        }
        const wrap = document.createElement("div");
        wrap.innerHTML = tpl.textContent.trim();
        const dlg = wrap.querySelector("#megamenu-scss-variables-dialog");
        if (dlg) {
            document.body.appendChild(dlg);
        }
    }

    function megamenuScssVariablesSetLoading($dialog, loading) {
        const $host = $dialog.find(".megamenu-admin-modal__loading-host").first();
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

    function megamenuCloseScssVariablesDialog() {
        const $dialog = $("#megamenu-scss-variables-dialog");
        if (!$dialog.length) {
            return;
        }
        if (
            window.MegamenuAdminModalExpand &&
            typeof window.MegamenuAdminModalExpand.collapseOnClose === "function"
        ) {
            window.MegamenuAdminModalExpand.collapseOnClose($dialog);
        }
        megamenuScssVariablesSetLoading($dialog, false);
        $("#megamenu-scss-variables-list").empty();
        $(".megamenu-scss-variables-dialog__error").prop("hidden", true).text("");
        $dialog.prop("hidden", true).removeClass("is-open");
        megamenuSyncThemeEditorDialogBodyClass();
    }

    function megamenuBuildScssVariablesAjaxPayload() {
        const $form = $("form.theme_editor");
        if (!$form.length) {
            return "";
        }
        megamenuSaveCustomCssFromCodeMirror();
        let serialized = $form.serialize();
        serialized = serialized.replace(/(^|&)action=[^&]*/, "");
        return serialized + "&action=megamenu_get_theme_scss_variables";
    }

    function initScssVariablesDialog() {
        megamenuMountScssVariablesDialogFromTemplate();

        const $dialog = $("#megamenu-scss-variables-dialog");
        const $list = $("#megamenu-scss-variables-list");
        const $err = $(".megamenu-scss-variables-dialog__error");
        const settings = window.megamenu_settings || {};

        if (!$dialog.length || !$list.length) {
            return;
        }

        $(document).on("click", "#megamenu-open-scss-variables", function (e) {
            e.preventDefault();
            megamenuSaveCustomCssFromCodeMirror();
            $list.empty();
            $err.prop("hidden", true).text("");

            megamenuScssVariablesSetLoading($dialog, true);

            $dialog.prop("hidden", false).addClass("is-open");
            megamenuSyncThemeEditorDialogBodyClass();
            if (window.MegamenuAdminModalExpand && typeof window.MegamenuAdminModalExpand.restoreOnOpen === "function") {
                window.MegamenuAdminModalExpand.restoreOnOpen($dialog);
            }
            $dialog.find(".megamenu-admin-modal__panel").trigger("focus");

            $.ajax({
                url: typeof ajaxurl !== "undefined" ? ajaxurl : "",
                type: "POST",
                data: megamenuBuildScssVariablesAjaxPayload(),
                dataType: "json",
            })
                .done(function (response) {
                    megamenuScssVariablesSetLoading($dialog, false);
                    if (!response || response.success !== true) {
                        let msg = settings.scss_vars_error || "";
                        if (response && response.data) {
                            if (typeof response.data === "string") {
                                msg = response.data;
                            } else if (response.data.message) {
                                msg = String(response.data.message);
                            }
                        }
                        $err.text(msg).prop("hidden", false);
                        return;
                    }
                    if (!response.data || !response.data.variables) {
                        $err.text(settings.scss_vars_error || "").prop("hidden", false);
                        return;
                    }
                    const vars = response.data.variables;
                    const keys = Object.keys(vars).sort(function (a, b) {
                        return a.localeCompare(b);
                    });
                    keys.forEach(function (key) {
                        $list.append($("<dt/>").text("$" + key));
                        $list.append($("<dd/>").text(String(vars[key])));
                    });
                })
                .fail(function () {
                    megamenuScssVariablesSetLoading($dialog, false);
                    $err.text(settings.scss_vars_error || "").prop("hidden", false);
                });
        });

        $dialog.on(
            "click",
            ".megamenu-admin-modal__backdrop, .megamenu-admin-modal__header .megamenu-modal-close",
            function (e) {
                e.preventDefault();
                megamenuCloseScssVariablesDialog();
            }
        );

        $(document).on("keydown.megamenuScssVars", function (e) {
            if (e.key !== "Escape") {
                return;
            }
            if ($dialog.hasClass("is-open")) {
                megamenuCloseScssVariablesDialog();
            }
        });
    }

    const COLOR_PICKER_OPTIONS = {
        defaultColor: "#DDDDDD",
        showCssVarPalette: false,
    };

    function bindColorPickersInFragment($root) {
        $(".mega-color-picker-input", $root).customColorPicker(COLOR_PICKER_OPTIONS);
    }

    function initColorPickers() {
        $(".mega-color-picker-input").customColorPicker(COLOR_PICKER_OPTIONS);

        $(".mega-copy_color").on("click", function () {
            const from = $(this).prev().find(".mega-color-picker-input").customColorPicker("get");
            const to = $(this).next().find(".mega-color-picker-input");
            to.customColorPicker("set", from);
        });
    }

    function initThemeSelector() {
        const $themeSelector = $("#theme_selector");

        $themeSelector.on("change", function () {
            const url = $(this).val();
            if (url) {
                window.location.assign(url);
            }
            return false;
        });

        const $titleInput = $('input[name="settings[title]"]');
        if ($titleInput.length && $themeSelector.length) {
            let selectedOptionSuffix = "";
            const selectedText = $themeSelector.find("option:selected").text();
            const suffixMatch = selectedText.match(/\s\([^)]*\)\s*$/);
            if (suffixMatch) {
                selectedOptionSuffix = suffixMatch[0];
            }

            $titleInput.on("input change", function () {
                const updatedTitle = $(this).val().trim();
                const displayTitle = updatedTitle.length ? updatedTitle : selectedText.replace(/\s\([^)]*\)\s*$/, "");
                $themeSelector.find("option:selected").text(displayTitle + selectedOptionSuffix);
            });
        }
    }

    function megaIconDropdownFormat(icon) {
        const cls = icon && icon.element && $(icon.element).attr("data-class");
        if (!cls) {
            return "";
        }
        return '<i class="' + cls + '"></i>';
    }

    function getIconSelect2Options() {
        return {
            containerCssClass: "tpx-select2-container select2-container-sm",
            dropdownCssClass: "tpx-select2-drop",
            minimumResultsForSearch: -1,
            formatResult: megaIconDropdownFormat,
            formatSelection: megaIconDropdownFormat,
        };
    }

    /**
     * Initializes Select2 on .icon_dropdown within $context, or on all such selects if $context is omitted.
     *
     * @param {JQuery} [$context] Root element (e.g. AJAX fragment); omit for full document pass.
     */
    function bindIconSelect2($context) {
        const $selects =
            $context && $context.length ? $context.find(".icon_dropdown") : $(".icon_dropdown");
        $selects.each(function () {
            const $el = $(this);
            if ($el.data("select2")) {
                return;
            }
            $el.select2(getIconSelect2Options());
        });
    }

    function initIconSelect2() {
        bindIconSelect2();
    }

    /**
     * Toggle bar designer: sortable blocks, settings panel, add block via AJAX.
     */
    function initToggleBarDesigner() {
        if (!$("#toggle-block-selector").length) {
            return;
        }

        const $toggleRoot = $(".mega-toggle_blocks");

        /**
         * Close any open toggle block settings panel (theme editor).
         */
        function closeToggleBarBlockPanels() {
            $toggleRoot.find(".block").removeClass("mega-open");
            $toggleRoot.find(".block-settings").hide();
        }

        /**
         * True if the event target is inside UI that is rendered outside `.mega-toggle_blocks`
         * (so a document mousedown must not dismiss the panel).
         *
         * @param {JQuery} $target
         * @returns {boolean}
         */
        function isToggleBarDismissSuppressedForTarget($target) {
            return (
                $target.closest(
                    ".select2-container--open, .select2-dropdown, " +
                        ".iris-picker, .iris-border, .wp-picker-holder, " +
                        ".mega-color-picker-container, " +
                        ".media-modal, .media-modal-backdrop, .ui-dialog"
                ).length > 0
            );
        }

        $(document).on("mousedown.toggleBarDesignerDismiss", function (e) {
            if (!$toggleRoot.find(".block.mega-open").length) {
                return;
            }
            const $t = $(e.target);
            if ($t.closest(".mega-toggle_blocks .block.mega-open").length) {
                return;
            }
            if (isToggleBarDismissSuppressedForTarget($t)) {
                return;
            }
            closeToggleBarBlockPanels();
        });

        $(document).on("keydown.toggleBarDesignerDismiss", function (e) {
            if (e.key !== "Escape" && e.keyCode !== 27) {
                return;
            }
            if (!$toggleRoot.find(".block.mega-open").length) {
                return;
            }
            if ($(".select2-container--open").length) {
                return;
            }
            if ($(".mega-color-picker-container:visible").length) {
                return;
            }
            e.preventDefault();
            closeToggleBarBlockPanels();
        });

        function reindexToggleBarBlocks() {
            let i = 0;
            $(".mega-blocks .block").each(function () {
                i += 1;
                const $block = $(this);

                $block.find("input, select, textarea").each(function () {
                    const $field = $(this);
                    const name = $field.attr("name");
                    if (typeof name !== "undefined") {
                        $field.attr("name", name.replace(/\[\d+\]/g, "[" + i + "]"));
                    }
                });

                $block.find("input.align").each(function () {
                    const $align = $(this);
                    if ($block.parent().hasClass("mega-right")) {
                        $align.attr("value", "right");
                    } else if ($block.parent().hasClass("mega-center")) {
                        $align.attr("value", "center");
                    } else {
                        $align.attr("value", "left");
                    }
                });
            });
        }

        const sortableBase = {
            forcePlaceholderSize: false,
            items: ".block",
            stop: reindexToggleBarBlocks,
        };

        $(".mega-blocks .mega-left").sortable(
            $.extend({}, sortableBase, {
                connectWith: ".mega-blocks .mega-right, .mega-blocks .mega-center",
            })
        );
        $(".mega-blocks .mega-right").sortable(
            $.extend({}, sortableBase, {
                connectWith: ".mega-blocks .mega-left, .mega-blocks .mega-center",
            })
        );
        $(".mega-blocks .mega-center").sortable(
            $.extend({}, sortableBase, {
                connectWith: ".mega-blocks .mega-left, .mega-blocks .mega-right",
            })
        );

        $toggleRoot.on("click", ".mega-delete", function (e) {
            e.preventDefault();
            $(this).closest(".block").remove();
            reindexToggleBarBlocks();
        });

        $toggleRoot.on("click", ".block-title", function (e) {
            e.preventDefault();
            e.stopPropagation();

            const $block = $(this).closest(".block");
            const $settings = $block.find(".block-settings");

            $toggleRoot.find(".block").removeClass("mega-open");

            if ($settings.is(":visible")) {
                $block.removeClass("mega-open");
                $settings.hide();
            } else {
                $toggleRoot.find(".block-settings").hide();
                $block.addClass("mega-open");
                $settings.show();
            }
        });

        $("#toggle-block-selector").on("change", function () {
            const $selected = $("#toggle-block-selector").find(":selected");
            const val = $selected.attr("value");

            if (val === "title") {
                return;
            }

            const settings = window.megamenu_settings || {};

            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: "mm_get_toggle_block_" + val,
                    _wpnonce: settings.edit_nonce || "",
                },
                cache: false,
                success: function (response) {
                    const $response = $(response);

                    bindColorPickersInFragment($response);
                    bindIconSelect2($response);

                    $(".mega-blocks .mega-left").append($response);
                    reindexToggleBarBlocks();
                    $("#toggle-block-selector").val("title");
                    $("body").trigger("toggle_block_content_loaded");
                },
            });
        });
    }

    /**
     * AJAX save theme editor form (same payload as Save).
     *
     * @param {object} opts
     * @param {JQuery} [opts.$busyPreviewButton] If set, only this control shows busy (not the Save button).
     * @param {boolean} [opts.showSuccessBanner=true] Append saved message next to Save on success.
     * @param {function} [opts.onSuccess] Called after a successful save (receives JSON message object).
     */
    function megamenuAjaxSaveThemeEditor(opts) {
        opts = $.extend(
            {
                $busyPreviewButton: null,
                showSuccessBanner: true,
                onSuccess: null,
            },
            opts || {}
        );
        const settings = window.megamenu_settings || {};
        const $form = $("form.theme_editor");
        if (!$form.length) {
            if (opts.onSuccess) {
                opts.onSuccess({});
            }
            return;
        }
        $(".theme_result_message").remove();
        const $submit = $form.find("button#submit, input#submit");
        const isInputSubmit = $submit.is("input");
        const getSubmitLabel = function () {
            return isInputSubmit ? $submit.val() : $submit.text();
        };
        const setSubmitLabel = function (text) {
            if (isInputSubmit) {
                $submit.val(text);
            } else {
                $submit.text(text);
            }
        };
        const original_value = getSubmitLabel();
        const $previewBusy =
            opts.$busyPreviewButton && opts.$busyPreviewButton.length ? opts.$busyPreviewButton : null;

        function setBusy(on) {
            if ($previewBusy) {
                $previewBusy.prop("disabled", !!on).toggleClass("is-busy", !!on);
            } else if (on) {
                $submit.addClass("is-busy");
                setSubmitLabel(settings.saving + "…");
            }
        }

        function clearBusy() {
            if ($previewBusy) {
                $previewBusy.prop("disabled", false).removeClass("is-busy");
            } else {
                $submit.removeClass("is-busy");
                setSubmitLabel(original_value);
            }
        }

        setBusy(true);

        $.ajax({
            url: ajaxurl,
            data: $form.serialize(),
            type: "POST",
            success: function (response) {
                if (isStructuredThemeSaveResponse(response)) {
                    if (response.success === true) {
                        if (opts.showSuccessBanner) {
                            const success = $("<p>").addClass("saved theme_result_message");
                            const icon = $("<span>").addClass("dashicons dashicons-yes");
                            success.append(icon).append(document.createTextNode(response.data));
                            $(".megamenu_submit").append(success);
                        }
                        if (opts.onSuccess) {
                            opts.onSuccess(response);
                        }
                        return;
                    }
                    if (response.success === false) {
                        const errScss = $("<p>")
                            .html(settings.theme_save_error + " ")
                            .append(settings.theme_save_error_refresh)
                            .append("<br /><br />")
                            .append(response.data);
                        appendSubmitAfterFailMessage(errScss);
                        return;
                    }
                    const errUnexpected = $("<p>")
                        .html(settings.theme_save_error + "<br />")
                        .append(
                            document.createTextNode(
                                typeof response.data !== "undefined" ? String(response.data) : ""
                            )
                        );
                    appendSubmitAfterFailMessage(errUnexpected);
                    return;
                }

                const rawText = typeof response === "string" ? response : String(response);
                let errOther;
                if (rawText.indexOf("exhausted") >= 0) {
                    errOther = $("<p>")
                        .html(settings.theme_save_error + " ")
                        .append(settings.theme_save_error_exhausted + " ")
                        .append(settings.theme_save_error_memory_limit + " ")
                        .append(getMemoryLimitLink(settings))
                        .append("<br />")
                        .append(rawText);
                } else {
                    errOther = $("<p>")
                        .html(settings.theme_save_error + "<br />")
                        .append(rawText);
                }
                appendSubmitAfterFailMessage(errOther);
            },
            error: function (xhr) {
                let error;
                if (xhr.status === 500) {
                    error = $("<p>")
                        .html(settings.theme_save_error_500 + " ")
                        .append(settings.theme_save_error_memory_limit + " ")
                        .append(getMemoryLimitLink(settings));
                } else if (xhr.responseText === "-1") {
                    error = $("<p>")
                        .html(settings.theme_save_error + " " + settings.theme_save_error_nonce_failed);
                }
                if (error) {
                    appendSubmitAfterFailMessage(error);
                }
            },
            complete: function () {
                clearBusy();
            },
        });
    }

    function initThemeEditorAjax() {
        window.megamenuSaveThemeEditorThenPreview = function ($btn) {
            megamenuAjaxSaveThemeEditor({
                $busyPreviewButton: $btn,
                showSuccessBanner: false,
                onSuccess: function () {
                    if (typeof window.megamenuOpenLocationPreview === "function") {
                        window.megamenuOpenLocationPreview($btn);
                    }
                },
            });
        };

        $(".theme_editor").on("submit", function (e) {
            e.preventDefault();
            megamenuAjaxSaveThemeEditor({
                showSuccessBanner: true,
                onSuccess: null,
            });
        });

        $(".theme_editor").on("change", function () {
            $(".theme_result_message").css("visibility", "hidden");
        });
    }

    /**
     * Horizontal theme editor tabs (button.mega-tab + .nav-tab-slider underline) and matching .mega-tab-content panels.
     * This script is only enqueued on maxmegamenu admin screens that load the theme editor.
     */
    function initThemeNavTabSlider() {
        const boundNavs = [];
        let resizeScheduled = false;

        function getPanelContainer(nav) {
            return nav.parentElement || document.body;
        }

        function queryThemeTabs(nav) {
            return nav.querySelectorAll("button.mega-tab");
        }

        function queryThemeNavWrappers(scope) {
            const root = scope && scope.nodeType === 1 ? scope : document;
            return root.querySelectorAll(".megamenu-nav-tab-wrapper");
        }

        function positionSlider(nav, activeTab, slider) {
            if (!slider || !activeTab || !nav) {
                return;
            }
            const navRect = nav.getBoundingClientRect();
            const tabRect = activeTab.getBoundingClientRect();
            if (navRect.width === 0 || tabRect.width === 0) {
                return;
            }
            slider.style.width = tabRect.width + "px";
            slider.style.left = tabRect.left - navRect.left + nav.scrollLeft + "px";
        }

        function activateTab(nav, tab, slider) {
            const contentClass = tab.getAttribute("data-tab");
            if (!contentClass) {
                return;
            }

            queryThemeTabs(nav).forEach(function (t) {
                t.classList.remove("nav-tab-active", "is-active");
                if (t.hasAttribute("aria-selected")) {
                    t.setAttribute("aria-selected", "false");
                }
            });
            tab.classList.add("is-active", "nav-tab-active");
            tab.setAttribute("aria-selected", "true");

            const container = getPanelContainer(nav);
            container.querySelectorAll(".mega-tab-content").forEach(function (panel) {
                const show = panel.classList.contains(contentClass);
                panel.style.display = show ? "block" : "none";
            });

            positionSlider(nav, tab, slider);
        }

        function repositionAll() {
            const stillConnected = boundNavs.filter(function (nav) {
                return nav.isConnected;
            });
            boundNavs.length = 0;
            stillConnected.forEach(function (nav) {
                boundNavs.push(nav);
            });
            boundNavs.forEach(function (nav) {
                const active =
                    nav.querySelector("button.mega-tab.is-active") ||
                    nav.querySelector("button.mega-tab.nav-tab-active");
                const slider = nav.querySelector(".nav-tab-slider");
                if (active && slider) {
                    positionSlider(nav, active, slider);
                }
            });
        }

        window.addEventListener("resize", function () {
            if (resizeScheduled) {
                return;
            }
            resizeScheduled = true;
            window.requestAnimationFrame(function () {
                resizeScheduled = false;
                repositionAll();
            });
        });

        function bindNav(nav) {
            if (!nav || nav.getAttribute("data-megamenu-tab-slider-bound") === "1") {
                return;
            }

            const slider = nav.querySelector(".nav-tab-slider");
            const tabs = queryThemeTabs(nav);
            if (!tabs.length) {
                return;
            }

            nav.setAttribute("data-megamenu-tab-slider-bound", "1");
            boundNavs.push(nav);

            const current =
                nav.querySelector("button.mega-tab.is-active") || nav.querySelector("button.mega-tab.nav-tab-active");

            Array.prototype.forEach.call(tabs, function (tab) {
                tab.addEventListener("click", function (e) {
                    e.preventDefault();
                    activateTab(nav, tab, slider);
                });
            });

            if (current) {
                activateTab(nav, current, slider);
            } else {
                activateTab(nav, tabs[0], slider);
            }
        }

        function init(root) {
            const list = queryThemeNavWrappers(root && root.nodeType === 1 ? root : document);
            Array.prototype.forEach.call(list, bindNav);
        }

        function refresh(root) {
            if (!root || root.nodeType !== 1) {
                return;
            }
            let nav = null;
            if (root.classList.contains("megamenu-nav-tab-wrapper")) {
                nav = root;
            } else {
                nav = root.querySelector(".megamenu-nav-tab-wrapper");
            }
            if (!nav) {
                return;
            }
            const active =
                nav.querySelector("button.mega-tab.is-active") || nav.querySelector("button.mega-tab.nav-tab-active");
            const slider = nav.querySelector(".nav-tab-slider");
            if (active && slider) {
                positionSlider(nav, active, slider);
            }
        }

        window.megamenuNavTabSlider = {
            init: init,
            refresh: refresh,
        };

        init(document);
    }

    function initMegaCssTabs() {
        $("#mega_css").on("change", function () {
            const $select = $(this);
            const selected = $select.val();
            $select.next().children().hide();
            $select.next().children("." + selected).show();
        });
    }

    function initThemeEditorValidationScroll() {
        const blurFocusedThemeInputs = debounce(function () {
            $(".theme_editor input:focus").trigger("blur");
        }, THEME_EDITOR_SCROLL_BLUR_MS);
        $(window).on("scroll", blurFocusedThemeInputs);
    }

    function initThemeEditorFieldValidation() {
        $("form.theme_editor label[data-validation]").each(function () {
            const $label = $(this);
            const validation = $label.attr("data-validation");
            const error_message = $label.siblings(".mega-validation-message-" + $label.attr("class"));
            const $input = $label.find("input");

            $input.on("blur", function () {
                const value = $(this).val();

                if ($label.hasClass("mega-flyout_width") && value === "auto") {
                    $label.removeClass("mega-error");
                    error_message.hide();
                    return;
                }

                if (megaThemeFieldValidationFails(validation, value)) {
                    $label.addClass("mega-error");
                    error_message.show();
                } else {
                    $label.removeClass("mega-error");
                    error_message.hide();
                }
            });
        });
    }

    function init() {
        initDestructiveConfirm();
        initMobileTabSync();
        initMobileToggleSync();
        initEffectMobileOffcanvas();
        initCodeMirrorTab();
        initColorPickers();
        initThemeSelector();
        initIconSelect2();
        initToggleBarDesigner();
        initThemeEditorAjax();
        initThemeNavTabSlider();
        initMegaCssTabs();
        initThemeEditorValidationScroll();
        initThemeEditorFieldValidation();
        initScssVariablesDialog();
    }

    init();
});
