<?php defined('ABSPATH') or die('Access denied.'); ?>

<div role="tabpanel" class="tab-pane fade" id="woo-table-settings">
    <div class="row">
        <div class="col-sm-3 m-b-16">
            <h4 class="c-title-color m-b-2">
                <?php esc_html_e('Show cart information', 'wpdatatables'); ?>
                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php esc_attr_e('Enable this to show the custom cart information (link to the cart, total number of items and total price) above the table.', 'wpdatatables'); ?>"></i>
            </h4>


            <div class="toggle-switch" data-ts-color="blue">
                <input id="wdt-shot-woo-cart-information" type="checkbox" checked>
                <label for="wdt-shot-woo-cart-information"
                       class="ts-label"><?php esc_html_e('Show cart information above the table', 'wpdatatables'); ?></label>
            </div>

        </div>
    </div>
</div>