/*global $,jQuery,megamenu,document,window */
/**
 * Max Mega Menu — Appearance > Menus: launch buttons and body class for MMM on this menu.
 * Menu item settings modal: js/admin/dialog-menu-item-settings.js ($.fn.megaMenu).
 */
jQuery(function($) {
    "use strict";

    /**
     * Reads the numeric WordPress menu item id from the list row's element id (e.g. menu-item-42).
     *
     * @param {jQuery} $menuItem li.menu-item
     * @returns {number|null}
     */
    function parseMenuItemId($menuItem) {
        var idAttr = $menuItem.attr("id");
        if (!idAttr || typeof idAttr !== "string") {
            return null;
        }
        var m = idAttr.match(/^menu-item-(-?\d+)$/);
        if (!m) {
            return null;
        }
        var n = parseInt(m[1], 10);
        return isNaN(n) ? null : n;
    }

    /**
     * When the plugin option is on, shows a short hint after the "CSS Classes" field on this row.
     *
     * @param {jQuery} $menuItem li.menu-item
     */
    function ensureCssPrefixHint($menuItem) {
        if (megamenu.css_prefix !== "true") {
            return;
        }
        var $customCssClasses = $menuItem.find(".edit-menu-item-classes");
        if (!$customCssClasses.length || $customCssClasses.next(".megamenu_prefix").length) {
            return;
        }
        $("<span>", { "class": "megamenu_prefix" }).text(megamenu.css_prefix_message).insertAfter($customCssClasses);
    }

    /**
     * Appends the Mega Menu launch control to a menu row if missing. New rows (before Save Menu)
     * get a disabled control until the menu is saved and the page reloads.
     *
     * @param {jQuery} $menuItem li.menu-item
     * @param {boolean} requiresSaveFirst true for rows from menu-item-added; false for rows present at load
     */
    function ensureMegaMenuLaunchButton($menuItem, requiresSaveFirst) {
        if (!$menuItem || !$menuItem.length || !$menuItem.is("li.menu-item")) {
            return;
        }
        var $title = $menuItem.find(".item-title").first();
        if (!$title.length || $title.find(".megamenu_launch").length) {
            return;
        }
        var itemId = parseMenuItemId($menuItem);
        if (itemId === null) {
            return;
        }

        var $btn = $("<button>", {
            type: "button",
            "class": "button button-primary button-small megamenu_launch" +
                (requiresSaveFirst ? " megamenu_disabled" : ""),
            "data-menu-item-id": String(itemId),
            "aria-label": megamenu.launch_lightbox
        });
        $btn.text(megamenu.launch_lightbox);
        if (requiresSaveFirst) {
            $btn.attr("aria-disabled", "true");
        }
        $title.append($btn);
        ensureCssPrefixHint($menuItem);
    }

    /**
     * Syncs body.megamenu_enabled with the "Enable Max Mega Menu" checkbox in the Mega Menu meta box.
     */
    function applyMegamenuEnabledClass() {
        if ($("input.megamenu_enabled:checked").length) {
            $("body").addClass("megamenu_enabled");
        } else {
            $("body").removeClass("megamenu_enabled");
        }
    }

    window.megamenuApplyEnabledBodyClass = applyMegamenuEnabledClass;

    $("input.megamenu_enabled").on("change", applyMegamenuEnabledClass);
    applyMegamenuEnabledClass();

    // Existing rows on first paint
    $("#menu-to-edit li.menu-item").each(function() {
        ensureMegaMenuLaunchButton($(this), false);
    });

    // Rows inserted from the left column (Add to Menu) before Save Menu
    $(document).on("menu-item-added", function(e, $menuMarkup) {
        if (!$menuMarkup || !$menuMarkup.length) {
            return;
        }
        $menuMarkup.filter("li.menu-item").add($menuMarkup.find("li.menu-item")).each(function() {
            ensureMegaMenuLaunchButton($(this), true);
        });
    });

    // One handler for every launch button (initial and dynamically added rows)
    $("#menu-to-edit").on("click", ".megamenu_launch", function(e) {
        e.preventDefault();
        var $btn = $(this);
        if ($btn.attr("aria-disabled") === "true" || $btn.hasClass("megamenu_disabled")) {
            window.alert(megamenu.save_menu);
            return;
        }
        if (!$("body").hasClass("megamenu_enabled")) {
            window.alert(megamenu.is_disabled_error);
            return;
        }
        var raw = $btn.attr("data-menu-item-id");
        var menuItemId = raw ? parseInt(raw, 10) : NaN;
        if (isNaN(menuItemId)) {
            return;
        }
        $btn.megaMenu({
            menu_item_id: menuItemId
        });
    });
});
