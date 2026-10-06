/**
 * wpDataTables AI — shared entry / bootstrap.
 *
 * Single entry point that both AI features build on. It checks
 * `wpAiSettings.aiAvailable` (set via wp_localize_script) and exposes a small
 * `window.wdtAi` namespace: a guard so feature scripts only initialise when a
 * provider is configured, and a thin REST helper that attaches the wp_rest
 * nonce. The table generator UI is rendered from PHP; interactive endpoints
 * gate on `aiAvailable` in JS via `wdtAi.ready()` or explicit checks.
 *
 * @copyright © Melograno Venture Studio. All rights reserved.
 */
(function (window, $) {
    'use strict';

    var settings = window.wpAiSettings || { aiAvailable: false, nonce: '', restUrl: '' };

    var wdtAi = {
        /**
         * @returns {boolean} Whether an AI provider is configured + usable.
         */
        isAvailable: function () {
            return !!settings.aiAvailable;
        },

        /**
         * Run a callback once the DOM is ready, but ONLY when AI is available.
         * Feature scripts register their UI injection through this.
         *
         * @param {Function} callback
         */
        ready: function (callback) {
            if (!this.isAvailable()) {
                return;
            }
            $(function () {
                callback(wdtAi);
            });
        },

        /**
         * POST a JSON payload to an AI REST endpoint.
         *
         * @param {string} endpoint Path relative to the AI REST base, e.g. "generate-table".
         * @param {Object} payload  Request body.
         * @returns {Promise} Resolves with the parsed `data` envelope, rejects with a message string.
         */
        post: function (endpoint, payload) {
            return new Promise(function (resolve, reject) {
                $.ajax({
                    url: settings.restUrl + endpoint,
                    method: 'POST',
                    dataType: 'json',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', settings.nonce);
                    }
                }).done(function (response) {
                    resolve(response && typeof response.data !== 'undefined' ? response.data : response);
                }).fail(function (xhr) {
                    var message = 'AI request failed.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    reject(message);
                });
            });
        }
    };

    window.wdtAi = wdtAi;
})(window, jQuery);
