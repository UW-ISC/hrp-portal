<?php defined('ABSPATH') or die('Access denied.'); ?>

<div class="wrap wdt-datatables-admin-wrap">
    <!-- .container -->
    <div class="container">
        <!-- .row -->
        <div class="row">
            <div class="card plugin-settings wdt-browse-table">
                <div class="card-header wdt-admin-card-header ch-alt">
                    <img id="wpdt-inline-logo" src="<?php echo esc_url(WDT_ROOT_URL); ?>assets/img/logo.svg"/>
                    <h2>
                        <span style="display: none">wpDataTables</span>
                        <?php esc_html_e('Permissions', 'wpdatatables'); ?>
                    </h2>
                    <ul class="actions p-t-5">
                        <li>
                            <button class="btn btn-primary wdt-add-manager-btn" id="wdt-add-manager-btn">
                                <i class="wpdt-icon-plus"></i>
                                <?php esc_html_e('Add Permission', 'wpdatatables'); ?>
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body card-padding p-t-10">
                    <div class="alert alert-info m-b-15">
                        <strong><?php esc_html_e('WordPress Capabilities', 'wpdatatables'); ?></strong><br>
                        <?php esc_html_e('Assign Role or User access rules for tables and charts. Global grants (all items) are mirrored as WordPress capabilities so Members / User Role Editor can see them. Item-scoped rules stay in wpDataTables only and are not mirrored as blanket caps.', 'wpdatatables'); ?>
                        <br><br>
                        <strong><?php esc_html_e('Tables:', 'wpdatatables'); ?></strong>
                        <code>wpdt_list_tables</code>,
                        <code>wpdt_create_tables</code>,
                        <code>wpdt_edit_tables</code>,
                        <code>wpdt_delete_tables</code>,
                        <code>wpdt_view_tables</code>
                        <br>
                        <strong><?php esc_html_e('Charts:', 'wpdatatables'); ?></strong>
                        <code>wpdt_list_charts</code>,
                        <code>wpdt_create_charts</code>,
                        <code>wpdt_edit_charts</code>,
                        <code>wpdt_delete_charts</code>,
                        <code>wpdt_view_charts</code>
                        <br>
                        <strong><?php esc_html_e('Menu access:', 'wpdatatables'); ?></strong>
                        <code>wpdt_access_plugin</code>
                        <?php esc_html_e('(granted automatically when a principal has any wpDataTables permission).', 'wpdatatables'); ?>
                        <br>
                        <?php esc_html_e('Settings, System info, and this Permissions screen stay limited to administrators (manage_options).', 'wpdatatables'); ?>
                    </div>
                    <div role="tabpanel">
                        <ul class="tab-nav" role="tablist">
                            <li class="<?php echo $activeTab === 'tables' ? 'active' : ''; ?>">
                                <a href="?page=wpdatatables_permissions&tab=tables" role="tab">
                                    <?php esc_html_e('Tables', 'wpdatatables'); ?>
                                </a>
                            </li>
                            <li class="<?php echo $activeTab === 'charts' ? 'active' : ''; ?>">
                                <a href="?page=wpdatatables_permissions&tab=charts" role="tab">
                                    <?php esc_html_e('Charts', 'wpdatatables'); ?>
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <form method="post" id="wdt-permissions-form">
                                <?php wp_nonce_field('wdt_permissions_nonce', 'wdt_permissions_nonce_field'); ?>
                                <div class="wdt-permissions-list-table-wrap">
                                    <?php require_once(WDT_ROOT_PATH . '/templates/admin/permissions/table_list.inc.php'); ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ .row -->
    </div>
    <!--/ .container -->

    <!-- Modals -->
    <?php include WDT_ROOT_PATH . '/templates/admin/permissions/modals.inc.php'; ?>
    <!-- /Modals -->
</div>
<!--/ .wdt-datatables-admin-wrap -->
