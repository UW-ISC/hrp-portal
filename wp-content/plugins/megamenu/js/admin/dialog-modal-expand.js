/* global jQuery, window */
/**
 * Shared expand/collapse for megamenu admin modals (preview, location settings, menu item).
 * Modal roots set `data-megamenu-expand-storage-key` (shared key across admin dialogs for one preference).
 */
(function ($) {
    "use strict";

    var EXPANDED_CLASS = "megamenu-admin-modal--wpcontent-expanded";

    function readPreference(storageKey) {
        if (!storageKey) {
            return false;
        }
        try {
            var v = window.localStorage.getItem(storageKey);
            return v === "1" || v === "true";
        } catch (ignore) {
            return false;
        }
    }

    function storePreference(storageKey, expanded) {
        if (!storageKey) {
            return;
        }
        try {
            window.localStorage.setItem(storageKey, expanded ? "1" : "0");
        } catch (ignore) {
            // Private mode / storage disabled — UI state only until close.
        }
    }

    function getStorageKey($modal) {
        return ($modal && $modal.attr && $modal.attr("data-megamenu-expand-storage-key")) || "";
    }

    function applyExpanded($modal, expanded) {
        if (!$modal || !$modal.length) {
            return;
        }
        var on = !!expanded;
        $modal.toggleClass(EXPANDED_CLASS, on);
        var $btn = $modal.find(".megamenu-admin-modal__expand-btn");
        if (!$btn.length) {
            return;
        }
        var expandLabel = $modal.attr("data-i18n-modal-expand") || "";
        var collapseLabel = $modal.attr("data-i18n-modal-collapse") || "";
        $btn.attr("aria-expanded", on ? "true" : "false");
        $btn.attr("aria-label", on ? collapseLabel : expandLabel);
        $btn.toggleClass("megamenu-admin-modal__expand-btn--expanded", on);
    }

    function restoreOnOpen($modal) {
        var key = getStorageKey($modal);
        applyExpanded($modal, readPreference(key));
    }

    function collapseOnClose($modal) {
        applyExpanded($modal, false);
    }

    /**
     * When the modal is expanded, Escape collapses it only if focus is on the expand/collapse
     * control; otherwise returns false so the dialog can close (or handle Escape as usual).
     * Call from each dialog's document keydown handler before close; returns true if handled.
     *
     * @param {JQuery} $modal
     * @param {KeyboardEvent} e
     * @return {boolean}
     */
    function handleEscapeCollapseIfExpanded($modal, e) {
        if (e.key !== "Escape" && e.keyCode !== 27) {
            return false;
        }
        if (!$modal || !$modal.length || !$modal.hasClass("is-open")) {
            return false;
        }
        if (!$modal.hasClass(EXPANDED_CLASS)) {
            return false;
        }
        var $expandBtn = $modal.find(".megamenu-admin-modal__expand-btn");
        if (!$expandBtn.length) {
            return false;
        }
        var active = document.activeElement;
        if (active !== $expandBtn[0] && !$.contains($expandBtn[0], active)) {
            return false;
        }
        e.preventDefault();
        var key = getStorageKey($modal);
        applyExpanded($modal, false);
        storePreference(key, false);
        return true;
    }

    $(document).on("click", ".megamenu-admin-modal__expand-btn", function (e) {
        e.preventDefault();
        var $modal = $(this).closest(".megamenu-admin-modal");
        if (!$modal.length || !$modal.hasClass("is-open")) {
            return;
        }
        var key = getStorageKey($modal);
        if (!key) {
            return;
        }
        var next = !$modal.hasClass(EXPANDED_CLASS);
        applyExpanded($modal, next);
        storePreference(key, next);
    });

    window.MegamenuAdminModalExpand = {
        EXPANDED_CLASS: EXPANDED_CLASS,
        readPreference: readPreference,
        storePreference: storePreference,
        applyExpanded: applyExpanded,
        restoreOnOpen: restoreOnOpen,
        collapseOnClose: collapseOnClose,
        handleEscapeCollapseIfExpanded: handleEscapeCollapseIfExpanded,
    };
})(jQuery);
