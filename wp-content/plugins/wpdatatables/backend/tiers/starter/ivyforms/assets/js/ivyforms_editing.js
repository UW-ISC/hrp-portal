/**
 * IvyForms editing adapters for wpDataTables.
 *
 * Thin wrappers that open the standard #wdt-frontend-modal / _edit_dialog UI
 * (same path as manual/MySQL). Save uses core wdt_save_table_frontend (PHP
 * intercept); delete posts to wdt_delete_ivyforms_table_row below.
 */
var wpDataTablesEditors = wpDataTablesEditors || {};
wpDataTablesEditors.ivyforms = {};

(function ($) {
    var newSkins = ['dark', 'aqua', 'purple'];

    /**
     * Apply skin-specific icon classes on modal action buttons.
     *
     * @param {jQuery} modal
     * @param {Object} tableDescription
     */
    function applyEditDialogSkinIcons(modal, tableDescription) {
        if (newSkins.indexOf(tableDescription.tableSkin) !== -1) {
            modal.find(tableDescription.selector + '_prev_edit_dialog i').addClass('wpdt-icon-chevron-left');
            modal.find(tableDescription.selector + '_prev_edit_dialog i').removeClass('wpdt-icon-step-backward');
            modal.find(tableDescription.selector + '_next_edit_dialog i').addClass('wpdt-icon-chevron-right');
            modal.find(tableDescription.selector + '_next_edit_dialog i').removeClass('wpdt-icon-step-forward');
            modal.find(tableDescription.selector + '_apply_edit_dialog i').addClass('wpdt-icon-check-circle-full');
            modal.find(tableDescription.selector + '_apply_edit_dialog i').removeClass('wpdt-icon-check');
            modal.find(tableDescription.selector + '_ok_edit_dialog i').addClass('wpdt-icon-check-circle');
            modal.find(tableDescription.selector + '_ok_edit_dialog i').removeClass('wpdt-icon-check-double-reg');
        } else {
            modal.find(tableDescription.selector + '_prev_edit_dialog i').removeClass('wpdt-icon-chevron-left');
            modal.find(tableDescription.selector + '_prev_edit_dialog i').addClass('wpdt-icon-step-backward');
            modal.find(tableDescription.selector + '_next_edit_dialog i').removeClass('wpdt-icon-chevron-right');
            modal.find(tableDescription.selector + '_next_edit_dialog i').addClass('wpdt-icon-step-forward');
            modal.find(tableDescription.selector + '_apply_edit_dialog i').removeClass('wpdt-icon-check-circle-full');
            modal.find(tableDescription.selector + '_apply_edit_dialog i').addClass('wpdt-icon-check');
            modal.find(tableDescription.selector + '_ok_edit_dialog i').removeClass('wpdt-icon-check-circle');
            modal.find(tableDescription.selector + '_ok_edit_dialog i').addClass('wpdt-icon-check-double-reg');
        }
    }

    /**
     * Init TinyMCE editors inside the frontend edit modal.
     */
    function initModalMceEditors() {
        $('#wdt-frontend-modal .editDialogInput').each(function () {
            if ($(this).data('input_type') == 'mce-editor') {
                if ($(this).siblings().length) {
                    tinymce.execCommand('mceRemoveEditor', true, $(this).attr('id'));
                }
                tinymce.init({
                    selector: '#' + $(this).attr('id'),
                    menubar: false,
                    plugins: 'link image media lists hr colorpicker fullscreen textcolor code',
                    toolbar: 'undo redo formatselect bold italic underline strikethrough subscript superscript | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent blockquote | hr fullscreen | link unlink image | forecolor backcolor removeformat | code'
                });
            }
        });
    }

    /**
     * Force hidden Entry ID input to 0 so save intercept creates a new IvyForms entry.
     */
    function resetEntryIdInputInModal() {
        $('#wdt-frontend-modal tr.idRow .editDialogInput').val('0');
    }

    /**
     * Append standard edit dialog + footer buttons into #wdt-frontend-modal.
     *
     * @param {Object} tableDescription
     * @returns {jQuery}
     */
    function appendStandardEditDialog(tableDescription) {
        var modal = $('#wdt-frontend-modal');
        modal.find('.modal-body').append($(tableDescription.selector + '_edit_dialog').show());
        modal.find('.modal-footer').append($(tableDescription.selector + '_edit_dialog_buttons').show());
        applyEditDialogSkinIcons(modal, tableDescription);
        return modal;
    }

    /**
     * Resolve the selected data row (handles responsive detail rows).
     *
     * @param {Object} tableDescription
     * @returns {HTMLElement|undefined}
     */
    function getSelectedDataRow(tableDescription) {
        var row = $(tableDescription.selector + ' tr.selected').get(0);

        if (tableDescription.responsive == 1 && $(row).hasClass('row-detail')) {
            row = $(tableDescription.selector + ' tr.selected').prev('.detail-show').get(0);
        }

        return row;
    }

    /**
     * Open New Entry modal using the standard WDT edit dialog.
     *
     * @param {Object} tableDescription
     */
    wpDataTablesEditors.ivyforms.new = function (tableDescription) {
        var modal = appendStandardEditDialog(tableDescription);

        $('#wdt-frontend-modal .editDialogInput').val('').css('border', '');
        resetEntryIdInputInModal();
        $('#wdt-frontend-modal .fileinput').removeClass('fileinput-exists').addClass('fileinput-new');
        $('#wdt-frontend-modal .fileinput').find('div.fileinput-exists').removeClass('fileinput-exists').addClass('fileinput-new');
        $('#wdt-frontend-modal .fileinput').find('.fileinput-filename').text('');
        $('#wdt-frontend-modal .fileinput').find('.fileinput-preview').html('');

        $('#wdt-frontend-modal .editDialogInput').each(function () {
            if ($(this).data('input_type') == 'mce-editor') {
                if (tinymce.activeEditor) {
                    tinymce.activeEditor.setContent('');
                }
                tinymce.execCommand('mceRemoveEditor', true, $(this).attr('id'));
                tinymce.init({
                    selector: '#' + $(this).attr('id'),
                    menubar: false,
                    plugins: 'link image media lists hr colorpicker fullscreen textcolor code',
                    toolbar: 'undo redo formatselect bold italic underline strikethrough subscript superscript | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent blockquote | hr fullscreen | link unlink image | forecolor backcolor removeformat | code'
                });
            }
        });

        wpDataTables[tableDescription.tableId].checkSelectedLimits();
        $('#wdt-frontend-modal .selectpicker').selectpicker('deselectAll').selectpicker('refresh');
        wpDataTablesFunctions[tableDescription.tableId].setPredefinedEditValues();
        resetEntryIdInputInModal();

        if (modal.find('.wdt-edit-dialog-fields-block').find('.form-group').length == 0) {
            $('#wdt-frontend-modal div.wdt-no-editor-inputs-selected-alert').show();
        }

        modal.modal('show');
        $('.wdt-apply-edit-button').removeClass('hidden');
        $('.wdt-apply-duplicate-button').addClass('hidden');
        singleClick = false;
    };

    /**
     * Open Edit (or Duplicate) modal using the standard WDT edit dialog.
     *
     * @param {Object} tableDescription
     * @param {boolean} isDuplicate
     */
    wpDataTablesEditors.ivyforms.edit = function (tableDescription, isDuplicate) {
        var modal = appendStandardEditDialog(tableDescription);
        var row = getSelectedDataRow(tableDescription);
        var data = wpDataTables[tableDescription.tableId].fnGetData(row);

        if (isDuplicate) {
            wpDataTablesFunctions[tableDescription.tableId].applyData(data, true, false);
            resetEntryIdInputInModal();
        } else {
            wpDataTablesFunctions[tableDescription.tableId].applyData(data);
        }

        wpDataTables[tableDescription.tableId].checkSelectedLimits();
        initModalMceEditors();

        if (modal.find('.wdt-edit-dialog-fields-block').find('.form-group').length == 0) {
            $('#wdt-frontend-modal div.wdt-no-editor-inputs-selected-alert').show();
        }

        modal.modal('show');

        if (isDuplicate) {
            $('.wdt-apply-edit-button').addClass('hidden');
            $('.wdt-apply-duplicate-button').removeClass('hidden');
        } else {
            $('.wdt-apply-edit-button').removeClass('hidden');
            $('.wdt-apply-duplicate-button').addClass('hidden');
        }

        singleClick = false;
    };

    /**
     * Delete selected IvyForms entry via wdt_delete_ivyforms_table_row.
     *
     * @param {Object} tableDescription
     */
    wpDataTablesEditors.ivyforms.delete = function (tableDescription) {
        var row = getSelectedDataRow(tableDescription);
        var data = wpDataTables[tableDescription.tableId].fnGetData(row);
        var id_val = data[tableDescription.idColumnIndex];
        var configuredFormId = tableDescription.ivyformsFormId;

        if (configuredFormId && tableDescription.ivyformsFormIdColumnIndex >= 0) {
            var entryFormId = parseInt(data[tableDescription.ivyformsFormIdColumnIndex], 10);
            if (!entryFormId || entryFormId !== parseInt(configuredFormId, 10)) {
                wdtNotify(
                    wpdatatables_edit_strings.error_common,
                    wpdatatables_edit_strings.error_common,
                    'danger'
                );
                return;
            }
        }

        $.ajax({
            url: tableDescription.adminAjaxBaseUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wdt_delete_ivyforms_table_row',
                id_val: id_val,
                table_id: tableDescription.tableWpId,
                wdtNonce: $('#wdtNonceFrontendEdit_' + tableDescription.tableWpId).val()
            },
            success: function (response) {
                if (response && response.error === '') {
                    wpDataTables[tableDescription.tableId].fnDraw(false);
                    $('#wdt-delete-modal').modal('hide');
                    wdtNotify(wpdatatables_edit_strings.success_common, wpdatatables_edit_strings.rowDeleted_common, 'success');
                } else {
                    wdtNotify(
                        wpdatatables_edit_strings.error_common,
                        (response && response.error) ? response.error : wpdatatables_edit_strings.error_common,
                        'danger'
                    );
                }
            },
            error: function () {
                wdtNotify(wpdatatables_edit_strings.error_common, wpdatatables_edit_strings.error_common, 'danger');
            }
        });
    };

})(jQuery);
