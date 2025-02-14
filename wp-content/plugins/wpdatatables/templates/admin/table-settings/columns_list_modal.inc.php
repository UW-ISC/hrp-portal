<?php defined('ABSPATH') or die('Access denied.'); ?>

<!-- #wdtColumnsListModal -->
<div class="modal fade" id="wdt-columns-list-modal" data-backdrop="static" data-keyboard="false"
     tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="wpdt-icon-times-full"></i></span>
                </button>
                <h4 class="modal-title"><?php esc_html_e('Columns', 'wpdatatables'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-info alert-dismissible" role="alert">
                    <i class="wpdt-icon-info-circle-full"></i>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true"><i class="wpdt-icon-times-full"></i></span></button>
                    <span class="wdt-alert-title f-600"><?php esc_html_e('List of the columns in the data source with quickaccess tools.', 'wpdatatables'); ?>
                        <br></span>
                    <span class="wdt-alert-subtitle"><?php esc_html_e('Click column header to rename it, toggle column visibility by clicking on the eye icon, open column settings by clicking on the wrench icon, drag and drop blocks to reorder columns.', 'wpdatatables'); ?></span>
                </div>
                <div class="wdt-column-block select-all" id="wdt-column-select-all-option">
                    <div class="fg-line m-l-10">
                        <span id="wdt-show-all"><?php esc_attr_e('Show/Hide All'); ?></span>
                    </div>
                    <?php if (isset($tableData) && $tableData->table->editable != 0) { ?>
                        <input id="wdt-select-all-column-editing" type="checkbox"
                               class="pull-right wdt-column-block-icon select-all-columns" data-toggle="tooltip"
                               title="<?php esc_attr_e('Enable/disable editing'); ?>">
                    <?php } ?>
                    <?php if (isset($tableData->wdtJsonConfig) && isset($tableData->wdtJsonConfig->masterDetail)) { ?>
                        <input id="wdt-select-all-column-master-detail" type="checkbox"
                               class="pull-right wdt-column-block-icon select-all-columns" data-toggle="tooltip"
                               title="<?php esc_attr_e('Show/hide Master-detail column'); ?>">
                    <?php } ?>
                    <?php if (isset($tableData)) do_action_deprecated('wpdt_add_small_column_block', array($tableData), WDT_INITIAL_STARTER_VERSION, 'wpdatatables_add_small_column_block'); ?>
                    <?php if (isset($tableData)) do_action('wpdatatables_add_small_column_block', $tableData); ?>

                    <input id="wdt-select-all-column-global-search" type="checkbox"
                           class="pull-right wdt-column-block-icon formula-remove-option select-all-columns"
                           data-toggle="tooltip" title="<?php esc_attr_e('Enable/disable in global search'); ?>">
                    <input id="wdt-select-all-column-filters" type="checkbox"
                           class="pull-right wdt-column-block-icon formula-remove-option select-all-columns"
                           data-toggle="tooltip" title="<?php esc_attr_e('Show/hide filters'); ?>">
                    <input id="wdt-select-all-column-sorting" type="checkbox"
                           class="pull-right wdt-column-block-icon formula-remove-option select-all-columns"
                           data-toggle="tooltip" title="<?php esc_attr_e('Show/hide sorting'); ?>">
                    <input id="wdt-select-all-column-visibility" type="checkbox"
                           class="pull-right wdt-column-block-icon select-all-columns" data-toggle="tooltip"
                           title="<?php esc_attr_e('Show/hide the columns'); ?>">
                    <input id="wdt-select-all-column-mobile-visibility" type="checkbox"
                           class="pull-right wdt-column-block-icon deselect-all-columns" data-toggle="tooltip"
                           title="<?php esc_attr_e('Show/hide on mobile'); ?>">
                    <input id="wdt-select-all-column-tablet-visibility" type="checkbox"
                           class="pull-right wdt-column-block-icon deselect-all-columns" data-toggle="tooltip"
                           title="<?php esc_attr_e('Show/hide on tablet'); ?>">
                </div>
                <div class="wdt-columns-container">
                    <!-- Column blocks go here -->
                </div>

            </div>
            <div class="modal-footer">
                <hr>
                <button type="button" class="btn btn-danger btn-icon-text" data-dismiss="modal"
                        id="wdt-cancel-columns-list">
                    <?php esc_html_e('Cancel', 'wpdatatables'); ?>
                </button>
                <button type="button" class="btn btn-primary btn-icon-text" id="wdt-apply-columns-list">
                    <i class="wpdt-icon-save"></i>
                    <?php esc_html_e('Save', 'wpdatatables'); ?>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- /#wdtColumnsListModal -->