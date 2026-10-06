<?php defined('ABSPATH') or die('Access denied.'); ?>

<div class="col-sm-2 p-l-0">
    <!-- Separate connection -->
    <h4 class="c-title-color m-b-2 f-15">
        <?php esc_html_e('Connection', 'wpdatatables'); ?>
        <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
           title="<?php _e('Use separate connection', 'wpdatatables'); ?>"></i>
    </h4>
    <!-- input source type selection -->
    <div class="form-group">
        <div class="fg-line">
            <div class="select">
                <select class="selectpicker" id="wdt-constructor-table-connection">
                    <option value="">WP Connection</option>
                    <?php foreach (Connection::getAll() as $key => $wdtSeparateConnection) { ?>
                        <option data-vendor='<?php echo esc_attr($wdtSeparateConnection['vendor']) ?>'
                                value="<?php echo esc_attr($wdtSeparateConnection['id']) ?>" <?php echo $wdtSeparateConnection['default'] ? 'selected' : '' ?>><?php echo esc_html($wdtSeparateConnection['name']) ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
    </div>
    <!-- /Separate connection -->
</div>