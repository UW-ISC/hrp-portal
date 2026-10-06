/**
 * Webhooks admin UI for the table editor settings tab.
 */
(function ($) {
    'use strict';

    function getTableId() {
        if (typeof wpdatatable_config !== 'undefined' && wpdatatable_config.id) {
            return wpdatatable_config.id;
        }
        if (typeof wdtWebhooks !== 'undefined' && wdtWebhooks.table_id) {
            return parseInt(wdtWebhooks.table_id, 10) || 0;
        }
        return 0;
    }

    function showTabWhenReady() {
        if (getTableId() > 0) {
            $('.webhooks-settings-tab').animateFadeIn();
        }
    }

    function ajax(action, data) {
        data = data || {};
        data.action = action;
        data.nonce = wdtWebhooks.nonce;
        data.table_id = getTableId();
        return $.ajax({
            type: 'POST',
            url: wdtWebhooks.ajax_url,
            data: data
        });
    }

    function loadWebhooks() {
        var tableId = getTableId();
        if (tableId < 1) {
            return;
        }

        ajax('wpdatatables_load_webhooks').done(function (response) {
            if (response.success) {
                $('#wdt-webhooks-list-container #the-list').html(response.data.html);
            }
        });
    }

    function resetModal() {
        $('#wdt-webhook-id').val('');
        $('#wdt-webhook-name').val('');
        $('#wdt-webhook-url').val('');
        $('#wdt-webhook-event').val('row.created').selectpicker('refresh');
        $('#wdt-webhook-method').val('POST').selectpicker('refresh');
        $('#wdt-webhook-format').val('json').selectpicker('refresh');
        $('#wdt-webhook-secret').val('');
        $('#wdt-webhook-secret-hint').hide();
        $('#wdt-webhook-enabled').prop('checked', true);
        $('#wdt-webhook-headers-list').empty();
        $('#wdt-webhook-form-error').hide().text('');
        $('#wdt-webhook-modal-title').text(wdtWebhooks.i18n.addTitle || 'Add webhook');
    }

    function addHeaderRow(key, value) {
        key = key || '';
        value = value || '';
        var $row = $(
            '<div class="row m-b-5 wdt-webhook-header-row">' +
            '<div class="col-sm-5"><input type="text" class="form-control input-sm wdt-webhook-header-key" placeholder="Header name"></div>' +
            '<div class="col-sm-5"><input type="text" class="form-control input-sm wdt-webhook-header-value" placeholder="Header value"></div>' +
            '<div class="col-sm-2"><button type="button" class="btn btn-default btn-xs wdt-webhook-remove-header"><i class="wpdt-icon-times-full"></i></button></div>' +
            '</div>'
        );
        $row.find('.wdt-webhook-header-key').val(key);
        $row.find('.wdt-webhook-header-value').val(value);
        $('#wdt-webhook-headers-list').append($row);
    }

    function collectHeaders() {
        var headers = [];
        $('#wdt-webhook-headers-list .wdt-webhook-header-row').each(function () {
            var key = $.trim($(this).find('.wdt-webhook-header-key').val());
            var value = $.trim($(this).find('.wdt-webhook-header-value').val());
            if (key !== '' && value !== '') {
                headers.push({key: key, value: value});
            }
        });
        return headers;
    }

    function openAddModal() {
        resetModal();
        $('#wdt-webhook-modal').modal('show');
    }

    function openEditModal(id) {
        resetModal();
        $('#wdt-webhook-modal-title').text(wdtWebhooks.i18n.editTitle || 'Edit webhook');

        ajax('wpdatatables_get_webhook', {id: id}).done(function (response) {
            if (!response.success) {
                return;
            }
            var wh = response.data;
            $('#wdt-webhook-id').val(wh.id);
            $('#wdt-webhook-name').val(wh.name);
            $('#wdt-webhook-url').val(wh.url);
            $('#wdt-webhook-event').val(wh.event).selectpicker('refresh');
            $('#wdt-webhook-method').val(wh.method).selectpicker('refresh');
            $('#wdt-webhook-format').val(wh.format).selectpicker('refresh');
            $('#wdt-webhook-enabled').prop('checked', !!wh.enabled);
            if (wh.has_secret) {
                $('#wdt-webhook-secret-hint').show();
            }
            if (wh.headers && typeof wh.headers === 'object') {
                $.each(wh.headers, function (key, value) {
                    addHeaderRow(key, value);
                });
            }
            $('#wdt-webhook-modal').modal('show');
        });
    }

    function saveWebhook() {
        var id = $('#wdt-webhook-id').val();
        var data = {
            name: $('#wdt-webhook-name').val(),
            url: $('#wdt-webhook-url').val(),
            event: $('#wdt-webhook-event').val(),
            method: $('#wdt-webhook-method').val(),
            format: $('#wdt-webhook-format').val(),
            secret: $('#wdt-webhook-secret').val(),
            headers: collectHeaders(),
            enabled: $('#wdt-webhook-enabled').is(':checked') ? 1 : 0
        };

        var action = id ? 'wpdatatables_update_webhook' : 'wpdatatables_save_webhook';
        if (id) {
            data.id = id;
        }

        $('#wdt-webhook-form-error').hide();

        ajax(action, data).done(function (response) {
            if (!response.success) {
                var msg = (response.data && response.data.message) ? response.data.message : 'Error';
                $('#wdt-webhook-form-error').text(msg).show();
                return;
            }
            $('#wdt-webhook-modal').modal('hide');
            loadWebhooks();
        }).fail(function () {
            $('#wdt-webhook-form-error').text('Request failed.').show();
        });
    }

    function confirmDelete(id) {
        $('#wdt-delete-webhook-modal').data('webhook-id', id).modal('show');
    }

    function deleteWebhook() {
        var id = $('#wdt-delete-webhook-modal').data('webhook-id');
        ajax('wpdatatables_delete_webhook', {id: id}).done(function (response) {
            $('#wdt-delete-webhook-modal').modal('hide');
            if (response.success) {
                loadWebhooks();
            }
        });
    }

    function toggleEnabled(id, enabled) {
        ajax('wpdatatables_update_webhook_status', {
            id: id,
            enabled: enabled ? 1 : 0
        });
    }

    function testWebhook(id) {
        var $row = $('.wdt-test-webhook[data-id="' + id + '"]');
        $row.addClass('disabled');
        ajax('wpdatatables_test_webhook', {id: id}).done(function (response) {
            var msg = (response.data && response.data.message) ? response.data.message : '';
            if (response.success) {
                if (typeof wdtNotify === 'function') {
                    wdtNotify(wdtWebhooks.i18n.testSuccess || 'Success', msg, 'success');
                } else {
                    window.alert(msg || 'Test succeeded');
                }
            } else {
                if (typeof wdtNotify === 'function') {
                    wdtNotify(wdtWebhooks.i18n.testFail || 'Error', msg, 'danger');
                } else {
                    window.alert(msg || 'Test failed');
                }
            }
            loadWebhooks();
        }).always(function () {
            $row.removeClass('disabled');
        });
    }

    $(document).ready(function () {
        if (typeof wdtWebhooks === 'undefined') {
            return;
        }

        showTabWhenReady();

        // Also reveal after first save when config ID appears.
        $(document).on('click', '.wdt-save-table, #wdt-save-table, button[data-table-action="save"]', function () {
            setTimeout(showTabWhenReady, 1500);
        });

        $(document).on('click', '#wdt-add-webhook', function (e) {
            e.preventDefault();
            if (getTableId() < 1) {
                return;
            }
            openAddModal();
        });

        $(document).on('click', '#wdt-webhook-add-header', function (e) {
            e.preventDefault();
            addHeaderRow();
        });

        $(document).on('click', '.wdt-webhook-remove-header', function (e) {
            e.preventDefault();
            $(this).closest('.wdt-webhook-header-row').remove();
        });

        $(document).on('click', '#wdt-webhook-submit', function (e) {
            e.preventDefault();
            saveWebhook();
        });

        $(document).on('click', '.wdt-edit-webhook', function (e) {
            e.preventDefault();
            openEditModal($(this).data('id'));
        });

        $(document).on('click', '.wdt-delete-webhook', function (e) {
            e.preventDefault();
            confirmDelete($(this).data('id'));
        });

        $(document).on('click', '#wdt-confirm-delete-webhook', function (e) {
            e.preventDefault();
            deleteWebhook();
        });

        $(document).on('click', '.wdt-test-webhook', function (e) {
            e.preventDefault();
            testWebhook($(this).data('id'));
        });

        $(document).on('change', '.wdt-webhook-enabled-toggle', function () {
            toggleEnabled($(this).data('id'), $(this).is(':checked'));
        });
    });
})(jQuery);
