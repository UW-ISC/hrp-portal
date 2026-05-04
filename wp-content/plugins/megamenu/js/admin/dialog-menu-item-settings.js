/*global console,ajaxurl,$,jQuery,megamenu,document,window,bstw,alert,wp,this,acf*/
/**
 * Max Mega Menu — menu item settings modal ($.fn.megaMenu, grid, builder).
 */
(function($) {
    "use strict";

    /** Root element for the Appearance > Menus menu-item settings modal (cloned from inline template). */
    var MENU_ITEM_DIALOG_SEL = ".megamenu-admin-modal.megamenu-menu-item-dialog";

    /**
     * Clones the menu-item modal from the inline HTML template into document.body if not already present.
     */
    function megamenuMountMenuItemDialogFromTemplate() {
        if (document.querySelector(MENU_ITEM_DIALOG_SEL)) {
            return;
        }
        var tpl = document.getElementById("mmm-mega-menu-dialog-template");
        if (!tpl || !tpl.textContent) {
            return;
        }
        var wrap = document.createElement("div");
        wrap.innerHTML = tpl.textContent.trim();
        var dlg = wrap.querySelector(MENU_ITEM_DIALOG_SEL);
        if (dlg) {
            document.body.appendChild(dlg);
        }
    }

    /**
     * Opens the menu item Mega Menu modal for the given menu_item_id (Appearance > Menus).
     *
     * @param {{ menu_item_id: number }} options
     */
    $.fn.megaMenu = function(options) {

        var panel = $("<div />");

        panel.settings = options;

        /**
         * Logs AJAX outcomes to the console (avoid logging undefined when the server sends success with empty data).
         * @param {*} message Parsed JSON from admin-ajax, or a fallback string on parse errors.
         * @param {string} [contextLabel] Short label for the operation (shown in the log prefix).
         */
        panel.log = function(message, contextLabel) {
            var prefix = "[Max Mega Menu]";
            var ctx = contextLabel ? " " + contextLabel : "";
            var saveOk = (typeof megamenu !== "undefined" && megamenu.console_save_ok) ? megamenu.console_save_ok : "Saved successfully.";
            var requestFailed = (typeof megamenu !== "undefined" && megamenu.console_request_failed) ? megamenu.console_request_failed : "Request failed.";
            var nonceFailed = (typeof megamenu !== "undefined" && megamenu.nonce_check_failed) ? megamenu.nonce_check_failed : requestFailed;

            if (!window.console || !console.log) {
                if (message && message.success !== true) {
                    alert(message.data != null && message.data !== "" ? message.data : nonceFailed);
                }
                return;
            }

            var isObject = message !== null && typeof message === "object" && !Array.isArray(message);
            var isSuccess = isObject && message.success === true;
            var data = isObject ? message.data : message;
            var hasPayload = data !== null && data !== undefined && data !== "" && data !== false;

            if (isSuccess) {
                if (hasPayload) {
                    console.log(prefix + ctx + ":", data);
                } else {
                    console.log(prefix + ctx + ":", saveOk);
                }
            } else {
                var detail = requestFailed;
                if (isObject && hasPayload) {
                    detail = data;
                } else if (typeof message === "string" || typeof message === "number") {
                    detail = message === "0" || message === 0 || message === "-1" ? nonceFailed : String(message);
                } else if (!isObject && message != null) {
                    detail = String(message);
                }
                if (console.warn) {
                    console.warn(prefix + ctx + ":", detail);
                } else {
                    console.log(prefix + ctx + ":", detail);
                }
            }

            if (!isSuccess) {
                var alertMsg = nonceFailed;
                if (isObject && hasPayload) {
                    alertMsg = data;
                } else if (typeof message === "string" && message !== "0" && message !== "-1") {
                    alertMsg = message;
                }
                alert(alertMsg);
            }
        };

        /**
         * Opens the modal shell, loads tab HTML via megamenu_get_dialog_html, wires tabs/close/Escape,
         * and tracks per-tab unsaved changes until the dialog closes.
         */
        panel.init = function() {

            var dirtyTabs = {};

            /** Marks a settings tab as having unsaved edits (form change). */
            var markTabDirty = function(tabId) {
                dirtyTabs[tabId] = true;
            };

            /** Clears dirty state and the warning UI for one tab (after successful save). */
            var clearTabDirty = function(tabId) {
                delete dirtyTabs[tabId];
                $(MENU_ITEM_DIALOG_SEL + " .megamenu-dialog-tablist button.megamenu-dialog-tab[data-tab=\"" + tabId + "\"]").removeClass("mm-tab-dirty-warn").find(".mm-tab-dirty-indicator").remove();
            };

            /** Resets all tab dirty state when the dialog closes. */
            var clearAllDirtyTabs = function() {
                dirtyTabs = {};
                $(MENU_ITEM_DIALOG_SEL + " .megamenu-dialog-tablist button.megamenu-dialog-tab").removeClass("mm-tab-dirty-warn").find(".mm-tab-dirty-indicator").remove();
            };

            /** Returns the active vertical tab id from `data-tab`, or null. */
            var getActiveTabId = function() {
                var $a = $(MENU_ITEM_DIALOG_SEL + " .megamenu-dialog-tablist button.megamenu-dialog-tab.is-active");
                return $a.length ? $a.attr("data-tab") : null;
            };

            /**
             * Shows or hides the dirty warning icon on a tab rail button (hidden on the active tab unless forced).
             *
             * @param {string} tabId
             * @param {boolean} forceShowEvenIfActive show warning even when this tab is selected (e.g. after dismiss close)
             */
            var ensureDirtyTabIndicator = function(tabId, forceShowEvenIfActive) {
                if (!dirtyTabs[tabId]) {
                    return;
                }
                var $t = $(MENU_ITEM_DIALOG_SEL + " .megamenu-dialog-tablist button.megamenu-dialog-tab[data-tab=\"" + tabId + "\"]");
                if (!$t.length) {
                    return;
                }
                var activeId = getActiveTabId();
                if (!forceShowEvenIfActive && tabId === activeId) {
                    $t.removeClass("mm-tab-dirty-warn").find(".mm-tab-dirty-indicator").remove();
                    return;
                }
                $t.addClass("mm-tab-dirty-warn");
                if (!$t.find(".mm-tab-dirty-indicator").length) {
                    var hint = megamenu.unsaved_changes_tab_hint || "";
                    $("<span />", {
                        "class": "mm-tab-dirty-indicator",
                        "data-mega-tooltip": hint,
                        "aria-label": hint
                    })
                        .append(
                            $("<span />", {
                                "class": "dashicons dashicons-warning",
                                "aria-hidden": "true"
                            })
                        )
                        .appendTo($t);
                }
            };

            /** Refreshes warning icons after tab switches so inactive dirty tabs stay visible. */
            var syncDirtyTabIndicators = function() {
                Object.keys(dirtyTabs).forEach(function(tabId) {
                    ensureDirtyTabIndicator(tabId, false);
                });
            };

            /** Forces warnings on all dirty tabs (used when user cancels close due to unsaved changes). */
            var showDirtyTabWarnings = function() {
                Object.keys(dirtyTabs).forEach(function(tabId) {
                    ensureDirtyTabIndicator(tabId, true);
                });
            };

            megamenuMountMenuItemDialogFromTemplate();

            var $dialog = $(MENU_ITEM_DIALOG_SEL);
            if (!$dialog.length) {
                panel.log({
                    success: false,
                    data: "Mega Menu dialog markup is missing from the page."
                }, "Dialog mount");
                return;
            }

            var $headerMeta = $dialog.find(".megamenu-admin-modal__header-meta");
            var $titleText = $dialog.find("#megamenu-menu-item-dialog-title .megamenu-admin-modal__title-text");
            var $breadcrumb = $dialog.find("#megamenu-menu-item-dialog-breadcrumb");
            var $panel = $dialog.find(".megamenu-admin-modal__panel");
            var $modalBody = $dialog.find(".megamenu-admin-modal__body.megamenu-admin-modal__loading-host");
            var $outerWrap = $dialog.find(".megamenu-admin-modal__body .megamenu_outer_wrap");

            var setMenuItemDialogLoading = function(loading) {
                if (!$modalBody.length) {
                    return;
                }
                $modalBody.toggleClass("megamenu-admin-modal__loading-host--loading", !!loading);
                if (loading) {
                    $modalBody.attr("aria-busy", "true");
                } else {
                    $modalBody.removeAttr("aria-busy");
                }
            };

            var escapeHandler;

            /** Hides the modal, clears header + body slots, removes listeners, and resets dirty tab state. */
            var closeDialog = function() {
                $(document).off("keydown.megaMenuModal", escapeHandler);
                $dialog.off("click.mmmItemBackdrop");
                $dialog.off("click.mmmWidgetDismiss");
                if (
                    window.MegamenuAdminModalExpand &&
                    typeof window.MegamenuAdminModalExpand.collapseOnClose === "function"
                ) {
                    window.MegamenuAdminModalExpand.collapseOnClose($dialog);
                }
                $dialog.prop("hidden", true).removeClass("is-open");
                $headerMeta.empty();
                $(MENU_ITEM_DIALOG_SEL + " .megamenu-menu-item-dialog-saving-indicator").prop("hidden", true);
                $titleText.empty();
                $breadcrumb.empty().prop("hidden", true);
                $panel.attr("aria-labelledby", "megamenu-menu-item-dialog-title");
                setMenuItemDialogLoading(false);
                $outerWrap.empty();
                $("html, body").removeClass("megamenu-dialog-open");
                clearAllDirtyTabs();
            };

            /** Prompts if any tab is dirty; otherwise closes immediately. */
            var guardedClose = function() {
                if (Object.keys(dirtyTabs).length === 0) {
                    closeDialog();
                    return;
                }
                if (confirm(megamenu.unsaved_changes)) {
                    closeDialog();
                } else {
                    showDirtyTabWarnings();
                }
            };

            /** Closes the modal on Escape unless a core WP modal is open; closes an open widget form first. */
            escapeHandler = function(e) {
                if (e.key !== "Escape" && e.keyCode !== 27) {
                    return;
                }
                if ($("body").hasClass("modal-open")) {
                    return;
                }
                var $openWidget = $dialog.find(".megamenu_content.mega_menu .mega-widget.open");
                if ($openWidget.length) {
                    e.preventDefault();
                    $openWidget.removeClass("open");
                    return;
                }
                if (
                    window.MegamenuAdminModalExpand &&
                    typeof window.MegamenuAdminModalExpand.handleEscapeCollapseIfExpanded === "function" &&
                    window.MegamenuAdminModalExpand.handleEscapeCollapseIfExpanded($dialog, e)
                ) {
                    return;
                }
                e.preventDefault();
                guardedClose();
            };

            $(document).off("keydown.megaMenuModal").on("keydown.megaMenuModal", escapeHandler);

            $dialog.off("click.mmmItemBackdrop").on("click.mmmItemBackdrop", ".megamenu-admin-modal__backdrop", function(e) {
                e.preventDefault();
                guardedClose();
            });

            $dialog.off("click.mmmItemClose").on("click.mmmItemClose", ".megamenu-modal-close", function(e) {
                e.preventDefault();
                guardedClose();
            });

            /** Collapse open widget when clicking outside that widget (still inside the dialog). */
            $dialog.off("click.mmmWidgetDismiss").on("click.mmmWidgetDismiss", function(e) {
                var $open = $dialog.find(".megamenu_content.mega_menu .mega-widget.open");
                if (!$open.length) {
                    return;
                }
                if ($(e.target).closest(".mega-widget.open").length) {
                    return;
                }
                $open.removeClass("open");
            });

            /** Close widget edit panel (header control). */
            $dialog.off("click.mmmWidgetDialogClose").on("click.mmmWidgetDialogClose", ".mega-widget-dialog-close", function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $w = $(this).closest(".mega-widget.open");
                if ($w.length) {
                    $w.removeClass("open");
                }
            });

            $("html, body").addClass("megamenu-dialog-open");
            clearAllDirtyTabs();
            $dialog.prop("hidden", false).addClass("is-open");
            if (
                window.MegamenuAdminModalExpand &&
                typeof window.MegamenuAdminModalExpand.restoreOnOpen === "function"
            ) {
                window.MegamenuAdminModalExpand.restoreOnOpen($dialog);
            }

            /**
             * Mega Menu tab: megamenu-pro outputs flat `label` + `#mm_enable_mega_menu` + `.mm_panel_options` (no toolbar wrapper).
             * Wrap in `.mega-submenu-toolbar` so free admin CSS matches the top-level menu item layout.
             *
             * @param {JQuery} $content `.megamenu_content.mega_menu` panel root
             */
            var ensureMegaSubmenuToolbar = function($content) {
                if (!$content || !$content.length) {
                    return;
                }
                if ($content.find(".mega-submenu-toolbar").length) {
                    return;
                }
                var $type = $content.find("#mm_enable_mega_menu").first();
                if (!$type.length) {
                    return;
                }
                var $toolbar = $("<div>", { "class": "mega-submenu-toolbar" });
                var $label = $content.find("label[for='mm_enable_mega_menu']").first();
                if ($label.length) {
                    $toolbar.append($label);
                }
                $toolbar.append($type);
                var $panel = $content.find(".mm_panel_options").first();
                if ($panel.length) {
                    $toolbar.append($panel);
                }
                $content.prepend($toolbar);
            };

            // Load tab HTML (megamenu_get_dialog_html); title + saving use shared __header; tabs + panels in .megamenu_outer_wrap.
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: "megamenu_get_dialog_html",
                    _wpnonce: megamenu.nonce,
                    menu_item_id: panel.settings.menu_item_id
                },
                cache: false,
                beforeSend: function() {
                    setMenuItemDialogLoading(true);
                    $headerMeta.empty();
                    $titleText.empty();
                    $breadcrumb.empty().prop("hidden", true);
                    $panel.attr("aria-labelledby", "megamenu-menu-item-dialog-title");
                    $outerWrap.empty();
                },
                success: function(response) {
                    // Tab map from megamenu_get_dialog_html (keys are tab ids; also title / active_tab).

                    var json = JSON.parse(response.data);

                    var active_tab = "mega_menu";
                    var tabs_container = $("<div class='megamenu-dialog-tablist mega-tablist' role='tablist' />");
                    var content_container = $("<div class='megamenu-dialog-panels' />");

                    $.each(json, function(idx) {

                        if (idx === "title") {
                            $titleText.html(this);
                            return;
                        }

                        if (idx === "active_tab") {
                            active_tab = (this);
                            return;
                        }

                        if (idx === "breadcrumb_html") {
                            return;
                        }

                        var content = $("<div />").addClass("megamenu_content").addClass(idx).html(this.content).hide();
                        
                        // bind save button action
                        content.find("form").on("submit", function(e) {
                            start_saving();
                            clearTabDirty(idx);
                            e.preventDefault();
                            var data = $(this).serialize();
                            $.post(ajaxurl, data, function(submit_response) {
                                end_saving();
                                panel.log(submit_response, "Tab form (" + idx + ")");
                            });
                        });

                        // register changes made
                        content.find("form").on("change", function(e) {
                            markTabDirty(idx);
                        });

                        if (idx === "menu_icon") {
                            var form = content.find("form.icon_selector").not(".icon_selector_custom");
                            var iconFilterTimer = null;
                            var ICON_FILTER_DEBOUNCE_MS = 150;
                            /** Rows get this class instead of inline display (cheaper batch updates; CSS owns visibility). */
                            var ICON_FILTER_HIDDEN_CLASS = "mmm-icon-filter-hidden";

                            function applyIconFilter() {
                                var q = (content.find(".filter_icons").val() || "").toLowerCase().trim();
                                var allRows = content.find(".icon_selector > div");

                                if (!q.length) {
                                    allRows.removeClass(ICON_FILTER_HIDDEN_CLASS);
                                    return;
                                }

                                var visibleForm = content.find(".icon_selector:visible")[0];
                                if (!visibleForm) {
                                    return;
                                }

                                var rows = visibleForm.querySelectorAll(":scope > div");
                                var i;
                                var row;
                                var inputEl;
                                var haystack;

                                for (i = 0; i < rows.length; i++) {
                                    row = rows[i];
                                    inputEl = row.querySelector("input.radio, input[type='radio']");
                                    haystack = (inputEl && inputEl.id ? inputEl.id : "").toLowerCase();
                                    row.classList.toggle(ICON_FILTER_HIDDEN_CLASS, haystack.indexOf(q) === -1);
                                }
                            }

                            // bind save button action
                            form.on("change", function(e) {
                                start_saving();
                                clearTabDirty(idx);
                                e.preventDefault();
                                $("input", form).not(e.target).prop("checked", false);
                                var data = $(this).serialize();
                                $.post(ajaxurl, data, function(submit_response) {
                                    end_saving();
                                    panel.log(submit_response, "Icon tab");
                                });

                            });

                            $(".megamenu_tab_horizontal", content).on("click", function() {
                                clearTimeout(iconFilterTimer);
                                iconFilterTimer = null;

                                var $li = $(this);
                                var tab_id = $li.attr("rel");

                                content.find(".filter_icons").val("");
                                content.find(".icon_selector > div").removeClass(ICON_FILTER_HIDDEN_CLASS);

                                $li.addClass("active");
                                $li.siblings().removeClass("active");
                                content.children("div[class^='megamenu_tab_']").hide();
                                content.children("div." + tab_id).show();
                            });

                            content.find(".filter_icons").on("input", function() {
                                if (!($(this).val() || "").trim().length) {
                                    clearTimeout(iconFilterTimer);
                                    iconFilterTimer = null;
                                    applyIconFilter();
                                    return;
                                }
                                clearTimeout(iconFilterTimer);
                                iconFilterTimer = setTimeout(function() {
                                    iconFilterTimer = null;
                                    applyIconFilter();
                                }, ICON_FILTER_DEBOUNCE_MS);
                            });
                        }

                        if (idx === "general_settings") {
                            content.find("select#mega-item-align").on("change", function() {
                                var select = $(this);
                                var selected = $(this).val();
                                select.next().children().hide();
                                select.next().children("." + selected).show();
                            });
                        }

                        if (idx === "mega_menu") {
                            ensureMegaSubmenuToolbar(content);

                            var submenu_type = content.find("#mm_enable_mega_menu");

                            submenu_type.parents(".megamenu_content.mega_menu").attr('data-type', submenu_type.val());
                            
                            submenu_type.on("change", function() {

                                submenu_type.parents(".megamenu_content.mega_menu").attr('data-type', submenu_type.val());

                                start_saving();

                                var postdata = {
                                    action: "megamenu_save_menu_item_settings",
                                    settings: {
                                        type: submenu_type.val()
                                    },
                                    menu_item_id: panel.settings.menu_item_id,
                                    _wpnonce: megamenu.nonce
                                };

                                $.post(ajaxurl, postdata, function(select_response) {
                                    end_saving();

                                    panel.log(select_response, "Sub menu display mode");
                                });

                            });

                            setup_megamenu(content);
                            setup_grid(content);

                        }

                        var tab = $("<button type='button' />")
                            .addClass("megamenu-dialog-tab")
                            .addClass(idx)
                            .attr("data-tab", idx)
                            .text(this.title);

                        tabs_container.append(tab);

                        content_container.append(content);
                    });

                    if (window.megamenuDialogTabs && typeof window.megamenuDialogTabs.bindVerticalRail === "function") {
                        window.megamenuDialogTabs.bindVerticalRail({
                            tablist: tabs_container[0],
                            panelsRoot: content_container[0],
                            tabSelector: "button.megamenu-dialog-tab",
                            panelsSelector: ":scope > .megamenu_content",
                            idPrefix: "mmm-item-" + String(panel.settings.menu_item_id),
                            getPanelKey: function (btn) {
                                return btn.getAttribute("data-tab");
                            },
                            panelMatches: function (panel, key) {
                                return panel.classList.contains("megamenu_content") && panel.classList.contains(key);
                            },
                            onAfterActivate: syncDirtyTabIndicators
                        });
                    }

                    $(".megamenu-dialog-tab." + active_tab + ":first", tabs_container).trigger("click");

                    if (json.breadcrumb_html) {
                        $breadcrumb.html(json.breadcrumb_html).prop("hidden", false);
                    } else {
                        $breadcrumb.empty().prop("hidden", true);
                    }

                    var ariaLabelledBy = "megamenu-menu-item-dialog-title";
                    if (json.breadcrumb_html) {
                        ariaLabelledBy += " megamenu-menu-item-dialog-breadcrumb";
                    }
                    $panel.attr("aria-labelledby", ariaLabelledBy);

                    $headerMeta.empty();
                    $outerWrap.append(tabs_container).append(content_container);

                    $dialog.find(".megamenu-modal-close").trigger("focus");

                    if (
                        window.megamenuSyncComponentsToggleWrappers &&
                        typeof window.megamenuSyncComponentsToggleWrappers ===
                            "function"
                    ) {
                        window.megamenuSyncComponentsToggleWrappers($outerWrap);
                    }

                    // Pro and extensions hook this after lightbox HTML is in the DOM.
                    $outerWrap.trigger("megamenu_content_loaded");
                },
                complete: function() {
                    setMenuItemDialogLoading(false);
                }
            });
        };

        /**
         * Records the panels container scrollTop as `--mmm-panels-scroll` for widget-inner vertical centering.
         * Shared by grid (#megamenu-grid) and classic (#megamenu-standard) builders — must live outside setup_grid.
         *
         * @param {JQuery} widget .mega-widget instance
         */
        function recordPanelsScroll(widget) {
            var panels = widget.closest(".megamenu-dialog-panels")[0];
            if (panels) {
                panels.style.setProperty("--mmm-panels-scroll", panels.scrollTop + "px");
            }
        }

        /**
         * Binds mega grid layout: columns, rows, widgets, drag/drop, and megamenu_save_grid_data.
         *
         * @param {jQuery} content .megamenu_content.mega_menu fragment containing #megamenu-grid
         */
        var setup_grid = function(content) {

            var grid = content.find("#megamenu-grid");

            /** Syncs `--row-tracks` / `--span` from data attributes (PHP always emits `.mega-row-cols`). */
            var syncMegaGridCssVars = function($context) {
                $context.find(".mega-row").each(function() {
                    var rowEl = this;
                    var tracks = rowEl.getAttribute("data-available-cols");
                    if (tracks) {
                        rowEl.style.setProperty("--row-tracks", String(tracks));
                    }
                    $(rowEl).find(".mega-row-cols .mega-col").each(function() {
                        var sp = this.getAttribute("data-span");
                        if (sp) {
                            this.style.setProperty("--span", sp);
                        }
                    });
                });
            };

            syncMegaGridCssVars(grid);

            var gridWidgetTargetColumn = null;
            var $widgetSelector = content.find("#mm_widget_selector");
            // Pulse outline on the widget `<select>` (matches megamenu-pro flat `mm_panel_options` markup).
            var $widgetSelectorPanel = $widgetSelector;

            /** Clears visual highlight for "add widget to this column". */
            var clearGridColumnAddTarget = function() {
                grid.find(".mega-col").removeClass("mega-col-add-target");
            };

            /** Pulse on grid widget chrome until the next document click (reuses `mm-mega-widget-selector-pulse`). */
            var dismissGridWidgetAddedPulse = function() {
                grid.find(".mega-widget-added-pulse").removeClass("mega-widget-added-pulse");
                $(document).off("click.mmmGridWidgetPulseDismiss");
            };

            var pulseGridWidgetAdded = function($widget) {
                if (!$widget || !$widget.length) {
                    return;
                }
                $widget.removeClass("mega-widget-added-pulse");
                if ($widget[0]) {
                    void $widget[0].offsetWidth;
                }
                $widget.addClass("mega-widget-added-pulse");
                $(document).off("click.mmmGridWidgetPulseDismiss");
                window.setTimeout(function() {
                    $(document).one("click.mmmGridWidgetPulseDismiss", dismissGridWidgetAddedPulse);
                }, 0);
            };

            /** Inserts a new widget block into the targeted or first column and re-runs sortable hooks. */
            var appendWidgetToGridColumn = function(widgetHtml) {
                var $widget = $(widgetHtml);
                var $target = gridWidgetTargetColumn && gridWidgetTargetColumn.length
                    ? gridWidgetTargetColumn
                    : grid.find(".mega-col-widgets").first();
                if (!$target.length) {
                    gridWidgetTargetColumn = null;
                    clearGridColumnAddTarget();
                    return;
                }
                var $anchor = $target.find(".mega-col-add-widget").first();
                if ($anchor.length) {
                    $anchor.before($widget);
                } else {
                    $target.append($widget);
                }
                pulseGridWidgetAdded($widget);
                // Next add from the widget selector (without choosing another column via +) uses this column.
                gridWidgetTargetColumn = $target;
                clearGridColumnAddTarget();
                grid.trigger("make_columns_sortable");
                grid.trigger("make_widgets_sortable");
                grid.trigger("update_column_block_count");
                grid.trigger("save_grid_data");
                window.requestAnimationFrame(function() {
                    var el = $widget[0];
                    if (el && typeof el.scrollIntoView === "function") {
                        el.scrollIntoView({ block: "nearest", inline: "nearest", behavior: "smooth" });
                    }
                });
            };

            content.find("#mm_widget_selector").on("change", function() {

                var submenu_type = content.find("#mm_enable_mega_menu");

                if (submenu_type.length && submenu_type.val() != "grid") {
                    return;
                }

                var selector = $(this);

                if (selector.val() != "disabled") {

                    var postdata = {
                        action: "megamenu_add_widget",
                        id_base: selector.val(),
                        menu_item_id: panel.settings.menu_item_id,
                        is_grid_widget: "true",
                        title: selector.find("option:selected").text(),
                        _wpnonce: megamenu.nonce
                    };

                    $.post(ajaxurl, postdata, function(response) {
                        appendWidgetToGridColumn(response.data);
                        selector.val("disabled");
                        $widgetSelectorPanel.removeClass("mega-widget-selector-pulse");
                    }).fail(function() {
                        gridWidgetTargetColumn = null;
                        clearGridColumnAddTarget();
                    });

                }
            });

            grid.on("click", ".mega-col-add-widget", function(e) {
                e.preventDefault();
                e.stopPropagation();
                gridWidgetTargetColumn = $(this).closest(".mega-col-widgets");
                clearGridColumnAddTarget();
                gridWidgetTargetColumn.closest(".mega-col").addClass("mega-col-add-target");
                $widgetSelectorPanel.addClass("mega-widget-selector-pulse");
                window.clearTimeout($widgetSelectorPanel.data("mega-pulse-timer"));
                $widgetSelectorPanel.data("mega-pulse-timer", window.setTimeout(function() {
                    $widgetSelectorPanel.removeClass("mega-widget-selector-pulse");
                }, 4000));
                var el = $widgetSelector[0];
                if (el) {
                    el.focus({ preventScroll: true });
                    // Chrome / Chromium: open native select list (requires transient user activation).
                    if (typeof el.showPicker === "function") {
                        try {
                            el.showPicker();
                        } catch (ignore) {
                            // NotAllowedError, hidden select, unsupported — keep focus + pulse only
                        }
                    }
                    if (typeof el.scrollIntoView === "function") {
                        el.scrollIntoView({ block: "nearest", behavior: "smooth" });
                    }
                }
            });

            // Click row chrome (not inside a column) — drop "add widget here" target + highlight.
            grid.on("click", ".mega-row", function(e) {
                if ($(e.target).closest(".mega-col").length) {
                    return;
                }
                gridWidgetTargetColumn = null;
                clearGridColumnAddTarget();
            });

            // Add Column
            grid.on("click", ".mega-add-column", function() {
                var button = $(this);
                var row = button.closest(".mega-row");
                var used_cols = parseInt(row.attr('data-used-cols'));
                var available_cols = parseInt(row.attr('data-available-cols'));

                row.find(".mega-row-is-full").hide();

                if ( used_cols + 1 > available_cols ) {
                    row.find(".mega-row-is-full").slideDown().delay(2000).slideUp();
                    return;
                }

                var space_left_on_row = available_cols - used_cols;

                var data = {
                    action: "megamenu_get_empty_grid_column",
                    _wpnonce: megamenu.nonce
                };

                $.post(ajaxurl, data, function(response) {
                    var column = $(response.data);

                    if (space_left_on_row < 3) {
                        column.attr('data-span', space_left_on_row);
                        column[0].style.setProperty("--span", String(space_left_on_row));
                        column.find('.mega-num-cols').html(space_left_on_row);
                    }

                    row.children(".mega-row-cols").append(column);

                    grid.trigger("make_columns_sortable");
                    grid.trigger("make_widgets_sortable");
                    grid.trigger("save_grid_data");
                    grid.trigger("update_row_column_count");
                    grid.trigger("update_column_block_count");
                });
            });

            // Delete Column
            grid.on("click", ".mega-col-actions .mega-col-header__action--delete", function() {
                $(this).closest(".mega-col").remove();

                grid.trigger("save_grid_data");
                grid.trigger("update_row_column_count");
            });

            // Add Row
            grid.on("click", ".mega-add-row", function() {
                var button = $(this);
                var data = {
                    action: "megamenu_get_empty_grid_row",
                    _wpnonce: megamenu.nonce
                };

                $.post(ajaxurl, data, function(response) {
                    var row = $(response.data);
                    button.before(row);

                    grid.trigger("make_columns_sortable");
                    grid.trigger("make_widgets_sortable");
                    grid.trigger("make_rows_sortable");
                    grid.trigger("save_grid_data");
                    grid.trigger("update_row_column_count");
                    grid.trigger("update_column_block_count");

                });
            });

            // Delete Row
            grid.on("click", ".mega-row-actions .mega-row-header__action--delete", function() {
                $(this).closest(".mega-row").remove();

                grid.trigger("make_rows_sortable");
                grid.trigger("save_grid_data");
            });

            // Expand Column
            grid.on("click", ".mega-col-expand", function() {

                var column = $(this).closest(".mega-col");
                var cols = parseInt(column.attr("data-span"), 10);

                if (cols < 12) {
                    cols = cols + 1;

                    column.attr("data-span", cols);
                    column[0].style.setProperty("--span", String(cols));

                    $(".mega-num-cols", column).html(cols);

                    grid.trigger("save_grid_data");
                    grid.trigger("update_row_column_count");
                }
            });

            // Contract Column
            grid.on("click", ".mega-col-contract", function() {

                var column = $(this).closest(".mega-col");

                var cols = parseInt(column.attr("data-span"), 10);

                if (cols > 1) {
                    cols = cols - 1;

                    column.attr("data-span", cols);
                    column[0].style.setProperty("--span", String(cols));

                    $(".mega-num-cols", column).html(cols);

                    grid.trigger("save_grid_data");
                    grid.trigger("update_row_column_count");
                }

            });

            grid.on("click", ".mega-widget-action", function() {

                var action = "megamenu_edit_widget";
                var is_menu_item = $(this).parent().parent().parent().attr('data-type') == 'item';

                if (is_menu_item) {
                    action = "megamenu_edit_menu_item";
                }

                var widget = $(this).closest(".mega-widget");
                var widget_title = widget.find("h4");
                var id = widget.attr("data-id");
                var widget_inner = widget.find(".mega-widget-inner");

                if (!widget.hasClass("open") && !widget.data("loaded")) {

                    widget_title.addClass("loading");

                    // retrieve the widget settings form
                    $.post(ajaxurl, {
                        action: action,
                        widget_id: id,
                        _wpnonce: megamenu.nonce
                    }, function(response) {

                        var $response = $(response);
                        var $form = $response;

                        // bind delete button action
                        $(".mega-delete", $form).on("click", function(e) {
                            e.preventDefault();

                            if (is_menu_item) {
                                alert(megamenu.delete_menu_item);
                                return;
                            }

                            widget.remove();

                            var data = {
                                action: "megamenu_delete_widget",
                                widget_id: id,
                                _wpnonce: megamenu.nonce
                            };

                            $.post(ajaxurl, data, function(delete_response) {
                                panel.log(delete_response, "Delete widget (grid)");
                                grid.trigger("save_grid_data");
                                grid.trigger("update_column_block_count");
                            });

                        });

                        // bind save button action
                        $form.on("submit", function(e) {
                            e.preventDefault();

                            var data = $(this).serialize();

                            start_saving();

                            $.post(ajaxurl, data, function(submit_response) {
                                end_saving();
                                panel.log(submit_response, "Widget settings (grid)");
                            });

                        });

                        widget_inner.html($response);

                        widget.data("loaded", true).toggleClass("open");
                        recordPanelsScroll(widget);

                        grid.trigger("check_widget_inner_position", [widget_inner]);

                        widget_title.removeClass("loading");

                        // Init Black Studio TinyMCE
                        if (widget.is("[id*=black-studio-tinymce]")) {
                            bstw(widget).deactivate().activate();
                        }

                        setTimeout(function(){
                            // fix for WordPress 4.8 widgets when lightbox is opened, closed and reopened
                            if (wp.textWidgets !== undefined) {
                                wp.textWidgets.widgetControls = {}; // WordPress 4.8 Text Widget
                            }

                            if (wp.mediaWidgets !== undefined) {
                                wp.mediaWidgets.widgetControls = {}; // WordPress 4.8 Media Widgets
                            }

                            if (wp.customHtmlWidgets !== undefined) {
                                wp.customHtmlWidgets.widgetControls = {}; // WordPress 4.9 Custom HTML Widgets
                            }

                            $(document).trigger("widget-added", [widget]);

                            if ('acf' in window) {
                                acf.getFields(document);
                            }
                        }, 100);

                    });

                } else {
                    widget.toggleClass("open");
                    if (widget.hasClass("open")) {
                        recordPanelsScroll(widget);
                    }
                }

                grid.trigger("check_widget_inner_position", [widget_inner]);

                // close all other widgets
                $(".mega-widget").not(widget).removeClass("open");

            });


            grid.on("click", ".mega-col-header .mega-col-header__action--settings", function() {
                var $btn = $(this);
                $btn.toggleClass("mega-settings-open");
                $btn.attr("aria-expanded", $btn.hasClass("mega-settings-open") ? "true" : "false");
                $btn.closest(".mega-col").find(".mega-col-settings").slideToggle();
            });

            grid.on("click", ".mega-row-header .mega-row-header__action--settings", function() {
                var $btn = $(this);
                $btn.toggleClass("mega-settings-open");
                $btn.attr("aria-expanded", $btn.hasClass("mega-settings-open") ? "true" : "false");
                $btn.closest(".mega-row").find(".mega-row-settings").slideToggle();
            });

            grid.on("keyup", ".mega-widget-content input[name*='[title]'], .media-widget-control [id*='_title'].title, .custom-html-widget-fields [id*='_title'].title", function() {
                var title = $(this).val();

                if (title.length == 0) {
                    var desc = $(this).closest(".mega-widget").find(".mega-widget-title .mega-widget-desc").html();
                    $(this).closest(".mega-widget").find(".mega-widget-title h4").html(desc);
                } else {
                    $(this).closest(".mega-widget").find(".mega-widget-title h4").html(title);
                }
            });

            grid.on(
                "click",
                ".mega-row-header .mega-row-header__action--desktop, .mega-col-header .mega-col-header__action--desktop",
                function() {
                    var $btn = $(this);
                    var $scope = $btn.closest(".mega-row, .mega-col");
                    var input = $scope.find("input[name='mega-hide-on-desktop']");

                    if (input.val() == "true") {
                        input.val("false");
                        $btn.removeClass("mega-disabled").addClass("mega-enabled");
                    } else {
                        input.val("true");
                        $btn.removeClass("mega-enabled").addClass("mega-disabled");
                    }

                    grid.trigger("save_grid_data");
                }
            );

            grid.on(
                "click",
                ".mega-row-header .mega-row-header__action--mobile, .mega-col-header .mega-col-header__action--mobile",
                function() {
                    var $btn = $(this);
                    var $scope = $btn.closest(".mega-row, .mega-col");
                    var input = $scope.find("input[name='mega-hide-on-mobile']");

                    if (input.val() == "true") {
                        input.val("false");
                        $btn.removeClass("mega-disabled").addClass("mega-enabled");
                    } else {
                        input.val("true");
                        $btn.removeClass("mega-enabled").addClass("mega-disabled");
                    }

                    grid.trigger("save_grid_data");
                }
            );

            grid.on("click", ".mega-save-column-settings, .mega-save-row-settings", function() {
                if ($(this).hasClass("mega-save-row-settings")) {
                    grid.trigger("update_total_columns_in_row");
                }
                grid.trigger("save_grid_data");
            });

            grid.on("check_widget_inner_position", function(event, widget_inner) {
                // Widget forms open as a centered modal; drop legacy right-edge alignment.
                widget_inner.css({ right: "", left: "" });
            });

            grid.on("save_grid_data", function() {
                start_saving();

                var rows = [];
                var cols = [];

                $(".mega-row", grid).each(function() {
                    var $r = $(this);
                    var row_index = grid.children(".mega-row").index($r);
                    var row_hide_on_desktop = $r.find("input[name='mega-hide-on-desktop']").val();
                    var row_hide_on_mobile = $r.find("input[name='mega-hide-on-mobile']").val();
                    var row_class = $r.find("input.mega-row-class").val();
                    var row_columns = $r.find("select.mega-row-columns").val();

                    rows[row_index] = {
                        "meta": {
                            "class": row_class,
                            "hide-on-desktop": row_hide_on_desktop,
                            "hide-on-mobile": row_hide_on_mobile,
                            "columns": row_columns
                        },
                        "columns": []
                    };
                });

                $(".mega-col", grid).each(function() {
                    var $col = $(this);
                    var col_index = $col.parent().children(".mega-col").index($col);
                    var row_index = grid.children(".mega-row").index($col.closest(".mega-row"));
                    var col_span = $col.attr("data-span");
                    var col_hide_on_desktop = $col.find("input[name='mega-hide-on-desktop']").val();
                    var col_hide_on_mobile = $col.find("input[name='mega-hide-on-mobile']").val();
                    var col_class = $col.find("input.mega-column-class").val();

                    rows[row_index]["columns"][col_index] = {
                        "meta": {
                            "span": col_span,
                            "class": col_class,
                            "hide-on-desktop": col_hide_on_desktop,
                            "hide-on-mobile": col_hide_on_mobile
                        },
                        "items": []
                    };
                });

                $(".mega-widget", grid).each(function() {
                    var block_index = $(this).index();
                    var id = $(this).attr("data-id");
                    var type = $(this).attr("data-type");
                    var $row = $(this).closest(".mega-row");
                    var row_index = grid.children(".mega-row").index($row);
                    var col = $(this).closest(".mega-col");
                    var col_index = col.parent().children(".mega-col").index(col);

                    var widget = {
                        "id": id,
                        "type": type
                    };

                    rows[row_index]["columns"][col_index]["items"].push(widget);
                });

                $.post(ajaxurl, {
                    action: "megamenu_save_grid_data",
                    grid: rows,
                    parent_menu_item: panel.settings.menu_item_id,
                    _wpnonce: megamenu.nonce
                }, function(move_response) {
                    end_saving();
                });

                grid.trigger("update_row_column_count");

            });

            grid.on("update_total_columns_in_row", function() {
                $(".mega-row", grid).each(function() {
                    var row = $(this);
                    var total_cols = $(this).find("select.mega-row-columns").val();
                    $(this).attr('data-available-cols', total_cols);
                    this.style.setProperty("--row-tracks", String(total_cols));

                    $(".mega-col", row).not(".ui-sortable-helper").each(function() {
                        var col = $(this);
                        
                        $(this).find('.mega-num-total-cols').html(total_cols);
                    });
                });
            });

            grid.on("update_row_column_count", function() {

                grid.trigger("update_total_columns_in_row");

                $(".mega-row", grid).each(function() {
                    var row = $(this);
                    var used_cols = 0;
                    var available_cols = row.attr("data-available-cols");
                    var $colsWrap = row.children(".mega-row-cols");

                    if ($colsWrap.length) {
                        var colCount = $colsWrap.children(".mega-col").not(".ui-sortable-helper").length;
                        var gapSlots = Math.max(0, colCount - 1);
                        $colsWrap[0].style.setProperty("--mega-cols-gap-slots", String(gapSlots));
                    }

                    $(".mega-col", row).not(".ui-sortable-helper").each(function() {
                        var col = $(this);
                        used_cols = used_cols + parseInt(col.attr("data-span"), 10);
                    });

                    row.attr("data-used-cols", used_cols);

                    row.removeAttr("data-too-many-cols");
                    row.removeAttr("data-row-is-full");

                    if ( used_cols > available_cols ) {
                        row.attr("data-too-many-cols", "true");
                    }

                    if ( used_cols == available_cols ) {
                        row.attr("data-row-is-full", "true");
                    }

                    var rowFull = row.attr("data-row-is-full") === "true";
                    row.find(".mega-col-expand").prop("disabled", rowFull);

                    $(".mega-col", row).not(".ui-sortable-helper").each(function() {
                        var col = $(this);
                        var span = parseInt(col.attr("data-span"), 10);
                        col.find(".mega-col-contract").prop("disabled", !isFinite(span) || span <= 1);
                    });
                });
            });

            grid.on("update_column_block_count", function() {
                $(".mega-col", grid).each(function() {
                    var col = $(this);
                    col.attr("data-total-blocks", $(".mega-col-widgets > .mega-widget", col).length);
                });
            });

            grid.on("make_rows_sortable", function() {
                if (grid.hasClass("ui-sortable")) {
                    grid.sortable("destroy");
                }

                var rowCount = grid.children(".mega-row").length;

                grid.toggleClass("mega-grid--rows-sortable", rowCount > 1);

                grid.sortable({
                    disabled: rowCount < 2,
                    forcePlaceholderSize: false,
                    items: ".mega-row",
                    placeholder: "drop-area",
                    handle: ".mega-row-header",
                    cancel: "a, button, input, select, textarea, .mega-row-header__action, .mega-row-settings",
                    tolerance: "pointer",
                    start: function(event, ui) {
                        $(".mega-widget").removeClass("open");
                        ui.item.data("start_pos", ui.item.index());
                        var h = ui.item.outerHeight();
                        var w = ui.item.outerWidth();
                        ui.helper.add(ui.placeholder).css({
                            boxSizing: "border-box",
                            width: w,
                            height: h
                        });
                    },
                    stop: function(event, ui) {
                        var rowEl = ui.item[0];
                        if (rowEl) {
                            var tracks = rowEl.getAttribute("data-available-cols");
                            rowEl.removeAttribute("style");
                            if (tracks) {
                                rowEl.style.setProperty("--row-tracks", String(tracks));
                            }
                        }

                        var start_pos = ui.item.data("start_pos");

                        if (start_pos !== ui.item.index()) {
                            grid.trigger("save_grid_data");
                        }
                    }
                });
            });

            grid.on("make_widgets_sortable", function() {
                // sortable widgets
                var cols = grid.find(".mega-col-widgets");

                cols.sortable({
                    connectWith: ".mega-col-widgets",
                    forcePlaceholderSize: true,
                    items: ".mega-widget",
                    placeholder: "drop-area",
                    handle: ".mega-widget-top",
                    helper: "clone",
                    tolerance: "pointer",
                    start: function(event, ui) {
                        $(".mega-widget").removeClass("open");
                        ui.item.data("mmm-widget-sort-parent", ui.item.parent()[0]);
                        ui.item.css("margin-top", $(window).scrollTop());

                    },
                    stop: function(event, ui) {
                        ui.item.css("margin-top", "");

                        var startParent = ui.item.data("mmm-widget-sort-parent");
                        if (startParent && ui.item.parent()[0] !== startParent) {
                            pulseGridWidgetAdded(ui.item);
                        }
                        ui.item.removeData("mmm-widget-sort-parent");

                        grid.trigger("save_grid_data");
                        grid.trigger("update_column_block_count");
                    }
                });
            });

            grid.on("make_columns_sortable", function() {
                grid.find(".mega-row-cols").each(function() {
                    var $cols = $(this);
                    if ($cols.hasClass("ui-sortable")) {
                        $cols.sortable("destroy");
                    }
                });

                grid.find(".mega-row-cols").sortable({
                    connectWith: ".mega-row-cols",
                    forcePlaceholderSize: false,
                    items: ".mega-col",
                    placeholder: "drop-area",
                    tolerance: "pointer",
                    // Drag from the filler strip between toolbar icons and width controls only.
                    handle: ".mega-col-drag-handle",
                    cancel: "a, button, input, select, textarea, .mega-col-header__action",
                    start: function(event, ui) {
                        $(".mega-widget").removeClass("open");
                        var w = ui.item.outerWidth();
                        var h = ui.item.outerHeight();
                        ui.helper.add(ui.placeholder).css({
                            boxSizing: "border-box",
                            width: w,
                            height: h,
                            minWidth: w,
                            maxWidth: w
                        });
                    },
                    stop: function(event, ui) {
                        grid.trigger("save_grid_data");

                        var colEl = ui.item[0];
                        if (colEl) {
                            var sp = colEl.getAttribute("data-span");
                            colEl.removeAttribute("style");
                            if (sp) {
                                colEl.style.setProperty("--span", String(sp));
                            }
                        }

                        grid.trigger("update_row_column_count");
                    }
                });
            });

            grid.trigger("update_row_column_count");
            grid.trigger("update_column_block_count");
            grid.trigger("make_rows_sortable");
            grid.trigger("make_columns_sortable");
            grid.trigger("make_widgets_sortable");

        }

        /**
         * Classic mega panel (non-grid): column count, widget list, reorder, expand/collapse widget forms.
         *
         * @param {jQuery} content .megamenu_content.mega_menu fragment containing #megamenu-standard
         */
        var setup_megamenu = function(content) {

            var megamenubuilder = content.find("#megamenu-standard");

            content.find("#mm_number_of_columns").on("change", function() {

                megamenubuilder.attr("data-columns", $(this).val());

                megamenubuilder.find(".mega-widget-total-cols").html($(this).val());

                start_saving();

                var postdata = {
                    action: "megamenu_save_menu_item_settings",
                    settings: {
                        panel_columns: $(this).val()
                    },
                    menu_item_id: panel.settings.menu_item_id,
                    _wpnonce: megamenu.nonce
                };

                $.post(ajaxurl, postdata, function(select_response) {
                    end_saving();
                    panel.log(select_response, "Mega panel column count");
                });

            });

            megamenubuilder.on("reorder_widgets", function() {
                start_saving();

                var items = [];

                $(".mega-widget").each(function() {
                    items.push({
                        "type": $(this).attr("data-type"),
                        "order": $(this).index() + 1,
                        "id": $(this).attr("data-id"),
                        "parent_menu_item": panel.settings.menu_item_id
                    });
                });

                $.post(ajaxurl, {
                    action: "megamenu_reorder_items",
                    items: items,
                    _wpnonce: megamenu.nonce
                }, function(move_response) {
                    end_saving();
                    panel.log(move_response, "Reorder mega menu widgets");
                });
            });

            megamenubuilder.sortable({
                forcePlaceholderSize: true,
                items: ".mega-widget",
                placeholder: "drop-area",
                handle: ".mega-widget-top",
                start: function(event, ui) {
                    $(".mega-widget").removeClass("open");
                    ui.item.data("start_pos", ui.item.index());
                },
                stop: function(event, ui) {
                    var start_pos = ui.item.data("start_pos");

                    if (start_pos !== ui.item.index()) {
                        megamenubuilder.trigger("reorder_widgets");
                    }
                }
            });

            content.find("#mm_widget_selector").on("change", function() {

                var submenu_type = content.find("#mm_enable_mega_menu");

                if (submenu_type.length && submenu_type.val() != "megamenu") {
                    return;
                }

                var selector = $(this);

                if (selector.val() != "disabled") {

                    start_saving();

                    var postdata = {
                        action: "megamenu_add_widget",
                        id_base: selector.val(),
                        menu_item_id: panel.settings.menu_item_id,
                        title: selector.find("option:selected").text(),
                        _wpnonce: megamenu.nonce
                    };

                    $.post(ajaxurl, postdata, function(response) {
                        $(".no_widgets").hide();
                        var widget = $(response.data);
                        var number_of_columns = content.find("#mm_number_of_columns").val();
                        widget.find(".mega-widget-total-cols").html(number_of_columns);
                        $("#megamenu-standard").append(widget);
                        megamenubuilder.trigger("reorder_widgets");
                        end_saving();
                        // reset the dropdown
                        selector.val("disabled");
                    });

                }

            });

            megamenubuilder.on("click", ".mega-widget .mega-widget-expand", function() {
                var widget = $(this).closest(".mega-widget");
                var type = widget.attr("data-type");
                var id = widget.attr("id");
                var cols = parseInt(widget.attr("data-columns"), 10);
                var maxcols = parseInt(content.find("#mm_number_of_columns").val(), 10);

                if (cols < maxcols) {
                    cols = cols + 1;

                    widget.attr("data-columns", cols);

                    $(".mega-widget-num-cols", widget).html(cols);

                    start_saving();

                    if (type == "widget") {

                        $.post(ajaxurl, {
                            action: "megamenu_update_widget_columns",
                            id: id,
                            columns: cols,
                            _wpnonce: megamenu.nonce
                        }, function(expand_response) {
                            end_saving();
                            panel.log(expand_response, "Widget column span");
                        });

                    }

                    if (type == "menu_item") {

                        $.post(ajaxurl, {
                            action: "megamenu_update_menu_item_columns",
                            id: id,
                            columns: cols,
                            _wpnonce: megamenu.nonce
                        }, function(contract_response) {
                            end_saving();
                            panel.log(contract_response, "Menu item block column span");
                        });

                    }

                }

            });

            megamenubuilder.on("click", ".mega-widget .mega-widget-contract", function() {
                var widget = $(this).closest(".mega-widget");
                var type = widget.attr("data-type");
                var id = widget.attr("id");
                var cols = parseInt(widget.attr("data-columns"), 10);

                // account for widgets that have say 8 columns but the panel is only 6 wide
                var maxcols = parseInt(content.find("#mm_number_of_columns").val(), 10);

                if (cols > maxcols) {
                    cols = maxcols;
                }

                if (cols > 1) {
                    cols = cols - 1;
                    widget.attr("data-columns", cols);

                    $(".mega-widget-num-cols", widget).html(cols);
                } else {
                    return;
                }

                start_saving();

                if (type == "widget") {

                    $.post(ajaxurl, {
                        action: "megamenu_update_widget_columns",
                        id: id,
                        columns: cols,
                        _wpnonce: megamenu.nonce
                    }, function(contract_response) {
                        end_saving();
                        panel.log(contract_response, "Widget column span");
                    });

                }

                if (type == "menu_item") {

                    $.post(ajaxurl, {
                        action: "megamenu_update_menu_item_columns",
                        id: id,
                        columns: cols,
                        _wpnonce: megamenu.nonce
                    }, function(contract_response) {
                        end_saving();
                        panel.log(contract_response, "Menu item block column span");
                    });

                }

            });


            megamenubuilder.on("click", ".mega-widget .mega-widget-action", function() {

                var action = "megamenu_edit_widget";
                var is_menu_item = $(this).parent().parent().parent().attr('data-type') == 'menu_item';

                if (is_menu_item) {
                    action = "megamenu_edit_menu_item";
                }

                var widget = $(this).closest(".mega-widget");
                var widget_title = widget.find(".mega-widget-title");
                var widget_inner = widget.find(".mega-widget-inner");
                var id = widget.attr("id");

                if (!widget.hasClass("open") && !widget.data("loaded")) {

                    widget_title.addClass("loading");

                    // retrieve the widget settings form
                    $.post(ajaxurl, {
                        action: action,
                        widget_id: id,
                        _wpnonce: megamenu.nonce
                    }, function(response) {

                        var $response = $(response);
                        var $form = $response;

                        // bind delete button action
                        $(".mega-delete", $form).on("click", function(e) {
                            e.preventDefault();

                            if (is_menu_item) {
                                alert(megamenu.delete_menu_item);
                                return;
                            }

                            var data = {
                                action: "megamenu_delete_widget",
                                widget_id: id,
                                _wpnonce: megamenu.nonce
                            };

                            $.post(ajaxurl, data, function(delete_response) {
                                widget.remove();
                                panel.log(delete_response, "Delete widget");
                            });

                        });

                        // bind save button action
                        $form.on("submit", function(e) {
                            e.preventDefault();

                            var data = $(this).serialize();

                            start_saving();

                            $.post(ajaxurl, data, function(submit_response) {
                                end_saving();
                                panel.log(submit_response, "Widget settings");
                            });

                        });

                        widget_inner.html($response);

                        widget.data("loaded", true).addClass("open");
                        recordPanelsScroll(widget);

                        widget_inner.removeClass("mmm-widget-inner--show").addClass("mmm-widget-inner--preparing");

                        // Init Black Studio TinyMCE
                        if (widget.is('[id*=black-studio-tinymce]')) {
                            bstw(widget).deactivate().activate();
                        }

                        setTimeout(function(){
                            // fix for WordPress 4.8 widgets when lightbox is opened, closed and reopened
                            if (wp.textWidgets !== undefined) {
                                wp.textWidgets.widgetControls = {}; // WordPress 4.8 Text Widget
                            }

                            if (wp.mediaWidgets !== undefined) {
                                wp.mediaWidgets.widgetControls = {}; // WordPress 4.8 Media Widgets
                            }

                            if (wp.customHtmlWidgets !== undefined) {
                                wp.customHtmlWidgets.widgetControls = {}; // WordPress 4.9 Custom HTML Widgets
                            }
                            
                            $(document).trigger("widget-added", [widget]);

                            if ('acf' in window) {
                                acf.getFields(document);
                            }

                            // After WP widget inits (layout may change), paint one frame then fade in.
                            window.requestAnimationFrame(function() {
                                window.requestAnimationFrame(function() {
                                    widget_title.removeClass("loading");
                                    widget_inner.removeClass("mmm-widget-inner--preparing").addClass("mmm-widget-inner--show");
                                });
                            });
                        }, 100);


                    }).fail(function() {
                        widget_title.removeClass("loading");
                        widget_inner.removeClass("mmm-widget-inner--preparing");
                    });

                } else {
                    widget.toggleClass("open");
                    if (widget.hasClass("open")) {
                        recordPanelsScroll(widget);
                    }
                }

                // close all other widgets
                $(".mega-widget").not(widget).removeClass("open");

            });

        }

        /** Disables visible tab save buttons and shows the header "Saving" state. */
        var start_saving = function() {
            $(".megamenu_content:visible p.submit, .megamenu_content:visible .mega-widget-form-footer")
                .find("button.button-primary")
                .each(function() {
                    var $el = $(this);
                    $el.attr("data-orig-label", $.trim($el.text()))
                        .addClass("is-busy")
                        .text(megamenu.saving + "…");
                });
            var $ind = $(MENU_ITEM_DIALOG_SEL + " .megamenu-menu-item-dialog-saving-indicator");
            if ($ind.length) {
                $ind.prop("hidden", false);
            }
        }

        /** Restores visible tab save buttons after an AJAX round-trip. */
        var end_saving = function() {
            $(".megamenu_content:visible p.submit, .megamenu_content:visible .mega-widget-form-footer")
                .find("button.button-primary")
                .each(function() {
                    var $el = $(this);
                    $el.removeClass("is-busy")
                        .text($el.attr("data-orig-label") || "")
                        .removeAttr("data-orig-label");
                });
            var $ind = $(MENU_ITEM_DIALOG_SEL + " .megamenu-menu-item-dialog-saving-indicator");
            if ($ind.length) {
                $ind.prop("hidden", true);
            }
        }

        panel.init();

    };

}(jQuery));
