(function ($) {
    var elementsPicker = document.querySelectorAll('select.wdt-default-add-column-db-type');
    var divPicker = document.querySelectorAll('.wdt-default-add-column-db-type div.open li a span.text');
    var $connectionTypeDB = $(document).find($('#wdt-table-connection')).data('vendor');
    if ($connectionTypeDB == 'postgresql') {
        for (var i = 0; i < elementsPicker.length; i++) {
            elementsPicker[i][9].innerHTML = elementsPicker[i][9].innerHTML.replace('DATETIME', 'TIMESTAMP');
            elementsPicker[i][9].innerText = elementsPicker[i][9].innerText.replace('DATETIME', 'TIMESTAMP');
        }
        for (var i = 0; i < divPicker.length; i++) {
            divPicker[i].innerHTML = divPicker[i].innerHTML.replace('DATETIME', 'TIMESTAMP');
            divPicker[i].innerText = divPicker[i].innerText.replace('DATETIME', 'TIMESTAMP');
        }
        $("select.wdt-default-add-column-db-type option[value='TINYINT']").hide();
        $('.wdt-default-add-column-db-type div.open li[data-original-index="2"]').hide();
        $("select.wdt-default-add-column-db-type option[value='MEDIUMINT']").hide();
        $('.wdt-default-add-column-db-type div.open li[data-original-index="5"]').hide();
    }
    if ($connectionTypeDB == 'mssql') {
        $("select.wdt-default-add-column-db-type option[value='TEXT']").hide();
        $('.wdt-default-add-column-db-type div.open li[data-original-index="1"]').hide();
        $("select.wdt-default-add-column-db-type option[value='MEDIUMINT']").hide();
        $('.wdt-default-add-column-db-type div.open li[data-original-index="5"]').hide();
    }

    $(document).on('change', 'select.wdt-default-add-column-db-type', function (e) {
        var $columnBlock = $(this).closest('div.wdt-add-column-modal-block')
        var $typeValueInDatabase = typeValueInDBFromTypeInDB($(this).val());
        var $connectionTypeDB = $(document).find($('#wdt-table-connection')).data('vendor');

        $columnBlock.find('.wdt-add-column-default-value')
            .replaceWith('<input type="text" class="form-control input-sm wdt-add-column-default-value" value="">');
        $columnBlock.find('.wdt-add-column-default-value')
            .attr('type', 'text');
        $columnBlock.find('.wdt-default-add-column-db-type-value')[0].type = 'text';
        if ($.inArray($(this).val(), ['INT', 'BIGINT', 'SMALLINT', 'TINYINT', 'MEDIUMINT', 'VARCHAR']) != -1) {
            if ($(this).val() != 'VARCHAR') {
                $columnBlock.find('.wdt-add-column-default-value')[0].type = 'number';
            }
            $columnBlock.find('.wdt-default-add-column-db-type-value')[0].type = 'number';
        }

        if ($.inArray($(this).val(), ['DATE', 'DATETIME', 'TIME']) != -1) {
            $columnBlock.find('.wdt-add-column-default-value')
                .addClass('wdt-' + $(this).val().toLowerCase() + 'picker');
        } else {
            $columnBlock.find('.wdt-add-column-default-value')
                .removeClass('wdt-datepicker wdt-datetimepicker wdt-timepicker');
        }
        if ($.inArray($(this).val(), ['DATE', 'DATETIME', 'TIME', 'TEXT']) != -1 || ($.inArray($connectionTypeDB, ['mssql', 'postgresql']) != -1 &&
            $.inArray($(this).val(), ['INT', 'BIGINT', 'SMALLINT', 'TINYINT']) != -1)) {
            $columnBlock.find('.wdt-default-add-column-db-type-value').addClass('hidden')
            $columnBlock.find('#wdt-add-column-db-type-value').addClass('hidden')
        } else {
            $columnBlock.find('.wdt-default-add-column-db-type-value').removeClass('hidden')
            $columnBlock.find('#wdt-add-column-db-type-value').removeClass('hidden')
        }
        $columnBlock.find('.wdt-default-add-column-db-type-value').val($typeValueInDatabase);
        $columnBlock.find('.wdt-default-add-column-db-type').selectpicker('refresh');
    });

    $('.wdt-add-column-column-type').change(function (e) {
        var $addColumnBlock = $(this).closest('div.wdt-add-column-modal-block');
        var $possibleValuesInput = $addColumnBlock.find('.wdt-add-column-possible-values');
        var $possibleValuesBlock = $addColumnBlock.find('.wdt-add-column-possible-values-block');
        var $defaultValuesBlock = $addColumnBlock.find('.wdt-add-column-default-value-block');
        var $hiddenDefaultValuesBlock = $addColumnBlock.find('.wdt-add-hidden-default-value-block');
        var $hiddenQueryParamsValuesBlock = $addColumnBlock.find('.wdt-add-hidden-query-param-value-block');
        var $hiddenPostMetaValuesBlock = $addColumnBlock.find('.wdt-add-hidden-post-meta-value-block');
        var $hiddenACFValuesBlock = $addColumnBlock.find('.wdt-add-hidden-acf-data-value-block');
        var $possibleValueDB = $(this).val();
        var $typeInDatabase = typeNameInDatabaseForSelectedType($possibleValueDB);
        var $typeValueInDatabase = typeValueInDBFromWpcolumnType($possibleValueDB);
        var $connectionTypeDB = $(document).find($('#wdt-table-connection')).data('vendor');

        $hiddenDefaultValuesBlock.hide()
        $hiddenQueryParamsValuesBlock.hide()
        $hiddenPostMetaValuesBlock.hide()
        $hiddenACFValuesBlock.hide()
        $defaultValuesBlock.show()
        $addColumnBlock.find('.wdt-default-add-column-db-type').prop('disabled', '');

        if ($(this).val() == 'float') {
            $addColumnBlock.find('.wdt-default-add-column-db-type-value')[0].type = 'text';
        } else {
            $addColumnBlock.find('.wdt-default-add-column-db-type-value')[0].type = 'number';
        }
        if ($(this).val() == 'select' || $(this).val() == 'multiselect') {
            $possibleValuesBlock.show();
            $('.wdt-add-column-possible-values').tagsinput({
                tagClass: 'label label-primary'
            });
            $addColumnBlock.find('.wdt-add-column-default-value').selectpicker('destroy');
            $addColumnBlock.find('.wdt-default-add-column-db-type').selectpicker('val', $typeInDatabase);
            $typeInDatabase = typeNameInDatabaseForSelectedType($possibleValueDB);
            $typeValueInDatabase = typeValueInDBFromWpcolumnType($possibleValueDB);
            if ($(this).val() == 'memo' && $connectionTypeDB == 'mssql') {
                $addColumnBlock.find('.wdt-default-add-column-db-type-value').removeClass('hidden')
                $addColumnBlock.find('#wdt-add-column-db-type-value').removeClass('hidden')
            } else if (($.inArray($typeInDatabase, ['DATE', 'DATETIME', 'TIME', 'TEXT']) != -1)
                || ($.inArray($(this).val(), ['date', 'datetime', 'time', 'memo']) != -1) || ($.inArray($connectionTypeDB, ['mssql', 'postgresql']) != -1 &&
                    $.inArray($(this).val(), ['INT', 'BIGINT', 'SMALLINT', 'TINYINT']) != -1)) {
                $addColumnBlock.find('.wdt-default-add-column-db-type-value').addClass('hidden')
                $addColumnBlock.find('#wdt-add-column-db-type-value').addClass('hidden')
            } else {
                $addColumnBlock.find('.wdt-default-add-column-db-type-value').removeClass('hidden')
                $addColumnBlock.find('#wdt-add-column-db-type-value').removeClass('hidden')
            }
            $addColumnBlock.find('.wdt-default-add-column-db-type-value').val($typeValueInDatabase);
            $addColumnBlock.find('.wdt-default-add-column-db-type').selectpicker('refresh');
            $addColumnBlock.find('.wdt-add-column-default-value')
                .replaceWith('<select class="selectpicker wdt-add-column-default-value"></select>');
            if ($(this).val() == 'multiselect') {
                $addColumnBlock.find('.wdt-add-column-default-value').attr('multiple', 'multiple');
            } else {
                $addColumnBlock.find('.wdt-add-column-default-value').prepend('<option value=""></option>').removeAttr('multiple');
            }
            $addColumnBlock.find('.wdt-add-column-default-value').selectpicker();

            if ($addColumnBlock.find('.wdt-add-column-possible-values').val() != '') {
                var possibleValues = $addColumnBlock.find('.wdt-add-column-possible-values').val().split(',');
                $.each(possibleValues, function (index, value) {
                    $addColumnBlock.find('select.wdt-add-column-default-value').append('<option value="' + value + '">' + value + '</option>');
                });
                $addColumnBlock.find('select.wdt-add-column-default-value').selectpicker('refresh');
            }

            $possibleValuesInput.on('itemAdded', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                $addColumnBlock.find('select.wdt-add-column-default-value').append('<option value="' + e.item + '">' + e.item + '</option>')
                    .selectpicker('refresh');
            });

            $possibleValuesInput.on('itemRemoved', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                $addColumnBlock.find('.wdt-add-column-default-value option[value="' + e.item + '"]').remove();
                $addColumnBlock.find('.wdt-add-column-default-value').selectpicker('refresh');
            });
        } else {
            $possibleValuesBlock.hide();
            $addColumnBlock.find('.wdt-add-column-default-value').selectpicker('destroy');
            $addColumnBlock.find('.wdt-add-column-default-value')
                .replaceWith('<input type="text" class="form-control input-sm wdt-add-column-default-value" value="">');
            $addColumnBlock.find('.wdt-add-column-default-value')
                .attr('type', 'text');

            if ($.inArray($(this).val(), ['date', 'datetime', 'time']) != -1) {
                $addColumnBlock.find('.wdt-add-column-default-value')
                    .addClass('wdt-' + $(this).val().toLowerCase() + 'picker');
            } else {
                $addColumnBlock.find('.wdt-add-column-default-value')
                    .removeClass('wdt-datepicker wdt-datetimepicker wdt-timepicker');
            }
            if ($.inArray($(this).val(), ['int', 'float']) != -1) {
                $addColumnBlock.find('.wdt-add-column-default-value')
                    .attr('type', 'number');
            }
        }
        $addColumnBlock.find('.wdt-default-add-column-db-type').selectpicker('val', $typeInDatabase);
        $typeInDatabase = typeNameInDatabaseForSelectedType($possibleValueDB);
        $typeValueInDatabase = typeValueInDBFromWpcolumnType($possibleValueDB);
        if ($(this).val() == 'memo' && $connectionTypeDB == 'mssql') {
            $addColumnBlock.find('.wdt-default-add-column-db-type-value').removeClass('hidden')
            $addColumnBlock.find('#wdt-add-column-db-type-value').removeClass('hidden')
        } else if (($.inArray($typeInDatabase, ['DATE', 'DATETIME', 'TIME', 'TEXT']) != -1)
            || ($.inArray($(this).val(), ['date', 'datetime', 'time', 'memo']) != -1) || ($.inArray($connectionTypeDB, ['mssql', 'postgresql']) != -1 &&
                $.inArray($(this).val(), ['INT', 'BIGINT', 'SMALLINT', 'TINYINT']) != -1)) {
            $addColumnBlock.find('.wdt-default-add-column-db-type-value').addClass('hidden')
            $addColumnBlock.find('#wdt-add-column-db-type-value').addClass('hidden')
        } else {
            $addColumnBlock.find('.wdt-default-add-column-db-type-value').removeClass('hidden')
            $addColumnBlock.find('#wdt-add-column-db-type-value').removeClass('hidden')
        }
        $addColumnBlock.find('.wdt-default-add-column-db-type-value').val($typeValueInDatabase);
        $addColumnBlock.find('.wdt-default-add-column-db-type').selectpicker('refresh');
        if ($(this).val() == 'hidden') {
            $possibleValuesBlock.hide();
            $defaultValuesBlock.hide();
            $hiddenDefaultValuesBlock.show()
            $addColumnBlock.find('.wdt-default-add-column-db-type').prop('disabled', 'disabled');
            $addColumnBlock.find('.wdt-add-hidden-default-value').selectpicker('refresh');
        }
    });

    $('.wdt-add-hidden-default-value').on('change', function () {
        if ($(this).val() == 'query-param') {
            $(this).closest('div.wdt-add-column-modal-block')
                .find('.wdt-add-hidden-post-meta-value-block')
                .hide()
                .closest('div.wdt-add-column-modal-block')
                .find('.wdt-add-hidden-acf-data-value-block')
                .hide()
                .closest('div.wdt-add-column-modal-block')
                .find('.wdt-add-hidden-query-param-value-block')
                .show()
        } else if ($(this).val() == 'post-meta') {
            $(this).closest('div.wdt-add-column-modal-block')
                .find('.wdt-add-hidden-query-param-value-block')
                .hide()
                .closest('div.wdt-add-column-modal-block')
                .find('.wdt-add-hidden-post-meta-value-block')
                .show()
                .closest('div.wdt-add-column-modal-block')
                .find('.wdt-add-hidden-acf-data-value-block')
                .hide()

        } else if ($(this).val() == 'acf-data') {
            $(this).closest('div.wdt-add-column-modal-block')
                .find('.wdt-add-hidden-query-param-value-block')
                .hide()
                .closest('div.wdt-add-column-modal-block')
                .find('.wdt-add-hidden-post-meta-value-block')
                .hide()
                .closest('div.wdt-add-column-modal-block')
                .find('.wdt-add-hidden-acf-data-value-block')
                .show()
        } else {
            $(this).closest('div.wdt-add-column-modal-block')
                .find('.wdt-add-hidden-query-param-value-block')
                .hide()
                .closest('div.wdt-add-column-modal-block')
                .find('.wdt-add-hidden-query-param-value')
                .val('')
                .closest('div.wdt-add-column-modal-block')
                .find('.wdt-add-hidden-post-meta-value-block')
                .hide()
                .closest('div.wdt-add-column-modal-block')
                .find('.wdt-add-hidden-post-meta-value')
                .val('')
                .closest('div.wdt-add-column-modal-block')
                .find('.wdt-add-hidden-acf-data-value-block')
                .hide()
                .closest('div.wdt-add-column-modal-block')
                .find('.wdt-add-hidden-acf-data-value')
                .val('')
        }
    });
    $('#wdt-add-column-submit').click(function () {

        var $addColumnModal = $('.wdt-add-column-modal-block');

        if ($('div#wdt-add-column-modal #wdt-add-column-column-header').val() == '') {
            wdtNotify(wpdatatables_add_remove_column_strings.errorAddRemoveColumn, wpdatatables_add_remove_column_strings.columnHeaderEmptyAddRemoveColumn, 'danger');
            return false;
        }

        if (!($.inArray($(document).find('.wdt-default-add-column-db-type').selectpicker('val'), ['DATE', 'DATETIME', 'TIME', 'TEXT']) != -1)) {
            if ($(document).find('.wdt-default-add-column-db-type-value').val() == '' || ($(document).find('.wdt-default-add-column-db-type').selectpicker('val') == 'VARCHAR' && parseInt($(document).find('.wdt-default-add-column-db-type-value').val()) > '4294967295')) {
                wdtNotify(wpdatatables_add_remove_column_strings.errorAddRemoveColumn, wpdatatables_add_remove_column_strings.outOfRangeTypeValueAddRemoveColumn, 'danger');
                return false;
            }
        }

        var columnType = $addColumnModal.find('.wdt-add-column-column-type').selectpicker('val');
        var defaultValue = $.inArray(columnType, ['select', 'multiselect']) != -1 && $(document).find('.wdt-default-add-column-db-type').selectpicker('val') == 'VARCHAR' ?
            $addColumnModal.find('.wdt-add-column-default-value').selectpicker('val') :
            $addColumnModal.find('.wdt-add-column-default-value').val();
        if (defaultValue != null && columnType == 'multiselect' && $(document).find('.wdt-default-add-column-db-type').selectpicker('val') == 'VARCHAR') {
            defaultValue.join('|');
        }
        if (defaultValue != null && (columnType === 'time' || columnType === 'datetime')) {
            let pattern = /^.*:[0-9]{2}:[0-9]{1}$/;
            if (pattern.test(defaultValue)) {
                defaultValue = defaultValue.replace(/:(\d)$/, ':0$1');
            }
        }
        if (columnType == 'hidden' && !$addColumnModal.find('.wdt-add-hidden-default-value').length) {
            wdtNotify(wpdatatables_add_remove_column_strings.error, wpdatatables_add_remove_column_strings.hiddenColumnNotAllowed, 'danger');
            return false;
        }

        var hiddenDefaultValue = '';
        if (columnType == 'hidden' && $addColumnModal.find('.wdt-add-hidden-default-value').length) {
            hiddenDefaultValue = $addColumnModal.find('.wdt-add-hidden-default-value').selectpicker('val');
            if ($.inArray(hiddenDefaultValue, ['query-param', 'post-meta', 'acf-data']) != -1) {
                hiddenDefaultValue += ":" + $addColumnModal.find('.wdt-add-hidden-' + hiddenDefaultValue + '-value').val();
            }
        }

        var newColumnData = {
            name: $('#wdt-add-column-column-header').val(),
            type: columnType,
            insert_after: $('.wdt-add-column-insert-after').selectpicker('val'),
            predefined_type_in_db: $(document).find('.wdt-default-add-column-db-type').selectpicker('val'),
            predefined_type_value_in_db: $(document).find('.wdt-default-add-column-db-type-value').val(),
            possible_values: $addColumnModal.find('.wdt-add-column-possible-values').val().replace(/,/g, '|'),
            default_value: defaultValue,
            fill_default: $('#wdt-add-column-fill-with-default').is(':checked') ? 1 : 0,
            hidden_default_value: columnType == 'hidden' ? hiddenDefaultValue : ''
        };

        $('#wdt-add-column-modal').find('.wdt-preload-layer').animateFadeIn();

        var tableWpID = typeof $('table.wpDataTable').data('wpdatatable_id') !== 'undefined' ?
            $('table.wpDataTable').data('wpdatatable_id') : $('.wpExcelTable').data('wpdatatable_id');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpdatatables_add_new_manual_column',
                table_id: tableWpID,
                wdtNonce: $('#wdtNonceFrontendEdit_' + tableWpID).val(),
                column_data: newColumnData
            },
            success: function () {
                $('#wdt-add-column-modal').find('.wdt-preload-layer').animateFadeOut();
                wdtNotify(wpdatatables_add_remove_column_strings.successAddRemoveColumn, wpdatatables_add_remove_column_strings.columnAddedAddRemoveColumn, 'success');
                setTimeout(function () {
                    $('#wdt-add-column-modal').modal('hide');
                    window.location.reload(true);
                }, 1500);
            }
        });

    });

    /**
     * Remove a column
     */
    $('#wdt-remove-column-submit').click(function (e) {
        e.preventDefault();

        if ($('#wdt-remove-column-confirm').is(':checked') == false) {
            wdtNotify(wpdatatables_add_remove_column_strings.errorAddRemoveColumn, wpdatatables_add_remove_column_strings.columnRemoveConfirmAddRemoveColumn, 'danger');
            return false;
        }

        $('#wdt-remove-column-modal').find('.wdt-preload-layer').animateFadeIn();

        var tableWpID = typeof $('table.wpDataTable').data('wpdatatable_id') !== 'undefined' ?
            $('table.wpDataTable').data('wpdatatable_id') : $('.wpExcelTable').data('wpdatatable_id');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpdatatables_delete_manual_column',
                table_id: tableWpID,
                wdtNonce: $('#wdtNonceFrontendEdit_' + tableWpID).val(),
                column_name: $('#wdtDeleteColumnSelect').val()
            },
            success: function () {
                $('#wdt-remove-column-modal').find('.wdt-preload-layer').animateFadeOut();
                wdtNotify(wpdatatables_add_remove_column_strings.successAddRemoveColumn, wpdatatables_add_remove_column_strings.columnRemovedAddRemoveColumn, 'success');
                setTimeout(function () {
                    $('#wdt-remove-column-modal').modal('hide');
                    window.location.reload(true);
                }, 1500);
            }
        });

    });

})(jQuery);
