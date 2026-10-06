<?php defined('ABSPATH') or die('Access denied.'); ?>

<!--Advanced table settings -->
<div role="tabpanel" class="tab-pane fade" id="advanced-table-settings">
    <div class="row">
        <div class="col-sm-4 m-b-16">

            <h4 class="c-title-color m-b-2 wdt-beta-feature">
                <?php esc_html_e('Fixed columns', 'wpdatatables'); ?>
                <p class="m-b-2 wdt-fixedcolumns wdt-beta-feature"><?php esc_html_e('BETA', 'wpdatatables'); ?></p>
                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#fixed-columns-hint"
                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
            </h4>

            <!-- Hidden popover for fixed header -->
            <div class="hidden" id="fixed-columns-hint">
                <div class="popover-heading">
                    <?php esc_html_e('Fixed columns', 'wpdatatables'); ?>
                </div>

                <div class="popover-body">
                    <?php esc_html_e('Enable this to make columns fixed.', 'wpdatatables'); ?>
                </div>
            </div>
            <!-- /Hidden popover for fixed header -->

            <div class="toggle-switch" data-ts-color="blue">
                <input id="wdt-fixed-columns" type="checkbox">
                <label for="wdt-fixed-columns"
                       class="ts-label"><?php esc_html_e('Enable fixed columns', 'wpdatatables'); ?></label>
            </div>
        </div>

        <div class="col-sm-4 m-b-16 advanced-table-settings-block-fixed-columns hidden">

            <h4 class="c-title-color m-b-2">
                <?php esc_html_e('Left columns numbers', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin"
                   data-popover-content="#fixed-left-columns-number-hint"
                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
            </h4>

            <!-- Hidden offset for fixed headers hint -->
            <div class="hidden" id="fixed-left-columns-number-hint">
                <div class="popover-heading">
                    <?php esc_html_e('Left columns number', 'wpdatatables'); ?>
                </div>

                <div class="popover-body">
                    <?php esc_html_e('You can enter the number for fixed left columns here.', 'wpdatatables'); ?>
                </div>
            </div>
            <!-- /Hidden offset for fixed headers hint -->
            <div class="form-group m-b-0">
                <div class="fg-line wdt-custom-number-input">
                    <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                            data-type="minus"
                            data-field="wdt-fixed-columns-left-number">
                        <i class="wpdt-icon-minus"></i>
                    </button>
                    <input type="number" name="wdt-fixed-columns-left-number" min="0" value="1"
                           class="form-control input-sm input-number"
                           id="wdt-fixed-columns-left-number">
                    <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                            data-type="plus"
                            data-field="wdt-fixed-columns-left-number">
                        <i class="wpdt-icon-plus-full"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="col-sm-4 m-b-16 advanced-table-settings-block-fixed-columns hidden">

            <h4 class="c-title-color m-b-2">
                <?php esc_html_e('Right columns numbers', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin"
                   data-popover-content="#fixed-right-columns-number-hint"
                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
            </h4>

            <!-- Hidden offset for fixed headers hint -->
            <div class="hidden" id="fixed-right-columns-number-hint">
                <div class="popover-heading">
                    <?php esc_html_e('Right columns number', 'wpdatatables'); ?>
                </div>

                <div class="popover-body">
                    <?php esc_html_e('You can enter the number for fixed right columns here.', 'wpdatatables'); ?>
                </div>
            </div>
            <!-- /Hidden offset for fixed headers hint -->
            <div class="form-group m-b-0">
                <div class="fg-line wdt-custom-number-input">
                    <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                            data-type="minus"
                            data-field="wdt-fixed-columns-right-number">
                        <i class="wpdt-icon-minus"></i>
                    </button>
                    <input type="number" name="wdt-fixed-columns-right-number" min="0" value="0"
                           class="form-control input-sm input-number"
                           id="wdt-fixed-columns-right-number">
                    <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                            data-type="plus"
                            data-field="wdt-fixed-columns-right-number">
                        <i class="wpdt-icon-plus-full"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4 m-b-16">

            <h4 class="c-title-color m-b-2 wdt-beta-feature">
                <?php esc_html_e('Fixed headers', 'wpdatatables'); ?>
                <p class="m-b-2 wdt-fixedheaders wdt-beta-feature"><?php esc_html_e('BETA', 'wpdatatables'); ?></p>
                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#fixed-headers-hint"
                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
            </h4>

            <!-- Hidden popover for fixed header -->
            <div class="hidden" id="fixed-headers-hint">
                <div class="popover-heading">
                    <?php esc_html_e('Fixed header', 'wpdatatables'); ?>
                </div>

                <div class="popover-body">
                    <?php esc_html_e('Enable this to make header fixed.', 'wpdatatables'); ?>
                </div>
            </div>
            <!-- /Hidden popover for fixed header -->

            <div class="toggle-switch" data-ts-color="blue">
                <input id="wdt-fixed-header" type="checkbox">
                <label for="wdt-fixed-header"
                       class="ts-label"><?php esc_html_e('Enable fixed header', 'wpdatatables'); ?></label>
            </div>
        </div>

        <div class="col-sm-4 m-b-16 advanced-table-settings-block-fixed-header hidden">

            <h4 class="c-title-color m-b-2">
                <?php esc_html_e('Header offset', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#fixed-header-offset-hint"
                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
            </h4>

            <!-- Hidden offset for fixed headers hint -->
            <div class="hidden" id="fixed-header-offset-hint">
                <div class="popover-heading">
                    <?php esc_html_e('Header offset', 'wpdatatables'); ?>
                </div>

                <div class="popover-body">
                    <?php esc_html_e('You can enter the offset for fixed header here.', 'wpdatatables'); ?>
                </div>
            </div>
            <!-- /Hidden offset for fixed headers hint -->
            <div class="form-group m-b-0">
                <div class="fg-line wdt-custom-number-input">
                    <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                            data-type="minus"
                            data-field="wdt-fixed-header-offset">
                        <i class="wpdt-icon-minus"></i>
                    </button>
                    <input type="number" name="wdt-fixed-header-offset" min="1" value="0"
                           class="form-control input-sm input-number" id="wdt-fixed-header-offset">
                    <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                            data-type="plus"
                            data-field="wdt-fixed-header-offset">
                        <i class="wpdt-icon-plus-full"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4 m-b-16">

            <h4 class="c-title-color m-b-2">
                <?php esc_html_e( 'Index column', 'wpdatatables' ); ?>
                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#index-column-hint"
                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
            </h4>

            <!-- Hidden popover for fixed header -->
            <div class="hidden" id="index-column-hint">
                <div class="popover-heading">
                    <?php esc_html_e( 'Index column', 'wpdatatables' ); ?>
                </div>

                <div class="popover-body">
                    <?php esc_html_e( 'Enable this to add index column.', 'wpdatatables' ); ?>
                </div>
            </div>
            <!-- /Hidden popover for fixed header -->

            <div class="toggle-switch" data-ts-color="blue">
                <input id="wdt-index-column" type="checkbox">
                <label for="wdt-index-column"
                       class="ts-label"><?php esc_html_e( 'Add index column', 'wpdatatables' ); ?></label>
            </div>
        </div>
    </div>
</div>
