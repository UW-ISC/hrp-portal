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
        return function (...args) {
            const ctx = this;
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
        const last2 = L >= 2 ? s.slice(-2) : "";
        const last3 = L >= 3 ? s.slice(-3) : "";
        const last1 = L >= 1 ? s.slice(-1) : "";
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

    /**
     * Shared CodeMirror utility exposed on window so companion plugins can use
     * the same initialization pattern without duplicating guard and wiring code.
     *
     * init(element, onChange) — initialize wp.codeEditor on a textarea; no-ops if
     *   wp/cm_settings are absent or the element was already initialized.
     * save(element) — flush the CM value back into the hidden textarea so that
     *   jQuery $.serialize() picks up the current content.
     * refresh(element) — tell CM to remeasure itself after becoming visible.
     */
    window.megamenuCodeEditor = {
        init: function (element, onChange) {
            if (typeof wp === "undefined" || typeof wp.codeEditor === "undefined" || typeof cm_settings === "undefined") {
                return null;
            }
            const $el = $(element);
            if ($el.data("megamenuCmInit")) {
                return null;
            }
            $el.data("megamenuCmInit", true);
            const editor = wp.codeEditor.initialize($el, cm_settings);
            if (editor && editor.codemirror && typeof onChange === "function") {
                editor.codemirror.on("change", onChange);
            }
            return editor;
        },
        save: function (element) {
            const $cm = $(element).next(".CodeMirror");
            if ($cm.length && $cm[0].CodeMirror) {
                $cm[0].CodeMirror.save();
            }
        },
        refresh: function (element) {
            const $cm = $(element).next(".CodeMirror");
            if ($cm.length && $cm[0].CodeMirror) {
                $cm[0].CodeMirror.refresh();
            }
        },
    };

    /**
     * Settings object for wp.codeEditor.initialize (page.php stores wp_enqueue_code_editor() under cm_settings.codeEditor).
     *
     * @returns {object}
     */
    function megamenuGetThemeCodeEditorSettings() {
        if (typeof cm_settings === "undefined") {
            return {};
        }
        if (cm_settings.codeEditor && typeof cm_settings.codeEditor === "object") {
            return $.extend(true, {}, cm_settings.codeEditor);
        }
        return $.extend(true, {}, cm_settings);
    }

    /**
     * Read-only SCSS CodeMirror for compile error notices (select/copy allowed; editing disabled).
     */
    function megamenuInitCompileFailedScssEditor() {
        if (typeof wp === "undefined" || typeof wp.codeEditor === "undefined") {
            return;
        }
        const $ta = $("#megamenu-compile-failed-scss");
        if (!$ta.length || $ta.data("megamenuCmInit")) {
            return;
        }
        if ($ta.next(".CodeMirror").length) {
            return;
        }
        const base = megamenuGetThemeCodeEditorSettings();
        const settings = $.extend(true, {}, base, {
            codemirror: $.extend({}, base.codemirror || {}, { readOnly: true }),
        });
        wp.codeEditor.initialize($ta, settings);
        $ta.data("megamenuCmInit", true);
        window.setTimeout(function () {
            window.megamenuCodeEditor.refresh($ta[0]);
        }, CODE_MIRROR_TAB_REFRESH_MS);
    }

    function initCodeMirrorTab() {
        const $themeTextarea = $("#megamenu-theme-textarea-custom_css");
        if ($themeTextarea.length) {
            window.megamenuCodeEditor.init($themeTextarea[0], markThemeEditorDirty);
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
        const $ta = $("#megamenu-theme-textarea-custom_css");
        if ($ta.length) {
            window.megamenuCodeEditor.save($ta[0]);
        }
    }

    function megamenuSyncThemeEditorDialogBodyClass() {
        const locOpen = $("#megamenu-location-settings-dialog").hasClass("is-open");
        const scssOpen = $("#megamenu-scss-variables-dialog").hasClass("is-open");
        $("body").toggleClass("megamenu-dialog-open", locOpen || scssOpen);
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

    function markThemeEditorDirty() {
        const $btn = $("form.theme_editor").find("button#submit, input#submit");
        const origLabel = $btn.data("mmm-save-label");
        if (origLabel) {
            $btn.is("input") ? $btn.val(origLabel) : $btn.text(origLabel);
        }
        $btn.prop("disabled", false);
    }

    const COLOR_PICKER_OPTIONS = {
        defaultColor: "#DDDDDD",
        showCssVarPalette: false,
        onColorChange: function() {
            markThemeEditorDirty();
            const $btn = $(this).closest(".block").find(".mega-block-save");
            if ($btn.length) {
                const origLabel = $btn.data("mmm-save-label");
                if (origLabel) $btn.text(origLabel);
                $btn.prop("disabled", false);
            }
        },
    };

    const COLOR_PICKER_EXCLUDE_PREVIEW_BG =
        ".mega-color-picker-input:not(.megamenu-preview-dialog__bg-custom-color-input)";

    function bindColorPickersInFragment($root) {
        $(COLOR_PICKER_EXCLUDE_PREVIEW_BG, $root).customColorPicker(COLOR_PICKER_OPTIONS);
    }

    function initColorPickers() {
        $(COLOR_PICKER_EXCLUDE_PREVIEW_BG).customColorPicker(COLOR_PICKER_OPTIONS);

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

    /**
     * Initializes Custom Icon Selector on .icon_dropdown within $context, or on all such selects if $context is omitted.
     *
     * @param {JQuery} [$context] Root element (e.g. AJAX fragment); omit for full document pass.
     */
    function bindIconSelect($context) {
        const $selects =
            $context && $context.length ? $context.find(".icon_dropdown") : $(".icon_dropdown");
        $selects.each(function () {
            const $el = $(this);
            if ($el.data("mmmIconSelector")) {
                return;
            }
            $el.mmmIconSelector();
        });
    }

    function initIconSelect() {
        bindIconSelect();
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
                    ".mmm-icon-selector-dropdown, " +
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
            if (e.key !== "Escape") {
                return;
            }
            if (!$toggleRoot.find(".block.mega-open").length) {
                return;
            }
            if ($(".mmm-icon-selector-dropdown:visible").length) {
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

        $toggleRoot.on("click", ".mega-block-close", function () {
            closeToggleBarBlockPanels();
        });

        $toggleRoot.on("click", ".mega-block-save", function () {
            const $btn = $(this);
            const originalText = $btn.text();
            if (!$btn.data("mmm-save-label")) {
                $btn.data("mmm-save-label", originalText);
            }
            const settings = window.megamenu_settings || {};
            $btn.prop("disabled", true).text(settings.saving + "…");
            var savedOk = false;
            megamenuAjaxSaveThemeEditor({
                onSuccess: function () {
                    savedOk = true;
                },
                onComplete: function () {
                    if (savedOk) {
                        $btn.html('<span class="dashicons dashicons-yes" aria-hidden="true"></span> ' + (settings.saved || "Saved"));
                    } else {
                        $btn.prop("disabled", false).text(originalText);
                    }
                },
            });
        });

        $toggleRoot.on("change input", ".block-settings :input", function () {
            const $btn = $(this).closest(".block").find(".mega-block-save");
            const origLabel = $btn.data("mmm-save-label");
            if (origLabel) $btn.text(origLabel);
            $btn.prop("disabled", false);
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
                $(document).trigger("megamenu_toggle_block_opened", [$block[0]]);
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
                    bindIconSelect($response);

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
     * @param {object}   [opts]
     * @param {function} [opts.onSuccess]  Called on success (receives JSON response).
     * @param {function} [opts.onComplete] Called after success or error (always fires).
     */
    function megamenuAjaxSaveThemeEditor(opts) {
        opts = $.extend({ onSuccess: null, onComplete: null }, opts || {});

        const settings = window.megamenu_settings || {};
        const $form = $("form.theme_editor");

        if (!$form.length) {
            opts.onSuccess?.({});
            opts.onComplete?.();
            return;
        }

        $(".theme_result_message").remove();

        $.ajax({
            url:  ajaxurl,
            data: $form.serialize(),
            type: "POST",

            success: function (response) {
                if (isStructuredThemeSaveResponse(response)) {
                    if (response.success === true) {
                        opts.onSuccess?.(response);
                        return;
                    }

                    if (response.success === false) {
                        appendSubmitAfterFailMessage(
                            $("<div>").addClass("megamenu-theme-save-error-body")
                                .append($("<p>").html(settings.theme_save_error + " ").append(settings.theme_save_error_refresh))
                                .append($("<div>").html(response.data).contents())
                        );
                        megamenuInitCompileFailedScssEditor();
                        return;
                    }

                    appendSubmitAfterFailMessage(
                        $("<p>").html(settings.theme_save_error + "<br />")
                            .append(document.createTextNode(response.data !== undefined ? String(response.data) : ""))
                    );
                    return;
                }

                const rawText = typeof response === "string" ? response : String(response);
                const $err = rawText.indexOf("exhausted") >= 0
                    ? $("<p>").html(settings.theme_save_error + " ")
                        .append(settings.theme_save_error_exhausted + " ")
                        .append(settings.theme_save_error_memory_limit + " ")
                        .append(getMemoryLimitLink(settings))
                        .append("<br />")
                        .append(rawText)
                    : $("<p>").html(settings.theme_save_error + "<br />").append(rawText);
                appendSubmitAfterFailMessage($err);
            },

            error: function (xhr) {
                const $err = xhr.status === 500
                    ? $("<p>").html(settings.theme_save_error_500 + " ")
                        .append(settings.theme_save_error_memory_limit + " ")
                        .append(getMemoryLimitLink(settings))
                    : xhr.responseText === "-1"
                        ? $("<p>").html(settings.theme_save_error + " " + settings.theme_save_error_nonce_failed)
                        : null;
                if ($err) appendSubmitAfterFailMessage($err);
            },

            complete: function () {
                opts.onComplete?.();
            },
        });
    }

    const MMM_PREVIEW_AFTER_SAVE_KEY = "megamenu_theme_editor_preview_after_save";
    const MMM_PREVIEW_AFTER_SAVE_LOC_KEY = "megamenu_theme_editor_preview_after_save_location";

    function megamenuThemeEditorReadPreviewMetaRows() {
        const el = document.getElementById("megamenu-theme-editor-preview-meta");
        if (!el || !el.textContent) {
            return [];
        }
        try {
            const rows = JSON.parse(el.textContent);
            return $.isArray(rows) ? rows : [];
        } catch (e) {
            return [];
        }
    }

    function megamenuThemeEditorProxyPreviewButtonFromRow(row) {
        const attrs = {
            "data-location": row.location,
            "data-preview-url": row.preview_url,
            "data-preview-title": row.preview_title,
            "data-preview-location-label": row.location_label,
            "data-preview-assigned-menu": row.assigned_menu || "",
            "data-assigned-menu": row.assigned_menu || "",
            "data-responsive-breakpoint": String(
                row.responsive_breakpoint != null ? row.responsive_breakpoint : 0
            ),
        };
        const mid =
            row.assigned_menu_id != null ? parseInt(String(row.assigned_menu_id), 10) : 0;
        if (mid > 0) {
            attrs["data-preview-menu-id"] = String(mid);
        }
        return $("<button>", { type: "button", class: "megamenu-preview-open" }).attr(attrs);
    }

    function megamenuThemeEditorFirstPreviewableSlug(rows) {
        let i;
        for (i = 0; i < rows.length; i++) {
            const r = rows[i];
            if (r && r.previewable && r.location) {
                return r.location;
            }
        }
        return rows[0] && rows[0].location ? rows[0].location : "";
    }

    function megamenuThemeEditorStorageSet(key, value) {
        try {
            if (value === null || typeof value === "undefined") {
                window.localStorage.removeItem(key);
            } else {
                window.localStorage.setItem(key, value);
            }
        } catch (e) {}
    }

    function megamenuThemeEditorStorageGet(key) {
        try {
            return window.localStorage.getItem(key);
        } catch (e) {
            return null;
        }
    }

    function megamenuThemeEditorSyncPreviewComponentsToggle() {
        const $input = $("#megamenu-theme-save-then-preview");
        const $wrap = $input.closest(".components-form-toggle");
        if ($wrap.length) {
            $wrap.toggleClass("is-checked", !!$input.prop("checked"));
        }
    }

    function megamenuThemeEditorApplyStoredPreviewPreferences() {
        const rows = megamenuThemeEditorReadPreviewMetaRows();
        if (!rows.length) {
            return;
        }
        const $cb = $("#megamenu-theme-save-then-preview");
        const $loc = $("#megamenu-theme-save-preview-location");
        if (!$cb.length || !$loc.length) {
            return;
        }
        const storedOn = megamenuThemeEditorStorageGet(MMM_PREVIEW_AFTER_SAVE_KEY);
        if (storedOn === "1" || storedOn === "true") {
            $cb.prop("checked", true);
        } else if (storedOn === "0" || storedOn === "false") {
            $cb.prop("checked", false);
        }
        let slug = megamenuThemeEditorStorageGet(MMM_PREVIEW_AFTER_SAVE_LOC_KEY);
        let valid = false;
        let j;
        if (slug) {
            for (j = 0; j < rows.length; j++) {
                if (rows[j].location === slug) {
                    valid = true;
                    break;
                }
            }
        }
        if (!valid) {
            slug = megamenuThemeEditorFirstPreviewableSlug(rows) || rows[0].location;
        }
        $loc.val(slug);
        megamenuThemeEditorSyncPreviewComponentsToggle();
    }

    function megamenuThemeEditorOnPreviewToggleChange() {
        megamenuThemeEditorSyncPreviewComponentsToggle();
        megamenuThemeEditorStorageSet(
            MMM_PREVIEW_AFTER_SAVE_KEY,
            $("#megamenu-theme-save-then-preview").prop("checked") ? "1" : "0"
        );
    }

    function megamenuThemeEditorPersistPreviewLocation() {
        const v = $("#megamenu-theme-save-preview-location").val();
        if (v) {
            megamenuThemeEditorStorageSet(MMM_PREVIEW_AFTER_SAVE_LOC_KEY, v);
        }
    }

    function megamenuThemeEditorMaybeOpenPreviewAfterSuccess() {
        const $cb = $("#megamenu-theme-save-then-preview");
        if (!$cb.length || !$cb.prop("checked")) {
            return;
        }
        const rows = megamenuThemeEditorReadPreviewMetaRows();
        if (!rows.length) {
            return;
        }
        const slug = $("#megamenu-theme-save-preview-location").val();
        let row = null;
        let k;
        for (k = 0; k < rows.length; k++) {
            if (rows[k].location === slug) {
                row = rows[k];
                break;
            }
        }
        if (!row || !row.previewable) {
            return;
        }
        const $bpInput = $("input.mega-setting-responsive_breakpoint");
        if ($bpInput.length) {
            const bpVal = parseInt($bpInput.val(), 10);
            if (!isNaN(bpVal) && bpVal >= 0) {
                row = $.extend({}, row, { responsive_breakpoint: bpVal });
            }
        }
        const $proxy = megamenuThemeEditorProxyPreviewButtonFromRow(row);
        if (typeof window.megamenuOpenLocationPreview === "function") {
            window.megamenuOpenLocationPreview($proxy);
        }
    }

    function initThemeEditorPreviewAfterSave() {
        const rows = megamenuThemeEditorReadPreviewMetaRows();
        if (!rows.length) {
            return;
        }
        megamenuThemeEditorApplyStoredPreviewPreferences();
        $(document).on("change", "#megamenu-theme-save-then-preview", megamenuThemeEditorOnPreviewToggleChange);
        $(document).on(
            "change",
            "#megamenu-theme-save-preview-location",
            megamenuThemeEditorPersistPreviewLocation
        );
    }

    function initThemeEditorAjax() {
        initThemeEditorPreviewAfterSave();

        $(".theme_editor").on("submit", function (e) {
            e.preventDefault();
            const $form   = $(this);
            const $submit = $form.find("button#submit, input#submit");
            const isInput = $submit.is("input");
            const getLabel = () => isInput ? $submit.val() : $submit.text();
            const setLabel = (t) => isInput ? $submit.val(t) : $submit.text(t);
            const origLabel = getLabel();
            if (!$submit.data("mmm-save-label")) {
                $submit.data("mmm-save-label", origLabel);
            }
            const settings = window.megamenu_settings || {};
            $submit.prop("disabled", true);
            setLabel(settings.saving + "…");
            let succeeded = false;
            megamenuAjaxSaveThemeEditor({
                onSuccess: function () {
                    succeeded = true;
                    megamenuThemeEditorMaybeOpenPreviewAfterSuccess();
                },
                onComplete: function () {
                    if (succeeded) {
                        if (isInput) {
                            $submit.val(origLabel);
                        } else {
                            $submit.html('<span class="dashicons dashicons-yes" aria-hidden="true"></span> ' + (settings.saved || "Saved"));
                        }
                    } else {
                        setLabel(origLabel);
                        $submit.prop("disabled", false);
                    }
                },
            });
        });

        $(".theme_editor").on("change input", markThemeEditorDirty);
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
                    var tabKey = tab.getAttribute("data-tab");
                    if (tabKey) {
                        history.replaceState(null, "", "#" + tabKey.replace(/^mega-tab-content-/, ""));
                    }
                });
            });

            var startTab = current;
            var hash = window.location.hash.replace(/^#/, "");
            if (hash) {
                Array.prototype.forEach.call(tabs, function (t) {
                    if (t.getAttribute("data-tab") === "mega-tab-content-" + hash) {
                        startTab = t;
                    }
                });
            }

            if (startTab) {
                activateTab(nav, startTab, slider);
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

    function initMenuFontLibraryLink() {
        $(document).on("change", "select[data-font-library-url]", function () {
            if ($(this).val() !== "__megamenu_font_library__") {
                return;
            }
            var url = $(this).data("font-library-url");
            $(this).val("inherit");
            window.open(url, "_blank");
        });
    }

    /**
     * Theme editor "font" type: inherit / Menu Font Family shows a + button; choosing a real font keeps the select visible.
     */
    function megaThemeFontOptionSyncCollapsed($wrap) {
        const $sel = $wrap.find(".mega-theme-font-option__select");
        if (!$sel.length) {
            return;
        }
        const v = $sel.val();
        const inherit = !v || v === "inherit";
        $wrap.toggleClass("mega-theme-font-option--collapsed", inherit);
        $wrap.find(".mega-theme-font-option__reveal").attr("aria-expanded", inherit ? "false" : "true");
    }

    function initThemeEditorInheritedFontPickers() {
        $("form.theme_editor .mega-theme-font-option").each(function () {
            megaThemeFontOptionSyncCollapsed($(this));
        });

        $(document).on("click", "form.theme_editor .mega-theme-font-option__reveal", function (e) {
            e.preventDefault();
            const $wrap = $(this).closest(".mega-theme-font-option");
            $wrap.removeClass("mega-theme-font-option--collapsed");
            $(this).attr("aria-expanded", "true");
            $wrap.find(".mega-theme-font-option__select").trigger("focus");
        });

        $(document).on("change", "form.theme_editor .mega-theme-font-option__select", function () {
            megaThemeFontOptionSyncCollapsed($(this).closest(".mega-theme-font-option"));
        });

        $(document).on("blur", "form.theme_editor .mega-theme-font-option__select", function () {
            const $sel = $(this);
            const $wrap = $sel.closest(".mega-theme-font-option");
            window.setTimeout(function () {
                if ($wrap.find(":focus").length) {
                    return;
                }
                megaThemeFontOptionSyncCollapsed($wrap);
            }, 0);
        });
    }

    function init() {
        initDestructiveConfirm();
        initMobileTabSync();
        initMobileToggleSync();
        initCodeMirrorTab();
        megamenuInitCompileFailedScssEditor();
        initColorPickers();
        initThemeSelector();
        initIconSelect();
        initToggleBarDesigner();
        initThemeEditorAjax();
        initThemeNavTabSlider();
        initMegaCssTabs();
        initThemeEditorValidationScroll();
        initThemeEditorFieldValidation();
        initScssVariablesDialog();
        initMenuFontLibraryLink();
        initThemeEditorInheritedFontPickers();
    }

    init();
});
