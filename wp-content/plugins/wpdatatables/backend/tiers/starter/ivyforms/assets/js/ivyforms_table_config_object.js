// Ivyforms table config object for wpDataTables integration
(function() {
    var IvyformsTableConfig = function() {
        this.formId = null;
        this.fields = [];
        this.dateFrom = null;
        this.dateTo = null;
        this.filterByUser = null;
        this.filterByStarred = false;
        this.filterByRead = null;
        this.hasServerSideIntegration = 1;
        this.columnMetaByOrigHeader = {};
        this.columnMetaById = {};
    };

    // Form ID
    IvyformsTableConfig.prototype.setFormId = function(formId) {
        this.formId = formId;
    };

    IvyformsTableConfig.prototype.getFormId = function() {
        return this.formId;
    };

    // Fields (user picker selection — PHP may expand fieldIds on save for edit-only subfields).
    IvyformsTableConfig.prototype.setFields = function(fields) {
        this.fields = fields ? fields.slice() : [];
        jQuery('#wdt-ivyforms-form-column-picker').selectpicker('val', this.fields);
    };

    IvyformsTableConfig.prototype.getFields = function() {
        return this.fields;
    };

    // Date from
    IvyformsTableConfig.prototype.setDateFrom = function(dateFrom) {
        this.dateFrom = dateFrom;
    };

    IvyformsTableConfig.prototype.getDateFrom = function() {
        return this.dateFrom;
    };

    // Date to
    IvyformsTableConfig.prototype.setDateTo = function(dateTo) {
        this.dateTo = dateTo;
    };

    IvyformsTableConfig.prototype.getDateTo = function() {
        return this.dateTo;
    };

    // Filter by user
    IvyformsTableConfig.prototype.setFilterByUser = function(userId) {
        this.filterByUser = userId;
    };

    IvyformsTableConfig.prototype.getFilterByUser = function() {
        return this.filterByUser;
    };

    // Filter by starred
    IvyformsTableConfig.prototype.setFilterByStarred = function(starred) {
        this.filterByStarred = starred;
    };

    IvyformsTableConfig.prototype.getFilterByStarred = function() {
        return this.filterByStarred;
    };

    // Filter by read status
    IvyformsTableConfig.prototype.setFilterByRead = function(status) {
        this.filterByRead = status;
    };

    IvyformsTableConfig.prototype.getFilterByRead = function() {
        return this.filterByRead;
    };

    /**
     * Cache IvyForms field / entry meta keyed by orig_header (from getIvyFormsFormFields AJAX).
     *
     * @param {Array} fields
     * @param {Array} entryData
     */
    IvyformsTableConfig.prototype.setColumnMetaFromAjax = function(fields, entryData) {
        this.columnMetaByOrigHeader = {};
        this.columnMetaById = {};
        var all = (fields || []).concat(entryData || []);
        for (var i = 0; i < all.length; i++) {
            if (all[i].origHeader) {
                this.columnMetaByOrigHeader[all[i].origHeader] = all[i];
            }
            if (all[i].id !== undefined && all[i].id !== null) {
                this.columnMetaById[all[i].id] = all[i];
            }
        }
    };

    /**
     * When a name/address parent is selected, auto-include its subfields (for editable tables).
     *
     * @param {Array} fieldIds
     * @returns {Array}
     */
    IvyformsTableConfig.prototype.expandCompoundFieldSelection = function(fieldIds) {
        fieldIds = fieldIds ? fieldIds.slice() : [];
        var metaById = this.columnMetaById || {};
        var expanded = fieldIds.slice();

        fieldIds.forEach(function(id) {
            var meta = metaById[id];
            if (!meta || !meta.isCompoundParent || !meta.compoundChildIds || !meta.compoundChildIds.length) {
                return;
            }
            meta.compoundChildIds.forEach(function(childId) {
                var hasChild = expanded.some(function(selectedId) {
                    return String(selectedId) === String(childId);
                });
                if (!hasChild) {
                    expanded.push(childId);
                }
            });
        });

        return expanded;
    };

    IvyformsTableConfig.prototype.applyColumnEditorDefaults = function() {
        if (typeof wpdatatable_config === 'undefined' || !wpdatatable_config.columns || !wpdatatable_config.columns.length) {
            return;
        }

        var editable = parseInt(wpdatatable_config.editable, 10) === 1 && this.canEdit();
        var metaByHeader = this.columnMetaByOrigHeader;
        var selectedFieldIds = this.getFields() || [];

        for (var i = 0; i < wpdatatable_config.columns.length; i++) {
            var col = wpdatatable_config.columns[i];
            var meta = metaByHeader[col.orig_header];
            if (!meta) {
                continue;
            }

            var applyDefaults = !col.id && !col._ivyformsDefaultsApplied;

            if (applyDefaults) {
                if (meta.column_type) {
                    col.type = meta.column_type;
                }
                if (meta.filter_type) {
                    col.filter_type = meta.filter_type;
                }
                if (meta.possible_values) {
                    col.valuesList = meta.possible_values;
                    col.possibleValuesType = 'list';
                }
            }

            // Parent name/address: read-only in modal. Subfields: editable inputs; visibility follows field picker.
            if (meta.isCompoundParent) {
                col.editor_type = 'none';
            } else if (meta.isCompoundChild && editable) {
                col.editor_type = 'text';
                var isSelected = selectedFieldIds.some(function(selectedId) {
                    return String(selectedId) === String(meta.id);
                });
                col.visible = isSelected ? 1 : 0;
            } else if (applyDefaults && meta.input_type) {
                col.editor_type = meta.input_type;
            }

            if (meta.id === 'id' && editable) {
                col.visible = 0;
                col.id_column = 1;
            }

            if (applyDefaults) {
                Object.defineProperty(col, '_ivyformsDefaultsApplied', {
                    value: true,
                    writable: true,
                    configurable: true,
                    enumerable: false
                });
            }
        }
    };

    /**
     * Ensure a hidden Entry ID column exists when Editable is on.
     *
     * @param {Array} fieldIds
     * @returns {Array}
     */
    IvyformsTableConfig.prototype.addEntryIdColumn = function(fieldIds) {
        fieldIds = fieldIds ? fieldIds.slice() : [];

        if (fieldIds.indexOf('id') === -1) {
            fieldIds.push('id');
        }

        var hasIdCol = false;
        if (typeof wpdatatable_config !== 'undefined' && wpdatatable_config.columns) {
            for (var i = 0; i < wpdatatable_config.columns.length; i++) {
                if (wpdatatable_config.columns[i].orig_header === 'id') {
                    hasIdCol = true;
                    wpdatatable_config.columns[i].visible = 0;
                    wpdatatable_config.columns[i].id_column = 1;
                    break;
                }
            }
        }

        if (!hasIdCol
            && typeof WDTColumn !== 'undefined'
            && typeof wpdatatable_config !== 'undefined'
            && wpdatatable_config.columns
            && Array.isArray(wpdatatable_config.columns)) {
            wpdatatable_config.columns.push(new WDTColumn({
                orig_header: 'id',
                visible: 0,
                id_column: 1
            }));
        }

        return fieldIds;
    };

    /**
     * Prevent removing Entry ID from the column picker while Editable is on.
     */
    IvyformsTableConfig.prototype.disableEntryId = function() {
        jQuery("select#wdt-ivyforms-form-column-picker option[value='id']").attr('disabled', true);
        jQuery('#wdt-ivyforms-form-column-picker').selectpicker('refresh');
    };

    IvyformsTableConfig.prototype.enableEntryId = function() {
        jQuery("select#wdt-ivyforms-form-column-picker option[value='id']").attr('disabled', false);
        jQuery('#wdt-ivyforms-form-column-picker').selectpicker('refresh');
    };

    /**
     * Snapshot picker/column state before Editable auto-adds Entry ID.
     */
    IvyformsTableConfig.prototype.captureEditableSnapshot = function() {
        var snapshot = {
            fields: this.getFields() ? this.getFields().slice() : [],
            columns: []
        };

        if (typeof wpdatatable_config !== 'undefined' && wpdatatable_config.columns && Array.isArray(wpdatatable_config.columns)) {
            snapshot.columns = wpdatatable_config.columns.map(function(col) {
                return {
                    orig_header: col.orig_header,
                    visible: col.visible,
                    id_column: col.id_column
                };
            });
        }

        this._editableSnapshot = snapshot;
    };

    /**
     * Restore state captured before Editable was enabled.
     */
    IvyformsTableConfig.prototype.restoreEditableSnapshot = function() {
        if (!this._editableSnapshot) {
            this.enableEntryId();
            this.enableTableEditingOptions();
            return;
        }

        this.setFields(this._editableSnapshot.fields.slice());

        if (typeof wpdatatable_config !== 'undefined' && wpdatatable_config.columns && Array.isArray(wpdatatable_config.columns)) {
            var snapshotByHeader = {};
            this._editableSnapshot.columns.forEach(function(col) {
                snapshotByHeader[col.orig_header] = col;
            });

            for (var i = wpdatatable_config.columns.length - 1; i >= 0; i--) {
                var column = wpdatatable_config.columns[i];
                if (column.orig_header === 'id' && !snapshotByHeader.id) {
                    wpdatatable_config.columns.splice(i, 1);
                }
            }

            wpdatatable_config.columns.forEach(function(column) {
                var saved = snapshotByHeader[column.orig_header];
                if (saved) {
                    column.visible = saved.visible;
                    column.id_column = saved.id_column;
                }
            });
        }

        this.enableEntryId();
        this.enableTableEditingOptions();
        this._editableSnapshot = null;
    };

    /**
     * Disable WDT editing options that are auto-managed / unsupported for IvyForms.
     * Column editor input types stay enabled (standard modal uses them).
     */
    IvyformsTableConfig.prototype.disableTableEditingOptions = function() {
        jQuery('#wdt-inline-editable, #wdt-mysql-table-name, #wdt-id-editing-column')
            .prop('disabled', true)
            .siblings('label')
            .prop('disabled', true)
            .parent()
            .addClass('c-gray')
            .siblings('h4')
            .addClass('c-gray');

        jQuery('#wdt-inline-editable')
            .siblings('label')
            .prop('disabled', true)
            .css({cursor: 'not-allowed'})
            .addClass('c-gray');

        jQuery('#wdt-id-editing-column, #wdt-user-id-column')
            .prop('disabled', true)
            .parents('div.select')
            .siblings('h4')
            .addClass('c-gray');

        jQuery('#wdt-mysql-table-name').val('');
        jQuery('#wdt-inline-editable').prop('checked', false);
        if (typeof wpdatatable_config !== 'undefined' && typeof wpdatatable_config.setMySQLTableName === 'function') {
            wpdatatable_config.setMySQLTableName('');
        }
    };

    /**
     * Re-enable WDT editing controls disabled by disableTableEditingOptions().
     */
    IvyformsTableConfig.prototype.enableTableEditingOptions = function() {
        jQuery('#wdt-inline-editable, #wdt-mysql-table-name, #wdt-id-editing-column')
            .prop('disabled', false)
            .siblings('label')
            .prop('disabled', false)
            .parent()
            .removeClass('c-gray')
            .siblings('h4')
            .removeClass('c-gray');

        jQuery('#wdt-inline-editable')
            .siblings('label')
            .prop('disabled', false)
            .css({cursor: ''})
            .removeClass('c-gray');

        jQuery('#wdt-id-editing-column, #wdt-user-id-column')
            .prop('disabled', false)
            .parents('div.select')
            .siblings('h4')
            .removeClass('c-gray');
    };

    /**
     * Whether Editable is allowed (IvyForms Pro). Localized from PHP.
     *
     * @returns {boolean}
     */
    IvyformsTableConfig.prototype.canEdit = function() {
        return typeof wdtIvyFormsEditing !== 'undefined' && !!wdtIvyFormsEditing.canEdit;
    };

    /**
     * Disable Editable toggle when IvyForms Pro is unavailable.
     */
    IvyformsTableConfig.prototype.applyProEditableGate = function() {
        if (this.canEdit()) {
            jQuery('#wdt-editable').prop('disabled', false);
            jQuery('#wdt-ivyforms-editable-pro-notice').remove();
            return;
        }

        jQuery('#wdt-editable')
            .prop('checked', false)
            .prop('disabled', true)
            .closest('.form-group')
            .addClass('c-gray');

        if (typeof wpdatatable_config !== 'undefined') {
            wpdatatable_config.setEditable(0);
        }

        if (!jQuery('#wdt-ivyforms-editable-pro-notice').length && typeof wdtIvyFormsEditing !== 'undefined') {
            jQuery('#wdt-editable').closest('.col-sm-4, .form-group').append(
                '<div id="wdt-ivyforms-editable-pro-notice" class="alert alert-info m-t-10" style="margin-top:8px;">' +
                wdtIvyFormsEditing.notice +
                '</div>'
            );
        }
    };

    // Get complete config as JSON
    IvyformsTableConfig.prototype.getConfig = function() {
        return {
            formId: this.formId,
            fields: this.fields,
            dateFrom: this.dateFrom,
            dateTo: this.dateTo,
            filterByUser: this.filterByUser,
            filterByStarred: this.filterByStarred,
            filterByRead: this.filterByRead,
            hasServerSideIntegration: this.hasServerSideIntegration
        };
    };

    // Initialize global instance
    window.ivyformsTableConfig = new IvyformsTableConfig();
})();
