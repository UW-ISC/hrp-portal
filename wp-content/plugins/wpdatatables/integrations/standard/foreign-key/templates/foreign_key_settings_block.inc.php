<?php defined('ABSPATH') or die('Access denied.'); ?>

<div class="col-sm-12 wdt-foreign-key-block" hidden="hidden">

    <div class="col-sm-6 wdt-foreign-values p-l-0">
        <h4 class="c-title-color m-b-2">
            <?php esc_html_e('Use values from another wpDataTable', 'wpdatatables'); ?>
            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="bottom"
               title="<?php esc_attr_e('Configure a relation with a separate wpDataTable: values in this column will be used as a foreign key, and replaced with values of a remote table column (e.g.: user name instead of user ID).', 'wpdatatables'); ?>"></i>
        </h4>
        <div class="form-group">
            <button class="btn btn-primary btn-sm"
                    id="wdt-foreign-key-open"><?php esc_html_e('Configure relation...', 'wpdatatables'); ?></button>
        </div>
    </div>

    <div class="col-sm-6 wdt-foreign-rule-display p-r-0">
        <div class="form-group">
            <div class="well">
                <strong><?php esc_html_e('Table', 'wpdatatables'); ?>:</strong> <span
                        id="wdt-connected-table-name"> -</span>,
                <strong><?php esc_html_e('Show column', 'wpdatatables'); ?>:</strong> <span
                        id="wdt-connected-table-show-column"> -</span>,
                <strong><?php esc_html_e('Value from column', 'wpdatatables'); ?>:</strong> <span
                        id="wdt-connected-table-value-column"> -</span>
            </div>
        </div>
    </div>

</div>
