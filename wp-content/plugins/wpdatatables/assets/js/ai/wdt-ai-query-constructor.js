/**
 * wpDataTables AI — Query Constructor Assistant (Feature 2).
 *
 * Binds to markup from constructor_ai_query_constructor.inc.php on WP (1-3)
 * and MySQL (1-4) GUI builder steps. UI matches Feature 3 (Ask AI → review →
 * Apply). Apply maps structured AI output onto the dual-list picker cards.
 *
 * @copyright © Melograno Venture Studio. All rights reserved.
 */
(function (window, $) {
    'use strict';

    if (!window.wdtAi) {
        return;
    }

    var OPERATOR_MAP = {
        '=': 'eq',
        '==': 'eq',
        'eq': 'eq',
        '!=': 'neq',
        '<>': 'neq',
        'neq': 'neq',
        '>': 'gt',
        'gt': 'gt',
        '>=': 'gtoreq',
        'gtoreq': 'gtoreq',
        '<': 'lt',
        'lt': 'lt',
        '<=': 'ltoreq',
        'ltoreq': 'ltoreq',
        'like': 'like',
        '%like%': 'plikep',
        'plikep': 'plikep',
        'in': 'in'
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

    function setSelectValue($select, value) {
        if (!$select.length) {
            return;
        }
        if ($select.parent().hasClass('bootstrap-select') && typeof $select.selectpicker === 'function') {
            $select.selectpicker('val', value);
        } else {
            $select.val(value);
            if (typeof $select.selectpicker === 'function') {
                $select.selectpicker('refresh');
            }
        }
    }

    function initControls($root) {
        $root.find('.selectpicker').selectpicker();
        if (typeof $.fn.wdtBootstrapTooltip === 'function') {
            $root.find('[data-toggle="tooltip"]').wdtBootstrapTooltip();
        }
    }

    function getConnection() {
        var $el = $('#wdt-constructor-table-connection');
        if (!$el.length) {
            $el = $('#wdt-table-connection');
        }
        return $el.length ? String($el.val() || '') : '';
    }

    function formatSummary(payload) {
        var lines = [];
        if (payload.tables && payload.tables.length) {
            lines.push('Tables / types: ' + payload.tables.join(', '));
        }
        if (payload.columns && payload.columns.length) {
            lines.push('Columns: ' + payload.columns.map(function (col) {
                return col.table + '.' + col.column + (col.alias ? ' AS ' + col.alias : '');
            }).join(', '));
        }
        if (payload.joins && payload.joins.length) {
            lines.push('Joins: ' + payload.joins.map(function (j) {
                return (j.type || 'INNER') + ' ' + j.from + ' → ' + j.to;
            }).join('; '));
        }
        if (payload.conditions && payload.conditions.length) {
            lines.push('Conditions: ' + payload.conditions.map(function (c) {
                return c.column + ' ' + c.operator + ' ' + c.value;
            }).join('; '));
        }
        if (payload.group_by && payload.group_by.length) {
            lines.push('Group by: ' + payload.group_by.join(', '));
        }
        if (payload.order_by && payload.order_by.length) {
            lines.push('Order by: ' + payload.order_by.map(function (o) {
                return o.column + ' ' + o.direction;
            }).join(', '));
        }
        return lines.join('\n');
    }

    function rowLabel($tr) {
        return $.trim($tr.find('td').first().text());
    }

    /**
     * Mark rows whose first-cell text is in values (exact match).
     * Searches both "all" and "selected" bodies so re-apply works.
     */
    function selectRowsByLabels($bodies, values) {
        var wanted = {};
        (values || []).forEach(function (v) {
            wanted[String(v)] = true;
        });
        var matched = 0;

        $bodies.each(function () {
            $(this).find('tr').each(function () {
                var $tr = $(this);
                var label = rowLabel($tr);
                if (wanted[label]) {
                    $tr.addClass('selected');
                    matched += 1;
                } else {
                    $tr.removeClass('selected');
                }
            });
        });

        return matched;
    }

    function moveSelectedBack($selectedBody, removeBtnSelector) {
        $selectedBody.find('tr').addClass('selected');
        if ($selectedBody.find('tr.selected').length) {
            $(removeBtnSelector).trigger('click');
        }
    }

    function waitFor(predicate, callback, attempts, interval) {
        attempts = attempts || 0;
        interval = interval || 200;
        if (predicate() || attempts > 40) {
            callback(predicate());
            return;
        }
        setTimeout(function () {
            waitFor(predicate, callback, attempts + 1, interval);
        }, interval);
    }

    function mapOperator(op) {
        var key = String(op || '=').toLowerCase();
        return OPERATOR_MAP[key] || OPERATOR_MAP[op] || 'eq';
    }

    function optionExists($select, value) {
        var found = false;
        $select.find('option').each(function () {
            if ($(this).attr('value') === value) {
                found = true;
                return false;
            }
        });
        return found;
    }

    function applyMysqlConditions(conditions) {
        if (!conditions || !conditions.length) {
            return;
        }

        var $block = $('.wdt-constructor-mysql-conditions-block');
        if (!$block.length) {
            return;
        }
        $block.removeClass('hidden').show();
        $('#wdt-constructor-mysql-conditions').empty();

        conditions.forEach(function (condition) {
            var column = condition.column || '';
            if (!column) {
                return;
            }
            $('#wdt-constructor-add-mysql-condition').trigger('click');
            var $row = $('#wdt-constructor-mysql-conditions .wdt-constructor-mysql-where-block').last();
            if (!$row.length) {
                return;
            }
            var $col = $row.find('.wdt-constructor-where-condition-column');
            if (!optionExists($col, column)) {
                $row.remove();
                return;
            }
            setSelectValue($col, column);
            setSelectValue($row.find('.wdt-constructor-where-operator'), mapOperator(condition.operator));
            $row.find('#wdt-constructor-where-value').val(condition.value || '');
        });
    }

    function applyWpConditions(conditions, postTypes) {
        if (!conditions || !conditions.length) {
            return;
        }

        var $block = $('.wdt-constructor-post-conditions-block');
        if (!$block.length) {
            return;
        }
        $block.removeClass('hidden').show();
        $('#wdt-constructor-post-conditions').empty();

        var defaultType = (postTypes && postTypes[0]) ? postTypes[0] : 'post';

        conditions.forEach(function (condition) {
            var column = condition.column || '';
            if (!column) {
                return;
            }
            if (column.indexOf('.') === -1) {
                column = defaultType + '.' + column;
            }
            $('#wdt-constructor-add-post-condition').trigger('click');
            var $row = $('#wdt-constructor-post-conditions .wdt-constructor-post-where-block').last();
            if (!$row.length) {
                return;
            }
            var $col = $row.find('.wdt-constructor-where-condition-column');
            if (!optionExists($col, column)) {
                $row.remove();
                return;
            }
            setSelectValue($col, column);
            setSelectValue($row.find('.wdt-constructor-where-operator'), mapOperator(condition.operator));
            $row.find('#wdt-constructor-where-value').val(condition.value || '');
        });
    }

    function applyMysqlJoins(joins, tables) {
        if (!joins || !joins.length || !tables || tables.length < 2) {
            return;
        }

        joins.forEach(function (join) {
            var fromParts = String(join.from || '').split('.');
            var toParts = String(join.to || '').split('.');
            if (fromParts.length < 2 || toParts.length < 2) {
                return;
            }
            var initiatorTable = fromParts[0];
            var initiatorCol = fromParts.slice(1).join('.');
            var connected = toParts.join('.');

            var $block = $('.wdt-constructor-mysql-block').filter(function () {
                return $(this).find('.wdt-constructor-relation-initiator-column').data('mysql-table') === initiatorTable;
            }).first();

            if (!$block.length) {
                return;
            }

            setSelectValue($block.find('.wdt-constructor-relation-initiator-column'), initiatorCol);
            setSelectValue($block.find('.wdt-constructor-relation-connected-column'), connected);

            var $joinToggle = $block.find('#wdt-constructor-relation-inner-join-' + initiatorTable);
            if ($joinToggle.length) {
                $joinToggle.prop('checked', String(join.type || 'INNER').toUpperCase() !== 'LEFT');
            }
        });
    }

    function applyToMysqlWizard(payload, $status) {
        var tables = payload.tables || [];
        var columnRefs = (payload.columns || []).map(function (col) {
            var table = col.table || '';
            var name = col.column || '';
            if (table && name.indexOf(table + '.') === 0) {
                name = name.slice(table.length + 1);
            }
            return table && name ? (table + '.' + name) : '';
        }).filter(Boolean);

        moveSelectedBack(
            $('#wdt-constructor-mysql-columns-selected-table'),
            '.wdt-constructor-remove-mysql-column'
        );
        moveSelectedBack(
            $('#wdt-constructor-mysql-tables-selected-table'),
            '.wdt-constructor-remove-mysql-table'
        );

        if (!tables.length) {
            $status.text('No tables to apply.');
            return;
        }

        var matchedTables = selectRowsByLabels(
            $('#wdt-constructor-mysql-tables-all-table, #wdt-constructor-mysql-tables-selected-table'),
            tables
        );

        if (!matchedTables) {
            $status.text('None of the suggested tables were found in the picker.');
            return;
        }

        // Only move rows still in the "all" list.
        selectRowsByLabels($('#wdt-constructor-mysql-tables-all-table'), tables);
        $('.wdt-constructor-add-mysql-table').trigger('click');

        waitFor(function () {
            var $all = $('#wdt-constructor-mysql-columns-all-table tr');
            if (!$all.length) {
                return false;
            }
            if (!columnRefs.length) {
                return true;
            }
            var found = 0;
            $all.each(function () {
                if ($.inArray(rowLabel($(this)), columnRefs) !== -1) {
                    found += 1;
                }
            });
            return found > 0;
        }, function (ready) {
            if (!ready) {
                $status.text('Tables applied; columns did not load in time — pick them manually.');
                return;
            }

            if (columnRefs.length) {
                selectRowsByLabels($('#wdt-constructor-mysql-columns-all-table'), columnRefs);
                $('.wdt-constructor-add-mysql-column').trigger('click');
            }

            applyMysqlJoins(payload.joins || [], tables);
            applyMysqlConditions(payload.conditions || []);

            $status.text('Applied to the wizard cards. Review selections, then continue.');
            $(document).trigger('wdt_ai_query_constructor_applied', [payload]);
        });
    }

    function applyToWpWizard(payload, $status) {
        var postTypes = (payload.tables || []).map(function (t) {
            return String(t);
        }).filter(Boolean);

        if (!postTypes.length) {
            postTypes = ['post'];
        }

        var columnRefs = (payload.columns || []).map(function (col) {
            var postType = col.table || 'post';
            var name = col.column || '';
            if (name.indexOf(postType + '.') === 0) {
                name = name.slice(postType.length + 1);
            }
            return name ? (postType + '.' + name) : '';
        }).filter(Boolean);

        // Also accept conditions that already use post_type.field form.
        (payload.conditions || []).forEach(function (c) {
            if (c.column && c.column.indexOf('.') !== -1 && $.inArray(c.column, columnRefs) === -1) {
                // no-op — conditions applied separately
            }
        });

        moveSelectedBack(
            $('#wdt-constructor-post-columns-selected-table'),
            '.wdt-constructor-remove-post-column'
        );
        moveSelectedBack(
            $('#wdt-constructor-post-types-selected-table'),
            '.wdt-constructor-remove-post-type'
        );

        var matchedTypes = selectRowsByLabels(
            $('#wdt-constructor-post-types-all-table, #wdt-constructor-post-types-selected-table'),
            postTypes
        );

        if (!matchedTypes) {
            // Fallback: select "post" if present.
            matchedTypes = selectRowsByLabels($('#wdt-constructor-post-types-all-table'), ['post']);
            postTypes = matchedTypes ? ['post'] : [];
        }

        if (!matchedTypes) {
            $status.text('None of the suggested post types were found in the picker.');
            return;
        }

        selectRowsByLabels($('#wdt-constructor-post-types-all-table'), postTypes);
        $('.wdt-constructor-add-post-type').trigger('click');

        waitFor(function () {
            var $all = $('#wdt-constructor-post-columns-all-table tr');
            if (!$all.length) {
                return false;
            }
            if (!columnRefs.length) {
                return true;
            }
            var found = 0;
            $all.each(function () {
                if ($.inArray(rowLabel($(this)), columnRefs) !== -1) {
                    found += 1;
                }
            });
            return found > 0;
        }, function (ready) {
            if (!ready) {
                $status.text('Post types applied; properties did not load in time — pick them manually.');
                return;
            }

            if (columnRefs.length) {
                selectRowsByLabels($('#wdt-constructor-post-columns-all-table'), columnRefs);
                $('.wdt-constructor-add-post-column').trigger('click');
            }

            applyWpConditions(payload.conditions || [], postTypes);

            $status.text('Applied to the wizard cards. Review selections, then continue.');
            $(document).trigger('wdt_ai_query_constructor_applied', [payload]);
        });
    }

    function bind($panel) {
        var builderType = $panel.data('builder-type');
        var lastPayload = null;

        $panel.on('click', '.wdt-ai-qc-run', function (e) {
            e.preventDefault();

            if (!window.wdtAi.isAvailable()) {
                return;
            }

            var description = $.trim($panel.find('.wdt-ai-qc-description').val());
            var $status = $panel.find('.wdt-ai-qc-status');
            var $btn = $(this);

            if (!description) {
                $status.text('Please describe the report first.');
                return;
            }

            $btn.prop('disabled', true);
            $status.text('Thinking…');

            window.wdtAi.post('query-constructor', {
                builder_type: builderType,
                description: description,
                connection: getConnection(),
                model: getSelectValue($panel.find('.wdt-ai-qc-model')) || ''
            }).then(function (data) {
                $btn.prop('disabled', false);
                $status.text('');
                lastPayload = data;

                $panel.find('.wdt-ai-qc-explanation').text(data.explanation || '');
                $panel.find('.wdt-ai-qc-summary').val(formatSummary(data));

                var sql = data.generated_sql || '';
                var $sqlWrap = $panel.find('.wdt-ai-qc-sql-wrap');
                $panel.find('.wdt-ai-qc-sql').val(sql);
                $sqlWrap.toggleClass('hidden', !sql);

                var warnings = data.warnings || [];
                var $warnings = $panel.find('.wdt-ai-qc-warnings');
                if (warnings.length) {
                    $warnings.html(warnings.map(function (w) {
                        return esc(w);
                    }).join('<br>')).removeClass('hidden');
                } else {
                    $warnings.addClass('hidden').empty();
                }

                var canApply = (data.columns && data.columns.length) || (data.tables && data.tables.length);
                $panel.find('.wdt-ai-qc-apply').prop('disabled', !canApply);
                $panel.find('.wdt-ai-qc-result').removeClass('hidden');
            }).catch(function (message) {
                $btn.prop('disabled', false);
                $status.text(message);
            });
        });

        $panel.on('click', '.wdt-ai-qc-apply', function (e) {
            e.preventDefault();
            var $status = $panel.find('.wdt-ai-qc-status');
            if (!lastPayload) {
                $status.text('Ask AI first.');
                return;
            }

            $status.text('Applying…');

            if (builderType === 'wp') {
                applyToWpWizard(lastPayload, $status);
            } else {
                applyToMysqlWizard(lastPayload, $status);
            }
        });
    }

    window.wdtAi.ready(function () {
        $('.wdt-ai-query-constructor').each(function () {
            var $panel = $(this);
            initControls($panel);
            bind($panel);
        });
    });
})(window, jQuery);
