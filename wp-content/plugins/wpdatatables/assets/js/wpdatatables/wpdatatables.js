/** New JS controller for wpDataTables **/


var wpDataTables = {};
var wpDataTablesSelRows = {};
var wpDataTablesFunctions = {};
var wpDataTablesUpdatingFlags = {};
var wpDataTablesResponsiveHelpers = {};
var wpDataTablesHooks = wpDataTablesHooks || {
    onRenderFilter: [],
    onRenderDetails: []
};
var wpDataTablesEditors = {};
var wdtBreakpointDefinition = {
    tablet: 1024,
    phone: 480
};
var wdtCustomUploader = null;

var wdtRenderDataTable = null;
var singleClick = false;

(function ($) {
    $(function () {

        /**
         * Helper function to render a DataTable
         *
         * @param $table jQuery link to the container table object
         * @param tableDescription JSON with the table description
         */
        wdtRenderDataTable = function ($table, tableDescription) {

            // Parse the DataTable init options
            var dataTableOptions = tableDescription.dataTableParams;

            //[<-- Full version -->]//
            /**
             * Responsive-mode related stuff
             */
            if (tableDescription.responsive) {
                wpDataTablesResponsiveHelpers[tableDescription.tableId] = false;
                dataTableOptions.preDrawCallback = function () {
                    if (!wpDataTablesResponsiveHelpers[tableDescription.tableId]) {
                        if (typeof tableDescription.mobileWidth !== 'undefined') {
                            wdtBreakpointDefinition.phone = parseInt(tableDescription.mobileWidth);
                        }
                        if (typeof tableDescription.tabletWidth !== 'undefined') {
                            wdtBreakpointDefinition.tablet = parseInt(tableDescription.tabletWidth);
                        }
                        wpDataTablesResponsiveHelpers[tableDescription.tableId] = new ResponsiveDatatablesHelper($(tableDescription.selector).dataTable(), wdtBreakpointDefinition, {
                            clickOn: tableDescription.responsiveAction ? tableDescription.responsiveAction : 'icon',
                            showDetail: function (detailsRow) {
                                if (tableDescription.conditional_formatting_columns) {
                                    var responsive_rows = detailsRow.find('li');
                                    var oSettings = wpDataTables[tableDescription.tableId].fnSettings();
                                    var params = {};

                                    params.thousandsSeparator = tableDescription.number_format == 1 ? '.' : ',';
                                    params.decimalSeparator = tableDescription.number_format == 1 ? ',' : '.';
                                    params.dateFormat = tableDescription.datepickFormat;
                                    params.momentDateFormat = params.dateFormat.replace('dd', 'DD').replace('M', 'MMM').replace('mm', 'MM');
                                    params.momentTimeFormat = tableDescription.timeFormat.replace('H', 'H').replace('i', 'mm');

                                    for (var i = 0; i < tableDescription.conditional_formatting_columns.length; i++) {
                                        var column = oSettings.oInstance.api().column(tableDescription.conditional_formatting_columns[i] + ':name', {search: 'applied'});
                                        var conditionalFormattingRules = oSettings.aoColumns[column.index()].conditionalFormattingRules;
                                        params.columnType = oSettings.aoColumns[column.index()].wdtType;

                                        for (var j in conditionalFormattingRules) {
                                            responsive_rows.each(function () {
                                                $(this).find('.columnValue').contents().filter(function () {
                                                    if (this.nodeType === 8) {
                                                        $(this).remove();
                                                    }
                                                });

                                                var value_cell = $(this).find('.columnValue').html();

                                                var column_index = $(this).data('column');
                                                if (column_index == column.index()) {
                                                    wdtCheckConditionalFormatting(conditionalFormattingRules[j], params, $(this), true);
                                                }
                                            });
                                        }
                                    }
                                }
                            }
                        });
                    }
                    wdtAddOverlay('#' + tableDescription.tableId);
                }
                dataTableOptions.fnRowCallback = function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                    wpDataTablesResponsiveHelpers[tableDescription.tableId].createExpandIcon(nRow);
                }
                if (!tableDescription.editable) {
                    dataTableOptions.fnDrawCallback = function () {
                        wpDataTablesResponsiveHelpers[tableDescription.tableId].respond();
                        wdtRemoveOverlay('#' + tableDescription.tableId);
                    }
                }
            } else {
                dataTableOptions.fnPreDrawCallback = function () {
                    wdtAddOverlay('#' + tableDescription.tableId);
                }
            }

            if (tableDescription.editable) {

                if (typeof wpDataTablesFunctions[tableDescription.tableId] === 'undefined') {
                    wpDataTablesFunctions[tableDescription.tableId] = {};
                }

                wpDataTablesSelRows[tableDescription.tableId] = -1;
                dataTableOptions.fnDrawCallback = function () {
                    wdtRemoveOverlay('#' + tableDescription.tableId);
                    if (tableDescription.responsive) {
                        wpDataTablesResponsiveHelpers[tableDescription.tableId].respond();
                    }

                    $('.edit_table[aria-controls="' + tableDescription.tableId + '"]').addClass('disabled');
                    $('.duplicate_table_entry[aria-controls="' + tableDescription.tableId + '"]').addClass('disabled');
                    $('.delete_table_entry[aria-controls="' + tableDescription.tableId + '"]').addClass('disabled');
                    $('.master_detail[aria-controls="' + tableDescription.tableId + '"]').addClass('disabled');

                    if (wpDataTablesSelRows[tableDescription.tableId] == -2) {
                        // -2 means select first row on "next" page
                        var sel_row_index = wpDataTables[tableDescription.tableId].fnSettings()._iDisplayLength - 1;
                        $(tableDescription.selector + ' > tbody > tr').removeClass('selected');
                        wpDataTablesSelRows[tableDescription.tableId] = wpDataTables[tableDescription.tableId].fnGetPosition($(tableDescription.selector + ' > tbody > tr:eq(' + sel_row_index + ')').get(0));
                        $(tableDescription.selector + ' > tbody > tr:eq(' + sel_row_index + ')').addClass('selected');
                    } else if (wpDataTablesSelRows[tableDescription.tableId] == -3) {
                        var sel_row_index = 0;
                        $(tableDescription.selector + ' > tbody > tr').removeClass('selected');
                        wpDataTablesSelRows[tableDescription.tableId] = wpDataTables[tableDescription.tableId].fnGetPosition($(tableDescription.selector + ' > tbody > tr:eq(' + sel_row_index + ')').get(0));
                        $(tableDescription.selector + ' > tbody > tr:eq(' + sel_row_index + ')').addClass('selected');
                    }

                    $(tableDescription.selector + '_edit_dialog').parent().removeClass('overlayed');

                    wpDataTablesUpdatingFlags[tableDescription.tableId] = false;
                };

                /**
                 * Data apply function for editable tables
                 * @param data
                 * @param isDuplicate
                 * @param applyDuplicate
                 */
                wpDataTablesFunctions[tableDescription.tableId].applyData = function (data, isDuplicate = false, applyDuplicate = false) {
                    $(data).each(function (index, el) {
                        var $inputElement = $('#' + tableDescription.tableId + '_edit_dialog .editDialogInput:not(.bootstrap-select):eq(' + index + ')');

                        if (el) {
                            if ($inputElement.data("key").toLowerCase() === 'wdt_id' && isDuplicate) {
                                val = "0"
                            } else {
                                var val = el.toString();
                            }
                        } else {
                            var val = '';
                        }
                        if (val.indexOf('span') != -1) {
                            val = val.replace(/<span>/g, '').replace(/<\/span>/g, '');
                        }
                        if (val.indexOf('<br/>') != -1 || val.indexOf('<br>') != -1) {
                            val = val.replace(/<br\s*[\/]?>/g, "\n");
                        }

                        var inputElementType = $inputElement.data('input_type');
                        var columnType = $inputElement.data('column_type');

                        if (inputElementType === 'multi-selectbox' || inputElementType === 'selectbox') {

                            if ($inputElement.hasClass('wdt-possible-values-ajax')) {

                                var $selectpickerBlock = $('select#' + tableDescription.tableId + '_' + $inputElement.data('key')).closest('.fg-line').parent();

                                var mandatory = $inputElement.hasClass('mandatory') ? 'mandatory ' : '';
                                var foreignKeyRule = $inputElement.hasClass('wdt-foreign-key-select') ? 'wdt-foreign-key-select ' : '';
                                var searchInSelect = $inputElement.hasClass('wdt-search-in-select') ? ' wdt-search-in-select ' : '';

                                // Recreate the selectbox element
                                $selectpickerBlock.html('<div class="fg-line"><select id="' + tableDescription.tableId + '_' + $inputElement.data('key') + '" title="' + wpdatatables_frontend_strings.nothingSelected + '" data-input_type="' + inputElementType + '" data-key="' + $inputElement.data('key') + '" class="form-control editDialogInput selectpicker ' + mandatory + 'wdt-possible-values-ajax ' + foreignKeyRule + searchInSelect + '" data-live-search="true" data-live-search-placeholder="' + wpdatatables_frontend_strings.search + '" data-column_header="' + $inputElement.data('column_header') + '"></select></div>');
                                if (inputElementType === 'multi-selectbox')
                                    $selectpickerBlock.find('select').attr('multiple', 'multiple');

                                // If default value is set, append it to selectbox HTML
                                if (inputElementType === 'multi-selectbox') {
                                    var values = val.split(', ');

                                    $.each(values, function (index, value) {
                                        if (value) {
                                            $selectpickerBlock.find('select').append('<option selected value="' + value + '">' + value + '</option>');
                                        }
                                    });
                                } else {
                                    $selectpickerBlock.find('select').append('<option selected value="' + val + '">' + val + '</option>');
                                }

                                $inputElement = $selectpickerBlock.find('select');

                                // Load possible values on modal open
                                $inputElement.on('show.bs.select', function (e) {
                                    jQuery(this).closest('div.editDialogInput').find('.bs-searchbox .form-control').val('').trigger('keyup');
                                });

                                // Add AJAX to selectbox
                                $inputElement.selectpicker('refresh')
                                    .ajaxSelectPicker({
                                        ajax: {
                                            url: tableDescription.adminAjaxBaseUrl,
                                            method: 'POST',
                                            data: {
                                                wdtNonce: $('#wdtNonce').val(),
                                                action: 'wpdatatables_get_column_possible_values',
                                                tableId: tableDescription.tableWpId,
                                                originalHeader: $inputElement.data('key')
                                            }
                                        },
                                        cache: false,
                                        preprocessData: function (data) {
                                            if ($('.editDialogInput.open').find('select').data('input_type') === 'selectbox') {
                                                data.unshift({value: ''});
                                            }
                                            return data
                                        },
                                        preserveSelected: true,
                                        emptyRequest: true,
                                        preserveSelectedPosition: 'before',
                                        locale: {
                                            emptyTitle: wpdatatables_frontend_strings.nothingSelected,
                                            statusSearching: wpdatatables_frontend_strings.sLoadingRecords,
                                            currentlySelected: wpdatatables_frontend_strings.currentlySelected,
                                            errorText: wpdatatables_frontend_strings.errorText,
                                            searchPlaceholder: wpdatatables_frontend_strings.search,
                                            statusInitialized: wpdatatables_frontend_strings.statusInitialized,
                                            statusNoResults: wpdatatables_frontend_strings.statusNoResults,
                                            statusTooShort: wpdatatables_frontend_strings.statusTooShort
                                        }
                                    });
                            }

                            if (inputElementType == 'multi-selectbox') {
                                values = val.split(', ');
                                $inputElement.selectpicker();
                                $inputElement.selectpicker('val', values);
                                $inputElement.selectpicker('refresh');
                            } else if (inputElementType == 'selectbox') {
                                $inputElement.selectpicker();
                                if ($inputElement.hasClass('wdt-foreign-key-select') && val != '') {
                                    val = typeof $inputElement.find('option[data-label="' + val + '"]').val() !== 'undefined' ?
                                        $inputElement.find('option[data-label="' + val + '"]').val() : val;
                                }
                                if (val == '') {
                                    val = 'possibleValuesAddEmpty';
                                }
                                $inputElement.selectpicker('val', val);
                                $inputElement.selectpicker('refresh');
                            }

                            // Hide/Show search box in select/multiselect edit input
                            if (searchInSelect === '') {
                                $inputElement.closest('div.editDialogInput').find('.bs-searchbox').hide();
                            } else {
                                $inputElement.closest('div.editDialogInput').find('.bs-searchbox').show();
                            }
                        } else {
                            if (inputElementType == 'attachment' || $.inArray(columnType, ['icon']) !== -1) {
                                columnType = $inputElement.parent().data('column_type') === undefined ? 'link' : $inputElement.parent().data('column_type');
                                if (isDuplicate && applyDuplicate) {
                                    val = wpDataTablesFunctions[tableDescription.tableId].transformDataForDuplicate(val, columnType);
                                }
                                if (val != '') {
                                    if ($(val).children('img').first().attr('src') != undefined) {
                                        val = $(val).children('img').first().attr('src') + '||' + $(val).attr('href');
                                    } else if ($(val).attr('href') != undefined) {
                                        val = $(val).attr('href');
                                    } else if ($(val).attr('src') != undefined) {
                                        val = $(val).attr('src');
                                    }

                                    $inputElement.parent().parent().removeClass('fileinput-new').addClass('fileinput-exists');
                                    if (columnType == 'icon') {
                                        $inputElement.parent().parent().parent().removeClass('fileinput-new').addClass('fileinput-exists');
                                        if (val.indexOf('||') != -1) {
                                            $inputElement.parent().parent().parent().find('.fileinput-preview').html('<img src=' + val.substring(val.indexOf('||') + 2, val.length) + '>');
                                        } else {
                                            $inputElement.parent().parent().parent().find('.fileinput-preview').html('<img src=' + val + '>');
                                        }
                                    } else {
                                        $inputElement.parent().parent().find('.fileinput-filename').text(val.split('/').pop());
                                    }
                                } else {
                                    $inputElement.closest('.fileinput').removeClass('fileinput-exists').addClass('fileinput-new');
                                    $inputElement.closest('.fileinput').find('div.fileinput-exists').removeClass('fileinput-exists').addClass('fileinput-new');
                                    $inputElement.closest('.fileinput').find('.fileinput-filename').text('');
                                    $inputElement.closest('.fileinput').find('.fileinput-preview').html('');
                                }
                            } else {
                                if (isDuplicate && applyDuplicate && $.inArray(columnType, ['link', 'int']) !== -1) {
                                    val = wpDataTablesFunctions[tableDescription.tableId].transformDataForDuplicate(val, columnType);
                                }
                                if (val.indexOf('<a ') != -1) {
                                    if ($.inArray(columnType, ['link', 'email', 'icon']) !== -1) {
                                        $link = $(val);
                                        if (applyDuplicate) {
                                            val = $link.attr('href');
                                        } else if ($link.attr('href').indexOf($link.html()) === -1) {
                                            val = $link.attr('href').replace('mailto:', '') + '||' + $link.html();
                                        } else {
                                            val = $link.html();
                                        }
                                    }
                                }

                                if (inputElementType == 'mce-editor') {
                                    tinymce.execCommand('mceRemoveEditor', true, $inputElement.attr('id'));
                                    tinymce.init({
                                        selector: '#' + $inputElement.attr('id'),
                                        init_instance_callback: function (editor) {
                                            editor.setContent(val);
                                        },
                                        menubar: false,
                                        plugins: 'link image media lists hr colorpicker fullscreen textcolor code',
                                        toolbar: 'undo redo formatselect bold italic underline strikethrough subscript superscript | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent blockquote | hr fullscreen | link unlink image | forecolor backcolor removeformat | code'
                                    });
                                }
                            }
                            $inputElement.val(val).css('border', '');
                        }
                    });
                };

                wpDataTablesFunctions[tableDescription.tableId].transformDataForDuplicate = function (val, columnType) {
                    var transformedVal = val;

                    if (val) {
                        if (columnType === 'int') {
                            var thousandsSeparator = tableDescription.number_format == 1 ? '.' : ',';
                            var decimalSeparator = tableDescription.number_format == 1 ? ',' : '.';

                            transformedVal = wdtFormatNumber(val, 0, decimalSeparator, thousandsSeparator);
                        } else if (columnType === 'icon') {
                            val = val.substring(val.lastIndexOf('|') + 1);
                            transformedVal = "<a href='" + val + "' target='_blank' rel='lightbox[-1]'><img src='" + val + "' /></a>";
                        } else if (columnType === 'link') {
                            transformedVal = "<a href='" + val + "' rel='' target='_self'>att</a>";
                        }
                    }

                    return transformedVal;
                }

                /**
                 * Saving of the table data for frontend
                 *
                 * @param forceRedraw
                 * @param closeDialog
                 * @returns {boolean}
                 */
                wpDataTablesFunctions[tableDescription.tableId].saveTableData = function (forceRedraw, closeDialog, duplicateEntry) {
                    $(tableDescription.selector + '_edit_dialog').closest('.modal-dialog').find('.wdt-preload-layer').animateFadeIn();
                    wpDataTablesUpdatingFlags[tableDescription.tableId] = true;
                    var formdata = {
                        table_id: tableDescription.tableWpId
                    };
                    var aoData = [];
                    var valid = true;
                    var validation_message = '';
                    if (tableDescription.popoverTools) {
                        $('.wpDataTablesPopover.editTools').hide();
                    }

                    //Moves tinymce value to hidden initial textarea
                    if (typeof tinymce != 'undefined') {
                        tinymce.triggerSave();
                    }
                    $(tableDescription.selector + '_edit_dialog .editDialogInput').not('.bootstrap-select').each(function () {
                        // validation
                        if ($(this).data('input_type') == 'email') {
                            if ($(this).val() != '') {
                                var field_valid = wdtValidateEmail($(this).val());
                                if (!field_valid) {
                                    valid = false;
                                    $(this).addClass('error');
                                    validation_message += wpdatatables_frontend_strings.invalid_email + ' <b>' + $(this).data('column_header') + '</b><br>';
                                } else {
                                    $(this).removeClass('error')
                                }
                            }
                        } else if ($(this).data('input_type') == 'link') {
                            if ($(this).val() != '') {
                                field_valid = wdtValidateURL($(this).val());
                                if (!field_valid) {
                                    valid = false;
                                    $(this).addClass('error');
                                    validation_message += wpdatatables_frontend_strings.invalid_link + ' <b>' + $(this).data('column_header') + '</b><br>';
                                } else {
                                    $(this).removeClass('error');
                                }
                            }
                        }
                        if ($(this).hasClass('mandatory')) {
                            if ($(this).val() == '' || $(this).val() == null) {
                                $(this).addClass('error');
                                valid = false;
                                validation_message += '<b>' + $(this).data('column_header') + '</b> ' + wpdatatables_frontend_strings.cannot_be_empty + '<br>';
                            } else {
                                if (valid) {
                                    $(this).removeClass('error');
                                }
                            }
                        }
                        if ($(this).hasClass('datepicker')) {
                            formdata[$(this).data('key')] = $.datepicker.formatDate(tableDescription.datepickFormat, $.datepicker.parseDate(tableDescription.datepickFormat, $(this).val()));
                        } else if ($(this).data('input_type') == 'multi-selectbox') {
                            if ($(this).val()) {
                                formdata[$(this).data('key')] = $(this).val().join(', ');
                            } else {
                                formdata[$(this).data('key')] = '';
                            }
                        } else if ($(this).data('column_type') == 'int') {
                            formdata[$(this).data('key')] = $(this).val().replace(/,/g, '').replace(/\./g, '');
                        } else {
                            formdata[$(this).data('key')] = $(this).val();
                        }
                        aoData.push(formdata[$(this).data('key')]);
                    });
                    if (!valid) {
                        $(tableDescription.selector + '_edit_dialog').closest('.modal-dialog').find('.wdt-preload-layer').animateFadeOut();
                        wdtNotify(wpdatatables_edit_strings.error, validation_message, 'danger');
                        return false;
                    }
                    wpDataTablesUpdatingFlags[tableDescription.tableId] = true;
                    $.ajax({
                        url: tableDescription.adminAjaxBaseUrl,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'wdt_save_table_frontend',
                            wdtNonce: $('#wdtNonceFrontendEdit_' + tableDescription.tableWpId).val(),
                            isDuplicate: duplicateEntry,
                            formdata: formdata
                        },
                        success: function (returnData) {
                            $(tableDescription.selector + '_edit_dialog').closest('.modal-dialog').find('.wdt-preload-layer').animateFadeOut();
                            if (returnData.error == '') {
                                var insert_id = returnData.success;
                                if (returnData.is_new) {
                                    forceRedraw = true;
                                }
                                if (insert_id) {
                                    $(tableDescription.selector + '_edit_dialog tr.idRow .editDialogInput').val(insert_id);
                                    if (forceRedraw) {
                                        wpDataTables[tableDescription.tableId].fnDraw(false);
                                        $('.edit_table[aria-controls="' + tableDescription.tableId + '"]').addClass('disabled');
                                        $('.duplicate_table_entry[aria-controls="' + tableDescription.tableId + '"]').addClass('disabled');
                                    }
                                } else {
                                    wpDataTables[tableDescription.tableId].fnDraw(false);
                                    $('.edit_table[aria-controls="' + tableDescription.tableId + '"]').addClass('disabled');
                                    $('.duplicate_table_entry[aria-controls="' + tableDescription.tableId + '"]').addClass('disabled');
                                }

                                wdtNotify(wpdatatables_edit_strings.success, wpdatatables_edit_strings.dataSaved, 'success');
                                setTimeout(function () {
                                    if (closeDialog) {
                                        $('#wdt-frontend-modal').modal('hide');
                                    } else {
                                        $(tableDescription.selector + '_edit_dialog .editDialogInput').val('');
                                        $(tableDescription.selector + '_edit_dialog .editDialogInput').selectpicker('val', '');
                                        //Fix for resetting currently selected block in select picker
                                        $(tableDescription.selector + '_edit_dialog .editDialogInput').trigger('change.abs.preserveSelected').selectpicker('refresh');
                                        $('.fileinput').removeClass('fileinput-exists').addClass('fileinput-new');
                                        $('.fileinput').find('div.fileinput-exists').removeClass('fileinput-exists').addClass('fileinput-new');
                                        $('.fileinput').find('.fileinput-filename').text('');
                                        $('.fileinput').find('.fileinput-preview').html('');
                                        if (tinymce.activeEditor)
                                            tinymce.activeEditor.setContent('');

                                        if (duplicateEntry === true) {
                                            wpDataTablesFunctions[tableDescription.tableId].applyData(aoData, true, true);
                                        } else {
                                            wpDataTablesFunctions[tableDescription.tableId].setPredefinedEditValues();
                                        }
                                    }
                                }, 1000);
                                if (!returnData.is_new && $(tableDescription.selector + ' > tbody > tr.selected').length) {
                                    var cursor = wpDataTables[tableDescription.tableId].fnGetPosition($(tableDescription.selector + ' > tbody > tr.selected').get(0));
                                    wpDataTables[tableDescription.tableId].fnSettings().aoData[cursor]._aData = aoData;
                                }
                            } else {
                                wdtNotify(wpdatatables_edit_strings.error, returnData.error, 'danger');
                            }
                        },
                        error: function (xhr, response) {
                            $(tableDescription.selector + '_edit_dialog').closest('.modal-dialog').find('.wdt-preload-layer').animateFadeOut();
                            wdtNotify(wpdatatables_edit_strings.error, wpdatatables_frontend_strings.databaseInsertError, 'danger');
                        }
                    });
                    return true;
                }

                wpDataTablesFunctions[tableDescription.tableId].setPredefinedEditValues = function () {
                    for (let i in tableDescription.advancedEditingOptions.aoColumns) {

                        let column = tableDescription.advancedEditingOptions.aoColumns[i];
                        let defaultValuesArr;

                        // Create selectbox based on "Number of possible values to load" option
                        if ($.inArray(column.editorInputType, ['selectbox', 'multi-selectbox']) !== -1) {
                            if (column.possibleValuesAjax !== -1) {

                                var $selectpickerBlock = $('select#' + tableDescription.tableId + '_' + column.origHeader).closest('.fg-line').parent();

                                var mandatory = column.mandatory ? 'mandatory ' : '';
                                var possibleValuesAjax = (column.possibleValuesAjax) ? 'wdt-possible-values-ajax ' : '';
                                var foreignKeyRule = column.foreignKeyRule ? 'wdt-foreign-key-select ' : '';
                                var searchInSelect = column.searchInSelectBoxEditing ? ' wdt-search-in-select ' : '';

                                // Recreate the selectbox element
                                $selectpickerBlock.html('<div class="fg-line"><select id="' + tableDescription.tableId + '_' + column.origHeader + '" data-input_type="' + column.editorInputType + '" data-key="' + column.origHeader + '" class="form-control editDialogInput selectpicker ' + mandatory + possibleValuesAjax + foreignKeyRule + searchInSelect + '" data-live-search="true" data-live-search-placeholder="' + wpdatatables_frontend_strings.search + '" data-column_header="' + column.displayHeader + '"></select></div>');
                                if (column.editorInputType === 'multi-selectbox')
                                    $selectpickerBlock.find('select').attr('multiple', 'multiple');

                                // If default value is set, append it to selectbox HTML
                                if (column.defaultValue) {
                                    if (column.editorInputType === 'multi-selectbox') {
                                        var defaultValues = !Array.isArray(column.defaultValue) ? column.defaultValue.split('|') : column.defaultValue;

                                        $.each(defaultValues, function (index, value) {
                                            if (value) {
                                                $selectpickerBlock.find('select').append('<option selected value="' + value + '">' + value + '</option>');
                                            }
                                        });
                                    } else {
                                        if (typeof column.defaultValue === 'object')
                                            $selectpickerBlock.find('select').append('<option selected value="' + column.defaultValue.value + '">' + column.defaultValue.text + '</option>');
                                        else
                                            $selectpickerBlock.find('select').append('<option selected value="' + column.defaultValue + '">' + column.defaultValue + '</option>');
                                    }
                                }

                                // Load possible values on modal open
                                $('select#' + tableDescription.tableId + '_' + column.origHeader).on('show.bs.select', function (e) {
                                    jQuery(this).closest('div.editDialogInput').find('.bs-searchbox .form-control').val('').trigger('keyup');
                                });

                                // Add AJAX to selectbox
                                $('select#' + tableDescription.tableId + '_' + column.origHeader).selectpicker('refresh').ajaxSelectPicker({
                                    ajax: {
                                        url: tableDescription.adminAjaxBaseUrl,
                                        method: 'POST',
                                        data: {
                                            wdtNonce: $('#wdtNonce').val(),
                                            action: 'wpdatatables_get_column_possible_values',
                                            tableId: tableDescription.tableWpId,
                                            originalHeader: column.origHeader
                                        }
                                    },
                                    cache: false,
                                    preprocessData: function (data) {
                                        if ($('.editDialogInput.open').find('select').data('input_type') === 'selectbox') {
                                            data.unshift({value: ''});
                                        }
                                        return data
                                    },
                                    preserveSelected: true,
                                    emptyRequest: true,
                                    preserveSelectedPosition: 'before',
                                    locale: {
                                        emptyTitle: wpdatatables_frontend_strings.nothingSelected,
                                        statusSearching: wpdatatables_frontend_strings.sLoadingRecords,
                                        currentlySelected: wpdatatables_frontend_strings.currentlySelected,
                                        errorText: wpdatatables_frontend_strings.errorText,
                                        searchPlaceholder: wpdatatables_frontend_strings.search,
                                        statusInitialized: wpdatatables_frontend_strings.statusInitialized,
                                        statusNoResults: wpdatatables_frontend_strings.statusNoResults,
                                        statusTooShort: wpdatatables_frontend_strings.statusTooShort
                                    }
                                });
                            }
                            // Hide/Show search box in select/multiselect edit input
                            if (column.searchInSelectBoxEditing !== 1) {
                                $('select#' + tableDescription.tableId + '_' + column.origHeader).closest('div.editDialogInput').find('.bs-searchbox').hide();
                            } else {
                                $('select#' + tableDescription.tableId + '_' + column.origHeader).closest('div.editDialogInput').find('.bs-searchbox').show();
                            }
                        }

                        if (column.defaultValue) {
                            let columnDefaultValue = column.defaultValue;
                            if ($.inArray(column.editorInputType, ['selectbox', 'multi-selectbox']) !== -1) {
                                if (typeof columnDefaultValue === 'object') {
                                    defaultValuesArr = columnDefaultValue.value;
                                } else {
                                    defaultValuesArr = column.editorInputType === 'multi-selectbox' && !Array.isArray(columnDefaultValue) ? columnDefaultValue.split('|') : column.defaultValue;
                                }
                                $('#wdt-frontend-modal .editDialogInput:not(.bootstrap-select):eq(' + i + ')').selectpicker('val', defaultValuesArr).trigger('change.abs.preserveSelected');
                            } else if ($.inArray(column.editorInputType, ['attachment', 'image']) !== -1 && ($('.fileupload-' + tableDescription.tableId).length)) {

                                //Reset attachment editor
                                var $fileUploadEl = $('.fileupload-' + tableDescription.tableId);
                                $($fileUploadEl).each(function () {
                                    if ($(this).data('column_type') == 'icon') {
                                        $(this).parent().parent().find('.fileinput-preview').html('<img src=' + columnDefaultValue + '>');
                                        $(this).parent().removeClass('fileinput-new').addClass('fileinput-exists');
                                        $(this).parent().parent().removeClass('fileinput-new').addClass('fileinput-exists');
                                    } else {
                                        $(this).parent().find('.fileinput-filename').val(columnDefaultValue);
                                        $(this).parent().removeClass('fileinput-new').addClass('fileinput-exists');
                                        $(this).parent().find('.fileinput-filename').text((columnDefaultValue).substring((columnDefaultValue).lastIndexOf("/") + 1));
                                    }
                                });
                                $('#wdt-frontend-modal .editDialogInput:not(.bootstrap-select):eq(' + i + ')').val(column.defaultValue);

                            } else if (column.editorInputType === 'mce-editor') {
                                if (tinymce.activeEditor) {
                                    $inputElement = $('#' + tableDescription.tableId + '_edit_dialog .editDialogInput:not(.bootstrap-select):eq(' + i + ')');
                                    tinymce.execCommand('mceRemoveEditor', true, $inputElement.attr('id'));
                                    tinymce.init({
                                        selector: '#' + $inputElement.attr('id'),
                                        init_instance_callback: function (editor) {
                                            editor.setContent(column.defaultValue);
                                        },
                                        menubar: false,
                                        plugins: 'link image media lists hr colorpicker fullscreen textcolor code',
                                        toolbar: 'undo redo formatselect bold italic underline strikethrough subscript superscript | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent blockquote | hr fullscreen | link unlink image | forecolor backcolor removeformat | code'
                                    });
                                }
                            } else {
                                $('#wdt-frontend-modal .editDialogInput:not(.bootstrap-select):eq(' + i + ')').val(column.defaultValue);
                            }
                        }
                    }
                }

            }

            /**
             * Remove overlay if the table is not responsive nor editable
             */
            if (!tableDescription.responsive
                && !tableDescription.editable) {
                dataTableOptions.fnDrawCallback = function () {
                    wdtRemoveOverlay('#' + tableDescription.tableId);
                }
            }
            //[<--/ Full version -->]//

            /**
             * If aggregate functions shortcode exists on the page add that column to the ajax data
             */
            if ($('.wdt-column-sum[data-table-id="' + tableDescription.tableWpId + '"]').length) {
                var sumColumns = [];
                $('.wdt-column-sum[data-table-id="' + tableDescription.tableWpId + '"]').each(function () {
                    sumColumns.push($(this).data('column-orig-header'));
                });
            }

            if ($('.wdt-column-avg[data-table-id="' + tableDescription.tableWpId + '"]').length) {
                var avgColumns = [];
                $('.wdt-column-avg[data-table-id="' + tableDescription.tableWpId + '"]').each(function () {
                    avgColumns.push($(this).data('column-orig-header'));
                });
            }

            if ($('.wdt-column-min[data-table-id="' + tableDescription.tableWpId + '"]').length) {
                var minColumns = [];
                $('.wdt-column-min[data-table-id="' + tableDescription.tableWpId + '"]').each(function () {
                    minColumns.push($(this).data('column-orig-header'));
                });
            }

            if ($('.wdt-column-max[data-table-id="' + tableDescription.tableWpId + '"]').length) {
                var maxColumns = [];
                $('.wdt-column-max[data-table-id="' + tableDescription.tableWpId + '"]').each(function () {
                    maxColumns.push($(this).data('column-orig-header'));
                });
            }

            if (tableDescription.serverSide) {
                dataTableOptions.ajax.data = function (data) {
                    data.sumColumns = sumColumns;
                    data.avgColumns = avgColumns;
                    data.minColumns = minColumns;
                    data.maxColumns = maxColumns;
                    data.currentUserId = $('#wdt-user-id-placeholder').val();
                    data.currentUserLogin = $('#wdt-user-login-placeholder').val();
                    data.currentPostIdPlaceholder = $('#wdt-post-id-placeholder').val();
                    data.currentUserFirstName = $('#wdt-user-first-name-placeholder').val();
                    data.currentUserLastName = $('#wdt-user-last-name-placeholder').val();
                    data.currentUserEmail = $('#wdt-user-email-placeholder').val();
                    data.currentDate = $('#wdt-date-placeholder').val();
                    data.currentDateTime = $('#wdt-datetime-placeholder').val();
                    data.currentTime = $('#wdt-time-placeholder').val();
                    data.wpdbPlaceholder = $('#wdt-wpdb-placeholder').val();
                    data.wdtNonce = $('#wdtNonceFrontendEdit_' + tableDescription.tableWpId).val();
                    data.showAllRows = $('#wdt-show-all-rows').val();
                };
            }

            /**
             * Show after load if configured
             */
            if (tableDescription.hideBeforeLoad) {
                dataTableOptions.fnInitComplete = function () {
                    $(tableDescription.selector).animateFadeIn();

                    if (tableDescription.dataTableParams.fixedColumns) {
                        addFixedColumnsHideBeforeLoad(tableDescription);
                    }
                }
            }
            /**
             * Add outline class to selected column col for initial table load
             */
            if ($.inArray(tableDescription.tableSkin, ['raspberry-cream', 'mojito', 'dark-mojito']) !== -1) {
                dataTableOptions.fnInitComplete = function () {
                    //  Find the column that the table is initially sorted by
                    let columnPos = tableDescription.dataTableParams.order[0][0];
                    let columnTitle = tableDescription.dataTableParams.columnDefs[columnPos].className.substring(
                        tableDescription.dataTableParams.columnDefs[columnPos].className.indexOf("column-") + 7,
                    );

                    let tableId = tableDescription.tableId;
                    addOutlineBorder(tableId, columnTitle);

                    if (tableDescription.tableSkin === 'mojito' || tableDescription.tableSkin === 'dark-mojito') {
                        cubeLoaderMojito(tableId);
                        if (tableDescription.showRowsPerPage)
                            hideLabelShowXEntries(tableId);
                    }

                    if (tableDescription.hideBeforeLoad) {
                        $(tableDescription.selector).animateFadeIn();
                        addFixedColumnsHideBeforeLoad(tableDescription);
                    }
                }
            }

            /**
             * Init the DataTable itself
             */
            wpDataTables[tableDescription.tableId] = $(tableDescription.selector).dataTable(dataTableOptions);

            /**
             * Remove pagination when "Default rows per page" is set to "All"
             */
            if (wpDataTables[tableDescription.tableId].fnSettings()._iDisplayLength >= wpDataTables[tableDescription.tableId].fnSettings().fnRecordsTotal() || dataTableOptions.iDisplayLength === -1) {
                $('#' + tableDescription.tableId + '_paginate').hide();
            }

            $(tableDescription.selector + '_wrapper').addClass('wpDataTables wpDataTablesWrapper wpDataTableID-' + tableDescription.tableWpId)

            /**
             * Set pagination alignment classes
             */
            if (tableDescription.paginationAlign) {
                switch (tableDescription.paginationAlign) {
                    case "right":
                        $(tableDescription.selector + '_wrapper').addClass('wpdt-pagination-right');
                        $(tableDescription.selector + '_wrapper').removeClass('wpdt-pagination-left');
                        $(tableDescription.selector + '_wrapper').removeClass('wpdt-pagination-center');
                        break;
                    case "left":
                        $(tableDescription.selector + '_wrapper').addClass('wpdt-pagination-left');
                        $(tableDescription.selector + '_wrapper').removeClass('wpdt-pagination-right');
                        $(tableDescription.selector + '_wrapper').removeClass('wpdt-pagination-center');
                        break;
                    case "center":
                        $(tableDescription.selector + '_wrapper').addClass('wpdt-pagination-center');
                        $(tableDescription.selector + '_wrapper').removeClass('wpdt-pagination-left');
                        $(tableDescription.selector + '_wrapper').removeClass('wpdt-pagination-right');
                        break;
                    default:
                        $(tableDescription.selector + '_wrapper').addClass('wpdt-pagination-right');
                        $(tableDescription.selector + '_wrapper').removeClass('wpdt-pagination-left');
                        $(tableDescription.selector + '_wrapper').removeClass('wpdt-pagination-center');
                        break;
                }

            }
            if(tableDescription.pagination && tableDescription.table_wcag){
                $(tableDescription.selector + '_paginate').addClass('wcag_paginate');
            }

            if (tableDescription.tableSkin) {
                $(tableDescription.selector + '_wrapper .dt-buttons .DTTT_button_export').on('click', function () {
                    $('.dt-button-collection').addClass('wdt-skin-' + tableDescription.tableSkin)
                });
                $(tableDescription.selector + '_wrapper .dt-buttons .dt-button.DTTT_button_colvis').attr('aria-label', wpdatatables_frontend_strings.columnVisibilityWCAG).attr('role', 'button');
                $(tableDescription.selector + '_wrapper .dt-buttons .dt-button.DTTT_button_spacer').attr('aria-label', wpdatatables_frontend_strings.spacerWCAG).attr('role', 'button');
                $(tableDescription.selector + '_wrapper .dt-buttons .dt-button.DTTT_button_print').attr('aria-label', wpdatatables_frontend_strings.printTableWCAG).attr('role', 'button');
                $(tableDescription.selector + '_wrapper .dt-buttons .dt-button.DTTT_button_export').attr('aria-label', wpdatatables_frontend_strings.exportTableWCAG).attr('role', 'button');
                $(tableDescription.selector + '_wrapper .dt-buttons .dt-button.DTTT_button_new').attr('aria-label', wpdatatables_frontend_strings.newEntryWCAG).attr('role', 'button');
                $(tableDescription.selector + '_wrapper .dt-buttons .dt-button.DTTT_button_delete').attr('aria-label', wpdatatables_frontend_strings.deleteRowWCAG).attr('role', 'button');
                $(tableDescription.selector + '_wrapper .dt-buttons .dt-button.DTTT_button_edit').attr('aria-label', wpdatatables_frontend_strings.editRowWCAG).attr('role', 'button');
                $(tableDescription.selector + '_wrapper .dt-buttons .dt-button.DTTT_button_duplicate').attr('aria-label', wpdatatables_frontend_strings.duplicateRowWCAG).attr('role', 'button');
                $(tableDescription.selector + '_wrapper .dt-buttons .dt-button.DTTT_button_clear_filters').attr('aria-label', wpdatatables_frontend_strings.clearFiltersWCAG).attr('role', 'button');
            }
            /**
             * Show "Show X entries" dropdown
             */
            if (tableDescription.showRowsPerPage) {
                if (!(jQuery(tableDescription.selector + '_wrapper .dataTables_length .length_menu.bootstrap-select').length))
                    jQuery(tableDescription.selector + '_wrapper .dataTables_length .length_menu.wdt-selectpicker').selectpicker();
            }

            /**
             * Remove pagination when "All" is selected from length menu or
             * if value length menu is greater than total records
             */
            wpDataTables[tableDescription.tableId].fnSettings().aoDrawCallback.push({
                sName: 'removePaginate',
                fn: function (oSettings) {
                    var api = oSettings.oInstance.api();

                    if (typeof (api.page.info()) != 'undefined') {
                        if (api.page.len() >= api.page.info().recordsDisplay || api.data().page.len() == -1) {
                            $('#' + tableDescription.tableId + '_paginate').hide();
                        } else {
                            $('#' + tableDescription.tableId + '_paginate').show();
                        }
                    }
                    if(tableDescription.table_wcag){
                        this.fnSettings().oLanguage.oPaginate.sFirst = wpdatatables_frontend_strings.firstPageWCAG;
                        this.fnSettings().oLanguage.oPaginate.sLast = wpdatatables_frontend_strings.lastPageWCAG;
                        this.fnSettings().oLanguage.oPaginate.sNext = wpdatatables_frontend_strings.nextPageWCAG;
                        this.fnSettings().oLanguage.oPaginate.sPrevious = wpdatatables_frontend_strings.previousPageWCAG;
                        $(document).ready(function () {
                            $('#' + tableDescription.tableId + '_paginate').find('span .paginate_button').each(function (index) {
                                $(this).attr('aria-label', wpdatatables_frontend_strings.pageWCAG + this.text);
                            });
                        });
                    }
                }

            });

            /**
             * Add fixed columns when a draw occurs
             */
            if (tableDescription.dataTableParams.fixedHeader || tableDescription.dataTableParams.fixedColumns || (tableDescription.hideBeforeLoad && tableDescription.dataTableParams.fixedColumns)) {
                wpDataTables[tableDescription.tableId].fnSettings().aoDrawCallback.push({
                    sName: 'addColumnFiltersFixedColumns',
                    fn: function (oSettings) {
                        if (tableDescription.dataTableParams.fixedColumns && tableDescription.filterInForm === false) {
                            if (oSettings.oInstance.api().settings()[0]._fixedColumns != undefined) {
                                oSettings.oInstance.api().fixedColumns().left(tableDescription.dataTableParams.fixedColumns.left);
                                oSettings.oInstance.api().fixedColumns().right(tableDescription.dataTableParams.fixedColumns.right);
                            }
                        }
                        if (tableDescription.dataTableParams.fixedHeader) {
                            if (jQuery(tableDescription.selector).find('div.dtfh-floatingparent').length != 0) {
                                if (oSettings.oInstance.api().settings()[0]._fixedHeader != undefined) {
                                    oSettings.oInstance.api().fixedHeader.adjust();
                                    //oSettings.oInstance.api().settings()[0]._fixedHeader.s.dt.fixedHeader.adjust();
                                }
                            }
                        }
                    }
                });
            }
            /**
             * Add outline class to selected column col when a draw occurs
             */
            wpDataTables[tableDescription.tableId].fnSettings().aoDrawCallback.push({
                sName: 'addOutlineClass',
                fn: function (oSettings) {
                    if ($.inArray(tableDescription.tableSkin, ['raspberry-cream', 'mojito', 'dark-mojito']) !== -1) {
                        //Find the column that the table is sorted by
                        let columnPos = oSettings.aaSorting[0][0];
                        let columnTitle = oSettings.aoColumns[columnPos].className.substring(
                            oSettings.aoColumns[columnPos].className.indexOf("column-") + 7,
                        );

                        let tableId = oSettings.sTableId;
                        addOutlineBorder(tableId, columnTitle);
                    }
                }
            });

            /**
             * Helper function for adding a border around the selected column
             */
            function addOutlineBorder(tableId, columnTitle) {
                if (columnTitle.indexOf(' ') !== -1) {
                    columnTitle = columnTitle.substring(0, columnTitle.indexOf(' '));
                }
                let colgroupList = document.getElementById("colgrup-" + tableId);
                colgroupList.replaceChildren();
                let visibleColumns = document.getElementById(tableId).tHead.getElementsByClassName('wdtheader');
                let fixedTable = document.getElementById(tableId);

                for (column of visibleColumns) {
                    let newCol = document.createElement('col');
                    let colTitle = column.className.substring(
                        column.className.indexOf("column-") + 7,
                    );
                    colTitle = colTitle.substring(0, colTitle.indexOf(' '));
                    newCol.setAttribute('id', tableId + '-column-' + colTitle + '-col');
                    colgroupList.append(newCol);
                }
                //Outline for fixed columns and fixed header
                $('#' + tableId + '-column-' + columnTitle + '-col').addClass('outlined');
                let indexColOutlined = $('#' + tableId + '-column-' + columnTitle + '-col').index() + 1;

                jQuery(fixedTable).find('th:not(.wdtheader)').removeClass('outlined');
                jQuery(fixedTable).find('tfoot tr td').removeClass('outlined');

                if (tableDescription.dataTableParams.fixedHeader.header || tableDescription.dataTableParams.fixedColumns) {
                    jQuery(fixedTable).find('th:not(.wdtheader):nth-child(' + indexColOutlined + ')').addClass('outlined');
                    $('tfoot tr td.column-' + columnTitle).addClass('outlined');
                }
            }

            function addFixedColumnsHideBeforeLoad(tableDescription) {
                jQuery(tableDescription.selector).DataTable().draw()
            }

            /**
             * Helper function for hiding label show entries' for mojito/dark mojito skin
             */

            function hideLabelShowXEntries(tableId) {
                let showEntriesText = $('#' + tableId + '_length')[0].firstChild;
                showEntriesText.removeChild(showEntriesText.firstChild);
                showEntriesText.removeChild(showEntriesText.lastChild);

            }

            function cubeLoaderMojito(tableId) {
                let cubesAnimation = '<div class="wdt_cubes">';
                for (let i = 1; i <= 9; i++) {
                    cubesAnimation += '<div class="wdt_cube wdt_cube-' + i + '"></div>';
                }
                cubesAnimation += ' </div>';
                $('#' + tableId).append(cubesAnimation)
            }

            /**
             * Enable auto-refresh if defined
             */
            if (tableDescription.serverSide) {
                if (parseInt(tableDescription.autoRefreshInterval) > 0) {
                    autoRefresh = setInterval(function () {
                            wpDataTables[tableDescription.tableId].fnDraw(false)
                        },
                        parseInt(tableDescription.autoRefreshInterval) * 1000
                    );
                } else {
                    if (typeof autoRefresh !== "undefined") {
                        clearInterval(autoRefresh);
                    }
                }
            }
            //[<--/ Full version -->]//

            /**
             * Add the draw callback
             * @param callback
             */
            wpDataTables[tableDescription.tableId].addOnDrawCallback = function (callback) {
                if (typeof callback !== 'function') {
                    return;
                }

                var index = wpDataTables[tableDescription.tableId].fnSettings().aoDrawCallback.length + 1;

                wpDataTables[tableDescription.tableId].fnSettings().aoDrawCallback.push({
                    sName: 'user_callback_' + index,
                    fn: callback
                });

            };

            //[<-- Full version -->]//
            /**
             * SUM, AVG, MIN, MAX functions callback
             */
            if (tableDescription.hasSumColumns || tableDescription.hasAvgColumns || tableDescription.hasMinColumns || tableDescription.hasMaxColumns
                || $('.wdt-column-sum').length || $('.wdt-column-avg').length || $('.wdt-column-min').length || $('.wdt-column-max').length) {

                var sumLabel = tableDescription.sumFunctionsLabel ? tableDescription.sumFunctionsLabel : '&#8721; = ';
                var avgLabel = tableDescription.avgFunctionsLabel ? tableDescription.avgFunctionsLabel : 'Avg = ';
                var minLabel = tableDescription.minFunctionsLabel ? tableDescription.minFunctionsLabel : 'Min = ';
                var maxLabel = tableDescription.maxFunctionsLabel ? tableDescription.maxFunctionsLabel : 'Max = ';

                if (tableDescription.serverSide) {
                    // Case with server-side table
                    wpDataTables[tableDescription.tableId].fnSettings().aoDrawCallback.push({
                        sName: 'updateFooterFunctions',
                        fn: function (oSettings) {
                            var api = oSettings.oInstance.api();

                            for (var columnName in api.ajax.json().sumFooterColumns) {
                                if (tableDescription.hasSumColumns) {
                                    $('#' + tableDescription.tableId + ' tfoot tr.wdt-sum-row td.wdt-sum-cell[data-column_header="' + columnName + '"]').html(sumLabel + ' ' + api.ajax.json().sumColumnsValues[columnName]);
                                }
                            }
                            for (columnName in api.ajax.json().avgFooterColumns) {
                                if (tableDescription.hasAvgColumns) {
                                    $('#' + tableDescription.tableId + ' tfoot tr.wdt-avg-row td.wdt-avg-cell[data-column_header="' + columnName + '"]').html(avgLabel + ' ' + api.ajax.json().avgColumnsValues[columnName]);
                                }
                            }
                            for (columnName in api.ajax.json().minFooterColumns) {
                                if (tableDescription.hasMinColumns) {
                                    $('#' + tableDescription.tableId + ' tfoot tr.wdt-min-row td.wdt-min-cell[data-column_header="' + columnName + '"]').html(minLabel + ' ' + api.ajax.json().minColumnsValues[columnName]);
                                }
                            }
                            for (columnName in api.ajax.json().maxFooterColumns) {
                                if (tableDescription.hasMaxColumns) {
                                    $('#' + tableDescription.tableId + ' tfoot tr.wdt-max-row td.wdt-max-cell[data-column_header="' + columnName + '"]').html(maxLabel + ' ' + api.ajax.json().maxColumnsValues[columnName]);
                                }
                            }

                            if ($('.wdt-column-sum').length) {
                                $('.wdt-column-sum[data-table-id="' + tableDescription.tableWpId + '"]').each(function () {
                                    $(this).find('.wdt-column-sum-value').text(api.ajax.json().sumColumnsValues[$(this).data('column-orig-header')]);
                                })
                            }

                            if ($('.wdt-column-avg').length) {
                                $('.wdt-column-avg[data-table-id="' + tableDescription.tableWpId + '"]').each(function () {
                                    $(this).find('.wdt-column-avg-value').text(api.ajax.json().avgColumnsValues[$(this).data('column-orig-header')]);
                                })
                            }

                            if ($('.wdt-column-min').length) {
                                $('.wdt-column-min[data-table-id="' + tableDescription.tableWpId + '"]').each(function () {
                                    $(this).find('.wdt-column-min-value').text(api.ajax.json().minColumnsValues[$(this).data('column-orig-header')]);
                                })
                            }

                            if ($('.wdt-column-max').length) {
                                $('.wdt-column-max[data-table-id="' + tableDescription.tableWpId + '"]').each(function () {
                                    $(this).find('.wdt-column-max-value').text(api.ajax.json().maxColumnsValues[$(this).data('column-orig-header')]);
                                })
                            }

                        }
                    });
                } else {
                    // Case with client-side table
                    wpDataTables[tableDescription.tableId].fnSettings().aoDrawCallback.push({
                        sName: 'updateFooterFunctions',
                        fn: function (oSettings) {
                            var api = oSettings.oInstance.api();

                            var thousandsSeparator = tableDescription.number_format == 1 ? '.' : ',';
                            var decimalSeparator = tableDescription.number_format == 1 ? ',' : '.';

                            for (var i in tableDescription.sumAvgColumns) {

                                var columnData = api.column(tableDescription.sumAvgColumns[i] + ':name', {search: 'applied'}).data().filter(function (el) {
                                    return el !== '';
                                });
                                var columnType = oSettings.aoColumns[api.column(tableDescription.sumAvgColumns[i] + ':name').index()].wdtType;

                                var sum = wdtCalculateColumnSum(columnData, thousandsSeparator);

                                var nonNullLength = columnData.length;

                                var sumStr = wdtFormatNumberByColumnType(parseFloat(sum), columnType, tableDescription.columnsDecimalPlaces[tableDescription.sumAvgColumns[i]],
                                    tableDescription.decimalPlaces, decimalSeparator, thousandsSeparator);

                                if (_.contains(tableDescription.sumColumns, tableDescription.sumAvgColumns[i])) {
                                    $('#' + tableDescription.tableId + ' tfoot tr.wdt-sum-row td.wdt-sum-cell[data-column_header="' + tableDescription.sumAvgColumns[i] + '"]')
                                        .html(sumLabel + ' ' + sumStr);
                                }

                                if (_.contains(tableDescription.avgColumns, tableDescription.sumAvgColumns[i])) {
                                    var avg = sum / nonNullLength;

                                    var avgStr = wdtFormatNumberByColumnType(avg, 'float', tableDescription.columnsDecimalPlaces[tableDescription.sumAvgColumns[i]],
                                        tableDescription.decimalPlaces, decimalSeparator, thousandsSeparator);

                                    $('#' + tableDescription.tableId + ' tfoot tr.wdt-avg-row td.wdt-avg-cell[data-column_header="' + tableDescription.sumAvgColumns[i] + '"]')
                                        .html(avgLabel + ' ' + avgStr);
                                }

                            }
                            for (i in tableDescription.minColumns) {

                                columnData = api.column(tableDescription.minColumns[i] + ':name', {search: 'applied'}).data().filter(function (el) {
                                    return el !== '';
                                });
                                columnType = oSettings.aoColumns[api.column(tableDescription.minColumns[i] + ':name').index()].wdtType;

                                var min = wdtCalculateColumnMin(columnData, thousandsSeparator);

                                var minStr = wdtFormatNumberByColumnType(parseFloat(min), columnType, tableDescription.columnsDecimalPlaces[tableDescription.minColumns[i]],
                                    tableDescription.decimalPlaces, decimalSeparator, thousandsSeparator);

                                $('#' + tableDescription.tableId + ' tfoot tr.wdt-min-row td.wdt-min-cell[data-column_header="' + tableDescription.minColumns[i] + '"]')
                                    .html(minLabel + ' ' + minStr);
                            }
                            for (i in tableDescription.maxColumns) {

                                columnData = api.column(tableDescription.maxColumns[i] + ':name', {search: 'applied'}).data().filter(function (el) {
                                    return el !== '';
                                });
                                columnType = oSettings.aoColumns[api.column(tableDescription.maxColumns[i] + ':name').index()].wdtType;

                                var max = wdtCalculateColumnMax(columnData, thousandsSeparator);

                                var maxStr = wdtFormatNumberByColumnType(parseFloat(max), columnType, tableDescription.columnsDecimalPlaces[tableDescription.maxColumns[i]],
                                    tableDescription.decimalPlaces, decimalSeparator, thousandsSeparator);

                                $('#' + tableDescription.tableId + ' tfoot tr.wdt-max-row td.wdt-max-cell[data-column_header="' + tableDescription.maxColumns[i] + '"]')
                                    .html(maxLabel + ' ' + maxStr);
                            }

                            // Update values from wpdatatables_{func} shortcode
                            if ($('.wdt-column-sum').length) {
                                $('.wdt-column-sum[data-table-id="' + tableDescription.tableWpId + '"]').each(function () {
                                    var columnData = api.column($(this).data('column-orig-header') + ':name', {search: 'applied'}).data().filter(function (el) {
                                        return el !== '';
                                    });
                                    var columnType = oSettings.aoColumns[api.column($(this).data('column-orig-header') + ':name').index()].wdtType;

                                    var sum = wdtCalculateColumnSum(columnData, thousandsSeparator);

                                    var sumStr = wdtFormatNumberByColumnType(parseFloat(sum), columnType, tableDescription.columnsDecimalPlaces[$(this).data('column-orig-header')],
                                        tableDescription.decimalPlaces, decimalSeparator, thousandsSeparator);

                                    $(this).find('.wdt-column-sum-value').text(sumStr);
                                })
                            }

                            if ($('.wdt-column-avg').length) {
                                $('.wdt-column-avg[data-table-id="' + tableDescription.tableWpId + '"]').each(function () {
                                    var columnData = api.column($(this).data('column-orig-header') + ':name', {search: 'applied'}).data().filter(function (el) {
                                        return el !== '';
                                    });

                                    var avg = wdtCalculateColumnSum(columnData, thousandsSeparator) / api.page.info().recordsDisplay;

                                    var avgStr = wdtFormatNumberByColumnType(parseFloat(avg), 'float', tableDescription.columnsDecimalPlaces[$(this).data('column-orig-header')],
                                        tableDescription.decimalPlaces, decimalSeparator, thousandsSeparator);

                                    $(this).find('.wdt-column-avg-value').text(avgStr);
                                })
                            }

                            if ($('.wdt-column-min').length) {
                                $('.wdt-column-min[data-table-id="' + tableDescription.tableWpId + '"]').each(function () {
                                    var columnData = api.column($(this).data('column-orig-header') + ':name', {search: 'applied'}).data().filter(function (el) {
                                        return el !== '';
                                    });
                                    var columnType = oSettings.aoColumns[api.column($(this).data('column-orig-header') + ':name').index()].wdtType;

                                    var min = wdtCalculateColumnMin(columnData, thousandsSeparator);

                                    var minStr = wdtFormatNumberByColumnType(parseFloat(min), columnType, tableDescription.columnsDecimalPlaces[$(this).data('column-orig-header')],
                                        tableDescription.decimalPlaces, decimalSeparator, thousandsSeparator);

                                    $(this).find('.wdt-column-min-value').text(minStr);
                                })
                            }

                            if ($('.wdt-column-max').length) {
                                $('.wdt-column-max[data-table-id="' + tableDescription.tableWpId + '"]').each(function () {
                                    var columnData = api.column($(this).data('column-orig-header') + ':name', {search: 'applied'}).data().filter(function (el) {
                                        return el !== '';
                                    });
                                    var columnType = oSettings.aoColumns[api.column($(this).data('column-orig-header') + ':name').index()].wdtType;

                                    var max = wdtCalculateColumnMax(columnData, thousandsSeparator);

                                    var maxStr = wdtFormatNumberByColumnType(parseFloat(max), columnType, tableDescription.columnsDecimalPlaces[$(this).data('column-orig-header')],
                                        tableDescription.decimalPlaces, decimalSeparator, thousandsSeparator);

                                    $(this).find('.wdt-column-max-value').text(maxStr);
                                })
                            }
                        }
                    });

                }
            }

            /**
             * Conditional formatting
             */
            if (tableDescription.conditional_formatting_columns) {
                wpDataTables[tableDescription.tableId].fnSettings().aoDrawCallback.push({
                    sName: 'updateConditionalFormatting',
                    fn: function (oSettings) {
                        for (var i = 0; i < tableDescription.conditional_formatting_columns.length; i++) {
                            var params = {};
                            var column = oSettings.oInstance.api().column(tableDescription.conditional_formatting_columns[i] + ':name', {search: 'applied'});
                            var conditionalFormattingRules = oSettings.aoColumns[column.index()].conditionalFormattingRules;
                            params.columnType = oSettings.aoColumns[column.index()].wdtType;
                            params.thousandsSeparator = tableDescription.number_format == 1 ? '.' : ',';
                            params.decimalSeparator = tableDescription.number_format == 1 ? ',' : '.';
                            params.dateFormat = tableDescription.datepickFormat;
                            params.momentDateFormat = params.dateFormat.replace('dd', 'DD').replace('M', 'MMM').replace('mm', 'MM').replace('yy', 'YYYY').replace('y', 'YY');
                            params.momentTimeFormat = tableDescription.timeFormat.replace('H', 'H').replace('i', 'mm');
                            for (var j in conditionalFormattingRules) {
                                var nodes = column.nodes();
                                column.nodes().to$().each(function () {
                                    wdtCheckConditionalFormatting(conditionalFormattingRules[j], params, $(this));
                                });
                            }
                        }
                    }
                });
                if (!tableDescription.serverSide) {
                    wpDataTables[tableDescription.tableId].fnDraw();
                }
            }
            /**
             * Transform Value of column
             */
            if (tableDescription.transform_value_columns) {
                wpDataTables[tableDescription.tableId].fnSettings().aoDrawCallback.push({
                    sName: 'updateTransformColumnValue',
                    fn: function (oSettings) {
                        var columnNamesArray = [];
                        for(var k =0; k < oSettings.aoColumns.length; k++){
                            var columnName = oSettings.aoColumns[k].name;
                            columnNamesArray.push(columnName);
                        }
                        for (var i = 0; i < tableDescription.transform_value_columns.length; i++) {
                            var params = {};
                            var column = oSettings.oInstance.api().column(tableDescription.transform_value_columns[i] + ':name', {search: 'applied'});
                            var transformValueRules = {};
                            transformValueRules[0] = oSettings.aoColumns[column.index()].transformValueRules;
                            for(var k =0; k < oSettings.aoColumns.length; k++){
                                var col = oSettings.oInstance.api().column(oSettings.aoColumns[k].name + ':name', {search: 'applied'});
                                var nodes = column.nodes();
                                column.nodes().to$().each(function () {
                                        if(transformValueRules[0].includes('{' + oSettings.aoColumns[k].name + '.value}')) {
                                            for (var m = 0; m < col.data().length; m++) {
                                                if (m != 0) {
                                                    if(transformValueRules[m + 1] != null){
                                                        wdtTransformValue(transformValueRules[m + 1].replaceAll(col.data()[m - 1], col.data()[m] === null ? '' : col.data()[m]), params, $(this), m, oSettings.sTableId);
                                                        transformValueRules[m + 1] = transformValueRules[m + 1].replaceAll('{' + oSettings.aoColumns[k].name + '.value}', col.data()[m] === null ? '' : col.data()[m]);
                                                    }else {
                                                        wdtTransformValue(transformValueRules[m].replaceAll(col.data()[m - 1], col.data()[m] === null ? '' : col.data()[m]), params, $(this), m, oSettings.sTableId);
                                                        transformValueRules[m + 1] = transformValueRules[m].replaceAll(col.data()[m - 1], col.data()[m] === null ? '' : col.data()[m]);
                                                    }
                                                } else {
                                                    if (transformValueRules[m + 1] != null) {
                                                        wdtTransformValue(transformValueRules[m+1].replaceAll('{' + oSettings.aoColumns[k].name + '.value}', col.data()[m] === null ? '' : col.data()[m]), params, $(this), m, oSettings.sTableId);
                                                        transformValueRules[m + 1] = transformValueRules[m+1].replaceAll('{' + oSettings.aoColumns[k].name + '.value}', col.data()[m] === null ? '' : col.data()[m]);
                                                    } else {
                                                        wdtTransformValue(transformValueRules[m].replaceAll('{' + oSettings.aoColumns[k].name + '.value}', col.data()[m] === null ? '' : col.data()[m]), params, $(this), m, oSettings.sTableId);
                                                        transformValueRules[m + 1] = transformValueRules[m].replaceAll('{' + oSettings.aoColumns[k].name + '.value}', col.data()[m] === null ? '' : col.data()[m]);
                                                    }
                                                }
                                            }
                                        }
                                });
                            }
                        }
                    }
                });
                if (!tableDescription.serverSide) {
                    wpDataTables[tableDescription.tableId].fnDraw();
                }
            }
            /**
             * Init the callback for checking if the selected row is first/last in the dataset
             */
            wpDataTables[tableDescription.tableId].checkSelectedLimits = function () {
                if (wpDataTablesUpdatingFlags[tableDescription.tableId]) {
                    return;
                }
                var sel_row_index = $(tableDescription.selector + ' > tbody > tr.selected').index();
                if (sel_row_index + wpDataTables[tableDescription.tableId].fnSettings()._iDisplayStart == wpDataTables[tableDescription.tableId].fnSettings()._iRecordsDisplay - 1) {
                    $(tableDescription.selector + '_next_edit_dialog').prop('disabled', true)
                } else {
                    $(tableDescription.selector + '_next_edit_dialog').prop('disabled', false)
                }
                if ((sel_row_index == 0 && wpDataTables[tableDescription.tableId].fnSettings()._iDisplayStart == 0) || wpDataTables[tableDescription.tableId].fnSettings()._iRecordsDisplay == 0) {
                    $(tableDescription.selector + '_prev_edit_dialog').prop('disabled', true)
                } else {
                    $(tableDescription.selector + '_prev_edit_dialog').prop('disabled', false)
                }
            };
            //[<--/ Full version -->]//

            /**
             * Init row grouping if enabled
             */
            if (tableDescription.groupingEnabled) {
                wpDataTables[tableDescription.tableId].rowGrouping({iGroupingColumnIndex: tableDescription.groupingColumnIndex});
            }

            //[<-- Full version -->]//
            /**
             * Init the advanced filtering if enabled
             */
            if (tableDescription.advancedFilterEnabled) {
                $('#' + tableDescription.tableId).dataTable().columnFilter(tableDescription.advancedFilterOptions);
                for (var i in wpDataTablesHooks.onRenderFilter) {
                    if (!isNaN(i))
                        wpDataTablesHooks.onRenderFilter[i](tableDescription);
                }
            }

            if (tableDescription.editable) {

                /**
                 * Previous button in edit dialog
                 */
                $(document).on('click', tableDescription.selector + '_prev_edit_dialog', function (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    var sel_row_index = $(tableDescription.selector + ' > tbody > tr.selected').index();
                    if (sel_row_index > 0) {
                        $(tableDescription.selector + ' > tbody > tr.selected').removeClass('selected');
                        $(tableDescription.selector + ' > tbody > tr:eq(' + (sel_row_index - 1) + ')').addClass('selected', 300);
                        wpDataTablesSelRows[tableDescription.tableId] = wpDataTables[tableDescription.tableId].fnGetPosition($(tableDescription.selector + ' > tbody > tr.selected').get(0));
                        var data = wpDataTables[tableDescription.tableId].fnGetData(wpDataTablesSelRows[tableDescription.tableId]);
                        wpDataTablesFunctions[tableDescription.tableId].applyData(data);
                    } else {
                        var cur_page = Math.ceil(wpDataTables[tableDescription.tableId].fnSettings()._iDisplayStart / wpDataTables[tableDescription.tableId].fnSettings()._iDisplayLength) + 1;
                        if (cur_page == 1)
                            return;
                        wpDataTablesSelRows[tableDescription.tableId] = -2;
                        wpDataTablesUpdatingFlags[tableDescription.tableId] = true;
                        wpDataTables[tableDescription.tableId].fnPageChange('previous');
                    }
                    wpDataTables[tableDescription.tableId].checkSelectedLimits();
                });

                /**
                 * Next button in edit dialog
                 */
                $(document).on('click', tableDescription.selector + '_next_edit_dialog', function (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    if (wpDataTables[tableDescription.tableId].fnSettings()._iDisplayLength == -1) {
                        wpDataTables[tableDescription.tableId].fnSettings()._iDisplayLength = wpDataTables[tableDescription.tableId].fnSettings()._iRecordsTotal;
                    }
                    var sel_row_index = $(tableDescription.selector + ' > tbody > tr.selected').index();
                    if (sel_row_index < wpDataTables[tableDescription.tableId].fnSettings()._iDisplayLength - 1) {
                        $(tableDescription.selector + ' > tbody > tr.selected').removeClass('selected');
                        $(tableDescription.selector + ' > tbody > tr:eq(' + (sel_row_index + 1) + ')').addClass('selected', 300);
                        wpDataTablesSelRows[tableDescription.tableId] = wpDataTables[tableDescription.tableId].fnGetPosition($(tableDescription.selector + ' > tbody > tr.selected').get(0));
                        var data = wpDataTables[tableDescription.tableId].fnGetData(wpDataTablesSelRows[tableDescription.tableId]);
                        wpDataTablesFunctions[tableDescription.tableId].applyData(data);
                    } else {
                        var cur_page = Math.ceil(wpDataTables[tableDescription.tableId].fnSettings()._iDisplayStart / wpDataTables[tableDescription.tableId].fnSettings()._iDisplayLength) + 1;
                        var total_pages = Math.ceil(wpDataTables[tableDescription.tableId].fnSettings()._iRecordsTotal / wpDataTables[tableDescription.tableId].fnSettings()._iDisplayLength);
                        if (cur_page == total_pages)
                            return;
                        wpDataTablesSelRows[tableDescription.tableId] = -3;
                        wpDataTablesUpdatingFlags[tableDescription.tableId] = true;
                        wpDataTables[tableDescription.tableId].fnPageChange('next');
                        wpDataTables[tableDescription.tableId].fnDraw(false);
                    }
                    wpDataTables[tableDescription.tableId].checkSelectedLimits();
                });

                /**
                 * Apply button in edit dialog
                 */
                $(document).on('click', tableDescription.selector + '_apply_edit_dialog', function (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    e.preventDefault();
                    wpDataTablesFunctions[tableDescription.tableId].saveTableData(true, false, false);
                });

                /**
                 * Duplicate button in edit dialog
                 */
                $(document).on('click', tableDescription.selector + '_apply_duplicate_dialog', function (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    e.preventDefault();
                    wpDataTablesFunctions[tableDescription.tableId].saveTableData(true, false, true);
                });

                /**
                 * OK button in edit dialog
                 */
                $(document).on('click', tableDescription.selector + '_ok_edit_dialog', function (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    e.preventDefault();
                    wpDataTablesFunctions[tableDescription.tableId].saveTableData(true, true, false);
                });

                /**
                 * Toggle OK when enter pressed in inputs (but not selectboxes or textareas)
                 */
                $(document).on('keyup', tableDescription.selector + '_edit_dialog input', function (e) {
                    if (e.which == 13) {
                        $(tableDescription.selector + '_ok_edit_dialog').click();
                    }
                });


                /**
                 * Apply maskmoney for Float column types and apply thousands separator and decimal places
                 * based on table description
                 */
                $(tableDescription.selector + '_edit_dialog input.wdt-maskmoney[data-column_type="float"]').each(function (i) {
                    var decimalPlaces = tableDescription.columnsDecimalPlaces[$(this).data('key')] != -1 ?
                        tableDescription.columnsDecimalPlaces[$(this).data('key')] :
                        parseInt(tableDescription.decimalPlaces);
                    $(this).maskMoney({
                        thousands: tableDescription.number_format == 1 ? '.' : ',',
                        decimal: tableDescription.number_format == 1 ? ',' : '.',
                        precision: decimalPlaces,
                        allowNegative: true,
                        allowEmpty: true,
                        allowZero: true
                    })
                });

                /**
                 * Apply maskmoney for Input column types
                 */
                $(tableDescription.selector + '_edit_dialog input.wdt-maskmoney[data-column_type="int"]').each(function (i) {
                    var thousandsSeparator = tableDescription.number_format == 1 ? '.' : ',';
                    if (tableDescription.columnsThousandsSeparator[$(this).data('key')] == 0) {
                        thousandsSeparator = '';
                    }
                    $(this).maskMoney({
                        thousands: thousandsSeparator,
                        precision: 0,
                        allowNegative: true,
                        allowEmpty: true,
                        allowZero: true
                    });
                });

                /**
                 * Apply fileuploaders
                 */
                var fileUploadInit = function (selector) {
                    if ($('.fileupload-' + selector).length) {

                        var attachment = null;
                        // Extend the wp.media object
                        wdtCustomUploader = wp.media({
                            title: wpdatatables_frontend_strings.select_upload_file,
                            button: {
                                text: wpdatatables_frontend_strings.choose_file
                            },
                            multiple: false
                        });


                        $('span.fileupload-' + selector).click(function (e) {
                            e.preventDefault();
                            var $button = $(this);
                            var $relInput = $('#' + $button.data('rel_input'));
                            // Fix for inputs in wp media dialog when bs modal is open
                            $(document).off('focusin.modal');

                            wdtCustomUploader = wp.media({
                                title: wpdatatables_frontend_strings.select_upload_file,
                                button: {
                                    text: wpdatatables_frontend_strings.choose_file
                                },
                                multiple: false,
                                library: {
                                    type: $button.data('column_type') == 'icon' ? 'image' : ''
                                }
                            });
                            if ($button.data('column_type') == 'icon') {
                                wdtCustomUploader.off('select').on('select', function () {
                                    attachment = wdtCustomUploader.state().get('selection').first().toJSON();

                                    var val = attachment.url;

                                    $relInput.parent().parent().parent().find('.fileinput-preview').html('<img src=' + val + '>');
                                    $relInput.parent().parent().parent().removeClass('fileinput-new').addClass('fileinput-exists');
                                    $relInput.parent().parent().removeClass('fileinput-new').addClass('fileinput-exists');

                                    if (attachment.sizes.thumbnail) {
                                        val = attachment.sizes.thumbnail.url + '||' + val;
                                    }

                                    $relInput.val(val);
                                });
                            } else {
                                // For columns that are not image column type, grab the URL and set it as the text field's value
                                wdtCustomUploader.off('select').on('select', function () {
                                    var attachment = wdtCustomUploader.state().get('selection').first().toJSON();
                                    $relInput.val(attachment.url);
                                    $relInput.parent().parent().removeClass('fileinput-new').addClass('fileinput-exists');
                                    $relInput.parent().parent().find('.fileinput-filename').text(attachment.filename);
                                });
                            }
                            // Open the uploader dialog
                            wdtCustomUploader.open();


                        });
                    }
                };

                fileUploadInit(tableDescription.tableId);

                /**
                 * Show edit dialog
                 */
                $('.edit_table[aria-controls="' + tableDescription.tableId + '"]').click(function () {
                    var modal = $('#wdt-frontend-modal');
                    var newSkins = ['dark', 'aqua', 'purple'];

                    if ($(this).hasClass('disabled'))
                        return false;

                    $('.wpDataTablesPopover.editTools').hide();

                    modal.addClass('wdt-skin-' + tableDescription.tableSkin);
                    if(tableDescription.table_wcag){
                        modal.addClass('wpTableWCAG');
                    }
                    modal.find('.modal-title').html(wpdatatables_frontend_strings.edit_entry);
                    modal.find('.modal-body').html('');
                    modal.find('.modal-footer').html('');

                    var row = $(tableDescription.selector + ' tr.selected').get(0);

                    if (tableDescription.responsive == 1 && $(row).hasClass('row-detail')) {
                        row = $(tableDescription.selector + ' tr.selected').prev('.detail-show').get(0);
                    }

                    if (['manual', 'mysql'].indexOf(tableDescription.tableType) === -1) {
                        if (typeof wpDataTablesEditors[tableDescription.tableType]['edit'] == 'function') {
                            if (singleClick === false) {
                                singleClick = true;
                                wpDataTablesEditors[tableDescription.tableType]['edit'](tableDescription, false);
                            }
                        }
                    } else {
                        var data = wpDataTables[tableDescription.tableId].fnGetData(row);
                        wpDataTablesFunctions[tableDescription.tableId].applyData(data);
                        wpDataTables[tableDescription.tableId].checkSelectedLimits();
                        modal.find('.modal-body').append($(tableDescription.selector + '_edit_dialog').show());
                        modal.find('.modal-footer').append($(tableDescription.selector + '_edit_dialog_buttons').show());

                        if (newSkins.includes(tableDescription.tableSkin)) {
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

                        $('#wdt-frontend-modal .editDialogInput').each(function (index) {
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
                        modal.modal('show');
                        $('.wdt-apply-edit-button').removeClass('hidden');
                        $('.wdt-apply-duplicate-button').addClass('hidden');
                    }


                    // Show 'No inputs selected' alert
                    if (modal.find('.wdt-edit-dialog-fields-block').find('.form-group').length == 0)
                        $('#wdt-frontend-modal div.wdt-no-editor-inputs-selected-alert').show();

                });

                /**
                 * Init inline editing
                 */
                if (tableDescription.inlineEditing) {
                    new inlineEditClass(tableDescription, dataTableOptions, $);
                }

                /**
                 * Add new entry dialog
                 */
                $('.new_table_entry[aria-controls="' + tableDescription.tableId + '"]').click(function () {
                    var modal = $('#wdt-frontend-modal');
                    var newSkins = ['dark', 'aqua', 'purple'];

                    $('.wpDataTablesPopover.editTools').hide();

                    modal.addClass('wdt-skin-' + tableDescription.tableSkin);
                    if(tableDescription.table_wcag){
                        modal.addClass('wpTableWCAG');
                    }
                    modal.find('.modal-title').html(wpdatatables_frontend_strings.add_new_entry);
                    modal.find('.modal-body').html('');
                    modal.find('.modal-footer').html('');


                    if (['manual', 'mysql'].indexOf(tableDescription.tableType) === -1) {
                        if (typeof wpDataTablesEditors[tableDescription.tableType]['new'] == 'function') {
                            if (singleClick === false) {
                                singleClick = true;
                                wpDataTablesEditors[tableDescription.tableType]['new'](tableDescription);
                            }
                        }
                    } else {
                        modal.find('.modal-body').append($(tableDescription.selector + '_edit_dialog').show());
                        modal.find('.modal-footer').append($(tableDescription.selector + '_edit_dialog_buttons').show());

                        if (newSkins.includes(tableDescription.tableSkin)) {
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
                        // Reset values in edit modal
                        $('#wdt-frontend-modal .editDialogInput').val('').css('border', '');
                        $('#wdt-frontend-modal tr.idRow .editDialogInput').val('0');
                        $('#wdt-frontend-modal .fileinput').removeClass('fileinput-exists').addClass('fileinput-new');
                        $('#wdt-frontend-modal .fileinput').find('div.fileinput-exists').removeClass('fileinput-exists').addClass('fileinput-new');
                        $('#wdt-frontend-modal .fileinput').find('.fileinput-filename').text('');
                        $('#wdt-frontend-modal .fileinput').find('.fileinput-preview').html('');

                        $('#wdt-frontend-modal .editDialogInput').each(function (index) {
                            if ($(this).data('input_type') == 'mce-editor') {
                                if (tinymce.activeEditor)
                                    tinymce.activeEditor.setContent('');
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

                        // Reset selectpickers values
                        $('#wdt-frontend-modal .selectpicker').selectpicker('deselectAll').selectpicker('refresh');

                        wpDataTablesFunctions[tableDescription.tableId].setPredefinedEditValues();

                        // Show 'No editor inputs selected' alert
                        if (modal.find('.wdt-edit-dialog-fields-block').find('.form-group').length == 0) {
                            $('#wdt-frontend-modal div.wdt-no-editor-inputs-selected-alert').show();
                        }

                        modal.modal('show');
                        $('.wdt-apply-edit-button').removeClass('hidden');
                        $('.wdt-apply-duplicate-button').addClass('hidden');

                    }


                });

                /**
                 * Duplicate entry
                 */
                $('.duplicate_table_entry[aria-controls="' + tableDescription.tableId + '"]').click(function () {
                    var modal = $('#wdt-frontend-modal');
                    var newSkins = ['dark', 'aqua', 'purple'];

                    var row = $(tableDescription.selector + ' tr.selected').get(0);

                    if (tableDescription.responsive == 1 && $(row).hasClass('row-detail')) {
                        row = $(tableDescription.selector + ' tr.selected').prev('.detail-show').get(0);
                    }

                    $('.wpDataTablesPopover.editTools').hide();

                    modal.addClass('wdt-skin-' + tableDescription.tableSkin);
                    if(tableDescription.table_wcag){
                        modal.addClass('wpTableWCAG');
                    }
                    modal.find('.modal-title').html(wpdatatables_frontend_strings.duplicate_entry);
                    modal.find('.modal-body').html('');
                    modal.find('.modal-footer').html('');


                    if (['manual', 'mysql'].indexOf(tableDescription.tableType) === -1) {
                        if (typeof wpDataTablesEditors[tableDescription.tableType]['edit'] == 'function') {
                            if (singleClick === false) {
                                singleClick = true;
                                wpDataTablesEditors[tableDescription.tableType]['edit'](tableDescription, true);
                            }
                        }
                    } else {
                        var data = wpDataTables[tableDescription.tableId].fnGetData(row);
                        wpDataTablesFunctions[tableDescription.tableId].applyData(data, true, false);
                        wpDataTables[tableDescription.tableId].checkSelectedLimits();
                        modal.find('.modal-body').append($(tableDescription.selector + '_edit_dialog').show());
                        modal.find('.modal-footer').append($(tableDescription.selector + '_edit_dialog_buttons').show());

                        if (newSkins.includes(tableDescription.tableSkin)) {
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

                        $('#wdt-frontend-modal .editDialogInput').each(function (index) {
                            if ($(this).data('input_type') == 'mce-editor') {
                                if (tinymce.activeEditor)
                                    tinymce.activeEditor.setContent('');
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

                        // Show 'No editor inputs selected' alert
                        if (modal.find('.wdt-edit-dialog-fields-block').find('.form-group').length == 0) {
                            $('#wdt-frontend-modal div.wdt-no-editor-inputs-selected-alert').show();
                        }

                        modal.modal('show');
                        $('.wdt-apply-edit-button').addClass('hidden');
                        $('.wdt-apply-duplicate-button').removeClass('hidden');

                    }
                });

                /**
                 * Hide modal dialog on Esc button
                 */
                $(document).on('keyup', '#wdt-frontend-modal', function (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    if (e.which == 27) {
                        $('#wdt-frontend-modal').modal('hide').removeClass('wdt-skin-' + tableDescription.tableSkin)
                        $('#wdt-frontend-modal').modal('hide').removeClass('wpTableWCAG')
                    }
                });

                /**
                 * When the hide instance method has been called append modal to related table( edit modal )
                 */
                $('#wdt-frontend-modal').on('hidden.bs.modal', function (e) {
                    $(tableDescription.selector + '_wrapper').append($(tableDescription.selector + '_edit_dialog').hide());
                    $(tableDescription.selector + '_wrapper').append($(tableDescription.selector + '_edit_dialog_buttons').hide());
                    $(this).removeClass('wdt-skin-' + tableDescription.tableSkin);
                    $(this).removeClass('wpTableWCAG');
                });

                /**
                 * When the hide instance method has been called append modal to related table( delete modal)
                 */
                $('#wdt-delete-modal').on('hidden.bs.modal', function (e) {
                    $(tableDescription.selector + '_wrapper').append($(tableDescription.selector + '_delete_dialog_buttons').hide());
                    $(this).removeClass('wdt-skin-' + tableDescription.tableSkin);
                    $(this).removeClass('wpTableWCAG');
                });

                /**
                 * Delete an entry dialog
                 */
                $('.delete_table_entry[aria-controls="' + tableDescription.tableId + '"]').click(function (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    if ($(this).hasClass('disabled')) {
                        return false;
                    }

                    $('.wpDataTablesPopover.editTools').hide();

                    var modal = $('#wdt-delete-modal');

                    modal.addClass('wdt-skin-' + tableDescription.tableSkin);
                    if(tableDescription.table_wcag){
                        modal.addClass('wpTableWCAG');
                    }
                    modal.find('.modal-footer').html('');
                    modal.find('.modal-footer').append($(tableDescription.selector + '_delete_dialog_buttons').show());

                    modal.modal('show');


                    $(tableDescription.selector + '_wdt-browse-delete-button').click(function (e) {
                        e.preventDefault();
                        e.stopImmediatePropagation();

                        if (['manual', 'mysql'].indexOf(tableDescription.tableType) === -1) {
                            if (typeof wpDataTablesEditors[tableDescription.tableType]['delete'] == 'function') {
                                wpDataTablesEditors[tableDescription.tableType]['delete'](tableDescription);
                            }
                        } else {
                            var row = $(tableDescription.selector + ' tr.selected').get(0);
                            if (tableDescription.responsive == 1 && $(row).hasClass('row-detail')) {
                                row = $(tableDescription.selector + ' tr.selected').prev('.detail-show').get(0);
                            }
                            var data = wpDataTables[tableDescription.tableId].fnGetData(row);
                            var id_val = data[tableDescription.idColumnIndex];
                            $.ajax({
                                url: tableDescription.adminAjaxBaseUrl,
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    action: 'wdt_delete_table_row',
                                    id_key: tableDescription.idColumnKey,
                                    id_val: id_val,
                                    table_id: tableDescription.tableWpId,
                                    wdtNonce: $('#wdtNonceFrontendEdit_' + tableDescription.tableWpId).val()
                                },
                                success: function (data) {
                                    wpDataTables[tableDescription.tableId].fnDraw(false);
                                    if (data.error == '') {
                                        $('#wdt-delete-modal').modal('hide');
                                        wdtNotify(wpdatatables_edit_strings.success, wpdatatables_edit_strings.rowDeleted, 'success');
                                    } else {
                                        wdtNotify(wpdatatables_edit_strings.error, data.error, 'danger');
                                    }
                                },
                                error: function () {
                                    wdtNotify(wpdatatables_edit_strings.error, wpdatatables_edit_strings.databaseDeleteError, 'danger');
                                }
                            });
                        }
                    });
                });

                /**
                 * Add a popover that includes edit elements
                 */
                if (tableDescription.popoverTools) {
                    $(tableDescription.selector + '_wrapper').css('position', 'relative');
                    $('<div class="wpDataTablesPopover editTools ' + tableDescription.tableId + '"></div>').prependTo(tableDescription.selector + '_wrapper').hide();
                    $('.new_table_entry[aria-controls="' + tableDescription.tableId + '"]').prependTo(tableDescription.selector + '_wrapper .wpDataTablesPopover.editTools').css('float', 'right');
                    $('.edit_table[aria-controls="' + tableDescription.tableId + '"]').prependTo(tableDescription.selector + '_wrapper .wpDataTablesPopover.editTools').css('float', 'right');
                    $('.duplicate_table_entry[aria-controls="' + tableDescription.tableId + '"]').prependTo(tableDescription.selector + '_wrapper .wpDataTablesPopover.editTools').css('float', 'right');
                    $('.delete_table_entry[aria-controls="' + tableDescription.tableId + '"]').prependTo(tableDescription.selector + '_wrapper .wpDataTablesPopover.editTools').css('float', 'right');
                }

                /**
                 * Select table row on click
                 * @param e
                 * @returns {boolean}
                 */
                var clickEvent = function (e) {

                    // Fix if td is URL Link
                    if (!$(e.target).is('a') && !$(e.target).is('button')) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        e.preventDefault();
                    }

                    if ($(this).hasClass('group')) {
                        return false;
                    }
                    // Set controls popover position
                    var popoverVerticalPosition = $(this).offset().top - $(tableDescription.selector + '_wrapper').offset().top - $('.wpDataTablesPopover.editTools').outerHeight() - 7;
                    // Check a cell is edited
                    var editedRow = ($(this).children('').hasClass('editing')) ? true : false;

                    if ($(this).hasClass('selected')) {
                        $(tableDescription.selector + ' tbody tr').removeClass('selected');
                        wpDataTablesSelRows[tableDescription.tableId] = -1;
                    } else if (!$(this).find('td').hasClass('dataTables_empty') || tableDescription.popoverTools) {
                        $(tableDescription.selector + '  tbody tr').removeClass('selected');
                        $(this).addClass('selected');
                        wpDataTablesSelRows[tableDescription.tableId] = wpDataTables[tableDescription.tableId].fnGetPosition($(tableDescription.selector + ' tbody tr.selected').get(0));
                    }
                    if ($(tableDescription.selector + ' tbody tr.selected').length > 0) {
                        $('.edit_table[aria-controls="' + tableDescription.tableId + '"]').removeClass('disabled');
                        $('.duplicate_table_entry[aria-controls="' + tableDescription.tableId + '"]').removeClass('disabled');
                        $('.delete_table_entry[aria-controls="' + tableDescription.tableId + '"]').removeClass('disabled');
                        $('.master_detail[aria-controls="' + tableDescription.tableId + '"]').removeClass('disabled');
                        if (!editedRow) {
                            $('.wpDataTablesPopover.editTools.' + tableDescription.tableId + '').show().css('top', popoverVerticalPosition);
                        } else {
                            return false;
                        }
                    } else {
                        $('.edit_table[aria-controls="' + tableDescription.tableId + '"]').addClass('disabled');
                        $('.duplicate_table_entry[aria-controls="' + tableDescription.tableId + '"]').addClass('disabled');
                        $('.delete_table_entry[aria-controls="' + tableDescription.tableId + '"]').addClass('disabled');
                        $('.master_detail[aria-controls="' + tableDescription.tableId + '"]').addClass('disabled');
                        $('.wpDataTablesPopover.editTools.' + tableDescription.tableId + '').hide();

                    }
                };

                var ua = navigator.userAgent,
                    event = (ua.match(/iPad/i)) ? "touchstart" : "click";

                $(document).off(event, tableDescription.selector + ' tbody tr').on(event, tableDescription.selector + ' tbody tr', clickEvent);

                /**
                 * Detached the chosen attachment
                 */
                $(document).on('click', tableDescription.selector + '_edit_dialog a.wdt-detach-attachment-file, a.wdt-detach-attachment-file', function (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    if ($(this).parent().find('span.fileupload-' + tableDescription.tableId).data('column_type') == 'icon') {
                        $(this).parent().find('input.editDialogInput').val('');
                        $(this).parent().parent().find('.fileinput-preview').html('');
                        $(this).parents('.fileinput-exists').removeClass('fileinput-exists').addClass('fileinput-new');
                    } else {
                        $(this).parent().find('input.editDialogInput').val('');
                        $(this).parent().find('.fileinput-filename').text('');
                        $(this).parent().removeClass('fileinput-exists').addClass('fileinput-new');
                    }
                });

            }

            /**
             * Prevent bootstrap dialog from blocking focusin on Tinymce editor
             */
            $(document).on('focusin', function (e) {
                if ($(e.target).closest(".mce-window").length) {
                    e.stopImmediatePropagation();
                }
            });
            /**
             * Add some JS hooks for Master-detail add-on
             */
            if (tableDescription.masterDetail !== undefined && tableDescription.masterDetail) {
                for (var i in wpDataTablesHooks.onRenderDetails) {
                    if (!isNaN(i))
                        wpDataTablesHooks.onRenderDetails[i](tableDescription);
                }
            }

            /**
             * Show the filter box if enabled in the widget if it is present
             */
            if (tableDescription.filterInForm == true) {
                if ($('#wdt-filter-widget').length) {
                    $('.wpDataTablesFilter').appendTo('#wdt-filter-widget');
                }
            }
            //[<--/ Full version -->]//

            return wpDataTables[tableDescription.tableId];

        };

        /**
         * Loop through all tables on the page and render the wpDataTables elements
         */
        $('table.wpDataTable:not(.wpdtSimpleTable)').each(function () {
            var tableDescription = JSON.parse($('#' + $(this).data('described-by')).val());
            wdtRenderDataTable($(this), tableDescription);
        });

    });

})(jQuery);

/**
 * Apply cell action for conditional formatting rule
 *
 * @param $cell
 * @param action
 * @param setVal
 */
function wdtApplyCellAction($cell, action, setVal) {
    let index = $cell.index() + 1;
    let classArr = $cell.attr("class").split(/\s+/);
    switch (action) {
        case 'setCellColor':
            $cell.attr('style', 'background-color: ' + setVal + ' !important');
            break;
        case 'defaultCellColor':
            $cell.attr('style', 'background-color: "" !important');
            break;
        case 'setCellContent':
            if ($cell.children().hasClass('responsiveExpander')) {
                $cell.html(setVal).prepend('<span class="responsiveExpander"></span>');
            } else {
                $cell.html(setVal);
            }
            break;
        case 'setCellClass':
            $cell.addClass(setVal);
            break;
        case 'removeCellClass':
            $cell.removeClass(setVal);
            break;
        case 'setRowColor':
            $cell.closest('tr').find('td').attr('style', 'background-color: ' + setVal + ' !important');
            break;
        case 'defaultRowColor':
            $cell.closest('tr').find('td').attr('style', 'background-color: "" !important');
            break;
        case 'setRowClass':
            $cell.closest('tr').addClass(setVal);
            break;
        case 'addColumnClass':
            $cell
                .closest('table.wpDataTable')
                .find('thead th:nth-child(' + index + ')')
                .addClass(setVal)
                .closest('table.wpDataTable')
                .find('tbody td:nth-child(' + index + ')')
                .addClass(setVal)
                .closest('table.wpDataTable')
                .find('tfoot td:nth-child(' + index + ')')
                .addClass(setVal);
            break;
        case 'setColumnColor':
            $cell
                .closest('table.wpDataTable')
                .find('thead th:nth-child(' + index + ')')
                .attr('style', 'background-color: ' + setVal + ' !important')
                .closest('table.wpDataTable')
                .find('tbody td:nth-child(' + index + ')')
                .attr('style', 'background-color: ' + setVal + ' !important')
                .closest('table.wpDataTable')
                .find('tfoot td:nth-child(' + index + ')')
                .attr('style', 'background-color: ' + setVal + ' !important');
            break;
    }
}

function wdtDialog(str, title) {
    var dialogId = Math.floor((Math.random() * 1000) + 1);
    var editModal = jQuery('.wdt-frontend-modal').clone();

    editModal.attr('id', 'remodal-' + dialogId);
    editModal.find('.modal-title').html(title);
    editModal.find('.modal-header').append(str);

    return editModal;
}

function wdtAddOverlay(table_selector) {
    jQuery(table_selector).not('.fixedHeader-floating').addClass('overlayed');
}

function wdtRemoveOverlay(table_selector) {
    jQuery(table_selector).removeClass('overlayed');
    jQuery('table.fixedHeader-floating').removeClass('overlayed');
}

/**
 * Get cell value cleared from neighbour html tags
 * @param element
 * @param responsive
 * @returns {*}
 */
function getPurifiedValue(element, responsive) {
    if (responsive) {
        var cellVal = element.children('.columnValue').html();
    } else {
        cellVal = element.clone().children().remove().end().html();
    }

    return cellVal;
}

/**
 * Conditional formatting
 * @param conditionalFormattingRules
 * @param params
 * @param element
 * @param responsive
 */
function wdtCheckConditionalFormatting(conditionalFormattingRules, params, element, responsive) {

    var cellVal = '';
    var ruleVal = '';
    var ruleMatched = false;
    if ((params.columnType == 'int') || (params.columnType == 'float')) {
        // Process numeric comparison
        if (responsive) {
            cellVal = element.children('.columnValue').html();
        } else {
            cellVal = element.clone().html();
            if (cellVal.indexOf('<span class="responsiveExpander"></span>') !== -1)
                cellVal = cellVal.replace('<span class="responsiveExpander"></span>', '');
        }
        cellVal = wdtUnformatNumber(cellVal, params.thousandsSeparator, params.decimalSeparator, true);
        if (!isNaN(cellVal)) {
            cellVal = cellVal === '' ? null : parseFloat(cellVal)
        }
        ruleVal = conditionalFormattingRules.cellVal;
    } else if (params.columnType == 'date') {
        cellVal = moment(getPurifiedValue(element, responsive), params.momentDateFormat).toDate();
        if (conditionalFormattingRules.cellVal == '%TODAY%') {
            ruleVal = moment().startOf('day').toDate();
        } else {
            ruleVal = moment(conditionalFormattingRules.cellVal, params.momentDateFormat).toDate();
        }
        if (conditionalFormattingRules.cellVal == '%LAST_WEEK%') {
            conditionalFormattingRules.ifClause = '%LAST_WEEK%'
            ruleVal = [moment().subtract(1, 'weeks').startOf('isoWeek').toDate(), moment().subtract(1, 'weeks').endOf('isoWeek').toDate()];
        }
        if (conditionalFormattingRules.cellVal == '%THIS_WEEK%') {
            conditionalFormattingRules.ifClause = '%THIS_WEEK%'
            ruleVal = [moment().startOf('isoweek').toDate(), moment().endOf('isoweek').toDate()];
        }
        if (conditionalFormattingRules.cellVal == '%NEXT_WEEK%') {
            conditionalFormattingRules.ifClause = '%LAST_WEEK%'
            ruleVal = [moment().add(1, 'weeks').startOf('isoWeek').toDate(), moment().add(1, 'weeks').endOf('isoWeek').toDate()];
        }
        if (conditionalFormattingRules.cellVal == '%LAST_30_DAYS%') {
            conditionalFormattingRules.ifClause = '%LAST_30_DAYS%'
            ruleVal = [moment().add(-30, 'days').startOf('day').toDate(), moment().toDate()];
        }
        if (conditionalFormattingRules.cellVal == '%LAST_MONTH%') {
            conditionalFormattingRules.ifClause = '%LAST_MONTH%'
            ruleVal = [moment().add(-1, 'months').startOf('months').toDate(), moment().startOf('month').toDate()];

        }
        if (conditionalFormattingRules.cellVal == '%NEXT_MONTH%') {
            conditionalFormattingRules.ifClause = '%NEXT_MONTH%'
            ruleVal = [moment().add(1, 'months').startOf('months').toDate(), moment().add(2, 'months').startOf('months').toDate()];
        }

        if (conditionalFormattingRules.cellVal == '%THIS_MONTH%') {
            conditionalFormattingRules.ifClause = '%THIS_MONTH%'
            ruleVal = [moment().startOf('months').toDate(), moment().endOf('month').toDate()];
        }

    } else if (params.columnType == 'datetime') {
        if (conditionalFormattingRules.cellVal == '%TODAY%') {
            cellVal = moment(getPurifiedValue(element, responsive), params.momentDateFormat + ' ' + params.momentTimeFormat).startOf('day').toDate();
            ruleVal = moment().startOf('day').toDate();
        } else {
            cellVal = moment(getPurifiedValue(element, responsive), params.momentDateFormat + ' ' + params.momentTimeFormat).toDate();
            ruleVal = moment(conditionalFormattingRules.cellVal, params.momentDateFormat + ' ' + params.momentTimeFormat).toDate();
        }
        if (conditionalFormattingRules.cellVal == '%LAST_WEEK%') {
            conditionalFormattingRules.ifClause = '%LAST_WEEK%'
            ruleVal = [moment().subtract(1, 'weeks').startOf('isoWeek').toDate(), moment().subtract(1, 'weeks').endOf('isoWeek').toDate()];
        }
        if (conditionalFormattingRules.cellVal == '%THIS_WEEK%') {
            conditionalFormattingRules.ifClause = '%THIS_WEEK%'
            ruleVal = [moment().startOf('isoweek').toDate(), moment().endOf('isoweek').toDate()];
        }
        if (conditionalFormattingRules.cellVal == '%NEXT_WEEK%') {
            conditionalFormattingRules.ifClause = '%LAST_WEEK%'
            ruleVal = [moment().add(1, 'weeks').startOf('isoWeek').toDate(), moment().add(1, 'weeks').endOf('isoWeek').toDate()];
        }
        if (conditionalFormattingRules.cellVal == '%LAST_30_DAYS%') {
            conditionalFormattingRules.ifClause = '%LAST_30_DAYS%'
            ruleVal = [moment().add(-30, 'days').startOf('day').toDate(), moment().toDate()];
        }
        if (conditionalFormattingRules.cellVal == '%LAST_MONTH%') {
            conditionalFormattingRules.ifClause = '%LAST_MONTH%'
            ruleVal = [moment().add(-1, 'months').startOf('months').toDate(), moment().startOf('month').toDate()];

        }
        if (conditionalFormattingRules.cellVal == '%NEXT_MONTH%') {
            conditionalFormattingRules.ifClause = '%NEXT_MONTH%'
            ruleVal = [moment().add(1, 'months').startOf('months').toDate(), moment().add(2, 'months').startOf('months').toDate()];
        }

        if (conditionalFormattingRules.cellVal == '%THIS_MONTH%') {
            conditionalFormattingRules.ifClause = '%THIS_MONTH%'
            ruleVal = [moment().startOf('months').toDate(), moment().endOf('month').toDate()];
        }
    } else if (params.columnType == 'time') {
        cellVal = moment(getPurifiedValue(element, responsive), params.momentTimeFormat).toDate();
        ruleVal = moment(conditionalFormattingRules.cellVal, params.momentTimeFormat).toDate();
    } else if (params.columnType == 'link') {
        if (responsive) {
            cellVal = element.children('.columnValue').html();
        } else {
            cellVal = element.clone().html();
        }
        ruleVal = conditionalFormattingRules.cellVal;
    } else {
        // Process string comparison
        if (responsive) {
            cellVal = element.children('.columnValue').html();
        } else {
            cellVal = element.clone().html();
        }
        ruleVal = conditionalFormattingRules.cellVal;
    }

    switch (conditionalFormattingRules.ifClause) {
        case 'lt':
            ruleMatched = cellVal < ruleVal;
            break;
        case 'lteq':
            ruleMatched = cellVal <= ruleVal;
            break;
        case 'eq':
            if (params.columnType == 'date'
                || params.columnType == 'datetime'
                || params.columnType == 'time') {
                cellVal = cellVal != null ? cellVal.getTime() : null;
                ruleVal = ruleVal != null ? ruleVal.getTime() : null;
            }
            ruleMatched = cellVal == ruleVal;
            break;
        case 'neq':
            if (params.columnType == 'date' || params.columnType == 'datetime') {
                cellVal = cellVal != null ? cellVal.getTime() : null;
                ruleVal = ruleVal != null ? ruleVal.getTime() : null;
            }
            ruleMatched = cellVal != ruleVal;
            break;
        case 'gteq':
            ruleMatched = cellVal >= ruleVal;
            break;
        case 'gt':
            ruleMatched = cellVal > ruleVal;
            break;
        case 'contains':
            ruleMatched = cellVal.indexOf(ruleVal) !== -1;
            break;
        case 'contains_not':
            ruleMatched = cellVal.indexOf(ruleVal) == -1;
            break;
        case '%THIS_WEEK%':
            ruleMatched = moment(cellVal) >= moment(ruleVal[0]) && moment(cellVal) <= moment(ruleVal[1]);
            break;
        case '%LAST_WEEK%':
            ruleMatched = moment(cellVal) >= moment(ruleVal[0]) && moment(cellVal) <= moment(ruleVal[1]);
            break;
        case '%NEXT_WEEK%':
            ruleMatched = moment(cellVal) >= moment(ruleVal[0]) && moment(cellVal) <= moment(ruleVal[1]);
            break;
        case '%LAST_30_DAYS%':
            ruleMatched = moment(cellVal) >= moment(ruleVal[0]) && moment(cellVal) <= moment(ruleVal[1]);
            break;
        case '%LAST_MONTH%':
            ruleMatched = moment(cellVal) >= moment(ruleVal[0]) && moment(cellVal) < moment(ruleVal[1]);
            break;
        case '%NEXT_MONTH%':
            ruleMatched = moment(cellVal) >= moment(ruleVal[0]) && moment(cellVal) < moment(ruleVal[1]);
            break;
        case '%THIS_MONTH%':
            ruleMatched = moment(cellVal) >= moment(ruleVal[0]) && moment(cellVal) <= moment(ruleVal[1]);
            break;
    }

    if (ruleMatched) {
        wdtApplyCellAction(element, conditionalFormattingRules.action, conditionalFormattingRules.setVal);
    }
}
function wdtTransformValue(trasnsformValue, params, element, m, tableID) {
    let index = element.index() + 1;
    jQuery(element.closest('table#' + tableID + '.wpDataTable').find('tbody td:nth-child(' + index + ')')[m]).html(trasnsformValue);
}

// jQuery.fn.dataTableExt.oStdClasses.sWrapper = "wpDataTables wpDataTablesWrapper";
jQuery.fn.dataTable.ext.classes.sLengthSelect = 'wdt-selectpicker length_menu';
jQuery.fn.dataTable.ext.classes.sFilterInput = 'form-control';
