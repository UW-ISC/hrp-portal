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

        plugin.getFocusableItemsInSubmenu = function($submenu, include_back_link = true) {
            let $focusable = $submenu.children("li.mega-menu-item:visible").find("> a.mega-menu-link, > .mega-search span[role=button]");

            if (!include_back_link) {
                $focusable = $focusable.not(".mega-mobile-back-link");
            }

            return $focusable;
        };

        plugin.focusFirstItemInOpenedSubmenu = function($item) {
            if ( ! plugin.isHorizontalMobileSubmenuMode() || ! $wrap.hasClass("mega-keyboard-navigation")) {
                return;
            }

            const $submenu = $item.children("ul.mega-sub-menu");

            if (!$submenu.length) {
                return;
            }

            const $firstFocusable = plugin.getFocusableItemsInSubmenu($submenu, false).first();

            if ($firstFocusable.length) {
                $firstFocusable.trigger("focus");
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

        plugin.showPanel = function(anchor, immediate) {
            if ( typeof anchor === 'number' || ( typeof anchor === 'string' && anchor.trim() !== '' && !isNaN(anchor) ) ) {
                anchor = $("li.mega-menu-item-" + anchor, $menu).find("a.mega-menu-link").first();
            } else if ( anchor.is("li.mega-menu-item") ) {
                anchor = anchor.find("a.mega-menu-link").first();
            }

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
            if ( typeof anchor === 'number' || ( typeof anchor === 'string' && anchor.trim() !== '' && !isNaN(anchor) ) ) {
                anchor = $("li.mega-menu-item-" + anchor, $menu).find("a.mega-menu-link").first();
            } else if ( anchor.is("li.mega-menu-item") ) {
                anchor = anchor.find("a.mega-menu-link").first();
            }

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
                        const $panel_width_el = $(plugin.settings.panel_width);

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
                const $panel_inner_width_el = $(plugin.settings.panel_inner_width);

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
            const $lastFocusable = $wrap.find("button.mega-close").first();

            const isMobileOffCanvasHorizontal = function() {
                return plugin.isHorizontalMobileSubmenuMode();
            };

            const getActiveHorizontalSubmenuBackLink = function() {
                const $activeSubmenu = $("li.mega-toggle-on > ul.mega-sub-menu", $menu).last();
                return $activeSubmenu.find("> li.mega-mobile-back:visible > a.mega-menu-link.mega-mobile-back-link").first();
            };

            const shouldTrapFocusInCurrentSubMenu = function(key) {
                return isMobileOffCanvasHorizontal() && ( key === up_arrow_key || key === down_arrow_key );
            };
            const togglePanelForAnchor = function(anchor) {
                if ( !anchor || !anchor.length ) {
                    return;
                }

                if ( anchor.parent().hasClass("mega-toggle-on") && ! anchor.closest("ul.mega-sub-menu").parent().hasClass("mega-menu-tabbed") ) {
                    plugin.hidePanel(anchor);
                } else {
                    plugin.showPanel(anchor);
                }
            };
            const closeNearestOpenPanelAndRefocus = function() {
                const focused_menu_item = $menu[0].contains(document.activeElement) ? $(document.activeElement) : $();
                const nearest_parent_of_focused_item_li = focused_menu_item.closest(".mega-toggle-on");
                const nearest_parent_of_focused_item_a = $("> a.mega-menu-link", nearest_parent_of_focused_item_li);

                if ( nearest_parent_of_focused_item_a.length ) {
                    plugin.hidePanel(nearest_parent_of_focused_item_a);
                    nearest_parent_of_focused_item_a.trigger("focus");
                    return true;
                }

                return false;
            };

            $lastFocusable.on('keydown.megamenu', function(e) {
                const key = e.key;

                if ( plugin.isMobileView() && plugin.isMobileOffCanvas() && key === tab_key && ! e.shiftKey ) {
                    e.preventDefault();
                    if ( isMobileOffCanvasHorizontal() ) {
                        const $backLink = getActiveHorizontalSubmenuBackLink();

                        if ( $backLink.length ) {
                            $backLink.trigger('focus');
                            return;
                        }
                    }

                    $firstFocusable.trigger('focus');
                }

                if ( plugin.isMobileView() && plugin.isMobileOffCanvas() && key === tab_key && e.shiftKey && isMobileOffCanvasHorizontal() ) {
                    const $activeSubmenu = $("li.mega-toggle-on > ul.mega-sub-menu", $menu).last();
                    const $focusableWithoutBack = plugin.getFocusableItemsInSubmenu($activeSubmenu, false);
                    const $lastFocusableInSubmenu = $focusableWithoutBack.last();

                    if ( $lastFocusableInSubmenu.length ) {
                        e.preventDefault();
                        $lastFocusableInSubmenu.trigger('focus');
                    }
                }
            });

            $firstFocusable.on('keydown.megamenu', function(e) {
                const key = e.key;

                if ( plugin.isMobileView() && plugin.isMobileOffCanvas() && key === tab_key && e.shiftKey) {
                    e.preventDefault();
                    $lastFocusable.trigger('focus');
                }
            });

            $wrap.on("keyup.megamenu", ".max-mega-menu, .mega-menu-toggle", function(e) {
                const key = e.key;
                const active_link = $(e.target);

                if (key === tab_key) {
                    $wrap.addClass("mega-keyboard-navigation");
                    plugin.bindClickEvents(); // Windows Narrator ignores the Enter keypress, so ensure click events are available when pressing tab

                    if ( plugin.isDesktopView() && active_link.is(".mega-menu-link") && active_link.parent().parent().hasClass('max-mega-menu') ) {
                        plugin.hideAllPanels();
                    }
                }
            });

            $wrap.on("keydown.megamenu", "a.mega-menu-link, .mega-indicator, .mega-menu-toggle-block, .mega-menu-toggle-animated-block button, button.mega-close", function(e) {

                if ( ! $wrap.hasClass("mega-keyboard-navigation") ) {
                    return;
                }

                const key = e.key;
                const active_link = $(e.target);

                if ( isMobileOffCanvasHorizontal() && key === tab_key && !e.shiftKey ) {
                    const $submenu = active_link.closest("ul.mega-sub-menu");

                    if ( $submenu.length !== 0 ) {
                        const $focusableWithoutBack = plugin.getFocusableItemsInSubmenu($submenu, false);

                        if ( $focusableWithoutBack.length !== 0 && active_link.is($focusableWithoutBack.last()) ) {
                            e.preventDefault();
                            $lastFocusable.trigger("focus");
                            return;
                        }
                    }
                }

                if ( isMobileOffCanvasHorizontal() && key === tab_key && e.shiftKey ) {
                    const $submenu = active_link.closest("ul.mega-sub-menu");

                    if ( $submenu.length !== 0 && active_link.hasClass("mega-mobile-back-link") ) {
                        e.preventDefault();
                        $lastFocusable.trigger("focus");
                        return;
                    }

                    if ( $submenu.length !== 0 ) {
                        const $focusableWithoutBack = plugin.getFocusableItemsInSubmenu($submenu, false);
                        const $firstFocusableInSubmenu = $focusableWithoutBack.first();
                        const $backLink = $submenu.find("> li.mega-mobile-back:visible > a.mega-menu-link.mega-mobile-back-link").first();

                        if ( $firstFocusableInSubmenu.length !== 0 && $backLink.length !== 0 && active_link.is($firstFocusableInSubmenu) ) {
                            e.preventDefault();
                            $backLink.trigger("focus");
                            return;
                        }
                    }
                }

                if ( key === space_key && active_link.is(".mega-menu-link") ) {
                    e.preventDefault();

                    // pressing space on a parent item will always toggle the sub menu
                    if ( active_link.parent().is(items_with_submenus) ) {
                        togglePanelForAnchor(active_link);
                    }
                }

                if ( key === space_key && active_link.is(".mega-indicator") ) {
                    e.preventDefault();
                    togglePanelForAnchor(active_link.parent());
                }

                if ( key === escape_key ) {
                    const submenu_open = $(".mega-toggle-on", $menu).length !== 0;

                    if ( submenu_open && closeNearestOpenPanelAndRefocus() ) {
                        return;
                    }

                    if ( plugin.isMobileView() && ! submenu_open ) {
                        plugin.hideMobileMenu();
                    }
                }

                if ( key === space_key || key === enter_key ) {
                    if ( active_link.is(".mega-menu-toggle-block button, .mega-menu-toggle-animated-block button") ) {
                        e.preventDefault();
                        
                        if ( $toggle_bar.hasClass("mega-menu-open") ) {
                            plugin.hideMobileMenu();
                        } else {
                            plugin.showMobileMenu();

                            html_body_class_timeout = setTimeout(function() {
                                $menu.find("a.mega-menu-link").first().trigger('focus');
                            }, plugin.settings.effect_speed_mobile);
                        }
                    }
                }

                if ( key === enter_key ) { // ignored by windows narrator

                    // pressing enter on an arrow will toggle the sub menu
                    if ( active_link.is(".mega-indicator") ) {
                        togglePanelForAnchor(active_link.parent());

                        return;
                    }
                    // pressing enter on a parent link
                    if ( active_link.parent().is(items_with_submenus) ) {

                        // when clicking on the parent of a hidden submenu, follow the link
                        if ( plugin.isMobileView() && active_link.parent().is(".mega-hide-sub-menu-on-mobile") ) {
                            return;
                        }

                        // pressing enter on a parent item without a link will toggle the sub menu
                        if ( active_link.is("[href]") === false ) {
                            togglePanelForAnchor(active_link);

                            return;
                        }

                        // pressing enter on a parent item will first open the sub menu, then follow the link
                        if ( active_link.parent().hasClass("mega-toggle-on") && ! active_link.closest("ul.mega-sub-menu").parent().hasClass("mega-menu-tabbed") ) {
                            return;
                        } else {
                            e.preventDefault();
                            plugin.showPanel(active_link);
                        }
                    }
                }

                if ( shouldTrapFocusInCurrentSubMenu(key) ) {
                    const focused_item = $menu[0].contains(document.activeElement) ? $(document.activeElement) : $();

                    // if the menu doesn't have focus, focus the first menu item
                    if ( focused_item.length === 0) {
                        e.preventDefault();
                        $("> li.mega-menu-item:visible", $menu).find("> a.mega-menu-link, .mega-search span[role=button]").first().trigger('focus');
                        return;
                    }

                    // try to find the next item at the same level
                    let next_item_to_focus = focused_item.parent().nextAll("li.mega-menu-item:visible").find("> a.mega-menu-link, .mega-search span[role=button]").first();

                    // can't find another item in the same level, attempt to skip back to the top
                    if ( next_item_to_focus.length === 0 && focused_item.closest(".mega-menu-megamenu").length !== 0 ) {
                        // are we inside a megamenu? find the 'back' button and focus on that
                        const all_li_parents = focused_item.parentsUntil(".mega-menu-megamenu");

                        if ( focused_item.is(all_li_parents.find("a.mega-menu-link").last()) ) {
                            next_item_to_focus = all_li_parents.find(".mega-back-button:visible > a.mega-menu-link").first();
                        }
                    }

                    // skip back to the top of non-megamenu menus
                    if ( next_item_to_focus.length === 0 ) {
                        next_item_to_focus = focused_item.parent().prevAll("li.mega-menu-item:visible").find("> a.mega-menu-link, .mega-search span[role=button]").first();
                    }

                    if ( next_item_to_focus.length !== 0 ) {
                        e.preventDefault();
                        next_item_to_focus.trigger('focus');
                    }
                    
                }

                if ( plugin.shouldGoToNextTopLevelItem(key) ) {
                    e.preventDefault();

                    const $focused_next = $(document.activeElement);
                    let next_top_level_item = $("> .mega-toggle-on", $menu).nextAll("li.mega-menu-item:visible").find("> a.mega-menu-link, .mega-search span[role=button]").first();

                    if (next_top_level_item.length === 0) {
                        next_top_level_item = $focused_next.parent().nextAll("li.mega-menu-item:visible").find("> a.mega-menu-link, .mega-search span[role=button]").first();
                    }

                    if (next_top_level_item.length === 0) {
                        next_top_level_item = $focused_next.parent().parent().parent().nextAll("li.mega-menu-item:visible").find("> a.mega-menu-link, .mega-search span[role=button]").first();
                    }

                    plugin.hideAllPanels();
                    next_top_level_item.trigger('focus');
                }

                if ( plugin.shouldGoToPreviousTopLevelItem(key) ) {
                    e.preventDefault();

                    const $focused_prev = $(document.activeElement);
                    let prev_top_level_item = $("> .mega-toggle-on", $menu).prevAll("li.mega-menu-item:visible").find("> a.mega-menu-link, .mega-search span[role=button]").last();

                    if (prev_top_level_item.length === 0) {
                        prev_top_level_item = $focused_prev.parent().prevAll("li.mega-menu-item:visible").find("> a.mega-menu-link, .mega-search span[role=button]").last();
                    }

                    if (prev_top_level_item.length === 0) {
                        prev_top_level_item = $focused_prev.parent().parent().parent().prevAll("li.mega-menu-item:visible").find("> a.mega-menu-link, .mega-search span[role=button]").last();
                    }

                    plugin.hideAllPanels();
                    prev_top_level_item.trigger('focus');
                }
            });

            $wrap.on("focusout.megamenu", function(e) {
                if ( $wrap.hasClass("mega-keyboard-navigation") ) {
                    setTimeout(function() {
                        const menu_has_focus = $wrap[0].contains(document.activeElement);
                        if (! menu_has_focus) {
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

            plugin.calculateDynamicSubmenuWidths($("> li.mega-menu-megamenu > a.mega-menu-link", $menu));
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

        };

        plugin.initToggleBar = function() {
            $toggle_bar.on("click", function(e) {
                if ( $(e.target).is(".mega-menu-toggle, .mega-menu-toggle-custom-block *, .mega-menu-toggle-block, .mega-menu-toggle-animated-block, .mega-menu-toggle-animated-block *, .mega-toggle-blocks-left, .mega-toggle-blocks-center, .mega-toggle-blocks-right, .mega-toggle-label, .mega-toggle-label span") ) {
                    e.preventDefault();
                    
                    if ($(this).hasClass("mega-menu-open")) {
                        plugin.hideMobileMenu();
                    } else {
                        plugin.showMobileMenu();
                    }
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

            if (plugin.settings.effect_mobile === "slide" && ! force ) {
                $menu.animate({"height":"hide"}, plugin.settings.effect_speed_mobile, function() {
                    $menu.css({
                        width: "",
                        left: "",
                        display: ""
                    });

                    $toggle_bar.removeClass("mega-menu-open");
                });
            } else {
                $menu.css({
                    width: "",
                    left: "",
                    display: ""
                });
                    
                $toggle_bar.removeClass("mega-menu-open");
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

            if (plugin.settings.panel_width !== undefined && ! cssWidthRegex.test(plugin.settings.panel_width) && ! $(plugin.settings.panel_width).length ) {
                console.warn('Max Mega Menu #' + $wrap.attr('id') + ': Panel Width (Outer) element (' + plugin.settings.panel_width + ') not found');
            }

            if (plugin.settings.panel_inner_width !== undefined && ! cssWidthRegex.test(plugin.settings.panel_inner_width) && ! $(plugin.settings.panel_inner_width).length ) {
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
                plugin.calculateDynamicSubmenuWidths($("> li.mega-menu-megamenu > a.mega-menu-link", $menu));
            } else {
                $(window).on("load", function() {
                    plugin.calculateDynamicSubmenuWidths($("> li.mega-menu-megamenu > a.mega-menu-link", $menu));
                });
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