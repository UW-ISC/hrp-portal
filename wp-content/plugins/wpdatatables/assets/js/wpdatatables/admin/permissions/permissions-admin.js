/**
 * Permissions Admin Page JavaScript — Role | User access rules.
 */

(function ($) {
    'use strict';

    var metaCache = null;
    var rowActionsBound = false;

    function getCurrentTab() {
        var urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('tab') || 'tables';
    }

    function i18n(key, fallback) {
        if (wdtPermissions.i18n && wdtPermissions.i18n[key]) {
            return wdtPermissions.i18n[key];
        }
        return fallback || key;
    }

    function catalogForTab(tab) {
        if (wdtPermissions.catalog && wdtPermissions.catalog[tab]) {
            return wdtPermissions.catalog[tab];
        }
        return [];
    }

    function loadManagersData() {
        var tab = getCurrentTab();
        $.ajax({
            type: 'POST',
            url: wdtPermissions.ajax_url,
            data: (function () {
                var params = {
                    action: 'wpdatatables_load_permissions',
                    tab: tab,
                    nonce: wdtPermissions.nonce
                };
                var $search = $('.wpdt-search-box input[name="s"]');
                if ($search.length) {
                    params.s = $search.val();
                } else {
                    var urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.has('s')) {
                        params.s = urlParams.get('s');
                    }
                }
                var urlParams2 = new URLSearchParams(window.location.search);
                if (urlParams2.has('orderby')) {
                    params.orderby = urlParams2.get('orderby');
                }
                if (urlParams2.has('order')) {
                    params.order = urlParams2.get('order');
                }
                if (urlParams2.has('paged')) {
                    params.paged = urlParams2.get('paged');
                }
                return params;
            })(),
            success: function (response) {
                if (response.success) {
                    $('#the-list').html(response.data.html);
                    bindRowActions();
                } else {
                    $('#the-list').html(
                        '<tr><td colspan="7" style="text-align: center; padding: 24px; color: red;">' +
                        'Error loading permissions: ' + (response.data && response.data.message ? response.data.message : '') +
                        '</td></tr>'
                    );
                }
            },
            error: function () {
                $('#the-list').html(
                    '<tr><td colspan="7" style="text-align: center; padding: 24px; color: red;">' +
                    'Error loading permissions.' +
                    '</td></tr>'
                );
            }
        });
    }

    function fetchMeta(callback) {
        var tab = getCurrentTab();
        if (metaCache && metaCache.resource === tab) {
            callback(metaCache);
            return;
        }

        $.ajax({
            type: 'POST',
            url: wdtPermissions.ajax_url,
            data: {
                action: 'wpdatatables_permissions_meta',
                tab: tab,
                nonce: wdtPermissions.nonce
            },
            success: function (response) {
                if (response.success) {
                    metaCache = response.data;
                    callback(metaCache);
                } else {
                    $('#wdt-permission-modal .form-general-error')
                        .text(response.data && response.data.message ? response.data.message : 'Error loading meta')
                        .show();
                }
            },
            error: function () {
                $('#wdt-permission-modal .form-general-error').text('Error loading meta').show();
            }
        });
    }

    function bindRowActions() {
        if (rowActionsBound) {
            return;
        }
        rowActionsBound = true;

        $(document).on('click', '.wdt-edit-permission', function (e) {
            e.preventDefault();
            editPermission($(this).data('id'));
        });

        $(document).on('click', '.wdt-delete-permission', function (e) {
            e.preventDefault();
            $('#wdt-delete-permission-modal').data('rule-id', $(this).data('id'));
            $('#wdt-delete-permission-modal').modal('show');
        });

        initTableSorting();
    }

    function initTableSorting() {
        $('.wdt-permissions-table thead th.sortable, .wdt-permissions-table thead th.sorted').off('click').on('click', function (e) {
            e.preventDefault();
            var $th = $(this);
            var columnIndex = $th.index();
            var isAsc = $th.hasClass('asc');

            $('.wdt-permissions-table thead th').removeClass('sorted asc desc');
            $th.addClass('sorted');
            if (isAsc) {
                $th.removeClass('asc').addClass('desc');
            } else {
                $th.removeClass('desc').addClass('asc');
            }
            sortTable(columnIndex, !isAsc);
        });
    }

    function sortTable(columnIndex, ascending) {
        var $tbody = $('.wdt-permissions-table tbody');
        var rows = $tbody.find('tr').toArray();

        rows.sort(function (a, b) {
            var aValue = $(a).find('td').eq(columnIndex).text().trim();
            var bValue = $(b).find('td').eq(columnIndex).text().trim();
            var aNum = parseFloat(aValue);
            var bNum = parseFloat(bValue);

            if (!isNaN(aNum) && !isNaN(bNum)) {
                return ascending ? aNum - bNum : bNum - aNum;
            }
            if (ascending) {
                return aValue.localeCompare(bValue);
            }
            return bValue.localeCompare(aValue);
        });

        $tbody.html(rows);
    }

    function renderPermissionCheckboxes(catalog, selected) {
        selected = selected || [];
        var $wrap = $('#wdt-permission-checkboxes').empty();
        catalog.forEach(function (def, index) {
            var id = 'wdt-perm-' + def.key;
            var checked = selected.indexOf(def.key) !== -1 || (selected.length === 0 && def.key.indexOf('view_') === 0);
            var $row = $('<div class="toggle-switch m-b-10" data-ts-color="blue"></div>');
            $row.append(
                $('<input type="checkbox">')
                    .attr({ id: id, value: def.key })
                    .prop('checked', checked)
                    .addClass('wdt-permission-key')
            );
            $row.append(
                $('<label class="ts-label"></label>').attr('for', id).text(def.label)
            );
            $wrap.append($row);
        });
    }

    function populateRolesSelect(roles, selectedSlugs) {
        selectedSlugs = selectedSlugs || [];
        var $select = $('#wdt-permission-roles-select').empty();
        roles.forEach(function (role) {
            $select.append(
                $('<option></option>')
                    .val(role.slug)
                    .text(role.name)
                    .prop('selected', selectedSlugs.indexOf(role.slug) !== -1)
            );
        });
        $select.selectpicker('refresh');
    }

    function populateItemsSelect(items, selectedIds) {
        selectedIds = (selectedIds || []).map(String);
        var $select = $('#wdt-permission-items-select').empty();
        items.forEach(function (item) {
            $select.append(
                $('<option></option>')
                    .val(String(item.id))
                    .text(item.title)
                    .prop('selected', selectedIds.indexOf(String(item.id)) !== -1)
            );
        });
        $select.selectpicker('refresh');
    }

    function populateUsersSelect(users, selectedIds) {
        selectedIds = (selectedIds || []).map(String);
        var $select = $('#wdt-permission-users-select');
        var existing = {};
        $select.find('option').each(function () {
            existing[String($(this).val())] = true;
        });

        users.forEach(function (user) {
            var id = String(user.id);
            if (existing[id]) {
                return;
            }
            $select.append(
                $('<option></option>')
                    .val(id)
                    .text(user.label || user.login)
                    .prop('selected', selectedIds.indexOf(id) !== -1)
            );
            existing[id] = true;
        });

        selectedIds.forEach(function (id) {
            if (!existing[id]) {
                return;
            }
            $select.find('option[value="' + id + '"]').prop('selected', true);
        });

        $select.selectpicker('refresh');
    }

    function searchUsers(term) {
        $.ajax({
            type: 'POST',
            url: wdtPermissions.ajax_url,
            data: {
                action: 'wpdatatables_search_permission_users',
                search: term || '',
                nonce: wdtPermissions.nonce
            },
            success: function (response) {
                if (response.success && response.data.users) {
                    var selected = $('#wdt-permission-users-select').val() || [];
                    populateUsersSelect(response.data.users, selected);
                }
            }
        });
    }

    function setTargetType(type, lock) {
        $('input[name="wdt-permission-target-type"][value="' + type + '"]').prop('checked', true);
        if (type === 'role') {
            $('#wdt-permission-roles-wrap').show();
            $('#wdt-permission-users-wrap').hide();
        } else {
            $('#wdt-permission-roles-wrap').hide();
            $('#wdt-permission-users-wrap').show();
        }
        if (lock) {
            $('#wdt-permission-target-type-wrap').hide();
            $('#wdt-permission-roles-select, #wdt-permission-users-select').prop('disabled', true);
        } else {
            $('#wdt-permission-target-type-wrap').show();
            $('#wdt-permission-roles-select, #wdt-permission-users-select').prop('disabled', false);
        }
        $('#wdt-permission-roles-select, #wdt-permission-users-select').selectpicker('refresh');
    }

    function applyTabCopy(tab) {
        if (tab === 'charts') {
            $('#wdt-enable-specific-items-label').text(i18n('limit_charts', 'Limit to specific charts'));
            $('#wdt-enable-specific-items-help').text(i18n('limit_charts_help', 'If unchecked, permissions apply to all charts.'));
            $('#wdt-specific-items-heading').text(i18n('select_charts', 'Select charts'));
        } else {
            $('#wdt-enable-specific-items-label').text(i18n('limit_tables', 'Limit to specific tables'));
            $('#wdt-enable-specific-items-help').text(i18n('limit_tables_help', 'If unchecked, permissions apply to all tables.'));
            $('#wdt-specific-items-heading').text(i18n('select_tables', 'Select tables'));
        }
    }

    function resetModal() {
        var tab = getCurrentTab();
        $('#wdt-permission-modal').removeData('rule-id');
        $('#wdt-permission-modal .form-general-error').hide().text('');
        $('#wdt-permission-roles-error, #wdt-permission-users-error, #wdt-permission-perms-error, #wdt-permission-items-error').hide();
        $('#wdt-enable-specific-items').prop('checked', false);
        $('#wdt-specific-items-container').hide();
        $('#wdt-permission-roles-select').val([]).empty();
        $('#wdt-permission-users-select').val([]).empty();
        $('#wdt-permission-items-select').val([]).empty();
        setTargetType('role', false);
        applyTabCopy(tab);
        renderPermissionCheckboxes(catalogForTab(tab), []);
    }

    function openAddManagerModal() {
        resetModal();
        fetchMeta(function (meta) {
            populateRolesSelect(meta.roles || [], []);
            populateItemsSelect(meta.items || [], []);
            renderPermissionCheckboxes(meta.catalog || catalogForTab(getCurrentTab()), []);
            searchUsers('');
            $('#wdt-permission-modal-title').text(i18n('add_permission', 'Add Permission'));
            $('#wdt-permission-modal').modal('show');
        });
    }

    function editPermission(ruleId) {
        var tab = getCurrentTab();
        resetModal();

        $.ajax({
            type: 'POST',
            url: wdtPermissions.ajax_url,
            data: {
                action: 'wpdatatables_get_permission',
                rule_id: ruleId,
                tab: tab,
                nonce: wdtPermissions.nonce
            },
            success: function (response) {
                if (!response.success) {
                    return;
                }
                var perm = response.data;
                fetchMeta(function (meta) {
                    populateRolesSelect(meta.roles || [], []);
                    populateItemsSelect(meta.items || [], perm.item_ids || []);
                    renderPermissionCheckboxes(meta.catalog || catalogForTab(tab), perm.permissions || []);

                    if (perm.type === 'role') {
                        setTargetType('role', true);
                        populateRolesSelect(meta.roles || [], [perm.role_slug]);
                    } else {
                        setTargetType('user', true);
                        populateUsersSelect([{
                            id: perm.user_id,
                            login: perm.principal,
                            email: perm.email || '',
                            label: perm.email ? (perm.principal + ' (' + perm.email + ')') : perm.principal
                        }], [perm.user_id]);
                    }

                    if (perm.all_items) {
                        $('#wdt-enable-specific-items').prop('checked', false);
                        $('#wdt-specific-items-container').hide();
                    } else {
                        $('#wdt-enable-specific-items').prop('checked', true);
                        $('#wdt-specific-items-container').show();
                        $('#wdt-permission-items-select').val((perm.item_ids || []).map(String)).selectpicker('refresh');
                    }

                    $('#wdt-permission-modal').data('rule-id', ruleId);
                    $('#wdt-permission-modal-title').text(i18n('edit_permission', 'Edit Permission'));
                    $('#wdt-permission-modal').modal('show');
                });
            }
        });
    }

    function collectCheckedPermissions() {
        var perms = [];
        $('#wdt-permission-checkboxes .wdt-permission-key:checked').each(function () {
            perms.push($(this).val());
        });
        return perms;
    }

    function savePermission() {
        var tab = getCurrentTab();
        var ruleId = $('#wdt-permission-modal').data('rule-id');
        var targetType = $('input[name="wdt-permission-target-type"]:checked').val() || 'role';
        var permissions = collectCheckedPermissions();
        var enableSpecific = $('#wdt-enable-specific-items').is(':checked') ? 1 : 0;
        var itemIds = enableSpecific ? ($('#wdt-permission-items-select').val() || []) : [];

        $('#wdt-permission-roles-error, #wdt-permission-users-error, #wdt-permission-perms-error, #wdt-permission-items-error').hide();
        $('#wdt-permission-modal .form-general-error').hide().text('');

        if (!ruleId) {
            if (targetType === 'role') {
                var roles = $('#wdt-permission-roles-select').val() || [];
                if (!roles.length) {
                    $('#wdt-permission-roles-error').show();
                    return;
                }
            } else {
                var users = $('#wdt-permission-users-select').val() || [];
                if (!users.length) {
                    $('#wdt-permission-users-error').show();
                    return;
                }
            }
        }

        if (!permissions.length) {
            $('#wdt-permission-perms-error').show();
            return;
        }

        if (enableSpecific && !itemIds.length) {
            $('#wdt-permission-items-error').show();
            return;
        }

        var data = {
            action: ruleId ? 'wpdatatables_update_permission' : 'wpdatatables_save_permission',
            tab: tab,
            permissions: permissions,
            enable_specific: enableSpecific,
            item_ids: itemIds,
            nonce: wdtPermissions.nonce
        };

        if (ruleId) {
            data.rule_id = ruleId;
        } else {
            data.target_type = targetType;
            if (targetType === 'role') {
                data.role_slugs = $('#wdt-permission-roles-select').val() || [];
            } else {
                data.user_ids = $('#wdt-permission-users-select').val() || [];
            }
        }

        $.ajax({
            type: 'POST',
            url: wdtPermissions.ajax_url,
            data: data,
            success: function (response) {
                if (response.success) {
                    metaCache = null;
                    $('#wdt-permission-modal').modal('hide');
                    loadManagersData();
                } else {
                    $('#wdt-permission-modal .form-general-error')
                        .text((response.data && response.data.message) ? response.data.message : 'Error saving permission')
                        .show();
                }
            },
            error: function () {
                $('#wdt-permission-modal .form-general-error').text('Error saving permission.').show();
            }
        });
    }

    function confirmDeletePermission() {
        var ruleId = $('#wdt-delete-permission-modal').data('rule-id');
        var tab = getCurrentTab();

        $.ajax({
            type: 'POST',
            url: wdtPermissions.ajax_url,
            data: {
                action: 'wpdatatables_delete_permission',
                rule_id: ruleId,
                tab: tab,
                nonce: wdtPermissions.nonce
            },
            success: function (response) {
                $('#wdt-delete-permission-modal').modal('hide');
                if (response.success) {
                    metaCache = null;
                    loadManagersData();
                } else {
                    window.alert('Error deleting permission: ' + (response.data && response.data.message ? response.data.message : ''));
                }
            },
            error: function () {
                window.alert('Error deleting permission.');
            }
        });
    }

    $(document).ready(function () {
        loadManagersData();

        var doSearch = function () {
            loadManagersData();
        };
        var debounceFn = null;
        if (typeof _ !== 'undefined' && typeof _.debounce === 'function') {
            debounceFn = _.debounce(doSearch, 800);
        } else {
            (function () {
                var timer = null;
                debounceFn = function () {
                    clearTimeout(timer);
                    timer = setTimeout(doSearch, 800);
                };
            })();
        }

        $(document).on('keyup input', 'input#search_id-search-input, .wpdt-search-box input[name="s"]', function () {
            debounceFn();
        });

        $(document).on('click', '#search-submit', function (e) {
            e.preventDefault();
            loadManagersData();
        });

        $('#wdt-add-manager-btn').on('click', function (e) {
            e.preventDefault();
            openAddManagerModal();
        });

        $(document).on('change', 'input[name="wdt-permission-target-type"]', function () {
            setTargetType($(this).val(), false);
            if ($(this).val() === 'user') {
                searchUsers('');
            }
        });

        $('#wdt-enable-specific-items').on('change', function () {
            if ($(this).is(':checked')) {
                $('#wdt-specific-items-container').slideDown();
            } else {
                $('#wdt-specific-items-container').slideUp();
            }
        });

        // Live-search users when bootstrap-select search is used.
        $(document).on('keyup', '.bootstrap-select .bs-searchbox input', function () {
            var $select = $(this).closest('.bootstrap-select').find('select');
            if ($select.attr('id') === 'wdt-permission-users-select') {
                searchUsers($(this).val());
            }
        });

        $('#wdt-permission-modal-submit').on('click', function (e) {
            e.preventDefault();
            savePermission();
        });

        $('#wdt-confirm-delete-permission').on('click', function (e) {
            e.preventDefault();
            confirmDeletePermission();
        });

        $('.tab-nav a').on('click', function () {
            metaCache = null;
        });
    });

})(jQuery);
