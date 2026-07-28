/**
 * Shared vertical tab rail for admin dialogs (menu item modal, location settings modal).
 * Sets role="tab" / aria-selected / aria-controls and tabpanel wiring; switches panels by key.
 */
(function (window) {
    "use strict";

    const BOUND = "data-megamenu-dialog-tabs-bound";

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
        const tablist = options.tablist;
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

        const tabSelector = options.tabSelector || "button.megamenu-dialog-tab";
        const panelsRoot = options.panelsRoot;
        const panelsSelector = options.panelsSelector;
        const idPrefix = options.idPrefix || "megamenu-dlg-tab";
        const getPanelKey = options.getPanelKey;
        const panelMatches = options.panelMatches;
        const onAfterActivate = options.onAfterActivate;

        const tabs = tablist.querySelectorAll(tabSelector);
        if (!tabs.length || !panelsRoot) {
            return;
        }

        function listPanels() {
            return panelsRoot.querySelectorAll(panelsSelector);
        }

        function findPanelForKey(key) {
            let found = null;
            for (const p of listPanels()) {
                if (panelMatches(p, key)) {
                    found = p;
                }
            }
            return found;
        }

        for (const btn of tabs) {
            if (btn.tagName !== "BUTTON") {
                continue;
            }
            btn.setAttribute("type", "button");
            const key = getPanelKey(btn);
            if (!key) {
                continue;
            }
            const tabId = idPrefix + "-tab-" + slugify(key);
            const panelId = idPrefix + "-panel-" + slugify(key);
            btn.setAttribute("role", "tab");
            btn.setAttribute("id", tabId);
            const panel = findPanelForKey(key);
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
        }

        function activateTab(activeBtn) {
            const key = getPanelKey(activeBtn);
            if (!key) {
                return;
            }

            for (const btn of tabs) {
                btn.classList.remove("is-active");
                btn.setAttribute("aria-selected", "false");
            }
            activeBtn.classList.add("is-active");
            activeBtn.setAttribute("aria-selected", "true");

            for (const panel of listPanels()) {
                const show = panelMatches(panel, key);
                panel.style.display = show ? "block" : "none";
            }

            if (typeof onAfterActivate === "function") {
                onAfterActivate();
            }
        }

        tablist.addEventListener("click", function (ev) {
            const btn = ev.target.closest(tabSelector);
            if (!btn || !tablist.contains(btn) || btn.tagName !== "BUTTON") {
                return;
            }
            ev.preventDefault();
            activateTab(btn);
        });

        const initial = tablist.querySelector(tabSelector + ".is-active");
        if (initial) {
            activateTab(initial);
        }
    }

    window.megamenuDialogTabs = {
        bindVerticalRail: bindVerticalRail,
    };
})(window);
