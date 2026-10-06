/**
 * wpDataTables AI — Table Generator behaviour (Feature 1).
 *
 * Binds to markup rendered by constructor_ai_table_generator.inc.php.
 * Supports Manual (column scaffold) and SQL (linked source + query) apply paths.
 *
 * @copyright © Melograno Venture Studio. All rights reserved.
 */
(function (window, $) {
    'use strict';

    var TYPES = ['string', 'int', 'float', 'date', 'datetime', 'time', 'url', 'email'];
    var PENDING_SQL_KEY = 'wdtAiPendingSqlScaffold';
    var EDITOR_ID = 'wdt-mysql-query';

    var CONSTRUCTOR_TYPE = {
        string: 'input',
        int: 'int',
        float: 'float',
        date: 'date',
        datetime: 'datetime',
        time: 'time',
        url: 'link',
        email: 'email'
    };

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

    function typeOptionsHtml(selected) {
        return TYPES.map(function (t) {
            var sel = (t === selected) ? ' selected' : '';
            return '<option value="' + t + '"' + sel + '>' + t + '</option>';
        }).join('');
    }

    function columnRow(column) {
        return '' +
            '<tr>' +
            '  <td><input type="text" class="wdt-ai-col-name form-control input-sm" value="' + esc(column.name) + '" title="' + esc(column.hint || '') + '"></td>' +
            '  <td><select class="selectpicker wdt-ai-dropdown wdt-ai-col-type">' + typeOptionsHtml(column.type) + '</select></td>' +
            '  <td style="text-align:center;"><input type="checkbox" class="wdt-ai-col-filter"' + (column.filter ? ' checked' : '') + '></td>' +
            '  <td style="text-align:center;"><button type="button" class="wdt-ai-col-remove btn btn-sm">&times;</button></td>' +
            '</tr>';
    }

    function renderColumns($panel, columns) {
        var $body = $panel.find('.wdt-ai-tg-columns tbody').empty();
        (columns || []).forEach(function (column) {
            $body.append(columnRow(column));
        });
        $body.find('.wdt-ai-col-type').selectpicker();
    }

    /**
     * Read Manual|SQL from the type picker (bootstrap-select aware).
     * Falls back to the last generated type stored on the panel.
     */
    function getSelectedTableType($panel) {
        var fromSelect = getSelectValue($panel.find('.wdt-ai-tg-type'));
        if (fromSelect === 'manual' || fromSelect === 'sql') {
            return fromSelect;
        }
        var fromData = $panel.data('wdtAiTableType');
        if (fromData === 'manual' || fromData === 'sql') {
            return fromData;
        }
        return 'manual';
    }

    function setPanelTableType($panel, tableType) {
        tableType = tableType === 'sql' ? 'sql' : 'manual';
        $panel.data('wdtAiTableType', tableType);
        var $select = $panel.find('.wdt-ai-tg-type');
        if (!$select.length) {
            return;
        }
        if ($select.parent().hasClass('bootstrap-select') && typeof $select.selectpicker === 'function') {
            $select.selectpicker('val', tableType);
        } else {
            $select.val(tableType);
            if (typeof $select.selectpicker === 'function') {
                $select.selectpicker('refresh');
            }
        }
    }

    function getConnection() {
        var $el = $('#wdt-constructor-table-connection');
        return $el.length ? String($el.val() || '') : '';
    }

    function syncTableTypeUi($panel) {
        var isSql = getSelectedTableType($panel) === 'sql';
        var $sqlWrap = $panel.find('.wdt-ai-tg-sql-wrap');
        if (isSql && $.trim($panel.find('.wdt-ai-tg-sql').val()) !== '') {
            $sqlWrap.removeClass('hidden');
        } else if (!isSql) {
            $sqlWrap.addClass('hidden');
        }
    }

    function collectScaffold($panel) {
        var sql = $.trim($panel.find('.wdt-ai-tg-sql').val());
        var columns = [];

        $panel.find('.wdt-ai-tg-columns tbody tr').each(function () {
            var $row = $(this);
            var name = $.trim($row.find('.wdt-ai-col-name').val());
            if (!name) {
                return;
            }
            columns.push({
                name: name,
                type: getSelectValue($row.find('.wdt-ai-col-type')),
                filter: $row.find('.wdt-ai-col-filter').is(':checked')
            });
        });

        return {
            table_type: getSelectedTableType($panel),
            title: $.trim($panel.find('.wdt-ai-tg-title').val()),
            columns: columns,
            sql: sql,
            suggested_sql: sql,
            connection: getConnection()
        };
    }

    function setColumnType($block, aiType) {
        var type = CONSTRUCTOR_TYPE[aiType] || 'input';
        var $select = $block.find('.wdt-constructor-column-type');
        if (!$select.length) {
            return false;
        }

        if ($select.parent().hasClass('bootstrap-select') && typeof $select.selectpicker === 'function') {
            $select.selectpicker('val', type);
        } else {
            $select.val(type);
            if (typeof $select.selectpicker === 'function') {
                $select.selectpicker('refresh');
            }
        }

        $select.trigger('change');

        var applied = getSelectValue($select);
        return applied === type;
    }

    function applyManualColumnScaffold(columns, attempt) {
        attempt = attempt || 0;
        var $blocks = $('.wdt-constructor-step[data-step="1-1"] .wdt-constructor-column-block');
        var needsRetry = $blocks.length < columns.length;

        if (!needsRetry && columns.length) {
            columns.forEach(function (column, i) {
                var $block = $blocks.eq(i);
                if (!$block.length) {
                    needsRetry = true;
                    return;
                }
                $block.find('.wdt-constructor-column-name').val(column.name).trigger('change');
                if (!setColumnType($block, column.type)) {
                    needsRetry = true;
                }
            });
        }

        if (needsRetry && attempt < 40) {
            setTimeout(function () {
                applyManualColumnScaffold(columns, attempt + 1);
            }, 50);
            return;
        }

        if (window.wdtAiScaffold) {
            $(document).trigger('wdt_ai_scaffold_ready', [window.wdtAiScaffold]);
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

    function applyManualScaffold(scaffold) {
        window.wdtAiScaffold = scaffold;

        var columns = scaffold.columns || [];
        var $manualCard = $('.wdt-constructor-type-selecter-block .card[data-value="manual"]');

        if (!$manualCard.length) {
            $(document).trigger('wdt_ai_scaffold_ready', [scaffold]);
            return;
        }

        $manualCard.trigger('click');

        if (typeof constructedTableData !== 'undefined') {
            constructedTableData.columnCount = 0;
        }
        $('.wdt-constructor-step[data-step="1-1"] .wdt-constructor-columns-container').empty();

        if (columns.length) {
            $('#wdt-constructor-number-of-columns').val(columns.length);
        }

        $('#wdt-constructor-next-step').trigger('click');

        if (scaffold.title) {
            $('#wdt-constructor-manual-table-name').val(scaffold.title).trigger('change');
        }

        applyManualColumnScaffold(columns);
    }

    /**
     * Persist scaffold and jump into the linked-source (SQL) constructor screen.
     * Navigates directly — does not rely on card selection (AI panel is also a .card).
     */
    function applySqlScaffold(scaffold) {
        window.wdtAiScaffold = scaffold;

        var connection = scaffold.connection || '';
        if (!connection && typeof constructedTableData !== 'undefined' && constructedTableData.connection) {
            connection = constructedTableData.connection;
        }

        sessionStorage.setItem(PENDING_SQL_KEY, JSON.stringify({
            title: scaffold.title || '',
            sql: scaffold.sql || scaffold.suggested_sql || '',
            connection: connection || ''
        }));

        var connectionQuery = connection ? '&connection=' + encodeURIComponent(connection) : '&connection';
        $('.wdt-preload-layer').animateFadeIn();
        window.location.replace(
            window.location.pathname + '?page=wpdatatables-constructor&source' + connectionQuery
        );
    }

    function consumePendingSqlScaffold() {
        if (!window.wdtAi || !window.wdtAi.isAvailable()) {
            return;
        }

        var raw = sessionStorage.getItem(PENDING_SQL_KEY);
        if (!raw || !$('.wdt-table-settings').length) {
            return;
        }

        sessionStorage.removeItem(PENDING_SQL_KEY);

        var scaffold;
        try {
            scaffold = JSON.parse(raw);
        } catch (e) {
            return;
        }

        window.wdtAiScaffold = scaffold;

        if (scaffold.connection && $('#wdt-table-connection').length) {
            var $conn = $('#wdt-table-connection');
            if ($conn.parent().hasClass('bootstrap-select') && typeof $conn.selectpicker === 'function') {
                $conn.selectpicker('val', scaffold.connection);
            } else {
                $conn.val(scaffold.connection);
                if (typeof $conn.selectpicker === 'function') {
                    $conn.selectpicker('refresh');
                }
            }
            $conn.trigger('change');
        }

        // Prefer the config API so mysql UI + Ace block are shown correctly.
        if (window.wpdatatable_config && typeof window.wpdatatable_config.setTableType === 'function') {
            window.wpdatatable_config.setTableType('mysql');
        } else {
            var $type = $('#wdt-table-type');
            if ($type.length) {
                if ($type.parent().hasClass('bootstrap-select') && typeof $type.selectpicker === 'function') {
                    $type.selectpicker('val', 'mysql');
                } else {
                    $type.val('mysql');
                    if (typeof $type.selectpicker === 'function') {
                        $type.selectpicker('refresh');
                    }
                }
                $type.trigger('change');
            }
        }

        if (scaffold.title) {
            if (window.wpdatatable_config && typeof window.wpdatatable_config.setTitle === 'function') {
                window.wpdatatable_config.setTitle(scaffold.title);
            } else {
                $('#wdt-table-title-edit').val(scaffold.title).trigger('change');
            }
        }

        var attempts = 0;
        var timer = setInterval(function () {
            attempts += 1;
            if (setEditorSql(scaffold.sql) || attempts > 40) {
                clearInterval(timer);
                $(document).trigger('wdt_ai_scaffold_ready', [scaffold]);
            }
        }, 150);
    }

    function bind($panel) {
        $panel.on('change changed.bs.select', '.wdt-ai-tg-type', function () {
            var tableType = getSelectValue($(this)) === 'sql' ? 'sql' : 'manual';
            $panel.data('wdtAiTableType', tableType);
            syncTableTypeUi($panel);
        });

        $panel.on('click', '.wdt-ai-tg-generate', function (e) {
            e.preventDefault();

            if (!window.wdtAi || !window.wdtAi.isAvailable()) {
                return;
            }

            var description = $.trim($panel.find('.wdt-ai-tg-description').val());
            var tableType = getSelectedTableType($panel);
            var $status = $panel.find('.wdt-ai-tg-status');

            if (!description) {
                $status.text('Please describe the table first.');
                return;
            }

            setPanelTableType($panel, tableType);
            $(this).prop('disabled', true);
            $status.text('Generating…');

            window.wdtAi.post('generate-table', {
                description: description,
                table_type: tableType,
                connection: getConnection(),
                model: getSelectValue($panel.find('.wdt-ai-tg-model')) || ''
            }).then(function (data) {
                $panel.find('.wdt-ai-tg-generate').prop('disabled', false);
                $status.text('');

                var resolvedType = (data && data.table_type === 'sql') ? 'sql' : tableType;
                setPanelTableType($panel, resolvedType);

                $panel.find('.wdt-ai-tg-title').val(data.title || '');
                renderColumns($panel, data.columns || []);

                var sql = data.sql || data.suggested_sql || '';
                var $sqlWrap = $panel.find('.wdt-ai-tg-sql-wrap');
                if (resolvedType === 'sql') {
                    $panel.find('.wdt-ai-tg-sql').val(sql);
                    $sqlWrap.toggleClass('hidden', !sql);
                } else {
                    $panel.find('.wdt-ai-tg-sql').val('');
                    $sqlWrap.addClass('hidden');
                }

                var warnings = data.warnings || [];
                var $warnings = $panel.find('.wdt-ai-tg-warnings');
                if (warnings.length) {
                    $warnings.html(warnings.map(function (w) {
                        return esc(w);
                    }).join('<br>')).removeClass('hidden');
                } else {
                    $warnings.addClass('hidden').empty();
                }

                $panel.find('.wdt-ai-tg-result').removeClass('hidden');
                syncTableTypeUi($panel);
            }).catch(function (message) {
                $panel.find('.wdt-ai-tg-generate').prop('disabled', false);
                $status.text(message);
            });
        });

        $panel.on('click', '.wdt-ai-tg-add-col', function (e) {
            e.preventDefault();
            var $row = $(columnRow({ name: '', type: 'string', filter: false }));
            $panel.find('.wdt-ai-tg-columns tbody').append($row);
            $row.find('.wdt-ai-col-type').selectpicker();
        });

        $panel.on('click', '.wdt-ai-col-remove', function (e) {
            e.preventDefault();
            $(this).closest('tr').remove();
        });

        $panel.on('click', '.wdt-ai-tg-apply', function (e) {
            e.preventDefault();

            var scaffold = collectScaffold($panel);
            // Prefer SQL path when type is sql, or when a suggested query is present
            // after an SQL generation (guards against selectpicker val glitches).
            var isSql = scaffold.table_type === 'sql' ||
                (scaffold.sql !== '' && !$panel.find('.wdt-ai-tg-sql-wrap').hasClass('hidden'));

            if (isSql) {
                if (!scaffold.sql) {
                    $panel.find('.wdt-ai-tg-status').text('SQL table type requires a suggested query.');
                    return;
                }
                scaffold.table_type = 'sql';
                applySqlScaffold(scaffold);
            } else {
                scaffold.table_type = 'manual';
                applyManualScaffold(scaffold);
            }
        });

        setPanelTableType($panel, getSelectedTableType($panel));
        syncTableTypeUi($panel);
    }

    $(function () {
        var $section = $('.wdt-ai-table-generator-section');
        if ($section.length) {
            initControls($section);
        }

        var $panel = $('.wdt-ai-generate-panel--active');
        if ($panel.length && window.wdtAi && window.wdtAi.isAvailable()) {
            bind($panel);
        }

        if (window.wdtAi && window.wdtAi.isAvailable()) {
            // Defer so table-settings main.js can create Ace + bind change handlers first.
            setTimeout(consumePendingSqlScaffold, 0);
        }
    });
})(window, jQuery);
