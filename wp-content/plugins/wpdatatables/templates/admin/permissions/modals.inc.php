<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
/**
 * Shared Add/Edit Permission modal (Role | User) for Tables and Charts tabs.
 *
 * Item options and permission checkboxes are filled by permissions-admin.js from
 * wdtPermissions / meta AJAX.
 */
?>

<!-- Add/Edit Permission Modal -->
<div class="modal fade wpdt-c" id="wdt-permission-modal" data-backdrop="static" data-keyboard="false" tabindex="-1"
     role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php include WDT_TEMPLATE_PATH . 'admin/common/preloader.inc.php'; ?>

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="wpdt-icon-times-full"></i></span>
                </button>
                <h4 class="modal-title" id="wdt-permission-modal-title">
                    <?php esc_html_e('Add Permission', 'wpdatatables'); ?>
                </h4>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12" id="wdt-permission-target-type-wrap">
                        <h5 class="c-black m-b-10">
                            <?php esc_html_e('Target type', 'wpdatatables'); ?>
                        </h5>
                        <div class="form-group wdt-permission-target-type">
                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-permission-target-role" type="radio"
                                               name="wdt-permission-target-type" value="role" checked>
                                        <label for="wdt-permission-target-role" class="ts-label">
                                            <?php esc_html_e('Role', 'wpdatatables'); ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-permission-target-user" type="radio"
                                               name="wdt-permission-target-type" value="user">
                                        <label for="wdt-permission-target-user" class="ts-label">
                                            <?php esc_html_e('User', 'wpdatatables'); ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12" id="wdt-permission-roles-wrap">
                        <h5 class="c-black m-b-10">
                            <?php esc_html_e('Select roles', 'wpdatatables'); ?>
                        </h5>
                        <div class="form-group">
                            <div class="fg-line">
                                <div class="select">
                                    <select class="selectpicker" id="wdt-permission-roles-select" multiple
                                            data-live-search="true"
                                            title="<?php esc_attr_e('Select roles', 'wpdatatables'); ?>">
                                    </select>
                                </div>
                            </div>
                            <small class="text-danger" id="wdt-permission-roles-error" style="display: none;">
                                <?php esc_html_e('Please select at least one role', 'wpdatatables'); ?>
                            </small>
                        </div>
                    </div>

                    <div class="col-sm-12" id="wdt-permission-users-wrap" style="display: none;">
                        <h5 class="c-black m-b-10">
                            <?php esc_html_e('Select users', 'wpdatatables'); ?>
                        </h5>
                        <div class="form-group">
                            <div class="fg-line">
                                <div class="select">
                                    <select class="selectpicker" id="wdt-permission-users-select" multiple
                                            data-live-search="true"
                                            title="<?php esc_attr_e('Select users', 'wpdatatables'); ?>">
                                    </select>
                                </div>
                            </div>
                            <small class="text-danger" id="wdt-permission-users-error" style="display: none;">
                                <?php esc_html_e('Please select at least one user', 'wpdatatables'); ?>
                            </small>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <h5 class="c-black m-b-10">
                            <?php esc_html_e('Permissions', 'wpdatatables'); ?>
                        </h5>
                        <div class="form-group" id="wdt-permission-checkboxes">
                            <!-- Filled from catalog -->
                        </div>
                        <small class="text-danger" id="wdt-permission-perms-error" style="display: none;">
                            <?php esc_html_e('Please select at least one permission', 'wpdatatables'); ?>
                        </small>
                    </div>

                    <div class="col-sm-12">
                        <div class="toggle-switch" data-ts-color="blue">
                            <input id="wdt-enable-specific-items" type="checkbox">
                            <label for="wdt-enable-specific-items" class="ts-label" id="wdt-enable-specific-items-label">
                                <?php esc_html_e('Limit to specific tables', 'wpdatatables'); ?>
                            </label>
                        </div>
                        <p class="m-t-5 m-b-10">
                            <small id="wdt-enable-specific-items-help">
                                <?php esc_html_e('If unchecked, permissions apply to all tables.', 'wpdatatables'); ?>
                            </small>
                        </p>
                    </div>

                    <div class="col-sm-12" id="wdt-specific-items-container" style="display: none;">
                        <h5 class="c-black m-b-10" id="wdt-specific-items-heading">
                            <?php esc_html_e('Select tables', 'wpdatatables'); ?>
                        </h5>
                        <div class="form-group">
                            <small class="text-danger" id="wdt-permission-items-error" style="display: none;">
                                <?php esc_html_e('Please select at least one item', 'wpdatatables'); ?>
                            </small>
                            <div class="fg-line">
                                <div class="select">
                                    <select class="selectpicker" id="wdt-permission-items-select" multiple
                                            data-live-search="true">
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <small class="text-danger form-general-error" style="display: none;"></small>
                <hr>
                <button type="button" class="btn btn-danger btn-icon-text" data-dismiss="modal">
                    <?php esc_html_e('Cancel', 'wpdatatables'); ?>
                </button>
                <button type="button" class="btn btn-primary btn-icon-text" id="wdt-permission-modal-submit">
                    <i class="wpdt-icon-save"></i>
                    <?php esc_html_e('Save', 'wpdatatables'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Permission Modal -->
<div class="modal fade wpdt-c in" id="wdt-delete-permission-modal" style="display: none" data-backdrop="static"
     data-keyboard="false"
     tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="wpdt-icon-times-full"></i></span>
                </button>
                <h4 class="modal-title"><?php esc_html_e('Are you sure?', 'wpdatatables') ?></h4>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <small><?php esc_html_e('Please confirm deletion. There is no undo!', 'wpdatatables'); ?></small>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <hr>
                <button type="button" class="btn btn-icon-text" data-dismiss="modal">
                    <?php esc_html_e('Cancel', 'wpdatatables'); ?>
                </button>
                <button type="button" class="btn btn-danger btn-icon-text" id="wdt-confirm-delete-permission">
                    <i class="wpdt-icon-trash"></i>
                    <?php esc_html_e('Delete', 'wpdatatables'); ?>
                </button>
            </div>
        </div>
    </div>
</div>
