/*jslint browser: true, white: true, this: true, long: true */
/*global console,jQuery,megamenu,window,navigator*/

/*! Max Mega Menu jQuery Plugin */
(function ( $ ) {
    "use strict";

    let instanceCounter = 0;

    $.maxmegamenu = function(menu, options) {

        // --- DOM references ---
        const plugin       = this;
        const $menu        = $(menu);
        const $wrap        = $menu.parent();
        const $toggle_bar  = $menu.siblings(".mega-menu-toggle");

        // --- Instance identifiers ---
        const menuId            = $menu.attr("id");
        const instanceId        = menuId + '-' + (++instanceCounter);
        const docEventNamespace = '.megamenu-' + instanceId;

        // --- Cached element sets (captured at init) ---
        const items_with_submenus = $([
            "li.mega-menu-megamenu.mega-menu-item-has-children",
            "li.mega-menu-flyout.mega-menu-item-has-children",
            "li.mega-menu-tabbed > ul.mega-sub-menu > li.mega-menu-item-has-children",
            "li.mega-menu-flyout li.mega-menu-item-has-children"
        ].join(","), $menu);

        const collapse_children_parents = $("li.mega-menu-megamenu li.mega-menu-item-has-children.mega-collapse-children > a.mega-menu-link", $menu);

        // Focusable link elements inside a menu item, shared by the keyboard
        // navigation helpers so the matched set cannot drift between them.
        const focusable_selector = "a.mega-menu-link, button.mega-menu-link, .mega-search span[role=button]";

        // --- Keyboard key identifiers (KeyboardEvent.key values) ---
        const tab_key         = "Tab";
        const escape_key      = "Escape";
        const enter_key       = "Enter";
        const space_key       = " ";
        const left_arrow_key  = "ArrowLeft";
        const up_arrow_key    = "ArrowUp";
        const right_arrow_key = "ArrowRight";
        const down_arrow_key  = "ArrowDown";

        // --- Settings defaults (read from data attributes) ---
        const defaults = {
            event:                $menu.attr("data-event"),
            effect:               $menu.attr("data-effect"),
            effect_speed:         parseInt($menu.attr("data-effect-speed")),
            effect_mobile:        $menu.attr("data-effect-mobile"),
            effect_speed_mobile:  parseInt($menu.attr("data-effect-speed-mobile")),
            panel_width:          $menu.attr("data-panel-width"),
            panel_inner_width:    $menu.attr("data-panel-inner-width"),
            mobile_force_width:   $menu.attr("data-mobile-force-width"),
            mobile_overlay:       $menu.attr("data-mobile-overlay"),
            mobile_state:         $menu.attr("data-mobile-state"),
            mobile_direction:     $menu.attr("data-mobile-direction"),
            second_click:         $menu.attr("data-second-click"),
            vertical_behaviour:   $menu.attr("data-vertical-behaviour"),
            document_click:       $menu.attr("data-document-click"),
            breakpoint:           $menu.attr("data-breakpoint"),
            unbind_events:        $menu.attr("data-unbind"),
            hover_intent_timeout: $menu.attr("data-hover-intent-timeout") ?? 300,
            hover_intent_interval: $menu.attr("data-hover-intent-interval") ?? 100
        };

        // --- Mutable state ---
        plugin.settings = {};
        let html_body_class_timeout;

        plugin.addAnimatingClass = function(element) {
            if (plugin.settings.effect === "disabled") {
                return;
            }

            $(".mega-animating", $wrap).removeClass("mega-animating");

            const timeout = plugin.settings.effect_speed + parseInt(plugin.settings.hover_intent_timeout, 10);

            element.addClass("mega-animating");

            setTimeout(function() {
               element.removeClass("mega-animating");
            }, timeout );
        };

        plugin.hideAllPanels = function() {
            $(".mega-toggle-on > a.mega-menu-link", $menu).each(function() {
                plugin.hidePanel($(this), false);
            });
        };

        plugin.expandMobileSubMenus = function() {
            if (plugin.settings.mobile_direction !== 'vertical') {
                return;
            }
            
            $(".mega-menu-item-has-children.mega-expand-on-mobile > a.mega-menu-link", $menu).each(function() {
                plugin.showPanel($(this), true);
            });

            if ( plugin.settings.mobile_state === 'expand_all' ) {
                $(".mega-menu-item-has-children:not(.mega-toggle-on) > a.mega-menu-link", $menu).each(function() {
                    plugin.showPanel($(this), true);
                });
            }

            if ( plugin.settings.mobile_state === 'expand_active' ) {
                const activeItemSelectors = [
                    "li.mega-current-menu-ancestor.mega-menu-item-has-children > a.mega-menu-link",
                    "li.mega-current-menu-item.mega-menu-item-has-children > a.mega-menu-link",
                    "li.mega-current-menu-parent.mega-menu-item-has-children > a.mega-menu-link",
                    "li.mega-current_page_ancestor.mega-menu-item-has-children > a.mega-menu-link",
                    "li.mega-current_page_item.mega-menu-item-has-children > a.mega-menu-link"
                ];

                $menu.find(activeItemSelectors.join(', ')).each(function() {
                    plugin.showPanel($(this), true);
                });
            }
        };

        plugin.hideSiblingPanels = function(anchor, immediate) {
            anchor.parent().parent().find(".mega-toggle-on").children("a.mega-menu-link").each(function() { // all open children of open siblings
                plugin.hidePanel($(this), immediate);
            });
        };

        plugin.isDesktopView = function() {
            const width = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
            return width > plugin.settings.breakpoint;
        };

        plugin.isMobileView = function() {
            return !plugin.isDesktopView();
        };

        plugin.isHorizontalMobileSubmenuMode = function() {
            return plugin.isMobileView() && plugin.isMobileOffCanvas() && plugin.settings.mobile_direction === "horizontal";
        };

        const getBackLink = function($submenu) {
            return $submenu.find("> li.mega-mobile-back:visible > button.mega-mobile-back-link").first();
        };

        // The open submenu of the most deeply nested toggled-on menu item.
        const getDeepestOpenPanel = function() {
            return $("li.mega-toggle-on:not(.mega-collapse-children) > ul.mega-sub-menu", $menu).last();
        };

        plugin.getFocusableItemsInSubmenu = function($submenu) {
            // jQuery :visible checks layout dimensions but ignores CSS visibility:hidden.
            // Sliding sub-panels use visibility:hidden (not display:none) for the closed
            // state, so we must also check computed visibility to exclude their items.
            return $submenu
                .find(focusable_selector)
                .not(".mega-mobile-back-link")
                .filter(function() {
                    return $(this).is(":visible") && window.getComputedStyle(this).visibility !== 'hidden';
                });
        };

        plugin.focusFirstItemInOpenedSubmenu = function($item) {
            if ( ! plugin.isHorizontalMobileSubmenuMode() || ! $wrap.hasClass("mega-keyboard-navigation")) {
                return;
            }

            const $submenu = $item.children("ul.mega-sub-menu");

            if (!$submenu.length) {
                return;
            }

            // Search all descendants, not just direct li.mega-menu-item children, so that
            // megamenu panels (where links are nested inside li.mega-menu-row > li.mega-menu-column)
            // are handled the same as flat flyout panels.
            const $firstFocusable = $submenu
                .find(focusable_selector)
                .not(".mega-mobile-back-link")
                .filter(":visible")
                .first();

            if ($firstFocusable.length) {
                $firstFocusable.trigger("focus");
            } else {
                getBackLink($submenu).trigger("focus");
            }
        };

        plugin.deferFocusFirstItemInOpenedSubmenu = function($item) {
            const delay = Math.min(120, parseInt(plugin.settings.effect_speed_mobile, 10) || 0);

            setTimeout(function() {
                plugin.focusFirstItemInOpenedSubmenu($item);

                // Retry once after the first paint in case CSS visibility/transform has not applied yet.
                setTimeout(function() {
                    const focusedInSubmenu = $item.find("ul.mega-sub-menu").has(document.activeElement).length !== 0;

                    if (!focusedInSubmenu) {
                        plugin.focusFirstItemInOpenedSubmenu($item);
                    }
                }, 40);
            }, delay);
        };

        // Accepts a menu item ID (number or numeric string), a li.mega-menu-item or an
        // anchor, and returns the item's anchor.
        const resolveAnchor = function(anchor) {
            if ( typeof anchor === 'number' || ( typeof anchor === 'string' && anchor.trim() !== '' && !isNaN(anchor) ) ) {
                return $("li.mega-menu-item-" + anchor, $menu).find("a.mega-menu-link").first();
            }

            if ( anchor.is("li.mega-menu-item") ) {
                return anchor.find("a.mega-menu-link").first();
            }

            return anchor;
        };

        plugin.showPanel = function(anchor, immediate) {
            anchor = resolveAnchor(anchor);

            const $item = anchor.parent();
            const isDesktop = plugin.isDesktopView();
            const isMobile = !isDesktop;

            $item.triggerHandler("before_open_panel");

            $item.find("[aria-expanded]").first().attr("aria-expanded", "true");

            $(".mega-animating", $wrap).removeClass("mega-animating");

            if (isMobile && $item.hasClass("mega-hide-sub-menu-on-mobile")) {
                return;
            }

            if (isDesktop && ( $menu.hasClass("mega-menu-horizontal") || $menu.hasClass("mega-menu-vertical") ) && !$item.hasClass("mega-collapse-children")) {
                plugin.hideSiblingPanels(anchor, true);
            }

            if ((isMobile && $wrap.hasClass("mega-keyboard-navigation")) || plugin.settings.vertical_behaviour === "accordion") {
                plugin.hideSiblingPanels(anchor, false);
            }

            plugin.calculateDynamicSubmenuWidths(anchor);

            // apply jQuery transition (only if the effect is set to "slide", other transitions are CSS based)
            if ( plugin.shouldUseSlideAnimation(anchor, immediate) ) {
                const speed = isMobile ? plugin.settings.effect_speed_mobile : plugin.settings.effect_speed;

                anchor.siblings(".mega-sub-menu").css("display", "none").animate({"height":"show", "paddingTop":"show", "paddingBottom":"show", "minHeight":"show"}, speed, function() {
                    $(this).css("display", "");
                });
            }

            $item.addClass("mega-toggle-on").triggerHandler("open_panel");
            plugin.deferFocusFirstItemInOpenedSubmenu($item);
        };
        

        plugin.shouldUseSlideAnimation = function(anchor, immediate) {

            if (immediate === true) {
                return false;
            }

            if (anchor.parent().hasClass("mega-collapse-children")) {
                return true;
            }

            const isDesktop = plugin.isDesktopView();

            if (isDesktop && plugin.settings.effect === "slide") {
                return true;
            }

            if (!isDesktop) {
                if (plugin.settings.effect_mobile === "slide") {
                    return true;
                }

                if ( plugin.isMobileOffCanvas() ) {
                    return plugin.settings.mobile_direction !== "horizontal";
                }
            }

            return false;
        };


        plugin.hidePanel = function(anchor, immediate) {
            anchor = resolveAnchor(anchor);

            const $item = anchor.parent();
            const $submenu = anchor.siblings(".mega-sub-menu");
            const isMobile = plugin.isMobileView();

            $item.triggerHandler("before_close_panel");

            $item.find("[aria-expanded]").first().attr("aria-expanded", "false");

            if ( plugin.shouldUseSlideAnimation(anchor) ) {
                const speed = isMobile ? plugin.settings.effect_speed_mobile : plugin.settings.effect_speed;

                $submenu.animate({"height":"hide", "paddingTop":"hide", "paddingBottom":"hide", "minHeight":"hide"}, speed, function() {
                    $submenu.css("display", "");
                    $item.removeClass("mega-toggle-on").triggerHandler("close_panel");
                });

                return;
            }

            if (immediate) {
                $submenu.css("display", "none").delay(plugin.settings.effect_speed).queue(function(){
                    $(this).css("display", "").dequeue();
                });
            }

            // pause video widget videos
            $submenu.find(".widget_media_video video").each(function() {
                if ( this.player ) {
                    this.player.pause();
                }
            });

            $item.removeClass("mega-toggle-on").triggerHandler("close_panel");
            plugin.addAnimatingClass($item);
        };

        // Resolve a panel width setting ('$menu', '$wrap' or a CSS selector) to elements.
        const resolveWidthEl = function(val) {
            return val === '$menu' ? $menu : val === '$wrap' ? $wrap : $(val);
        };

        plugin.calculateDynamicSubmenuWidths = function(anchor) {
            const $item = anchor.parent();
            const $submenu = anchor.siblings(".mega-sub-menu");
            const isDesktop = plugin.isDesktopView();
            const isTopLevelMegamenu = $item.hasClass("mega-menu-megamenu") && $item.parent().hasClass("max-mega-menu");

            // apply dynamic width and sub menu position (only to top level mega menus)
            if (isTopLevelMegamenu && plugin.settings.panel_width) {
                if (isDesktop) {
                    const submenu_offset = $menu.offset();

                    if ( plugin.settings.panel_width === '100vw' ) {
                        const target_offset = $('body').offset();

                        $submenu.css({
                            left: (target_offset.left - submenu_offset.left) + "px"
                        });
                    } else {
                        const $panel_width_el = resolveWidthEl(plugin.settings.panel_width);

                        if ( $panel_width_el.length > 0 ) {
                            $submenu.css({
                                width: $panel_width_el.outerWidth(),
                                left: ($panel_width_el.offset().left - submenu_offset.left) + "px"
                            });
                        }
                    }
                } else {
                    $submenu.css({
                        width: "",
                        left: ""
                    });
                }
            }

            // apply inner width to sub menu by adding padding to the left and right of the mega menu
            if (isTopLevelMegamenu && plugin.settings.panel_inner_width) {
                const $panel_inner_width_el = resolveWidthEl(plugin.settings.panel_inner_width);

                if ($panel_inner_width_el.length > 0) {
                    const target_width = parseInt($panel_inner_width_el.width(), 10);

                    $submenu.css({
                        "paddingLeft": "",
                        "paddingRight": ""
                    });

                    const submenu_width = parseInt($submenu.innerWidth(), 10);

                    if (isDesktop && target_width > 0 && target_width < submenu_width) {
                        $submenu.css({
                            "paddingLeft": (submenu_width - target_width) / 2 + "px",
                            "paddingRight": (submenu_width - target_width) / 2 + "px"
                        });
                    }
                }
            }
        };

        const recalculateSubmenuWidths = function() {
            plugin.calculateDynamicSubmenuWidths($("> li.mega-menu-megamenu > a.mega-menu-link", $menu));
        };

        plugin.bindClickEvents = function() {

            plugin.unbindClickEvents();

            let dragging = false;

            $(document).on({
                ["touchmove" + docEventNamespace]: function() { dragging = true; },
                ["touchstart" + docEventNamespace]: function() { dragging = false; }
            });

            $(document).on("click" + docEventNamespace + " touchend" + docEventNamespace, function(e) { // hide menu when clicked away from
                if (!dragging && plugin.settings.document_click === "collapse" && ! $(e.target).closest(".mega-menu-wrap").length ) {
                    plugin.hideAllPanels();
                    plugin.hideMobileMenu();
                }
                dragging = false;
            });

            collapse_children_parents.off("click.megamenu touchend.megamenu");
            const clickable_parents = $("> a.mega-menu-link", items_with_submenus).add(collapse_children_parents);

            clickable_parents.on("touchend.megamenu", function(e) {
                if (plugin.settings.event === "hover_intent") {
                    plugin.unbindHoverIntentEvents();
                }

                if (plugin.settings.event === "hover") {
                    plugin.unbindHoverEvents();
                }
            });

            clickable_parents.on("click.megamenu", function(e) {
                if ( $(e.target).hasClass('mega-indicator') ) {
                    return;
                }

                if (plugin.isDesktopView() && $(this).parent().hasClass("mega-toggle-on") && $(this).closest("ul.mega-sub-menu").parent().hasClass("mega-menu-tabbed") ) {
                    if (plugin.settings.second_click === "go") {
                        return;
                    } else {
                        e.preventDefault();
                        return;
                    }
                }
                if (dragging) {
                    return;
                }
                if (plugin.isMobileView() && $(this).parent().hasClass("mega-hide-sub-menu-on-mobile")) {
                    return; // allow all clicks on parent items when sub menu is hidden on mobile
                }
                if ((plugin.settings.second_click === "go" || $(this).parent().hasClass("mega-click-click-go")) && $(this).attr("href") !== undefined) { // check for second click
                    if (!$(this).parent().hasClass("mega-toggle-on")) {
                        e.preventDefault();
                        plugin.showPanel($(this));
                    }
                } else {
                    e.preventDefault();

                    if ($(this).parent().hasClass("mega-toggle-on")) {
                        plugin.hidePanel($(this), false);
                    } else {
                        plugin.showPanel($(this));
                    }
                }
            });

            if ( plugin.settings.second_click === "disabled" ) {
                clickable_parents.off("click.megamenu");
            }

            $(".mega-close-after-click:not(.mega-menu-item-has-children) > a.mega-menu-link", $menu).on("click.megamenu", function() {
                plugin.hideAllPanels();
                plugin.hideMobileMenu();
            });

            $("button.mega-close", $wrap).on("click.megamenu", function(e) {
                plugin.hideMobileMenu();
            });
        };

        plugin.bindHoverEvents = function() {
            items_with_submenus.on({
                "mouseenter.megamenu" : function() {
                    plugin.unbindClickEvents();
                    if (! $(this).hasClass("mega-toggle-on")) {
                        plugin.showPanel($(this).children("a.mega-menu-link"));
                    }
                },
                "mouseleave.megamenu" : function() {
                    if ($(this).hasClass("mega-toggle-on") && ! $(this).hasClass("mega-disable-collapse") && ! $(this).parent().parent().hasClass("mega-menu-tabbed")) {
                        plugin.hidePanel($(this).children("a.mega-menu-link"), false);
                    }
                }
            });
        };

        plugin.bindHoverIntentEvents = function() {
            items_with_submenus.hoverIntent({
                over: function () {
                    plugin.unbindClickEvents();
                    if (! $(this).hasClass("mega-toggle-on")) {
                        plugin.showPanel($(this).children("a.mega-menu-link"));
                    }
                },
                out: function () {
                    if ($(this).hasClass("mega-toggle-on") && ! $(this).hasClass("mega-disable-collapse") && ! $(this).parent().parent().hasClass("mega-menu-tabbed")) {
                        plugin.hidePanel($(this).children("a.mega-menu-link"), false);
                    }
                },
                timeout: plugin.settings.hover_intent_timeout,
                interval: plugin.settings.hover_intent_interval
            });
        };

        plugin.isMobileOffCanvas = function() {
            return plugin.settings.effect_mobile === 'slide_left' || plugin.settings.effect_mobile === 'slide_right';
        };

        plugin.shouldGoToNextTopLevelItem = function(key) {
            return ( ( key === right_arrow_key && plugin.isDesktopView() ) || ( key === down_arrow_key && plugin.isMobileView() ) ) && $menu.hasClass("mega-menu-horizontal");
        };

        plugin.shouldGoToPreviousTopLevelItem = function(key) {
            return ( ( key === left_arrow_key && plugin.isDesktopView() ) || ( key === up_arrow_key && plugin.isMobileView() ) ) && $menu.hasClass("mega-menu-horizontal");
        };

        plugin.bindKeyboardEvents = function() {
            const $firstFocusable = $menu.find("a.mega-menu-link").first();
            const $lastFocusable  = $wrap.find("button.mega-close").first();
            // Direct-child variant of focusable_selector: matches only the link element
            // belonging to the item itself, not links inside nested submenus.
            const focusableLinkSelector = focusable_selector.split(", ").map(s => "> " + s).join(", ");

            const togglePanelForAnchor = function(anchor) {
                if ( !anchor || !anchor.length ) return;
                if ( anchor.parent().hasClass("mega-toggle-on") && !anchor.closest("ul.mega-sub-menu").parent().hasClass("mega-menu-tabbed") ) {
                    plugin.hidePanel(anchor);
                } else {
                    plugin.showPanel(anchor);
                }
            };

            const closeNearestOpenPanelAndRefocus = function() {
                const $focused = $menu[0].contains(document.activeElement) ? $(document.activeElement) : $();
                const $parentAnchor = $("> a.mega-menu-link", $focused.closest(".mega-toggle-on"));
                if ( $parentAnchor.length ) {
                    plugin.hidePanel($parentAnchor);
                    $parentAnchor.trigger("focus");
                    return true;
                }
                return false;
            };

            // ── Key Handlers ─────────────────────────────────────────────────────────

            const handleTabKey = function(e, $active, isOffCanvasHorizontal, isMobileOffCanvas) {
                // Close button: hand focus off to the menu, or receive it back from the menu
                if ( $active.is($lastFocusable) && isMobileOffCanvas ) {
                    if ( !e.shiftKey ) {
                        // Tab → forward to back link (if a flyout is open) or first menu item
                        e.preventDefault();
                        const $backLink = isOffCanvasHorizontal ? getBackLink(getDeepestOpenPanel()) : $();
                        ( $backLink.length ? $backLink : $firstFocusable ).trigger('focus');
                    } else if ( isOffCanvasHorizontal ) {
                        // Shift+Tab → last item in the active submenu
                        const $last = plugin.getFocusableItemsInSubmenu(getDeepestOpenPanel()).last();
                        if ( $last.length ) { e.preventDefault(); $last.trigger('focus'); }
                    }
                    return;
                }

                // First top-level item: Shift+Tab wraps around to the close button
                if ( isMobileOffCanvas && e.shiftKey && $active.is($firstFocusable) ) {
                    e.preventDefault();
                    $lastFocusable.trigger('focus');
                    return;
                }

                // Inside an offcanvas horizontal submenu: keep focus trapped within the panel
                if ( isOffCanvasHorizontal ) {
                    const $deepestPanel = getDeepestOpenPanel();

                    // Ghost-item guard: a panel is open but focus escaped to an item outside it
                    // (top-level links, or parent-panel items covered by a child panel).
                    if ( $deepestPanel.length && !$deepestPanel.has($active).length ) {
                        e.preventDefault();
                        if ( !e.shiftKey ) {
                            const $backLink = getBackLink($deepestPanel);
                            ( $backLink.length ? $backLink : plugin.getFocusableItemsInSubmenu($deepestPanel).first() ).trigger('focus');
                        } else {
                            $lastFocusable.trigger('focus');
                        }
                        return;
                    }

                    // Walk up to the nearest ul.mega-sub-menu whose parent li has mega-toggle-on.
                    // For mega menus, $active may be inside a column ul rather than the panel ul itself.
                    const $submenu = $active.parentsUntil($menu, "ul.mega-sub-menu")
                        .filter(function() {
                            const $p = $(this).parent();
                            return $p.hasClass("mega-toggle-on") && !$p.hasClass("mega-collapse-children");
                        })
                        .first();
                    if ( $submenu.length ) {
                        if ( !e.shiftKey ) {
                            // Tab forward: when on the last item, hand off to the close button
                            const $items = plugin.getFocusableItemsInSubmenu($submenu);
                            if ( $items.length && $active.is($items.last()) ) {
                                e.preventDefault();
                                $lastFocusable.trigger('focus');
                            }
                        } else if ( $active.hasClass("mega-mobile-back-link") ) {
                            // Shift+Tab on back link → close button
                            e.preventDefault();
                            $lastFocusable.trigger('focus');
                        } else {
                            // Shift+Tab on first item → back link
                            const $items   = plugin.getFocusableItemsInSubmenu($submenu);
                            const $backLink = getBackLink($submenu);
                            if ( $items.length && $backLink.length && $active.is($items.first()) ) {
                                e.preventDefault();
                                $backLink.trigger('focus');
                            }
                        }
                    }
                }
            };

            const handleToggleBarTrigger = function(e) {
                e.preventDefault();
                if ( $toggle_bar.hasClass("mega-menu-open") ) {
                    plugin.hideMobileMenu();
                } else {
                    plugin.showMobileMenu();
                    html_body_class_timeout = setTimeout(function() {
                        $menu.find("a.mega-menu-link").first().trigger('focus');
                    }, plugin.settings.effect_speed_mobile);
                }
            };

            const handleSpaceKey = function(e, $active) {
                if ( $active.is("a.mega-menu-link") ) {
                    e.preventDefault(); // prevent page scroll on any menu link
                    if ( $active.parent().is(items_with_submenus) ) togglePanelForAnchor($active);
                } else if ( $active.is(".mega-indicator") ) {
                    e.preventDefault();
                    togglePanelForAnchor($active.parent());
                }
            };

            const handleEscapeKey = function() {
                const submenu_open = $(".mega-toggle-on", $menu).length !== 0;
                if ( submenu_open && closeNearestOpenPanelAndRefocus() ) return;
                if ( plugin.isMobileView() && !submenu_open ) plugin.hideMobileMenu();
            };

            const handleEnterKey = function(e, $active) {
                if ( $active.is(".mega-indicator") ) {
                    togglePanelForAnchor($active.parent());
                    return;
                }
                if ( $active.parent().is(items_with_submenus) ) {
                    if ( plugin.isMobileView() && $active.parent().is(".mega-hide-sub-menu-on-mobile") ) return;
                    if ( !$active.is("[href]") ) { togglePanelForAnchor($active); return; }
                    if ( $active.parent().hasClass("mega-toggle-on") && !$active.closest("ul.mega-sub-menu").parent().hasClass("mega-menu-tabbed") ) return;
                    e.preventDefault();
                    plugin.showPanel($active);
                }
            };

            const handleArrowUpDown = function(e, goingDown) {
                e.preventDefault();
                const $activeSubmenu = getDeepestOpenPanel();

                if ( $activeSubmenu.length ) {
                    // Inside a flyout panel: cycle through the same sequence as Tab/Shift+Tab —
                    // back-link → items → close-button — using an explicit ordered array so
                    // the back button (li.mega-mobile-back, not li.mega-menu-item) is included.
                    const $backLink  = getBackLink($activeSubmenu);
                    const $items     = plugin.getFocusableItemsInSubmenu($activeSubmenu);
                    const focusOrder = [];
                    if ( $backLink.length ) focusOrder.push($backLink[0]);
                    $items.each(function() { focusOrder.push(this); });
                    if ( $lastFocusable.length ) focusOrder.push($lastFocusable[0]);

                    const idx     = focusOrder.indexOf(document.activeElement);
                    const nextIdx = (idx < 0)
                        ? (goingDown ? 0 : focusOrder.length - 1)
                        : (idx + (goingDown ? 1 : -1) + focusOrder.length) % focusOrder.length;
                    $(focusOrder[nextIdx]).trigger('focus');
                } else {
                    // Top level: cycle through the visible top-level links.
                    const $topLinks = $menu.children("li.mega-menu-item:visible").find(focusableLinkSelector);
                    const idx       = $topLinks.index(document.activeElement);
                    if ( idx >= 0 ) {
                        const nextIdx = (idx + (goingDown ? 1 : -1) + $topLinks.length) % $topLinks.length;
                        $topLinks.eq(nextIdx).trigger('focus');
                    }
                }
            };

            const handleArrowLeftRight = function(e, goingToNext) {
                e.preventDefault();
                // Use find(focusableLinkSelector) with > so only direct-child links are
                // matched — prevents picking up nested submenu links as top-level items.
                const $topLinks = $menu.children("li.mega-menu-item:visible").find(focusableLinkSelector);
                const $topLink  = $(document.activeElement)
                    .closest($menu.children("li.mega-menu-item"))
                    .find(focusableLinkSelector).first();
                const idx     = $topLinks.index($topLink);
                const nextIdx = goingToNext ? idx + 1 : idx - 1;
                // Guard: nextIdx < 0 would let jQuery's eq(-1) silently wrap to the last item
                if ( idx >= 0 && nextIdx >= 0 ) {
                    plugin.hideAllPanels();
                    $topLinks.eq(nextIdx).trigger('focus'); // eq(length) = empty set = no-op at boundary
                }
            };

            // ── Event Bindings ───────────────────────────────────────────────────────

            $wrap.on("keyup.megamenu", ".max-mega-menu, .mega-menu-toggle", function(e) {
                if ( e.key !== tab_key ) return;
                $wrap.addClass("mega-keyboard-navigation");
                plugin.bindClickEvents(); // Windows Narrator ignores Enter, so ensure click events are bound on tab
                const $target = $(e.target);
                if ( plugin.isDesktopView() && $target.is(".mega-menu-link") && $target.parent().parent().hasClass('max-mega-menu') ) {
                    plugin.hideAllPanels();
                }
            });

            $wrap.on("keydown.megamenu", "a.mega-menu-link, button.mega-menu-link, button.mega-mobile-back-link, .mega-indicator, .mega-menu-toggle-block, .mega-menu-toggle-animated-block button, button.mega-close", function(e) {
                if ( !$wrap.hasClass("mega-keyboard-navigation") ) return;

                const key = e.key;
                const $active = $(e.target);
                const isOffCanvasHorizontal = plugin.isHorizontalMobileSubmenuMode();
                const isMobileOffCanvas = plugin.isMobileView() && plugin.isMobileOffCanvas();

                switch (key) {
                    case tab_key:
                        handleTabKey(e, $active, isOffCanvasHorizontal, isMobileOffCanvas);
                        break;

                    case space_key:
                        if ( $active.is(".mega-menu-toggle-block button, .mega-menu-toggle-animated-block button") ) {
                            handleToggleBarTrigger(e);
                        } else {
                            handleSpaceKey(e, $active);
                        }
                        break;

                    case enter_key:
                        if ( $active.is(".mega-menu-toggle-block button, .mega-menu-toggle-animated-block button") ) {
                            handleToggleBarTrigger(e);
                        } else {
                            handleEnterKey(e, $active);
                        }
                        break;

                    case escape_key:
                        handleEscapeKey();
                        break;

                    case up_arrow_key:
                    case down_arrow_key:
                        if ( isOffCanvasHorizontal ) {
                            handleArrowUpDown(e, key === down_arrow_key);
                        }
                        break;

                    case left_arrow_key:
                    case right_arrow_key:
                        const goingToNext = plugin.shouldGoToNextTopLevelItem(key);
                        const goingToPrev = plugin.shouldGoToPreviousTopLevelItem(key);
                        if ( goingToNext || goingToPrev ) {
                            handleArrowLeftRight(e, goingToNext);
                        }
                        break;
                }
            });

            $wrap.on("focusout.megamenu", function(e) {
                if ( $wrap.hasClass("mega-keyboard-navigation") ) {
                    setTimeout(function() {
                        if ( !$wrap[0].contains(document.activeElement) ) {
                            $wrap.removeClass("mega-keyboard-navigation");
                            plugin.hideAllPanels();
                            plugin.hideMobileMenu();
                        }
                    }, 10);
                }
            });
        };

        plugin.unbindAllEvents = function() {
            $(document).off(docEventNamespace);
            $("ul.mega-sub-menu, li.mega-menu-item, li.mega-menu-row, li.mega-menu-column, a.mega-menu-link, .mega-indicator", $menu).off();
        };

        plugin.unbindClickEvents = function() {
            if ( $wrap.hasClass('mega-keyboard-navigation') ) {
                return;
            }

            $(document).off(docEventNamespace);

            // collapsable parents always have a click event
            $("> a.mega-menu-link", items_with_submenus).not(collapse_children_parents).off("click.megamenu touchend.megamenu");
        };

        plugin.unbindHoverEvents = function() {
            items_with_submenus.off("mouseenter.megamenu mouseleave.megamenu");
        };

        plugin.unbindHoverIntentEvents = function() {
            items_with_submenus.off("mouseenter mouseleave").removeProp("hoverIntent_t").removeProp("hoverIntent_s"); // hoverintent does not allow namespaced events
        };

        plugin.unbindKeyboardEvents = function() {
            $wrap.off("keyup.megamenu keydown.megamenu focusout.megamenu");
        };

        plugin.unbindMegaMenuEvents = function() {
            if (plugin.settings.event === "hover_intent") {
                plugin.unbindHoverIntentEvents();
            }

            if (plugin.settings.event === "hover") {
                plugin.unbindHoverEvents();
            }

            plugin.unbindClickEvents();
            plugin.unbindKeyboardEvents();
        };

        plugin.bindMegaMenuEvents = function() {
            plugin.unbindMegaMenuEvents();

            const isDesktop = plugin.isDesktopView();

            if (isDesktop && plugin.settings.event === "hover_intent") {
                plugin.bindHoverIntentEvents();
            }

            if (isDesktop && plugin.settings.event === "hover") {
                plugin.bindHoverEvents();
            }

            plugin.bindClickEvents(); // always bind click events for touch screen devices
            plugin.bindKeyboardEvents();
        };

        plugin.checkWidth = function() {
            if ( plugin.isMobileView() && $menu.data("view") === "desktop" ) {
                plugin.switchToMobile();
            }

            if ( plugin.isDesktopView() && $menu.data("view") === "mobile" ) {
                plugin.switchToDesktop();
            }

            recalculateSubmenuWidths();
        };

        plugin.reverseRightAlignedItems = function() {
            if ( ! $("body").hasClass("rtl") && $menu.hasClass("mega-menu-horizontal") && $menu.css("display") !== 'flex' ) {
                $menu.append($menu.children("li.mega-item-align-right").get().reverse());
            }
        };

        plugin.addClearClassesToMobileItems = function() {
            $(".mega-menu-row", $menu).each(function() {
                $("> .mega-sub-menu > .mega-menu-column:not(.mega-hide-on-mobile)", $(this)).filter(":even").addClass("mega-menu-clear"); // :even is 0 based
            });
        };

        plugin.initDesktop = function() {
            $menu.data("view", "desktop");
            plugin.bindMegaMenuEvents();
            plugin.initIndicators();
            $menu.trigger("mmm:switchToDesktop");
        };

        plugin.initMobile = function() {
            plugin.switchToMobile();
        };

        plugin.switchToDesktop = function() {
            $menu.data("view", "desktop");
            plugin.bindMegaMenuEvents();
            plugin.reverseRightAlignedItems();
            plugin.hideAllPanels();
            plugin.hideMobileMenu(true);
            $menu.removeAttr('role');
            $menu.removeAttr('aria-modal');
            $menu.removeAttr('aria-hidden');
            $menu.trigger("mmm:switchToDesktop");
        };

        plugin.switchToMobile = function() {
            $menu.data("view", "mobile");

            if (plugin.isMobileOffCanvas() && $toggle_bar.is(":visible") ) {
                $menu.attr('role', 'dialog');
                $menu.attr('aria-modal', 'true');
                $menu.attr('aria-hidden', 'true');
            }

            plugin.bindMegaMenuEvents();
            plugin.initIndicators();
            plugin.reverseRightAlignedItems();
            plugin.addClearClassesToMobileItems();
            plugin.hideAllPanels();
            plugin.expandMobileSubMenus();

            $menu.trigger("mmm:switchToMobile");

        };

        plugin.initToggleBar = function() {
            $toggle_bar.on("click", function(e) {
                const isToggleTrigger = $(e.target).closest(".mega-menu-toggle-block, button.mega-toggle-animated, .mega-menu-toggle-custom-block", this).length;

                if ( isToggleTrigger ) {
                    e.preventDefault();
                    if ($(this).hasClass("mega-menu-open")) {
                        plugin.hideMobileMenu();
                    } else {
                        plugin.showMobileMenu();
                    }
                } else if ( e.target === this && plugin.isMobileOffCanvas() ) {
                    plugin.hideMobileMenu();
                }
            });
        };

        plugin.initIndicators = function() {
             $menu.off('click.megamenu', '.mega-indicator');

             $menu.on('click.megamenu', '.mega-indicator', function(e) {
                e.preventDefault();
                e.stopPropagation();

                if ( $(this).closest(".mega-menu-item").hasClass("mega-toggle-on") ) {
                    if ( ! $(this).closest("ul.mega-sub-menu").parent().hasClass("mega-menu-tabbed") || plugin.isMobileView() ) {
                        plugin.hidePanel($(this).parent(), false);
                    }
                } else {
                    plugin.showPanel($(this).parent(), false);
                }
             });
        };

        plugin.hideMobileMenu = function(force = false) {

            if ( ! $toggle_bar.is(":visible") && ! force ) {
                return;
            }

            $menu.attr("aria-hidden", "true");

            clearTimeout(html_body_class_timeout);
            html_body_class_timeout = setTimeout(function() {
                $("body").removeClass(menuId + "-mobile-open");
                $("html").removeClass(menuId + "-off-canvas-open");
            }, plugin.settings.effect_speed_mobile);

            if ($wrap.hasClass("mega-keyboard-navigation")) {
                $(".mega-menu-toggle-block button, button.mega-toggle-animated", $toggle_bar).first().trigger('focus');
            }

            $(".mega-toggle-label, .mega-toggle-animated", $toggle_bar).attr("aria-expanded", "false");

            const resetMenuStyles = function() {
                $menu.css({
                    width: "",
                    left: "",
                    display: ""
                });

                $toggle_bar.removeClass("mega-menu-open");
            };

            if (plugin.settings.effect_mobile === "slide" && ! force ) {
                $menu.animate({"height":"hide"}, plugin.settings.effect_speed_mobile, resetMenuStyles);
            } else {
                resetMenuStyles();
            }

            $menu.triggerHandler("mmm:hideMobileMenu");
        };

        plugin.showMobileMenu = function() {
            if ( ! $toggle_bar.is(":visible") ) {
                return;
            }

            clearTimeout(html_body_class_timeout);

            $("body").addClass(menuId + "-mobile-open");

            plugin.expandMobileSubMenus();

            if ( plugin.isMobileOffCanvas() ) {
                $("html").addClass(menuId + "-off-canvas-open");
            }

            if (plugin.settings.effect_mobile === "slide") {
                $menu.animate({"height":"show"}, plugin.settings.effect_speed_mobile, function() {
                    $(this).css("display", "");
                });
            }

            $(".mega-toggle-label, .mega-toggle-animated", $toggle_bar).attr("aria-expanded", "true");

            $toggle_bar.addClass("mega-menu-open");

            plugin.toggleBarForceWidth();

            $menu.attr("aria-hidden", "false");
            $menu.triggerHandler("mmm:showMobileMenu");
        };

        plugin.toggleBarForceWidth = function() {
            const $force_width_el = $(plugin.settings.mobile_force_width);

            if ($force_width_el.length && ( plugin.settings.effect_mobile === "slide" || plugin.settings.effect_mobile === "disabled" ) ) {
                const submenu_offset = $toggle_bar.offset();
                const target_offset = $force_width_el.offset();

                $menu.css({
                    width: $force_width_el.outerWidth(),
                    left: (target_offset.left - submenu_offset.left) + "px"
                });
            }
        };

        plugin.doConsoleChecks = function() {
            if (plugin.settings.mobile_force_width !== "false" && ! $(plugin.settings.mobile_force_width).length && ( plugin.settings.effect_mobile === "slide" || plugin.settings.effect_mobile === "disabled" ) ) {
                console.warn('Max Mega Menu #' + $wrap.attr('id') + ': Mobile Force Width element (' + plugin.settings.mobile_force_width + ') not found');
            }

            const cssWidthRegex = /^((\d+(\.\d+)?(px|%|em|rem|vw|vh|ch|ex|cm|mm|in|pt|pc))|auto)$/i;

            if (plugin.settings.panel_width !== undefined && ! cssWidthRegex.test(plugin.settings.panel_width) && ! resolveWidthEl(plugin.settings.panel_width).length ) {
                console.warn('Max Mega Menu #' + $wrap.attr('id') + ': Panel Width (Outer) element (' + plugin.settings.panel_width + ') not found');
            }

            if (plugin.settings.panel_inner_width !== undefined && ! cssWidthRegex.test(plugin.settings.panel_inner_width) && ! resolveWidthEl(plugin.settings.panel_inner_width).length ) {
                console.warn('Max Mega Menu #' + $wrap.attr('id') + ': Panel Width (Inner) element (' + plugin.settings.panel_inner_width + ') not found');
            }
        };

        plugin.init = function() {
            $menu.triggerHandler("before_mega_menu_init");
            plugin.settings = $.extend({}, defaults, options);

            if (window.console) {
                plugin.doConsoleChecks();
            }

            $menu.removeClass("mega-no-js");

            plugin.initToggleBar();
            
            if (plugin.settings.unbind_events === "true") {
                plugin.unbindAllEvents();
            }

            if ( document.readyState === 'complete' ) {
                recalculateSubmenuWidths();
            } else {
                $(window).on("load", recalculateSubmenuWidths);
            }

            if ( plugin.isDesktopView() ) {
                plugin.initDesktop();
            } else {
                plugin.initMobile();
            }

            $(window).on("resize", function() {
                plugin.checkWidth();
            });

            $menu.triggerHandler("after_mega_menu_init");
        };

        plugin.init();
    };

    $.fn.maxmegamenu = function(options) {
        return this.each(function() {
            if (undefined === $(this).data("maxmegamenu")) {
                const plugin = new $.maxmegamenu(this, options);
                $(this).data("maxmegamenu", plugin);
            }
        });
    };

    $(function() {
        $(".max-mega-menu").maxmegamenu();
    });
}( jQuery ));