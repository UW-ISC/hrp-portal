/**
 * Shared vertical tab rail for admin dialogs (menu item modal, location settings modal).
 * Sets role="tab" / aria-selected / aria-controls and tabpanel wiring; switches panels by key.
 */
(function (window) {
    "use strict";

    var BOUND = "data-megamenu-dialog-tabs-bound";

    function slugify(key) {
        return String(key).replace(/[^a-zA-Z0-9_-]/g, "-");
    }

    /**
     * @param {object} options
     * @param {HTMLElement} options.tablist
     * @param {string} [options.tabSelector="button.megamenu-dialog-tab"] Must match button elements inside tablist.
     * @param {HTMLElement} options.panelsRoot
     * @param {string} options.panelsSelector Passed to panelsRoot.querySelectorAll (e.g. "> .megamenu_content" or ".mega-tab-content").
     * @param {function(HTMLButtonElement): string|null|undefined} options.getPanelKey
     * @param {function(HTMLElement, string): boolean} options.panelMatches
     * @param {string} options.idPrefix Unique HTML id prefix for this dialog instance.
     * @param {function(): void} [options.onAfterActivate]
     */
    function bindVerticalRail(options) {
        var tablist = options.tablist;
        if (!tablist || tablist.nodeType !== 1) {
            return;
        }
        if (tablist.getAttribute(BOUND) === "1") {
            return;
        }
        tablist.setAttribute(BOUND, "1");

        if (!tablist.getAttribute("role")) {
            tablist.setAttribute("role", "tablist");
        }

        var tabSelector = options.tabSelector || "button.megamenu-dialog-tab";
        var panelsRoot = options.panelsRoot;
        var panelsSelector = options.panelsSelector;
        var idPrefix = options.idPrefix || "megamenu-dlg-tab";
        var getPanelKey = options.getPanelKey;
        var panelMatches = options.panelMatches;
        var onAfterActivate = options.onAfterActivate;

        var tabs = tablist.querySelectorAll(tabSelector);
        if (!tabs.length || !panelsRoot) {
            return;
        }

        function listPanels() {
            return panelsRoot.querySelectorAll(panelsSelector);
        }

        function findPanelForKey(key) {
            var found = null;
            Array.prototype.forEach.call(listPanels(), function (p) {
                if (panelMatches(p, key)) {
                    found = p;
                }
            });
            return found;
        }

        Array.prototype.forEach.call(tabs, function (btn) {
            if (btn.tagName !== "BUTTON") {
                return;
            }
            btn.setAttribute("type", "button");
            var key = getPanelKey(btn);
            if (!key) {
                return;
            }
            var tabId = idPrefix + "-tab-" + slugify(key);
            var panelId = idPrefix + "-panel-" + slugify(key);
            btn.setAttribute("role", "tab");
            btn.setAttribute("id", tabId);
            var panel = findPanelForKey(key);
            if (panel) {
                panel.setAttribute("role", "tabpanel");
                panel.setAttribute("id", panelId);
                panel.setAttribute("aria-labelledby", tabId);
                btn.setAttribute("aria-controls", panelId);
            }
            btn.setAttribute(
                "aria-selected",
                btn.classList.contains("is-active") ? "true" : "false"
            );
        });

        function activateTab(activeBtn) {
            var key = getPanelKey(activeBtn);
            if (!key) {
                return;
            }

            Array.prototype.forEach.call(tabs, function (btn) {
                btn.classList.remove("is-active");
                btn.setAttribute("aria-selected", "false");
            });
            activeBtn.classList.add("is-active");
            activeBtn.setAttribute("aria-selected", "true");

            Array.prototype.forEach.call(listPanels(), function (panel) {
                var show = panelMatches(panel, key);
                panel.style.display = show ? "block" : "none";
            });

            if (typeof onAfterActivate === "function") {
                onAfterActivate();
            }
        }

        tablist.addEventListener("click", function (ev) {
            var btn = ev.target.closest(tabSelector);
            if (!btn || !tablist.contains(btn) || btn.tagName !== "BUTTON") {
                return;
            }
            ev.preventDefault();
            activateTab(btn);
        });

        var initial = tablist.querySelector(tabSelector + ".is-active");
        if (initial) {
            activateTab(initial);
        }
    }

    window.megamenuDialogTabs = {
        bindVerticalRail: bindVerticalRail,
    };
})(window);
