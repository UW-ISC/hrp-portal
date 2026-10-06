/**
 * wpDataTables AI — SQL Query Assistant (Feature 3).
 *
 * Binds to markup from ai_sql_assistant.inc.php (above `#wdt-mysql-query`).
 * UI matches Feature 1: prompt → Mode/Model selectpickers → Ask AI.
 *
 * Modes: generate | fix | improve | explain.
 * On Apply, updates Ace + `wpdatatable_config.content`. AI never saves.
 *
 * @copyright © Melograno Venture Studio. All rights reserved.
 */
(function (window, $) {
    'use strict';

    if (!window.wdtAi) {
        return;
    }

    var EDITOR_ID = 'wdt-mysql-query';

    function esc(value) {
        return $('<div/>').text(value == null ? '' : value).html();
    }

    function getSelectValue($select) {
        if (!$select.length) {
            return '';
        }
        if ($select.parent().hasClass('bootstrap-select') && typeof $select.selectpicker === 'function') {
            return $select.selectpicker('val') || '';
        }
        return $select.val() || '';
    }

    function initControls($root) {
        $root.find('.selectpicker').selectpicker();
        if (typeof $.fn.wdtBootstrapTooltip === 'function') {
            $root.find('[data-toggle="tooltip"]').wdtBootstrapTooltip();
        }
    }

    function getEditorSql() {
        if (!$('#' + EDITOR_ID).length || typeof window.ace === 'undefined') {
            return '';
        }
        try {
            return ace.edit(EDITOR_ID).getValue() || '';
        } catch (e) {
            return '';
        }
    }

    function setEditorSql(sql) {
        if (!$('#' + EDITOR_ID).length || typeof window.ace === 'undefined') {
            return false;
        }
        try {
            var editor = ace.edit(EDITOR_ID);
            editor.setValue(sql || '', -1);
            editor.clearSelection();
            if (window.wpdatatable_config && typeof window.wpdatatable_config.setContent === 'function') {
                window.wpdatatable_config.setContent(sql || '');
            }
            return true;
        } catch (e) {
            return false;
        }
    }

    function getConnection() {
        var $el = $('#wdt-table-connection');
        return $el.length ? String($el.val() || '') : '';
    }

    function syncModeUi($panel) {
        var mode = getSelectValue($panel.find('.wdt-ai-qa-mode')) || 'generate';
        var $errorWrap = $panel.find('.wdt-ai-qa-error-wrap');
        var $prompt = $panel.find('.wdt-ai-qa-prompt');

        if (mode === 'fix') {
            $errorWrap.removeClass('hidden');
            $prompt.attr('placeholder', 'e.g. Fix this query so it runs / keep the same result set');
        } else if (mode === 'improve') {
            $errorWrap.addClass('hidden');
            $prompt.attr('placeholder', 'e.g. Lower complexity, add a date filter for last 30 days, or join users');
        } else if (mode === 'explain') {
            $errorWrap.addClass('hidden');
            $prompt.attr('placeholder', 'Optional focus: e.g. Explain the JOINs / suggest indexes');
        } else {
            $errorWrap.addClass('hidden');
            $prompt.attr('placeholder', 'e.g. Create a table of all WP posts that have comments');
        }
    }

    function lastSqlError() {
        var modalText = $.trim($('#wdt-error-modal .modal-body').text() || '');
        if (modalText && /sql|mysql|syntax|query/i.test(modalText)) {
            return modalText;
        }
        return '';
    }

    function bind($panel) {
        $panel.on('change changed.bs.select', '.wdt-ai-qa-mode', function () {
            syncModeUi($panel);
        });

        $panel.on('click', '.wdt-ai-qa-run', function (e) {
            e.preventDefault();

            if (!window.wdtAi.isAvailable()) {
                return;
            }

            var mode = getSelectValue($panel.find('.wdt-ai-qa-mode')) || 'generate';
            var prompt = $.trim($panel.find('.wdt-ai-qa-prompt').val());
            var currentQuery = getEditorSql();
            var errorMessage = $.trim($panel.find('.wdt-ai-qa-error').val()) || lastSqlError();
            var $status = $panel.find('.wdt-ai-qa-status');
            var $btn = $(this);

            if (mode === 'generate' && !prompt) {
                $status.text('Please describe the query you want.');
                return;
            }

            if (mode !== 'generate' && !currentQuery && !prompt) {
                $status.text('Add a query in the editor and/or a prompt first.');
                return;
            }

            $btn.prop('disabled', true);
            $status.text('Thinking…');

            window.wdtAi.post('query-assistant', {
                mode: mode,
                prompt: prompt,
                current_query: currentQuery,
                error_message: errorMessage,
                connection: getConnection(),
                model: getSelectValue($panel.find('.wdt-ai-qa-model')) || ''
            }).then(function (data) {
                $btn.prop('disabled', false);
                $status.text('');

                $panel.find('.wdt-ai-qa-explanation').text(data.explanation || '');
                $panel.find('.wdt-ai-qa-sql').val(data.sql || '');

                var warnings = data.warnings || [];
                var $warnings = $panel.find('.wdt-ai-qa-warnings');
                if (warnings.length) {
                    $warnings.html(warnings.map(function (w) {
                        return esc(w);
                    }).join('<br>')).removeClass('hidden');
                } else {
                    $warnings.addClass('hidden').empty();
                }

                $panel.find('.wdt-ai-qa-result').removeClass('hidden');
                if (mode === 'explain' && !data.sql) {
                    $panel.find('.wdt-ai-qa-apply').prop('disabled', true);
                } else {
                    $panel.find('.wdt-ai-qa-apply').prop('disabled', !data.sql);
                }
            }).catch(function (message) {
                $btn.prop('disabled', false);
                $status.text(message);
            });
        });

        $panel.on('click', '.wdt-ai-qa-apply', function (e) {
            e.preventDefault();
            var sql = $.trim($panel.find('.wdt-ai-qa-sql').val());
            var $status = $panel.find('.wdt-ai-qa-status');
            if (!sql) {
                $status.text('No SQL to apply.');
                return;
            }
            if (setEditorSql(sql)) {
                $status.text('Applied to the SQL editor. Review, then Save Changes.');
            } else {
                $status.text('Could not update the SQL editor.');
            }
        });

        syncModeUi($panel);
    }

    window.wdtAi.ready(function () {
        var $panel = $('.wdt-table-settings .wdt-ai-query-assistant');
        if (!$panel.length) {
            return;
        }
        initControls($panel);
        bind($panel);
    });
})(window, jQuery);
