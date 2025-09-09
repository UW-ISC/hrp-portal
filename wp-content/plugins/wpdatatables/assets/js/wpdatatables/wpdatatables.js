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
                                if (tableDescription.transform_value_columns) {
                                    var responsive_rows = detailsRow.find('li');
                                    var oSettings = wpDataTables[tableDescription.tableId].fnSettings();
                                    for (var i = 0; i < tableDescription.transform_value_columns.length; i++) {
                                        var column = oSettings.oInstance.api().column(tableDescription.transform_value_columns[i] + ':name', {search: 'applied'});
                                        var transformValueRules = {};
                                        transformValueRules[0] = oSettings.aoColumns[column.index()].transformValueRules;
                                        var heleprTransformValue = 0;
                                        for (var k = 0; k < oSettings.aoColumns.length; k++) {
                                            var col = oSettings.oInstance.api().column(oSettings.aoColumns[k].name + ':name', {search: 'applied'});
                                            responsive_rows.each(function () {
                                                $(this).find('.columnValue').contents().filter(function () {
                                                    if (this.nodeType === 8) {
                                                        $(this).remove();
                                                    }
                                                });
                                                var column_index = $(this).data('column');
                                                if (column_index == column.index()) {
                                                    if (transformValueRules[0].includes('{' + oSettings.aoColumns[k].name + '.value}')) {
                                                        var array = oSettings.aiDisplay;
                                                        var valueToFind = $(this).parent().parent().parent()[0].previousSibling._DT_RowIndex;
                                                        var position = array.indexOf(valueToFind);
                                                        wdtTransformValueResponsive(transformValueRules[heleprTransformValue].replaceAll('{' + oSettings.aoColumns[k].name + '.value}', col.data()[position] === null ? '' : col.data()[position]), params, $(this), 0, oSettings.sTableId);
                                                        transformValueRules[1] = transformValueRules[heleprTransformValue].replaceAll('{' + oSettings.aoColumns[k].name + '.value}', col.data()[position] === null ? '' : col.data()[position]);
                                                        heleprTransformValue = 1;
                                                    }
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
                            if ($inputElement.length) {
                                if ($inputElement.data("key").toLowerCase() === 'wdt_id' && isDuplicate) {
                                    val = "0"
                                } else {
                                    var val = el.toString();
                                }
                            } else {
                                var val = '';
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
                                $selectpickerBlock.html('<div class="fg-line"><select id="' + tableDescription.tableId + '_' + $inputElement.data('key') + '" title="' + wpdatatables_frontend_strings.nothingSelected_wpdatatables + '" data-input_type="' + inputElementType + '" data-key="' + $inputElement.data('key') + '" class="form-control editDialogInput selectpicker ' + mandatory + 'wdt-possible-values-ajax ' + foreignKeyRule + searchInSelect + '" data-live-search="true" data-live-search-placeholder="' + wpdatatables_frontend_strings.search_wpdatatables + '" data-column_header="' + $inputElement.data('column_header') + '"></select></div>');
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
                                            emptyTitle: wpdatatables_frontend_strings.nothingSelected_wpdatatables,
                                            statusSearching: wpdatatables_frontend_strings.sLoadingRecords_wpdatatables,
                                            currentlySelected: wpdatatables_frontend_strings.currentlySelected_wpdatatables,
                                            errorText: wpdatatables_frontend_strings.errorText_wpdatatables,
                                            searchPlaceholder: wpdatatables_frontend_strings.search_wpdatatables,
                                            statusInitialized: wpdatatables_frontend_strings.statusInitialized_wpdatatables,
                                            statusNoResults: wpdatatables_frontend_strings.statusNoResults_wpdatatables,
                                            statusTooShort: wpdatatables_frontend_strings.statusTooShort_wpdatatables
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
                 * @param duplicateEntry
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
                                    validation_message += wpdatatables_frontend_strings.invalid_email_wpdatatables + ' <b>' + $(this).data('column_header') + '</b><br>';
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
                                    validation_message += wpdatatables_frontend_strings.invalid_link_wpdatatables + ' <b>' + $(this).data('column_header') + '</b><br>';
                                } else {
                                    $(this).removeClass('error');
                                }
                            }
                        }
                        if ($(this).hasClass('mandatory')) {
                            if ($(this).val() == '' || $(this).val() == null) {
                                $(this).addClass('error');
                                valid = false;
                                validation_message += '<b>' + $(this).data('column_header') + '</b> ' + wpdatatables_frontend_strings.cannot_be_empty_wpdatatables + '<br>';
                            } else {
                                if (valid) {
                                    $(this).removeClass('error');
                                }
                            }
                        }
                        if ($(this).hasClass('datepicker')) {
                            formdata[$(this).data('key')] = $.datepicker.formatDate(tableDescription.datepickFormat, $.datepicker.parseDate(tableDescription.datepickFormat, $(this).val()));
                        } else if ($(this).hasClass('wdt-timepicker') || $(this).hasClass('wdt-datetimepicker')) {
                            let pattern = /^.*:[0-9]{2}:[0-9]{1}$/;
                            if (pattern.test($(this).val())) {
                                formdata[$(this).data('key')] = $(this).val().replace(/:(\d)$/, ':0$1');
                            } else {
                                formdata[$(this).data('key')] = $(this).val();
                            }
                        } else if ($(this).data('input_type') == 'multi-selectbox') {
                            if ($(this).val()) {
                                formdata[$(this).data('key')] = $(this).val().join(', ');
                            } else {
                                formdata[$(this).data('key')] = '';
                            }
                        } else if ($(this).data('column_type') == 'int') {
                            formdata[$(this).data('key')] = $(this).val().replace(/,/g, '').replace(/\./g, '');
                        } else if ($(this).data('column_type') == 'hidden') {
                            formdata[$(this).data('key')] = '';
                        } else {
                            formdata[$(this).data('key')] = $(this).val();
                        }
                        aoData.push(formdata[$(this).data('key')]);
                    });
                    if (!valid) {
                        $(tableDescription.selector + '_edit_dialog').closest('.modal-dialog').find('.wdt-preload-layer').animateFadeOut();
                        wdtNotify(wpdatatables_edit_strings.error_common, validation_message, 'danger');
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
                            formdata: formdata,
                            wdtAjaxURL: tableDescription.dataTableParams.ajax.url,
                            queryParams: getAllUrlParams()
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

                                wdtNotify(wpdatatables_edit_strings.success_common, wpdatatables_edit_strings.dataSaved_common, 'success');
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
                                wdtNotify(wpdatatables_edit_strings.error_common, returnData.error, 'danger');
                            }
                        },
                        error: function (xhr, response) {
                            $(tableDescription.selector + '_edit_dialog').closest('.modal-dialog').find('.wdt-preload-layer').animateFadeOut();
                            wdtNotify(wpdatatables_edit_strings.error_common, wpdatatables_frontend_strings.databaseInsertError_wpdatatables, 'danger');
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
                                $selectpickerBlock.html('<div class="fg-line"><select id="' + tableDescription.tableId + '_' + column.origHeader + '" data-input_type="' + column.editorInputType + '" data-key="' + column.origHeader + '" class="form-control editDialogInput selectpicker ' + mandatory + possibleValuesAjax + foreignKeyRule + searchInSelect + '" data-live-search="true" data-live-search-placeholder="' + wpdatatables_frontend_strings.search_wpdatatables + '" data-column_header="' + column.displayHeader + '"></select></div>');
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
                                        emptyTitle: wpdatatables_frontend_strings.nothingSelected_wpdatatables,
                                        statusSearching: wpdatatables_frontend_strings.sLoadingRecords_wpdatatables,
                                        currentlySelected: wpdatatables_frontend_strings.currentlySelected_wpdatatables,
                                        errorText: wpdatatables_frontend_strings.errorText_wpdatatables,
                                        searchPlaceholder: wpdatatables_frontend_strings.search_wpdatatables,
                                        statusInitialized: wpdatatables_frontend_strings.statusInitialized_wpdatatables,
                                        statusNoResults: wpdatatables_frontend_strings.statusNoResults_wpdatatables,
                                        statusTooShort: wpdatatables_frontend_strings.statusTooShort_wpdatatables
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
                    data.currentUserDisplayName = $('#wdt-user-display-name-placeholder').val();
                    data.currentUserFirstName = $('#wdt-user-first-name-placeholder').val();
                    data.currentUserLastName = $('#wdt-user-last-name-placeholder').val();
                    data.currentUserEmail = $('#wdt-user-email-placeholder').val();
                    data.currentDate = $('#wdt-date-placeholder').val();
                    data.currentDateTime = $('#wdt-datetime-placeholder').val();
                    data.currentTime = $('#wdt-time-placeholder').val();
                    data.wpdbPlaceholder = $('#wdt-wpdb-placeholder').val();
                    data.wdtNonce = $('#wdtNonceFrontendServerSide_' + tableDescription.tableWpId).val();
                    data.showAllRows = $('#wdt-show-all-rows').val();
                };
            }

            /**
             * Add the select column functionality for WooCommerce tables
             */
            if (tableDescription.tableType === "woo_commerce") {
                let selectColumn = dataTableOptions.columnDefs.find(column => column.origHeader === "select");
                if(selectColumn.bVisible) {
                    if (selectColumn) {
                        dataTableOptions.select = {
                            style: 'multi',
                            selector: 'td.wdt_woo_select_column',
                        };

                        // Dynamically handle select column index
                        let selectColumnIndex = selectColumn.aTargets[0];
                        selectColumn.orderable = false;
                        selectColumn.searchable = false;
                        selectColumn.InputType = "none";
                        selectColumn.sType = selectColumn.wdtType = "select";
                        selectColumn.className = 'wdt_woo_select_column';

                        dataTableOptions.columnDefs[selectColumnIndex] = selectColumn;

                        // Ensure valid initial order column
                        let defaultOrder = dataTableOptions.order[0];
                        if (defaultOrder && defaultOrder[0] === selectColumnIndex) {
                            let firstSortableIndex = dataTableOptions.columnDefs.findIndex(column => column.orderable !== false);
                            if (firstSortableIndex !== -1) {
                                defaultOrder[0] = firstSortableIndex;
                            } else {
                                defaultOrder[0] = 1;
                            }
                        }

                        // Adjust select column functionality post-reordering
                        let findSelectColumnIndex = () => {
                            let columns = $(tableDescription.selector).DataTable().settings()[0].aoColumns;
                            return columns.findIndex(col => col.className === 'wdt_woo_select_column');
                        };

                        // Row callback for checkbox rendering
                        dataTableOptions.rowCallback = function (row, data) {
                            let currentSelectColumnIndex = findSelectColumnIndex();
                            if (currentSelectColumnIndex !== -1) {
                                $('td:eq(' + currentSelectColumnIndex + ')', row).html('<input type="checkbox" class="select-checkbox">');
                            }
                        };

                        // Header callback for "select all" checkbox
                        dataTableOptions.headerCallback = function (thead, data, start, end, display) {
                            let currentSelectColumnIndex = findSelectColumnIndex();
                            if (currentSelectColumnIndex !== -1) {
                                let $headerCell = $(thead).find('th').eq(currentSelectColumnIndex);
                                let currentHtml = $headerCell.html();
                                let updatedHtml = currentHtml.replace(/select/, '<input type="checkbox" class="wdt-get-all-checkbox">');
                                $headerCell.html(updatedHtml);
                            }
                        };

                        // Update select column index after reorder
                        $(tableDescription.selector).on('column-reorder.dt', function (e, settings, details) {
                            selectColumnIndex = findSelectColumnIndex();
                        });

                        // Event handlers for "select all" and individual checkboxes
                        $(tableDescription.selector).on('click', '.wdt-get-all-checkbox', function () {
                            let rows = $(tableDescription.selector).DataTable().rows({'search': 'applied'}).nodes();
                            $('input[type="checkbox"]', rows).prop('checked', this.checked);
                            if (this.checked) {
                                $(tableDescription.selector).DataTable().rows({'search': 'applied'}).select();
                            } else {
                                $(tableDescription.selector).DataTable().rows({'search': 'applied'}).deselect();
                            }
                        });

                        $(tableDescription.selector).on('click', '.select-checkbox', function () {
                            let $row = $(this).closest('tr');
                            let isChecked = $(this).prop('checked');
                            if (isChecked) {
                                $(tableDescription.selector).DataTable().row($row).select();
                            } else {
                                $(tableDescription.selector).DataTable().row($row).deselect();
                            }
                        });
                    }
                }
            }

            /**
             * Show after load if configured
             */
            if (tableDescription.hideBeforeLoad) {
                dataTableOptions.fnInitComplete = function () {
                    $(tableDescription.selector).animateFadeIn();

                    // TODO Check if is necessary
                    // and add conditions for server-side
                    if (tableDescription.dataTableParams.fixedColumns) {
                        $(tableDescription.selector).DataTable().draw()
                    }
                }
            }
            /**
             * Add outline class to selected column col for initial table load
             */
            if ($.inArray(tableDescription.tableSkin, ['raspberry-cream', 'mojito', 'dark-mojito']) !== -1) {
                dataTableOptions.fnInitComplete = function () {
                    if ($(tableDescription.selector).length != 0) {
                        //  Find the column that the table is initially sorted by
                        let columnPos = tableDescription.dataTableParams.order[0][0];
                        let columnTitle = tableDescription.dataTableParams.columnDefs[columnPos].className.substring(
                            tableDescription.dataTableParams.columnDefs[columnPos].className.indexOf("column-") + 7,
                        );

                        let tableId = tableDescription.tableId;
                        if ($(tableDescription.selector + ' table').length >= 1) {
                            let parentTable = $(tableDescription.selector + ' > table').eq(0);
                        }
                        if ($(tableDescription.selector).parent().closest('table').length >= 1) {
                            let parentTable = $(tableDescription.selector).parent().closest('table');
                        }
                        tableId = typeof parentTable != 'undefined' ? parentTable[0].id : tableId;

                        addOutlineBorder(tableId, columnTitle);
                        if (tableDescription.tableSkin === 'mojito' || tableDescription.tableSkin === 'dark-mojito') {
                            cubeLoaderMojito(tableId);
                            if (tableDescription.showRowsPerPage)
                                hideLabelShowXEntries(tableId);
                        }

                        if (tableDescription.hideBeforeLoad) {
                            $(tableDescription.selector).animateFadeIn();
                            // TODO Check if is necessary
                            // and add conditions for server-side or fixed columns
                            $(tableDescription.selector).DataTable().draw()
                        }
                    }
                }
            }

            var wdtFirstDraw = false;

            $(document).on('draw.dt', function (e, settings) {
                if (!tableDescription.loader) wdtFirstDraw = false;
                if (!wdtFirstDraw) {
                    if (tableDescription.loader) {
                        var tableID = tableDescription.tableId;
                        var tableLoaderId = $('.wdt-timeline-' + tableID);
                        if (tableLoaderId.length != 0) {
                            tableLoaderId.hide();
                        }
                    }

                    $(tableDescription.selector).removeClass('wdt-no-display');
                    $(tableDescription.selector + '_wrapper').removeClass('wdt-no-display');

                    wdtFirstDraw = true;
                }
            });

            wpDataTables[tableDescription.tableId] = $(tableDescription.selector).dataTable(dataTableOptions);

            /**
             * Remove pagination when "Default rows per page" is set to "All"
             */
            if (wpDataTables[tableDescription.tableId].fnSettings()._iDisplayLength >= wpDataTables[tableDescription.tableId].fnSettings().fnRecordsTotal() || dataTableOptions.iDisplayLength === -1) {
                $('#' + tableDescription.tableId + '_paginate').hide();
            }

            $(tableDescription.selector + '_wrapper').addClass('wpDataTableID-' + tableDescription.tableWpId)

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
            if (tableDescription.pagination && tableDescription.table_wcag) {
                $(tableDescription.selector + '_paginate').addClass('wcag_paginate');
            }
            if (tableDescription.table_wcag) {
                $(tableDescription.selector + '_length label').attr('for', tableDescription.tableId + ' length_menu');
                $(tableDescription.selector + '_length label').prepend('<span class="wpdt-c wpdt-visually-hidden">wpdatatables_frontend_strings.lenghtMenuWCAG_wpdatatables</span>')
                $(tableDescription.selector + '_filter input[type="search"]').attr('aria-label', wpdatatables_frontend_strings.globalSearchWCAG_wpdatatables);
                $(tableDescription.selector + '_filter label').attr('for', tableDescription.tableId + ' search');
                $(tableDescription.selector + '_filter label').prepend('<span class="wpdt-c wpdt-visually-hidden">wpdatatables_frontend_strings.searchTableWCAG_wpdatatables</span>');

            }
            if (tableDescription.tableSkin) {
                $(tableDescription.selector + '_wrapper .dt-buttons .DTTT_button_export').on('click', function () {
                    $('.dt-button-collection').addClass('wdt-skin-' + tableDescription.tableSkin)
                    if (tableDescription.table_wcag) {
                        $('.dt-button-collection.wdt-skin-' + tableDescription.tableSkin).attr('aria-label', wpdatatables_frontend_strings.chooseExportWCAG_wpdatatables);
                        $('.dt-button-collection.wdt-skin-' + tableDescription.tableSkin + ' div').attr('role', 'listbox').attr('aria-label', wpdatatables_frontend_strings.chooseExportWCAG_wpdatatables).removeAttr('aria-busy');
                        $('.dt-button-collection .buttons-excel').attr('role', 'option').removeAttr('aria-controls').removeAttr('tabindex');
                        $('.dt-button-collection .buttons-csv').attr('role', 'option').removeAttr('aria-controls').removeAttr('tabindex');
                        $('.dt-button-collection .buttons-copy').attr('role', 'option').removeAttr('aria-controls').removeAttr('tabindex');
                        $('.dt-button-collection .buttons-pdf').attr('role', 'option').removeAttr('aria-controls').removeAttr('tabindex');
                    }
                });
                $(tableDescription.selector + '_wrapper .dt-buttons .DTTT_button_colvis').on('click', function () {
                    $('.dt-button-collection').addClass('wdt-skin-' + tableDescription.tableSkin)
                    if (tableDescription.table_wcag) {
                        $('.dt-button-collection.wdt-skin-' + tableDescription.tableSkin).attr('aria-label', wpdatatables_frontend_strings.optionHideWCAG_wpdatatables);
                        $('.dt-button-collection.wdt-skin-' + tableDescription.tableSkin + ' div').attr('role', 'listbox').attr('aria-label', wpdatatables_frontend_strings.optionHideWCAG_wpdatatables).removeAttr('aria-busy');
                        $('.dt-button-collection .buttons-columnVisibility').attr('role', 'option').removeAttr('aria-controls').removeAttr('tabindex');
                    }
                });
                if (tableDescription.table_wcag) {
                    $(tableDescription.selector + '_wrapper .dt-buttons .dt-button.DTTT_button_colvis').attr('aria-label', wpdatatables_frontend_strings.columnVisibilityWCAG_wpdatatables).attr('role', 'button');
                    $(tableDescription.selector + '_wrapper .dt-buttons .dt-button.DTTT_button_spacer').attr('aria-label', wpdatatables_frontend_strings.spacerWCAG_wpdatatables).attr('role', 'button');
                    $(tableDescription.selector + '_wrapper .dt-buttons .dt-button.DTTT_button_print').attr('aria-label', wpdatatables_frontend_strings.printTableWCAG_wpdatatables).attr('role', 'button');
                    $(tableDescription.selector + '_wrapper .dt-buttons .dt-button.DTTT_button_export').attr('aria-label', wpdatatables_frontend_strings.exportTableWCAG_wpdatatables).attr('role', 'button');
                    $(tableDescription.selector + '_wrapper .dt-buttons .dt-button.DTTT_button_new').attr('aria-label', wpdatatables_frontend_strings.newEntryWCAG_wpdatatables).attr('role', 'button');
                    $(tableDescription.selector + '_wrapper .dt-buttons .dt-button.DTTT_button_delete').attr('aria-label', wpdatatables_frontend_strings.deleteRowWCAG_wpdatatables).attr('role', 'button');
                    $(tableDescription.selector + '_wrapper .dt-buttons .dt-button.DTTT_button_edit').attr('aria-label', wpdatatables_frontend_strings.editRowWCAG_wpdatatables).attr('role', 'button');
                    $(tableDescription.selector + '_wrapper .dt-buttons .dt-button.DTTT_button_duplicate').attr('aria-label', wpdatatables_frontend_strings.duplicateRowWCAG_wpdatatables).attr('role', 'button');
                    $(tableDescription.selector + '_wrapper .dt-buttons .dt-button.DTTT_button_clear_filters').attr('aria-label', wpdatatables_frontend_strings.clearFiltersWCAG_wpdatatables).attr('role', 'button');
                    if (tableDescription.masterDetail) {
                        $(tableDescription.selector + '_wrapper .dt-buttons .dt-button.master_detail').attr('aria-label', wpdatatables_frontend_strings.masterDetailWCAG_wpdatatables).attr('role', 'button');
                    }
                }
                $(tableDescription.selector + '_wrapper .dt-buttons .dt-button.DTTT_button_spacer span').addClass('wpdt-visually-hidden');
            }
            /**
             * Show "Show X entries" dropdown
             */
            if (tableDescription.showRowsPerPage) {
                if (!(jQuery(tableDescription.selector + '_wrapper .dataTables_length .length_menu.bootstrap-select').length))
                    jQuery(tableDescription.selector + '_wrapper .dataTables_length .length_menu.wdt-selectpicker').selectpicker();
                if (tableDescription.table_wcag) {
                    jQuery(tableDescription.selector + '_length .dropdown-menu.open ul').attr('role', 'listbox');
                    jQuery(tableDescription.selector + '_length .dropdown-menu.open').attr('aria-label', wpdatatables_frontend_strings.rowsPerPageWCAG_wpdatatables).attr('aria-controls', tableDescription.tableId).attr('aria-expanded', 'false');
                    jQuery(tableDescription.selector + '_length .dropdown-menu.open ul li').attr('role', 'option');
                    jQuery(tableDescription.selector + '_length .dropdown-menu.open ul li a').removeAttr('tabindex').removeAttr('aria-selected').removeAttr('role');
                    jQuery(tableDescription.selector + ' .dataTables_length .dropdown-menu.open ul li').attr('role', 'list').attr('aria-required-parent', 'listbox');

                    if (tableDescription.filterInForm) {
                        jQuery('.wpDataTableFilterSection .filter_column .dropdown-menu.open ul li').attr('role', 'option').attr('aria-required-parent', 'listbox');
                    }
                }

            }

            /**
             * Remove pagination when "All" is selected from length menu or
             * if value length menu is greater than total records
             */
            var counter = false;
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

                    if (!counter && $(this).parents('.elementor-widget-container').length) {
                        oSettings.sDom += "<'pagination-wrapper'p>";
                        counter = true;
                        $(this).DataTable().draw();
                    }
                    if (tableDescription.table_wcag) {
                        this.fnSettings().oLanguage.oPaginate.sFirst = wpdatatables_frontend_strings.firstPageWCAG_wpdatatables;
                        this.fnSettings().oLanguage.oPaginate.sLast = wpdatatables_frontend_strings.lastPageWCAG_wpdatatables;
                        this.fnSettings().oLanguage.oPaginate.sNext = wpdatatables_frontend_strings.nextPageWCAG_wpdatatables;
                        this.fnSettings().oLanguage.oPaginate.sPrevious = wpdatatables_frontend_strings.previousPageWCAG_wpdatatables;
                        $(document).ready(function () {
                            $('.paginate_button.first').attr('aria-label', wpdatatables_frontend_strings.firstPageWCAG_wpdatatables).attr('role', 'link');
                            $('.paginate_button.last').attr('aria-label', wpdatatables_frontend_strings.lastPageWCAG_wpdatatables).attr('role', 'link');
                            $('.paginate_button.next').attr('aria-label', wpdatatables_frontend_strings.nextPageWCAG_wpdatatables).attr('role', 'link');
                            $('.paginate_button.previous').attr('aria-label', wpdatatables_frontend_strings.previousPageWCAG_wpdatatables).attr('role', 'link');
                            $('#' + tableDescription.tableId + '_paginate').find('span .paginate_button').each(function (index) {
                                $(this).attr('aria-label', wpdatatables_frontend_strings.pageWCAG_wpdatatables + this.text).attr('role', 'link');
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
                    if ($(tableDescription.selector + ' table:not(.fixedHeader-floating)').length >= 1) {
                        $(tableDescription.selector + ' table.wpDataTable:not(.wpdtSimpleTable)').each(function () {
                            let tableDescriptionChild = JSON.parse($('#' + $(this).data('described-by')).val());
                            $(tableDescriptionChild.selector).DataTable().destroy();
                            wdtRenderDataTable($(tableDescription.selector + ' table'), tableDescriptionChild);
                        });
                    } else if ($(tableDescription.selector + ' .wpDataTablesWrapper').length >= 1) {
                        $(tableDescription.selector + ' .wpDataTablesWrapper').each(function () {
                            let id = $(this)[0].id.replace('_wrapper', '');
                            let tableDescriptionChild = JSON.parse($('#' + $('#' + id).data('described-by')).val());
                            $(tableDescriptionChild.selector).DataTable().destroy();
                            wdtRenderDataTable($('#' + id), tableDescriptionChild);
                        });
                    }
                    if ($(tableDescription.selector).length != 0) {
                        if ($(tableDescription.selector + ' table:not(.fixedHeader-floating)').length >= 1) {
                            let parentTable = $(tableDescription.selector + ' > table').eq(0);
                        }
                        if ($(tableDescription.selector).parent().closest('table:not(.fixedHeader-floating)').length >= 1) {
                            let parentTable = $(tableDescription.selector).parent().closest('table');
                        }
                        if ($.inArray(tableDescription.tableSkin, ['raspberry-cream', 'mojito', 'dark-mojito']) !== -1) {
                            //Find the column that the table is sorted by
                            let columnPos = (oSettings.aaSorting.length && oSettings.aaSorting[0].length) ?
                                oSettings.aaSorting[0][0] :
                                oSettings.aoColumns.findIndex(col => col.bVisible && col.bSortable);
                            if (columnPos === -1) {
                                columnPos = oSettings.aoColumns.findIndex(col => col.bVisible);
                            }
                            let columnTitle = oSettings.aoColumns[columnPos].className.substring(
                                oSettings.aoColumns[columnPos].className.indexOf("column-") + 7);
                            let tableId = typeof parentTable != 'undefined' ? parentTable[0].id : oSettings.sTableId;
                            addOutlineBorder(tableId, columnTitle);
                        }
                    }
                }
            });
            if (tableDescription.table_wcag) {
                wpDataTables[tableDescription.tableId].fnSettings().aoDrawCallback.push({
                    sName: 'addFilteredValues',
                    fn: function (oSettings) {
                        if (tableDescription.advancedFilterEnabled) {
                            var arrayofSearchedColumns = [];
                            var numberOfSearchedColumn = 0;
                            var numberOfSearchedDateTimeColumn = 0;
                            var i = 0;
                            for (i = 0; i < oSettings.aoPreSearchCols.length; i++) {
                                if (oSettings.aoPreSearchCols[i].sSearch != '') {
                                    arrayofSearchedColumns.push(i);
                                    numberOfSearchedColumn++;
                                } else if (($(oSettings.aoColumns[i].nTf).attr('data-value-to') != undefined && $(oSettings.aoColumns[i].nTf).attr('data-value-to') != '')
                                    || ($(oSettings.aoColumns[i].nTf).attr('data-value-from') != '' && $(oSettings.aoColumns[i].nTf).attr('data-value-from') != undefined)) {
                                    arrayofSearchedColumns.push(i);
                                    numberOfSearchedColumn++;
                                    numberOfSearchedDateTimeColumn++;
                                } else if ($.inArray(tableDescription.advancedFilterOptions.aoColumns[i].type, ['time-range', 'date-range', 'datetime-range']) !== -1
                                    && tableDescription.advancedFilterOptions.aoColumns[i].defaultValue !== '') {
                                    arrayofSearchedColumns.push(i);
                                    numberOfSearchedColumn++;
                                    numberOfSearchedDateTimeColumn++;
                                } else if (($('tr th.wpdt_using_wcag_filter span[data-index=' + i + ']').closest('th').attr('data-value-from') != undefined && $('tr th.wpdt_using_wcag_filter span[data-index=' + i + ']').closest('th').attr('data-value-from') != '')
                                    || ($('tr th.wpdt_using_wcag_filter span[data-index=' + i + ']').closest('th').attr('data-value-to') != '' && $('tr th.wpdt_using_wcag_filter span[data-index=' + i + ']').closest('th').attr('data-value-to') != undefined)) {
                                    arrayofSearchedColumns.push(i);
                                    numberOfSearchedColumn++;
                                    numberOfSearchedDateTimeColumn++;
                                } else if (($('div.wpDataTableFilterSection div span[data-index=' + i + ']').parent().attr('data-value-from') != undefined && $('div.wpDataTableFilterSection div span[data-index=' + i + ']').parent().attr('data-value-from') != '')
                                    || ($('div.wpDataTableFilterSection div span[data-index=' + i + ']').parent().attr('data-value-to') != '' && $('div.wpDataTableFilterSection div span[data-index=' + i + ']').parent().attr('data-value-to') != undefined)) {
                                    arrayofSearchedColumns.push(i);
                                    numberOfSearchedColumn++;
                                    numberOfSearchedDateTimeColumn++;
                                }
                            }
                            oSettings.oLanguage.sInfo = wpdatatables_frontend_strings.sInfo_wpdatatables;
                            oSettings.oLanguage.sInfoEmpty = wpdatatables_frontend_strings.sInfoEmpty_wpdatatables;
                            if (tableDescription.globalSearch) {
                                if ($('.dataTables_filter input[type="search"]').val() != '') {
                                    oSettings.oLanguage.sInfo = wpdatatables_frontend_strings.sInfoWCAG_wpdatatables;
                                    oSettings.oLanguage.sInfoEmpty = wpdatatables_frontend_strings.sInfoEmptyWCAG_wpdatatables;
                                    oSettings.oLanguage.sInfo = oSettings.oLanguage.sInfo.replace('_COLUMN_', wpdatatables_frontend_strings.forGloablWCAG_wpdatatables);
                                    oSettings.oLanguage.sInfo = oSettings.oLanguage.sInfo.replace('_DATA_', $('.dataTables_filter input[type="search"]').val());
                                    oSettings.oLanguage.sInfoEmpty = oSettings.oLanguage.sInfoEmpty.replace('_COLUMN_', wpdatatables_frontend_strings.forGloablWCAG_wpdatatables);
                                    oSettings.oLanguage.sInfoEmpty = oSettings.oLanguage.sInfoEmpty.replace('_DATA_', $('.dataTables_filter input[type="search"]').val());
                                }
                            }
                            if (tableDescription.renderFilter == "header" && !tableDescription.filterInForm) {
                                addWCAGElementsForFilters(arrayofSearchedColumns, oSettings, this, numberOfSearchedColumn, 'tr th.wpdt_using_wcag_filter span');
                            } else if (tableDescription.renderFilter == "footer" && !tableDescription.filterInForm) {
                                addWCAGElementsForFilters(arrayofSearchedColumns, oSettings, this, numberOfSearchedColumn, '');
                            } else if (tableDescription.filterInForm) {
                                addWCAGElementsForFilters(arrayofSearchedColumns, oSettings, this, numberOfSearchedColumn, 'div.wpDataTableFilterSection div span');
                            }
                            $(tableDescription.selector + ' .filter_column .wdt-select-filter .length_menu.open ul li').attr('role', 'list').attr('aria-required-parent', 'listbox');
                            $(tableDescription.selector + ' .dataTables_length .dropdown-menu.open ul li').attr('role', 'list').attr('aria-required-parent', 'listbox');
                        } else if (tableDescription.globalSearch) {
                            oSettings.oLanguage.sInfo = wpdatatables_frontend_strings.sInfo_wpdatatables;
                            oSettings.oLanguage.sInfoEmpty = wpdatatables_frontend_strings.sInfoEmpty_wpdatatables;
                            if ($('.dataTables_filter input[type="search"]').val() != '') {
                                oSettings.oLanguage.sInfo = wpdatatables_frontend_strings.sInfoWCAG_wpdatatables;
                                oSettings.oLanguage.sInfoEmpty = wpdatatables_frontend_strings.sInfoEmptyWCAG_wpdatatables;
                                oSettings.oLanguage.sInfo = oSettings.oLanguage.sInfo.replace('_COLUMN_', wpdatatables_frontend_strings.forGloablWCAG_wpdatatables);
                                oSettings.oLanguage.sInfo = oSettings.oLanguage.sInfo.replace('_DATA_', $('.dataTables_filter input[type="search"]').val());
                                oSettings.oLanguage.sInfoEmpty = oSettings.oLanguage.sInfoEmpty.replace('_COLUMN_', wpdatatables_frontend_strings.forGloablWCAG_wpdatatables);
                                oSettings.oLanguage.sInfoEmpty = oSettings.oLanguage.sInfoEmpty.replace('_DATA_', $('.dataTables_filter input[type="search"]').val());
                            }
                        }
                    }
                });
            }

            function addWCAGElementsForFilters(arrayofSearchedColumns, oSettings, thisTable, numberOfSearchedColumn, selector1) {
                var selector;
                if (arrayofSearchedColumns.length > 0) {
                    oSettings.oLanguage.sInfo = wpdatatables_frontend_strings.sInfoWCAG_wpdatatables;
                    oSettings.oLanguage.sInfoEmpty = wpdatatables_frontend_strings.sInfoEmptyWCAG_wpdatatables;

                    if (numberOfSearchedColumn => 1) {
                        for (var i = 0; i < numberOfSearchedColumn; i++) {
                            if (tableDescription.renderFilter == "header" && !tableDescription.filterInForm) {
                                selector = getElementDataValueTH(selector1 + '[data-index=' + arrayofSearchedColumns[i] + ']');
                            } else if (tableDescription.filterInForm) {
                                selector = getElementDataValueParent(selector1 + '[data-index=' + arrayofSearchedColumns[i] + ']');
                            } else if (tableDescription.renderFilter == "footer" && !tableDescription.filterInForm) {
                                selector = $(oSettings.aoColumns[arrayofSearchedColumns[i]].nTf);
                            }
                            if (i == 0) {
                                oSettings.oLanguage.sInfo = oSettings.oLanguage.sInfo.replace('_COLUMN_', wpdatatables_frontend_strings.forWCAG_wpdatatables + thisTable.fnSettings().aoColumns[arrayofSearchedColumns[0]].sTitle + wpdatatables_frontend_strings.columnSearchWCAG_wpdatatables);
                                oSettings.oLanguage.sInfo = oSettings.oLanguage.sInfo.replace('_DATA_',
                                    selector.attr('data-value-to') == undefined && selector.attr('data-value-from') == undefined ?
                                        thisTable.fnSettings().aoPreSearchCols[arrayofSearchedColumns[0]].sSearch + ' ' :
                                        ($.inArray(selector.attr('data-value-from'), ['', undefined]) === -1 ? wpdatatables_frontend_strings.valueFromWCAG_wpdatatables + selector.attr('data-value-from') : '')
                                        + ($.inArray(selector.attr('data-value-to'), ['', undefined]) === -1 ? wpdatatables_frontend_strings.valueToWCAG_wpdatatables + selector.attr('data-value-to') : '') + ' ');
                                oSettings.oLanguage.sInfoEmpty = oSettings.oLanguage.sInfoEmpty.replace('_COLUMN_', wpdatatables_frontend_strings.forWCAG_wpdatatables + thisTable.fnSettings().aoColumns[arrayofSearchedColumns[0]].sTitle + wpdatatables_frontend_strings.columnSearchWCAG_wpdatatables);
                                oSettings.oLanguage.sInfoEmpty = oSettings.oLanguage.sInfoEmpty.replace('_DATA_',
                                    selector.attr('data-value-to') == undefined && selector.attr('data-value-from') == undefined ?
                                        thisTable.fnSettings().aoPreSearchCols[arrayofSearchedColumns[0]].sSearch + ' ' :
                                        ($.inArray(selector.attr('data-value-from'), ['', undefined]) === -1 ? wpdatatables_frontend_strings.valueFromWCAG_wpdatatables + selector.attr('data-value-from') : '')
                                        + ($.inArray(selector.attr('data-value-to'), ['', undefined]) === -1 ? wpdatatables_frontend_strings.valueToWCAG_wpdatatables + selector.attr('data-value-to') : '') + ' ');
                            } else {
                                oSettings.oLanguage.sInfo += wpdatatables_frontend_strings.andforWCAG_wpdatatables + thisTable.fnSettings().aoColumns[arrayofSearchedColumns[i]].sTitle + wpdatatables_frontend_strings.columnSearchWCAG_wpdatatables;
                                oSettings.oLanguage.sInfo += selector.attr('data-value-to') == undefined && selector.attr('data-value-from') == undefined ?
                                    thisTable.fnSettings().aoPreSearchCols[arrayofSearchedColumns[i]].sSearch + ' ' :
                                    ($.inArray(selector.attr('data-value-from'), ['', undefined]) === -1 ? wpdatatables_frontend_strings.valueFromWCAG_wpdatatables + selector.attr('data-value-from') : '')
                                    + ($.inArray(selector.attr('data-value-to'), ['', undefined]) === -1 ? wpdatatables_frontend_strings.valueToWCAG_wpdatatables + selector.attr('data-value-to') : '') + ' ';
                                oSettings.oLanguage.sInfoEmpty += wpdatatables_frontend_strings.andforWCAG_wpdatatables + thisTable.fnSettings().aoColumns[arrayofSearchedColumns[i]].sTitle + wpdatatables_frontend_strings.columnSearchWCAG_wpdatatables;
                                oSettings.oLanguage.sInfoEmpty += selector.attr('data-value-to') == undefined && selector.attr('data-value-from') == undefined ?
                                    thisTable.fnSettings().aoPreSearchCols[arrayofSearchedColumns[i]].sSearch + ' ' :
                                    ($.inArray(selector.attr('data-value-from'), ['', undefined]) === -1 ? wpdatatables_frontend_strings.valueFromWCAG_wpdatatables + selector.attr('data-value-from') : '')
                                    + ($.inArray(selector.attr('data-value-to'), ['', undefined]) === -1 ? wpdatatables_frontend_strings.valueToWCAG_wpdatatables + selector.attr('data-value-to') : '') + ' ';
                            }
                        }
                        if ($('.dataTables_filter input[type="search"]').val() != '') {
                            oSettings.oLanguage.sInfo += wpdatatables_frontend_strings.andforGloablWCAG_wpdatatables + $('.dataTables_filter input[type="search"]').val();
                            oSettings.oLanguage.sInfoEmpty += wpdatatables_frontend_strings.andforGloablWCAG_wpdatatables + $('.dataTables_filter input[type="search"]').val();
                        }
                    }
                }
            }

            function getElementDataValueTH(selector) {
                var closestElement = $(selector).closest('th');
                return closestElement;
            }

            function getElementDataValueParent(selector) {
                var closestElement = $(selector).parent();
                return closestElement;
            }

            /**
             * Helper function for adding a border around the selected column
             */
            function addOutlineBorder(tableId, columnTitle) {
                if (columnTitle.indexOf(' ') !== -1) {
                    columnTitle = columnTitle.substring(0, columnTitle.indexOf(' '));
                }
                let colgroupList = document.getElementById("colgrup-" + tableId);
                if (colgroupList) {
                    colgroupList.replaceChildren();
                    let visibleColumns = document.getElementById(tableId).tHead.getElementsByClassName('wdtheader');
                    for (column of visibleColumns) {
                        let newCol = document.createElement('col');
                        let colTitle = column.className.substring(
                            column.className.indexOf("column-") + 7,
                        );
                        colTitle = colTitle.substring(0, colTitle.indexOf(' '));
                        newCol.setAttribute('id', tableId + '-column-' + colTitle + '-col');
                        colgroupList.append(newCol);
                    }

                    let fixedTable = document.getElementById(tableId);
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
                        for (var k = 0; k < oSettings.aoColumns.length; k++) {
                            var columnName = oSettings.aoColumns[k].name;
                            columnNamesArray.push(columnName);
                        }
                        for (var i = 0; i < tableDescription.transform_value_columns.length; i++) {
                            var params = {};
                            var column = oSettings.oInstance.api().column(tableDescription.transform_value_columns[i] + ':name', {search: 'applied'});
                            var transformValueRules = {};
                            var position = 0;
                            var checkPosition = false;
                            var checkPositionFilter = false;
                            var checkPositionFilterHelper = false;
                            transformValueRules[0] = oSettings.aoColumns[column.index()].transformValueRules;
                            for (var k = 0; k < oSettings.aoColumns.length; k++) {
                                var col = oSettings.oInstance.api().column(oSettings.aoColumns[k].name + ':name', {search: 'applied'});
                                var nodes = column.nodes();
                                if (!tableDescription.serverSide && oSettings.aiDisplay.length >= oSettings._iDisplayLength && !tableDescription.groupingEnabled) {
                                    position = parseInt($(tableDescription.selector + ' tbody tr')[0].attributes[1].value) === undefined ? 0 : oSettings.aiDisplayMaster.indexOf(parseInt($(tableDescription.selector + ' tbody tr')[0].attributes[1].value));
                                    checkPosition = true;
                                } else if (!tableDescription.serverSide && tableDescription.groupingEnabled && !$(tableDescription.selector + ' tbody tr')[0].classList.contains('odd')) {
                                    position = parseInt($(tableDescription.selector + ' tbody tr')[1].attributes[1].value) === undefined ? 0 : oSettings.aiDisplayMaster.indexOf(parseInt($(tableDescription.selector + ' tbody tr')[1].attributes[1].value));
                                    checkPosition = true;
                                }
                                if (oSettings.aaSorting[0][1] === 'desc' && transformValueRules[0].includes('{' + oSettings.aoColumns[oSettings.aaSorting[0][0]].name + '.value}') && checkPosition && !tableDescription.serverSide) {
                                    if (position + 1 === oSettings.aiDisplay.length) {
                                        position = 0;
                                    }
                                }
                                ;
                                column.nodes().to$().each(function () {
                                    if (transformValueRules[0].includes('{' + oSettings.aoColumns[k].name + '.value}')) {
                                        for (var m = 0; m < col.data().length; m++) {
                                            if (tableDescription.serverSide) {
                                                position = m;
                                            }
                                            if (position >= oSettings.aiDisplay.length) {
                                                position = m;
                                            }
                                            if (!tableDescription.serverSide && m === 0 && position > oSettings.aiDisplay.length) {
                                                position = m;
                                                checkPositionFilter = false;
                                            }
                                            for (var n = 0; n < oSettings.aoColumns.length; n++) {
                                                if (oSettings.aoPreSearchCols[n].sSearch != '') {
                                                    checkPositionFilterHelper = true;
                                                }
                                            }
                                           if (!tableDescription.serverSide && checkPositionFilterHelper && !checkPositionFilter) {
                                                position = m;
                                                if ((oSettings._iDisplayLength - $(tableDescription.selector + ' tbody tr').length) > 0 ) {
                                                    position = (oSettings.aiDisplay.length - $(tableDescription.selector + ' tbody tr').length) < 0 ? 0 : oSettings.aiDisplay.length - $(tableDescription.selector + ' tbody tr').length;
                                                    checkPositionFilter = true;
                                                } else {
                                                    position = oSettings._iDisplayStart;
                                                    checkPositionFilter = true;
                                                }
                                            }
                                            if (m != 0) {
                                                if (transformValueRules[m + 1] != null) {
                                                    wdtTransformValue(transformValueRules[m + 1].replaceAll(new RegExp('\\b' + col.data()[position - 1] + '\\b', 'g'), col.data()[position] === null ? '' : col.data()[position]), params, $(this), m, oSettings.sTableId);
                                                    transformValueRules[m + 1] = transformValueRules[m + 1].replaceAll('{' + oSettings.aoColumns[k].name + '.value}', col.data()[position] === null ? '' : col.data()[position]);
                                                } else {
                                                    wdtTransformValue(transformValueRules[m].replaceAll(new RegExp('\\b' + col.data()[position - 1] + '\\b', 'g'), col.data()[position] === null ? '' : col.data()[position]), params, $(this), m, oSettings.sTableId);
                                                    transformValueRules[m + 1] = transformValueRules[m].replaceAll(col.data()[position - 1], col.data()[position] === null ? '' : col.data()[position]);
                                                }
                                            } else {
                                                if (transformValueRules[m + 1] != null) {
                                                    wdtTransformValue(transformValueRules[m + 1].replaceAll('{' + oSettings.aoColumns[k].name + '.value}', col.data()[position] === null ? '' : col.data()[position]), params, $(this), m, oSettings.sTableId);
                                                    transformValueRules[m + 1] = transformValueRules[m + 1].replaceAll('{' + oSettings.aoColumns[k].name + '.value}', col.data()[position] === null ? '' : col.data()[position]);
                                                } else {
                                                    wdtTransformValue(transformValueRules[m].replaceAll('{' + oSettings.aoColumns[k].name + '.value}', col.data()[position] === null ? '' : col.data()[position]), params, $(this), m, oSettings.sTableId);
                                                    transformValueRules[m + 1] = transformValueRules[m].replaceAll('{' + oSettings.aoColumns[k].name + '.value}', col.data()[position] === null ? '' : col.data()[position]);
                                                }
                                            }
                                            position++;
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
            $(document).on('click', '.wpdt-c .dt-button-collection .buttons-columnVisibility', function (e) {
                $('#' + tableDescription.tableId).dataTable().columnFilter(tableDescription.advancedFilterOptions);
            });

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
                    if ($(tableDescription.selector + '_edit_dialog_buttons').find('.wdt-apply-duplicate-button').hasClass('hidden')
                        || $(tableDescription.selector + '_edit_dialog_buttons').find('.wdt-apply-duplicate-button').length == 0) {
                        wpDataTablesFunctions[tableDescription.tableId].saveTableData(true, true, false);
                    } else {
                        wpDataTablesFunctions[tableDescription.tableId].saveTableData(true, true, true);
                    }

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
                            title: wpdatatables_frontend_strings.select_upload_file_wpdatatables,
                            button: {
                                text: wpdatatables_frontend_strings.choose_file_wpdatatables
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
                                title: wpdatatables_frontend_strings.select_upload_file_wpdatatables,
                                button: {
                                    text: wpdatatables_frontend_strings.choose_file_wpdatatables
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
                    if (tableDescription.table_wcag) {
                        modal.addClass('wpTableWCAG');
                    }
                    modal.find('.modal-title').html(wpdatatables_frontend_strings.edit_entry_wpdatatables);
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
                    if (tableDescription.table_wcag) {
                        modal.addClass('wpTableWCAG');
                    }
                    modal.find('.modal-title').html(wpdatatables_frontend_strings.add_new_entry_wpdatatables);
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
                    if (tableDescription.table_wcag) {
                        modal.addClass('wpTableWCAG');
                    }
                    modal.find('.modal-title').html(wpdatatables_frontend_strings.duplicate_entry_wpdatatables);
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
                    if (tableDescription.table_wcag) {
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
                                        wdtNotify(wpdatatables_edit_strings.success_common, wpdatatables_edit_strings.rowDeleted_common, 'success');
                                    } else {
                                        wdtNotify(wpdatatables_edit_strings.error_common, data.error, 'danger');
                                    }
                                },
                                error: function () {
                                    wdtNotify(wpdatatables_edit_strings.error_common, wpdatatables_edit_strings.databaseDeleteError_common, 'danger');
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
             * Correct fixed header focus for non server side tables
             */
            var wdtScrollTop = $(window).scrollTop();
            if (tableDescription.dataTableParams.fixedHeader.header && !tableDescription.serverSide) {
                $(window).on('scroll', function () {
                    var wdtCurrentScrollTop = $(this).scrollTop();
                    if (wdtCurrentScrollTop > wdtScrollTop) {
                        wpDataTables[tableDescription.tableId].fnDraw(false);
                    }
                    wdtScrollTop = wdtCurrentScrollTop;
                });
            }

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
            $(document).on('click', '.paginate_button', function () {
                var tableSelector = $(this)[0].attributes[1].value;
                if (JSON.parse($('#' + $('#' + tableSelector).data('described-by')).val()).pagination_top) {
                    function paginateScroll() {
                        $('html, body').animate({
                            scrollTop: $('#' + tableSelector + "_wrapper").offset().top
                        }, 100);
                    }

                    paginateScroll();

                    $(this).closest(tableDescription.selector + "_paginate.paginate_button").off('click').on('click', paginateScroll);
                }
            });

            // Divi integration fix for tables with fixed headers in tabs
            $('.et_pb_tabs_controls li,.et_pb_tabs_controls li a').on("click", function (e) {
                e.preventDefault();

                var tabClass = $(this).parent()[0].classList.value;
                if (tabClass.includes('controls clearfix')) {
                    return;
                }
                var selector = 'div.' + tabClass;
                var table = $(selector).find('table');
                var tblDesc = JSON.parse($('#' + $(table).data('described-by')).val());
                var oSettingsTBL = wpDataTables[tableDescription.tableId].fnSettings();

                if (tblDesc.dataTableParams.fixedHeader) {
                    setTimeout(function () {
                        if ($.fn.dataTable.isDataTable(table)) {
                            if (oSettingsTBL.oInstance.api().settings()[0]._fixedHeader !== undefined) {
                                oSettingsTBL.oInstance.api().fixedHeader.enable();
                                oSettingsTBL.oInstance.api().fixedHeader.adjust();
                            }
                        }
                    }, 1000);
                }
            });
            //[<--/ Full version -->]//
            if (tableDescription.index_column) {
                $(tableDescription.selector).DataTable().on('order.dt search.dt', function () {
                    let i = 1;
                    let pos = 0;
                    let indexcolumn = false;
                    for(var k = 0; k < tableDescription.dataTableParams.columnDefs.length ; k++){
                        if (tableDescription.dataTableParams.columnDefs[k].name == 'wdt_indexcolumn'){
                            pos = k;
                            indexcolumn = true;
                        }
                    }
                    $(tableDescription.selector).DataTable()
                        .cells(null, (indexcolumn ? pos : tableDescription.dataTableParams.columnDefs.length - 1), {})
                        .every(function (cell) {
                            this.data(i++);
                        });
                })
                    .draw();
            }
            return wpDataTables[tableDescription.tableId];

        };

        /**
         * Loop through all tables on the page and render the wpDataTables elements
         */
        $('table.wpDataTable:not(.wpdtSimpleTable)').each(function () {
            let tableObject = $('#' + $(this).data('described-by')).val();
            if (!$(this).parents('.elementor-location-popup').length) {
                if (tableObject) {
                    var tableDescription = JSON.parse(tableObject);
                    if (tableDescription.loader) {
                        if (!tableDescription.hideTableBeforeFiltering) {
                            $('.wdt-timeline-' + tableDescription.tableId).show();
                        }
                    }
                    wdtRenderDataTable($(this), tableDescription);
                }
            }
        });

        $('.wpdt-c .dataTables_length').on('shown.bs.select', function (e) {
            var currentValue = e.target.value;
            $('.wpdt-c .dataTables_length').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
                var tableSelector = e.currentTarget.id.replace('_length', '')
                if (currentValue == '0') {
                    $(document).ready(function () {
                        wpDataTables[tableSelector].api().page(0).draw(false);
                    });
                }
            });
        });
        if ($('.button-search-all-tables').length == 0) {
            $('.wdt-filter-all-tables input[type="search"]').on('keyup', function () {
                var searchText = $(this).val();
                $('table.wpDataTable:not(.wpdtSimpleTable)').each(function () {
                    var tableDescription = JSON.parse($('#' + $(this).data('described-by')).val());
                    if (tableDescription.globalSearch || $('.wdt-use-global-only').length != 0) {
                        $(this).DataTable().search(searchText).draw();
                    }
                });
            });
        } else {
            $('.button-search-all-tables').on('click', function () {
                var searchText = $(this).parent().find('.wdt-deleteicon input[type="search"]').val();
                $('table.wpDataTable:not(.wpdtSimpleTable)').each(function () {
                    var tableDescription = JSON.parse($('#' + $(this).data('described-by')).val());
                    if (tableDescription.globalSearch || $('.wdt-use-global-only').length != 0) {
                        $(this).DataTable().search(searchText).draw();
                    }
                });
            });
        }

        $('.wdt-filter-all-tables input').each(function () {
            $(this).wrap('<span class="wdt-deleteicon"></span>').after($('<span class="wdt-button-delete">x</span>').click(function () {
                $(this).prev('input').val('').trigger('change').focus();
                toggleDeleteIcon($(this).prev('input')); // Toggle visibility after clearing
                $('table.wpDataTable:not(.wpdtSimpleTable)').each(function () {
                    var tableDescription = JSON.parse($('#' + $(this).data('described-by')).val());
                    if (tableDescription.globalSearch || $('.wdt-use-global-only').length != 0) {
                        $(this).DataTable().search('').draw();
                    }
                });
            }));

            toggleDeleteIcon(this);
        });

        $('.wdt-filter-all-tables input').on('input focus blur', function () {
            toggleDeleteIcon(this);
        });

        $(document).on('elementor/popup/show', (event, id, instance) => {
            let tableObject = $('#' + $(instance.$element.find('table')).data('described-by')).val();

            if (tableObject) {
                var tableDescription = JSON.parse(tableObject);
                var tableSelector = tableDescription.selector;

                if ($(instance.$element.find('.elementor-shortcode')).length) {
                    $(instance.$element.find('.elementor-shortcode')).empty();
                } else {
                    $(instance.$element.find('.elementor-widget-container')).empty();
                }
                var formdata = {
                    table_id: tableDescription.tableWpId
                };
                jQuery.fn.dataTableExt.oStdClasses.sWrapper = "wpDataTables wpDataTablesWrapper datatables_wrapper";
                jQuery.fn.dataTable.ext.classes.sLengthSelect = 'wdt-selectpicker length_menu';
                jQuery.fn.dataTable.ext.classes.sFilterInput = 'form-control';

                $.ajax({
                    url: wdt_ajax_object.ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'wpdatatables_do_shortcode_elementor',
                        wdtNonce: $('#wdtFrontendelementorNonce_' + tableDescription.tableWpId).val(),
                        formdata: formdata,
                    },
                    success: function (response) {
                        if (response.success) {
                            if (tableDescription.serverSide) {
                                var modalHTML = $(response.data).find('.wdt-frontend-modal');
                                $('body').append(modalHTML);

                                var deleteModalHTML = $(response.data).find('#wdt-delete-modal');
                                $('body').append(deleteModalHTML);
                            }
                            response.data = response.data.replace(/table_1/g, tableDescription.tableId);
                            var tableHTML = $(response.data).find('table').first();
                            var tableDescHtml = JSON.parse($(response.data).find('input#' + $(response.data).find('table').first()[0].id + '_desc')[0].value);
                            var tableWrapperHTML = tableDescHtml.selector + '_wrapper';

                            if ($(instance.$element.find('.elementor-shortcode')).length) {
                                var wrapperHTML = `<div id="${tableWrapperHTML}" class="wpDataTables wpDataTablesWrapper datatables_wrapper">`;
                                $(tableHTML).wrap(wrapperHTML);

                                $(instance.$element.find('.elementor-shortcode')).html(response.data);
                                $(instance.$element.find('.elementor-shortcode')).find('table').remove();
                                $(instance.$element.find('.elementor-shortcode div.wpdt-c')).append($(tableHTML).parent());
                            } else {
                                var wrapperHTML = `<div id="${tableWrapperHTML}" class="wpDataTables wpDataTablesWrapper datatables_wrapper">`;
                                $(tableHTML).wrap(wrapperHTML);

                                $(instance.$element.find('.elementor-widget-container')).html(response.data);
                                $(instance.$element.find('.elementor-widget-container')).find('table').remove();
                                $(instance.$element.find('.elementor-widget-container div.wpdt-c')).append($(tableHTML).parent());
                            }
                            wdtRenderDataTable($(tableDescHtml.selector), tableDescHtml);
                        } else {
                            wdtNotify(wpdatatables_edit_strings.error_common, response.error, 'danger');
                        }
                    },
                    error: function (xhr, response) {
                        wdtNotify(wpdatatables_edit_strings.error_common, response.error, 'danger');
                    }
                });
            }
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
    jQuery(jQuery('table#' + tableID + '.wpDataTable').find('tbody td:nth-child(' + index + ')')[m]).html(trasnsformValue)
}

function wdtTransformValueResponsive(trasnsformValue, params, element, m, tableID) {
    let index = element.index() + 1;
    jQuery(element.find('.columnValue')[m]).html(trasnsformValue);
}

function toggleDeleteIcon(input) {
    var deleteIcon = jQuery(input).next('.wdt-button-delete');
    if (jQuery(input).val().trim() !== '') {
        deleteIcon.show();
    } else {
        deleteIcon.hide();
    }
}

function findSelectColumnVisibleIndex(columnDefs) {
    let visibleIndex = -1;
    let index = 0;
    columnDefs.some(function (column) {
        let isVisible = column.bVisible !== false;

        if (isVisible) {
            if (column.origHeader === "select") {
                visibleIndex = index;
                return true;
            }
            index++;
        }
        return false;
    });
    return visibleIndex;
}

jQuery.fn.dataTableExt.oStdClasses.sWrapper = "wpDataTables wpDataTablesWrapper datatables_wrapper wdt-no-display";
jQuery.fn.dataTable.ext.classes.sLengthSelect = 'wdt-selectpicker length_menu';
jQuery.fn.dataTable.ext.classes.sFilterInput = 'form-control';