<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
/**
 * Template for Column Settings widget
 * @author Alexander Gilmanov
 * @since 13.10.2016
 */
?>
<!-- .card .column-settings-panel -->
<div class="card column-settings-panel">

    <div class="card-header ch-alt">
        <h2><?php esc_html_e('Column settings', 'wpdatatables'); ?>: <span
                    class="label wdtColumnOrigHeader">Position</span>
        </h2>
        <ul class="actions">
            <li>
                <button class="btn wdt-cancel-column-settings">
                    <?php esc_html_e('Cancel', 'wpdatatables'); ?>
                </button>
            </li>
            <li>
                <button class="btn btn-primary wdt-column-apply">
                    <i class="wpdt-icon-save"></i><?php esc_html_e('Save Changes', 'wpdatatables'); ?>
                </button>
            </li>
        </ul>
    </div>
    <!-- /.card-header -->
    <div class="card-body card-padding wpdt-column-settings-card">

        <div class="row wpDataTableContainer wpDataTables wpDataTablesWrapper m-l-5">

            <div role="tabpanel">
                <ul class="tab-nav" role="tablist">
                    <li class="active column-display-settings-tab">
                        <a href="#column-display-settings" aria-controls="column-display-settings" role="tab"
                           data-toggle="tab"><?php esc_html_e('Display', 'wpdatatables'); ?></a>
                    </li>
                    <li class="column-data-settings-tab">
                        <a href="#column-data-settings" aria-controls="column-data-settings" role="tab"
                           data-toggle="tab"><?php esc_html_e('Data', 'wpdatatables'); ?></a>
                    </li>
                    <li class="column-sorting-settings-tab">
                        <a href="#column-sorting-settings" aria-controls="column-sorting-settings" role="tab"
                           data-toggle="tab"><?php esc_html_e('Sorting', 'wpdatatables'); ?></a>
                    </li>
                    <li class="column-filtering-settings-tab">
                        <a href="#column-filtering-settings" aria-controls="column-filtering-settings" role="tab"
                           data-toggle="tab"><?php esc_html_e('Filtering', 'wpdatatables'); ?></a>
                    </li>
                    <li class="column-editing-settings-tab">
                        <a href="#column-editing-settings" aria-controls="column-editing-settings" role="tab"
                           data-toggle="tab"><?php esc_html_e('Editing', 'wpdatatables'); ?></a>
                    </li>
                    <li class="column-conditional-formatting-settings-tab">
                        <a href="#column-conditional-formatting-settings"
                           aria-controls="column-conditional-formatting-settings" role="tab"
                           data-toggle="tab"><?php esc_html_e('Conditional formatting', 'wpdatatables'); ?></a>
                    </li>
                    <li class="column-transform-value-tab">
                        <a href="#column-transform-value"
                           aria-controls="column-transform-value" role="tab"
                           data-toggle="tab"><?php esc_html_e('Transform value', 'wpdatatables'); ?></a>
                    </li>
                </ul>
                <!-- /ul .tab-nav -->

                <div class="tab-content">
                    <!-- Column display settings -->
                    <div role="tabpanel" class="tab-pane active" id="column-display-settings">
                        <div class="row">

                            <div class="col-sm-6">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Displayed header', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('You can redefine the visible column header here, it will be shown instead of the original header.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="fg-line">
                                        <input type="text" class="form-control input-sm" value="Column"
                                               id="wdt-column-display-header">
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Column position', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('You can redefine the position of the column here. Other columns will automatically re-position if you change it and click Apply.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="fg-line wdt-custom-number-input">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="wdt-column-position">
                                            <i class="wpdt-icon-minus"></i>
                                        </button>
                                        <input type="number" name="wdt-column-position" min="0" value="" class="form-control input-sm input-number" id="wdt-column-position">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="wdt-column-position">
                                            <i class="wpdt-icon-plus-full"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- /.row -->

                        <div class="row">

                            <div class="col-sm-6">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Cell content prefix', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-popover-content="#column-cell-prefix-hint"
                                       data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                                </h4>

                                <!-- Hidden popover with image hint -->
                                <div class="hidden" id="column-cell-prefix-hint">
                                    <div class="popover-heading">
                                        <?php esc_html_e('Cell content prefix', 'wpdatatables'); ?>
                                    </div>

                                    <div class="popover-body">
                                        <div class="thumbnail">
                                            <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/text_before.png"/>
                                        </div>
                                        <?php esc_html_e('Any text or symbol entered here will be shown before the value in every cell inside of this column. E.g.: $, €. Does not influence filtering and sorting. Convenient for prices, percentages, etc.', 'wpdatatables'); ?>
                                    </div>
                                </div>
                                <!-- /Hidden popover with image hint -->

                                <div class="form-group">
                                    <div class="fg-line">
                                        <input type="text" class="form-control input-sm"
                                               id="wdt-column-display-text-before" value="">
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Cell content suffix', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-popover-content="#column-cell-suffix-hint"
                                       data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                                </h4>

                                <!-- Hidden popover with image hint -->
                                <div class="hidden" id="column-cell-suffix-hint">
                                    <div class="popover-heading">
                                        <?php esc_html_e('Cell content suffix', 'wpdatatables'); ?>
                                    </div>

                                    <div class="popover-body">
                                        <div class="thumbnail">
                                            <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/text_after.png"/>
                                        </div>
                                        <?php esc_html_e('Any text or symbol entered here will be shown after the value in every cell inside of this column. E.g.: %, pcs. Does not influence filtering and sorting. Convenient for prices, percentages, etc.', 'wpdatatables'); ?>
                                    </div>
                                </div>
                                <!-- /Hidden popover with image hint -->

                                <div class="form-group">
                                    <div class="fg-line">
                                        <input type="text" class="form-control input-sm"
                                               id="wdt-column-display-text-after" value="">
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- /.row -->


                        <div class="row wdt-columns-responsive-block">

                            <div class="col-sm-6">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Hide on mobiles', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Disabling this will make the column collapse to an expandable block when table viewed from mobile devices.','wpdatatables'); ?>"></i>
                                </h4>

                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-hide-column-on-mobiles" type="checkbox">
                                        <label for="wdt-hide-column-on-mobiles"
                                               class="ts-label"><?php esc_html_e('Collapse column on mobile devices', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Hide on tablets', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Disabling this will make the column collapse to an expandable block when table viewed from tablet devices.','wpdatatables'); ?>"></i>
                                </h4>

                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-hide-column-on-tablets" type="checkbox">
                                        <label for="wdt-hide-column-on-tablets"
                                               class="ts-label"><?php esc_html_e('Collapse column on tablet devices', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- /.row -->

                        <!-- .row -->
                        <div class="row">

                            <div class="col-sm-6 cp-container">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('CSS class(es)', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Here you can define additional CSS classes, which will be applied to all the cells in this column and can be used for customizing the styling.','wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="fg-line">
                                        <input type="text" class="form-control input-sm" id="wdt-column-css-class"
                                               value="">
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Visible on front-end', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Disabling this will hide the column from table front-end. Please note that if column visibility is enabled in Table Tools block, front-end users will be able to access hidden columns as well.','wpdatatables'); ?>"></i>
                                </h4>

                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-visible" type="checkbox">
                                        <label for="wdt-column-visible"
                                               class="ts-label"><?php esc_html_e('Show column in front-end', 'wpdatatables'); ?></label>
                                    </div>
                                </div>

                            </div>

                        </div>
                        <!-- /.row -->
                        <div class="row">
                        <div class="col-sm-6">
                            <h4 class="c-title-color m-b-2">
			                    <?php esc_html_e('Column header alignment', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right" title=""
                                   data-original-title="<?php esc_attr_e('You can override the default alignment for the column header here.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="fg-line">
                                <div class="select">
                                    <select class="form-control selectpicker" id="wdt-column-align-header" autocomplete="off"
                                            title="<?php esc_attr_e('Default', 'wpdatatables'); ?>">
                                        <option value="left"><?php esc_html_e('Left', 'wpdatatables'); ?></option>
                                        <option value="center"><?php esc_html_e('Center', 'wpdatatables'); ?></option>
                                        <option value="right"><?php esc_html_e('Right', 'wpdatatables'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <h4 class="c-title-color m-b-2">
			                    <?php esc_html_e('Column cells alignment', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right" title=""
                                   data-original-title="<?php esc_attr_e('You can override the default alignment for the column cells here.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="fg-line">
                                <div class="select">
                                    <select class="form-control selectpicker" id="wdt-column-align-fields" autocomplete="off"
                                            title="<?php esc_attr_e('Default', 'wpdatatables'); ?>">
                                        <option value="left"><?php esc_html_e('Left', 'wpdatatables'); ?></option>
                                        <option value="center"><?php esc_html_e('Center', 'wpdatatables'); ?></option>
                                        <option value="right"><?php esc_html_e('Right', 'wpdatatables'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        </div>
                        <!-- .row -->
                        <div class="row">

                            <div class="col-sm-6 wdt-column-width-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Column width', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Input width for column (in percents with % or pixels without px). Leave a blank if you want to leave auto width.','wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="fg-line">
                                        <input type="text" class="form-control input-sm" id="wdt-column-width">
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 cp-container">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Column color', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-popover-content="#column-color-hint  "
                                       data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                                </h4>

                                <!-- Hidden popover with image hint -->
                                <div class="hidden" id="column-color-hint">
                                    <div class="popover-heading">
                                        <?php esc_html_e('Column color', 'wpdatatables'); ?>
                                    </div>

                                    <div class="popover-body">
                                        <div class="thumbnail">
                                            <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/column_color.png"/>
                                        </div>
                                        <?php esc_html_e('Here you can override the default color for the complete column', 'wpdatatables'); ?>
                                    </div>
                                </div>
                                <!-- /Hidden popover with image hint -->

                                <div class="cp-container">
                                    <div class="form-group">
                                        <div class="fg-line dropdown">
                                            <div id="cp"
                                                 class="input-group wdt-color-picker">
                                                <input type="text" id="wdt-column-color" value=""
                                                       class="form-control cp-value cp-inside wdt-add-picker"/>
                                                <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-group-column-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Group column', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-popover-content="#group-column"
                                       data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                                </h4>

                                <!-- Hidden popover with image hint -->
                                <div class="hidden" id="group-column">
                                    <div class="popover-heading">
                                        <?php esc_html_e('Group column', 'wpdatatables'); ?>
                                    </div>

                                    <div class="popover-body">
                                        <div class="thumbnail">
                                            <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/group_column.png"/>
                                        </div>
                                        <?php esc_html_e('If one column of your table contains similar values for many rows, maybe it makes sense to use it as a “group column”: it means that it will not be rendered as a column, but its values will be used to group the rows. Each group will be marked with one row with joined cells above, containing the group value.', 'wpdatatables'); ?>
                                        <strong><?php esc_html_e('Group column will not work with MySQL tables with server-side processing enabled and with manual tables!', 'wpdatatables'); ?></strong>
                                    </div>
                                </div>
                                <!-- /Hidden popover with image hint -->

                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-group-column" type="checkbox">
                                        <label for="wdt-group-column"
                                               class="ts-label"><?php esc_html_e('Toggle column grouping', 'wpdatatables'); ?></label>
                                    </div>
                                </div>

                            </div>


                            <div class="col-sm-6">
                                <h4 class="c-title-color m-b-2 wdt-beta-feature">
                                    <?php esc_html_e('Column header text rotation', 'wpdatatables'); ?>
                                    <p class="m-b-2 wdt-beta-feature"><?php esc_html_e('BETA', 'wpdatatables'); ?></p>
                                       <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right" title=""
                                          data-original-title="<?php esc_attr_e('Here you can override the default rotation for the column header text.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="form-control selectpicker" id="wdt-column-rotate-header-name" autocomplete="off"
                                                title="<?php esc_attr_e('Default', 'wpdatatables'); ?>">
                                            <option value="360"><?php esc_html_e('-90', 'wpdatatables'); ?></option>
                                            <option value=""><?php esc_html_e('0', 'wpdatatables'); ?></option>
                                            <option value="180"><?php esc_html_e('90', 'wpdatatables'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <?php do_action('wdt_add_column_display_settings_element'); ?>
                        </div>

                        <!-- /.row -->
                    </div>
                    <!-- /#column-display-settings -->

                    <!-- Column data settings -->
                    <div role="tabpanel" class="tab-pane" id="column-data-settings">
                        <div class="row">

                            <div class="col-sm-6">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Column type', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('You can redefine the column data type here. Affects sorting, filtering and display logic. For manually created tables this will change the column type in database as well, which may result in data loss.', 'wpdatatables'); ?>"></i>
                                </h4>

                                <div class="form-group">
                                    <div class="fg-line">
                                        <div class="select">
                                            <select class="selectpicker" id="wdt-column-type">
                                                <option value="string"><?php esc_html_e('String', 'wpdatatables'); ?></option>
                                                <option value="int"><?php esc_html_e('Integer', 'wpdatatables'); ?></option>
                                                <option value="float"><?php esc_html_e('Float', 'wpdatatables'); ?></option>
                                                <option value="date"><?php esc_html_e('Date', 'wpdatatables'); ?></option>
                                                <option value="datetime"><?php esc_html_e('DateTime', 'wpdatatables'); ?></option>
                                                <option value="time"><?php esc_html_e('Time', 'wpdatatables'); ?></option>
                                                <option value="link"><?php esc_html_e('URL link', 'wpdatatables'); ?></option>
                                                <option value="email"><?php esc_html_e('E-mail link', 'wpdatatables'); ?></option>
                                                <option value="image"><?php esc_html_e('Image', 'wpdatatables'); ?></option>
                                                <option disabled="disabled"
                                                        value="formula"><?php esc_html_e('Formula', 'wpdatatables'); ?></option>

                                                <?php do_action('wpdatatables_add_custom_column_type_option'); ?>

                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="col-sm-6 wdt-formula-column-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Formula for calculation', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('You can create a formula (calculated column) based on other numeric columns (Integer and Float)', 'wpdatatables'); ?>"></i>
                                </h4>

                                <div class="form-group">
                                    <button class="btn btn-primary wdt-open-formula-editor"><?php esc_html_e('Open formula editor', 'wpdatatables'); ?></button>
                                </div>

                            </div>

                            <div class="col-sm-6 wdt-numeric-column-block wdt-float-column-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Decimal places', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('You can redefine the visible decimal places for a float and formula columns here. If you leave this field empty amount of decimal places will be loaded from the settings page. 0 is available just for formula columns ', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="fg-line wdt-custom-number-input">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus" data-type="minus" data-field="wdt-column-decimal-places">
                                            <i class="wpdt-icon-minus"></i>
                                        </button>
                                        <input type="number" name="wdt-column-decimal-places" min="1" value="" class="form-control input-sm input-number" id="wdt-column-decimal-places">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus" data-type="plus" data-field="wdt-column-decimal-places">
                                            <i class="wpdt-icon-plus-full"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-numeric-column-block wdt-skip-thousands-separator-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Skip thousands separator', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Do not show thousands separator for this column (e.g. when showing years, IDs).', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-skip-thousands" type="checkbox">
                                        <label for="wdt-column-skip-thousands"
                                               class="ts-label"><?php esc_html_e('Skip thousands separator', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-possible-values-type-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Possible values for column', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="left"
                                       title="<?php esc_attr_e('Define the logic for fetching the possible values for this column for filtering, and for editing: read from table itself on page load (will always contain only the values that already exist in the table), hard-code the list, or configure a relation with a different wpDataTable (use values in this column as foreign key).', 'wpdatatables'); ?>"></i>
                                </h4>

                                <div class="form-group">
                                    <div class="fg-line">
                                        <div class="select">
                                            <select class="selectpicker" id="wdt-column-values">
                                                <option value="read"><?php esc_html_e('Read from table on page load', 'wpdatatables'); ?></option>
                                                <option value="list"><?php esc_html_e('Define values list', 'wpdatatables'); ?></option>
                                                <option value="foreignkey"><?php esc_html_e('Use values from another wpDataTable (foreign key)', 'wpdatatables'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-date-input-format-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Date input format', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Define date format which is used in the provided data source. Necessary for smooth reading of your dates.', 'wpdatatables'); ?>"></i>
                                </h4>

                                <div class="form-group">
                                    <div class="fg-line">
                                        <div class="select">
                                            <select class="selectpicker" id="wdt-date-input-format">
                                                <option value="d/m/Y"> 15/07/2005 (d/m/Y)</option>
                                                <option value="m/d/Y"> 07/15/2005 (m/d/Y)</option>
                                                <option value="Y/m/d"> 2005/15/07 (Y/m/d)</option>
                                                <option value="d.m.Y"> 15.07.2005 (d.m.Y)</option>
                                                <option value="m.d.Y"> 07.15.2005 (m.d.Y)</option>
                                                <option value="Y.m.d"> 2005.07.15 (Y.m.d)</option>
                                                <option value="d-m-Y"> 15-07-2005 (d-m-Y)</option>
                                                <option value="m-d-Y"> 07-15-2005 (m-d-Y)</option>
                                                <option value="Y-m-d"> 2005-07-15 (Y-m-d)</option>
                                                <option value="d.m.y"> 15.07.05 (d.m.y)</option>
                                                <option value="m.d.y"> 07.15.05 (m.d.y)</option>
                                                <option value="d.m">15.07 (d.m)</option>
                                                <option value="d-m-y"> 15-07-05 (d-m-y)</option>
                                                <option value="m-d-y"> 07-15-05 (m-d-y)</option>
                                                <option value="d M Y"> 15 Jul 2005 (d Mon Y)</option>
                                                <option value="m/Y"> 07/2005 (m/Y)</option>
                                                <option value="M Y"> Jul 2005 (Mon Y)</option>
                                                <option value="F Y"> July 2005 (F Y)</option>
                                                <option value="F j, Y"> July 15, 2005 (F j, Y)</option>
                                                <option value="j. F Y."> 15. July 2005. (j. F Y.)</option>
                                                <option value="Y">2005 (Y)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>
                        <!-- /.row -->

                        <!-- .row -->
                        <div class="row wdt-possible-values-options-block">

                            <div class="col-sm-12 wdt-manual-list-enter-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Values list', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Enter the possible values for this column in this list, separate by pressing ENTER or comma. Click Read from table to fetch the list from existing column values. Values will be used in selectbox and checkbox filters and editors for the column.', 'wpdatatables'); ?>"></i>
                                    <button class="btn btn-danger btn-xs pull-right"
                                            id="wdt-column-values-reset"><i class="wpdt-icon-exclamation-triangle"></i><?php esc_html_e('Reset', 'wpdatatables'); ?></button>
                                    <button class="btn btn-xs pull-right"
                                            id="wdt-column-values-read-from-table"><?php esc_html_e('Read from table', 'wpdatatables'); ?></button>
                                    <div class="clear"></div>
                                </h4>
                                <div class="form-group">
                                    <div class="fg-line">
                                        <input class="form-control input-sm" value="" id="wdt-column-values-list"/>
                                    </div>
                                </div>
                            </div>

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
                                            <strong><?php esc_html_e('Table', 'wpdatatables'); ?>:</strong> <span id="wdt-connected-table-name"> -</span>,
                                            <strong><?php esc_html_e('Show column', 'wpdatatables'); ?>:</strong> <span
                                                    id="wdt-connected-table-show-column"> -</span>,
                                            <strong><?php esc_html_e('Value from column', 'wpdatatables'); ?>:</strong> <span
                                                    id="wdt-connected-table-value-column"> -</span>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="col-sm-6">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Allow empty value', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Add an empty value to the possible values list, to allow empty editing and filtering inputs.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-values-add-empty" type="checkbox">
                                        <label for="wdt-column-values-add-empty"
                                               class="ts-label"><?php esc_html_e('Add an empty value to the list', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-possible-values-foreign-keys-block" >
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Possible values in edit modal for foreign key column', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('By turning this on you will show all possible values in edit modal from separate table that is connect with foreign keys when option Users can see and edit own data is enabled', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-possible-values-foreign-keys" type="checkbox">
                                        <label for="wdt-possible-values-foreign-keys"
                                               class="ts-label"><?php esc_html_e('Allow all possible values in edit modal', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-possible-values-ajax-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Number of possible values to load', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Define here how many possible values per page will be loaded in selectbox filters and editor inputs. It is recommended not to be set to All if you have more than 50 possible values for this column. This option doesn\'t work when Cascade Filtering option from Powerful Filters add-on is enabled.', 'wpdatatables'); ?>"></i>
                                </h4>

                                <div class="form-group">
                                    <div class="fg-line">
                                        <div class="select">
                                            <select class="selectpicker" id="wdt-possible-values-ajax">
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                                <option value="-1"><?php esc_html_e('All', 'wpdatatables'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                        <!-- /.row -->

                        <div class="row wdt-numeric-column-block">

                            <div class="col-sm-6 wdt-column-calc-total-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Calculate total', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Enable this to show a total (sum) for all cells in this column in table footer. You can also show it somewhere outside of the table by pasting the shortcode below the switch.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-calc-total" type="checkbox">
                                        <label for="wdt-column-calc-total"
                                               class="ts-label"><?php esc_html_e('Calculate a total for this column', 'wpdatatables'); ?></label>
                                    </div>
                                    <div class="m-t-5" id="wdt-column-calc-total-shortcode">
                                        <a class="wdt-copy-shortcode" data-toggle="tooltip" data-shortcode-type="calc-total" title="<?php esc_attr_e('Click to copy this shortcode. By placing this shortcode anywhere in your posts or pages you can see the total for this column.', 'wpdatatables'); ?>" data-placement="right" >
                                            <i class="wpdt-icon-copy"></i></a>
                                        <span id="wdt-calc-total-shortcode-id"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-column-calc-avg-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Calculate average', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Enable this to show an average value for all cells in this column in table footer. You can also show it somewhere outside of the table by pasting the shortcode below the switch.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-calc-avg" type="checkbox">
                                        <label for="wdt-column-calc-avg"
                                               class="ts-label"><?php esc_html_e('Calculate average for this column', 'wpdatatables'); ?></label>
                                    </div>
                                    <div class="m-t-5" id="wdt-column-calc-avg-shortcode">
                                        <a class="wdt-copy-shortcode" data-toggle="tooltip" data-shortcode-type="calc-avg" title="<?php esc_attr_e('Click to copy this shortcode. By placing this shortcode anywhere in your posts or pages you can see the average for this column.', 'wpdatatables'); ?>" data-placement="right" >
                                            <i class="wpdt-icon-copy"></i></a>
                                        <span id="wdt-calc-avg-shortcode-id"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-column-calc-min-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Calculate MIN value', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Enable this to show a minimum value within all cells in this column in table footer. You can also show it somewhere outside of the table by pasting the shortcode below the switch.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-calc-min" type="checkbox">
                                        <label for="wdt-column-calc-min"
                                               class="ts-label"><?php esc_html_e('Find a minimum value for this column', 'wpdatatables'); ?></label>
                                    </div>
                                    <div class="m-t-5" id="wdt-column-calc-min-shortcode">
                                        <a class="wdt-copy-shortcode" data-toggle="tooltip" data-shortcode-type="calc-min" title="<?php esc_attr_e('Click to copy this shortcode. By placing this shortcode anywhere in your posts or pages you can see the minimum for this column.', 'wpdatatables'); ?>" data-placement="right" >
                                            <i class="wpdt-icon-copy"></i></a>
                                        <span id="wdt-calc-min-shortcode-id"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-column-calc-max-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Calculate MAX value', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Enable this to show a maximum value within all cells in this column in table footer. You can also show it somewhere outside of the table by pasting the shortcode below the switch.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-calc-max" type="checkbox">
                                        <label for="wdt-column-calc-max"
                                               class="ts-label"><?php esc_html_e('Find a maximum value for this column', 'wpdatatables'); ?></label>
                                    </div>
                                    <div class="m-t-5" id="wdt-column-calc-max-shortcode">
                                        <a class="wdt-copy-shortcode" data-toggle="tooltip" data-shortcode-type="calc-max" title="<?php esc_attr_e('Click to copy this shortcode. By placing this shortcode anywhere in your posts or pages you can see the maximum for this column.', 'wpdatatables'); ?>" data-placement="right" >
                                            <i class="wpdt-icon-copy"></i></a>
                                        <span id="wdt-calc-max-shortcode-id"></span>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- /.row -->

                        <div class="row wdt-link-column-block">

                            <div class="col-sm-6 wdt-link-target-attribute-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('URL target attribute', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Set how to open URL Target', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-link-target-attribute" type="checkbox">
                                        <label for="wdt-link-target-attribute"
                                               class="ts-label"><?php esc_html_e('Open link in the new tab', 'wpdatatables'); ?></label>
                                    </div>
                                </div>

                            </div>

                            <div class="col-sm-6 wdt-link-nofollow-attribute-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Make NOFOLLOW link', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Set the link to a nofollow link', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-link-nofollow-attribute" type="checkbox">
                                        <label for="wdt-link-nofollow-attribute"
                                               class="ts-label"><?php esc_html_e('Make the link a nofollow link', 'wpdatatables'); ?></label>
                                    </div>
                                </div>

                            </div>

                            <div class="col-sm-6 wdt-link-noreferrer-attribute-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Make NOREFERRER link', 'wpdatatables'); ?>
                                    <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Set the link to a noreferrer link', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-link-noreferrer-attribute" type="checkbox">
                                        <label for="wdt-link-noreferrer-attribute"
                                               class="ts-label"><?php esc_html_e('Make the link a noreferrer link', 'wpdatatables'); ?></label>
                                    </div>
                                </div>

                            </div>

                            <div class="col-sm-6 wdt-link-sponsored-attribute-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Make SPONSORED link', 'wpdatatables'); ?>
                                    <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Set the link to a sponsored link', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-link-sponsored-attribute" type="checkbox">
                                        <label for="wdt-link-sponsored-attribute"
                                               class="ts-label"><?php esc_html_e('Make the link a sponsored link', 'wpdatatables'); ?></label>
                                    </div>
                                </div>

                            </div>

                            <div class="col-sm-6 wdt-link-button-attribute-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Show link as a button', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('The link will be shown as a button ', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-link-button-attribute" type="checkbox">
                                        <label for="wdt-link-button-attribute"
                                               class="ts-label"><?php esc_html_e('Set the link to appear as a button', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-link-button-label-block" >
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Button text', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('You can set the button display text','wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="fg-line">
                                        <input type="text" class="form-control input-sm" value=""
                                               id="wdt-link-button-label">
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-link-button-class-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Button class', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('You can set the button class','wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="fg-line">
                                        <input type="text" class="form-control input-sm" value=""
                                               id="wdt-link-button-class">
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- /.row -->

                    </div>
                    <!--/#column-data-settings -->

                    <!-- Column sorting settings -->
                    <div role="tabpanel" class="tab-pane" id="column-sorting-settings">
                        <!-- .row -->
                        <div class="row">
                            <div class="col-sm-6">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Allow sorting', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Disable this to disallow sorting for this column.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-allow-sorting" type="checkbox">
                                        <label for="wdt-column-allow-sorting"
                                               class="ts-label"><?php esc_html_e('Allow sorting for this column', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-column-default-sorting-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Use as default sorting column', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Sort table by this column on load', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-default-sort" type="checkbox">
                                        <label for="wdt-column-default-sort"
                                               class="ts-label"><?php esc_html_e('Sort table by this column on load', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.row -->

                        <!-- .row -->
                        <div class="row">
                            <div class="col-sm-6 wdt-column-default-sorting-direction-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Default sorting direction', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Choose whether to sort ascending or descending by default.', 'wpdatatables'); ?>"></i>
                                </h4>

                                <div class="form-group">
                                    <div class="fg-line">
                                        <div class="select">
                                            <select class="selectpicker" id="wdt-column-default-sorting-direction">
                                                <option value="1"><?php esc_html_e('Ascending', 'wpdatatables'); ?></option>
                                                <option value="2"><?php esc_html_e('Descending', 'wpdatatables'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.row -->

                    </div>
                    <!--/#column-sorting-settings -->

                    <!-- Column filtering settings -->
                    <div role="tabpanel" class="tab-pane" id="column-filtering-settings">
                        <div class="row">

                            <div class="col-sm-6 wdt-column-enable-filter-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Add a filter for this column', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-popover-content="#column-filter-hint"
                                       data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                                </h4>

                                <!-- Hidden popover with image hint -->
                                <div class="hidden" id="column-filter-hint">
                                    <div class="popover-heading">
                                        <?php esc_html_e('Enable filtering for column', 'wpdatatables'); ?>
                                    </div>

                                    <div class="popover-body">
                                        <div class="thumbnail">
                                            <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/column_filter.png"/>
                                        </div>
                                        <?php esc_html_e('Enabling this switch will add a filter for this column. Disable to remove the filter for this column.', 'wpdatatables'); ?>
                                    </div>
                                </div>
                                <!-- /Hidden popover with image hint -->

                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-enable-filter" type="checkbox">
                                        <label for="wdt-column-enable-filter"
                                               class="ts-label"><?php esc_html_e('Allow filtering', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-global-search-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Global search', 'wpdatatables'); ?>
                                    <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('If this option is turned off, the column will not appear in your global search results. The filtering option will also be disabled.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-enable-global-search" type="checkbox">
                                        <label for="wdt-column-enable-global-search"
                                               class="ts-label"><?php esc_html_e('Enable this column in Global search', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>
                            <!-- /.row -->

                            <!-- .row -->
                            <div class="col-sm-6 wdt-filtering-enabled-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Filter type', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('You can redefine the filter type here, it will affect the filtering logic.', 'wpdatatables'); ?>"></i>
                                </h4>

                                <div class="form-group">
                                    <div class="fg-line">
                                        <div class="select">
                                            <select class="selectpicker" id="wdt-column-filter-type">
                                                <option value="text"><?php esc_html_e('Text', 'wpdatatables'); ?></option>
                                                <option value="number"><?php esc_html_e('Number', 'wpdatatables'); ?></option>
                                                <option value="number-range"><?php esc_html_e('Number range', 'wpdatatables'); ?></option>
                                                <option value="date-range"><?php esc_html_e('Date range', 'wpdatatables'); ?></option>
                                                <option value="datetime-range"><?php esc_html_e('DateTime range', 'wpdatatables'); ?></option>
                                                <option value="time-range"><?php esc_html_e('Time range', 'wpdatatables'); ?></option>
                                                <option value="select"><?php esc_html_e('Selectbox', 'wpdatatables'); ?></option>
                                                <option value="multiselect"><?php esc_html_e('Multiselectbox', 'wpdatatables'); ?></option>
                                                <option value="checkbox"><?php esc_html_e('Checkbox', 'wpdatatables'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-filtering-enabled-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Filter label', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-popover-content="#filter-label-hint"
                                       data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                                </h4>

                                <!-- Hidden popover with image hint -->
                                <div class="hidden" id="filter-label-hint">
                                    <div class="popover-heading">
                                        <?php esc_html_e('Custom filter label', 'wpdatatables'); ?>
                                    </div>

                                    <div class="popover-body">
                                        <div class="thumbnail">
                                            <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/custom_filter_label.png"/>
                                        </div>
                                        <?php esc_html_e('You can change the filter label (placeholder) for this column here, if you would like to show some custom text instead of default.', 'wpdatatables'); ?>
                                    </div>
                                </div>
                                <!-- /Hidden popover with image hint -->

                                <div class="form-group">
                                    <div class="fg-line">
                                        <input type="text" class="form-control input-sm" value=""
                                               id="wdt-column-filter-label">
                                    </div>
                                </div>
                            </div>
                            <!-- /.row -->

                            <!-- .row -->

                            <div class="col-sm-6 wdt-filtering-enabled-block wdt-exact-filtering-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Exact filtering', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Enable exact search to use exact match logic for filtering, disable to allow partial match.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-exact-filtering" type="checkbox">
                                        <label for="wdt-column-exact-filtering"
                                               class="ts-label"><?php esc_html_e('Enable exact filtering', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 wdt-filtering-enabled-block wdt-number-range-slider">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Range slider', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Enable number range slider  for filtering', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-range-slider" type="checkbox">
                                        <label for="wdt-column-range-slider"
                                               class="ts-label"><?php esc_html_e('Number range slider', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-filtering-enabled-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Predefined value(s)', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Define value(s) that will be set as default pre-defined filter value(s) on page load.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group wdt-filter-default-value-block">
                                    <div class="fg-line">
                                        <input type="text" class="form-control input-sm" value=""
                                               id="wdt-filter-default-value">
                                    </div>
                                </div>
                                <div class="form-group col-sm-6 p-l-0 wdt-filter-default-value-from-block"
                                     hidden="hidden">
                                    <div class="fg-line">
                                        <input type="text" class="form-control input-sm" value=""
                                               id="wdt-filter-default-value-from" placeholder="<?php esc_attr_e('From','wpdatatables'); ?>">
                                    </div>
                                </div>
                                <div class="form-group col-sm-6 p-r-0 wdt-filter-default-value-to-block"
                                     hidden="hidden">
                                    <div class="fg-line">
                                        <input type="text" class="form-control input-sm" value=""
                                               id="wdt-filter-default-value-to" placeholder="<?php esc_attr_e('To','wpdatatables'); ?>">
                                    </div>
                                </div>
                                <div class="form-group wdt-filter-default-value-selectpicker-block" hidden="hidden">
                                    <div class="fg-line">
                                        <div class="select">
                                            <select class="selectpicker" id="wdt-filter-default-value-selectpicker" data-live-search="true">

                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-filtering-enabled-block wdt-range-max-value">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Max value', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Choose how the max value on the range slider will be displayed.', 'wpdatatables'); ?>"></i>
                                </h4>

                                <div class="form-group">
                                    <div class="fg-line">
                                        <div class="select">
                                            <select class="selectpicker" id="wdt-max-value-display">
                                                <option value="default"><?php esc_html_e('Show max value', 'wpdatatables'); ?></option>
                                                <option value="unlimited_text"><?php esc_html_e('Show \'Unlimited\' text', 'wpdatatables'); ?></option>
                                                <option value="unlimited_symbol"><?php esc_html_e('Show unlimited symbol (∞)', 'wpdatatables'); ?></option>
                                                <option value="custom_text"><?php esc_html_e('Custom text', 'wpdatatables'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-range-max-value-custom">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Custom max value', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Use a custom string as your max value on the range slider,', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="fg-line">
                                        <input type="text" class="form-control input-sm" id="wdt-custom-max-value"
                                               value="">
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 wdt-filtering-enabled-block wdt-checkboxes-in-modal-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Render in modal', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Render checkboxes in modal.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-checkboxes-in-modal" type="checkbox">
                                        <label for="wdt-checkboxes-in-modal"
                                               class="ts-label"><?php esc_html_e('Render checkboxes in modal', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 wdt-filtering-enabled-block wdt-search-in-selectbox-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Search in selectbox/multiselectbox', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Enable search in selectbox/multiselectbox. This option doesn\'t work when Cascade Filtering option from Powerful Filters add-on is enabled.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-search-in-selectbox" type="checkbox">
                                        <label for="wdt-search-in-selectbox"
                                               class="ts-label"><?php esc_html_e('Enable search in selectbox/multiselectbox', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 wdt-filtering-enabled-block wdt-and-logic-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Use AND logic in multiselectbox/checkbox', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Use AND logic instead of OR in multiselectbox/checkbox. This means that checking multiple options restricts the results instead of broadening them.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-and-logic" type="checkbox">
                                        <label for="wdt-and-logic"
                                               class="ts-label"><?php esc_html_e('Enable AND logic in multiselectbox/checkbox', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--/ .row -->

                    </div>
                    <!--/#column-filtering-settings -->

                    <!-- Column editing settings -->
                    <div role="tabpanel" class="tab-pane" id="column-editing-settings">

                        <div class="row">

                            <div class="col-sm-6">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Editor input type', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Choose which kind of editor input to use for this column.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="fg-line">
                                        <div class="select">
                                            <select class="selectpicker" id="wdt-column-editor-input-type">
                                                <option value="none"><?php esc_html_e('None', 'wpdatatables'); ?></option>
                                                <option value="text"><?php esc_html_e('One-line edit', 'wpdatatables'); ?></option>
                                                <option value="textarea"><?php esc_html_e('Multi-line edit', 'wpdatatables'); ?></option>
                                                <option value="mce-editor"><?php esc_html_e('HTML editor', 'wpdatatables'); ?></option>
                                                <option value="selectbox"><?php esc_html_e('Single-value selectbox', 'wpdatatables'); ?></option>
                                                <option value="multi-selectbox"><?php esc_html_e('Multi-value selectbox', 'wpdatatables'); ?></option>
                                                <option value="date"><?php esc_html_e('Date', 'wpdatatables'); ?></option>
                                                <option value="datetime"><?php esc_html_e('Datetime', 'wpdatatables'); ?></option>
                                                <option value="time"><?php esc_html_e('Time', 'wpdatatables'); ?></option>
                                                <option value="link"><?php esc_html_e('URL link', 'wpdatatables'); ?></option>
                                                <option value="email"><?php esc_html_e('E-mail link', 'wpdatatables'); ?></option>
                                                <option value="attachment"><?php esc_html_e('Attachment', 'wpdatatables'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="col-sm-6 wdt-editing-enabled-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Column cannot be empty', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Enable to make this column mandatory. Users will see a warning when trying to save with empty input.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-column-not-null" type="checkbox">
                                        <label for="wdt-column-not-null"
                                               class="ts-label"><?php esc_html_e('Cannot be empty', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- /.row -->

                        <div class="row">

                            <div class="col-sm-6 wdt-editing-enabled-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Predefined value(s)', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('If you would like to have some values pre-defined in editors (i.e. default editor values) please enter these here.', 'wpdatatables'); ?>"></i>
                                </h4>

                                <div class="form-group wdt-editing-default-value-block">
                                    <div class="fg-line">
                                        <input type="text" class="form-control input-sm" value=""
                                               id="wdt-editing-default-value">
                                    </div>
                                </div>

                                <div class="form-group wdt-editing-default-value-selectpicker-block" hidden="hidden">
                                    <div class="fg-line">
                                        <div class="select">
                                            <select class="selectpicker" id="wdt-editing-default-value-selectpicker" data-live-search="true">

                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="form-group col-sm-6 wdt-editing-enabled-block wdt-search-in-selectbox-editing-block">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Search in select-box for editing', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Enable search in select-box for entry editing when number of possible values to load is All.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group">
                                    <div class="toggle-switch" data-ts-color="blue">
                                        <input id="wdt-search-in-selectbox-editing" type="checkbox">
                                        <label for="wdt-search-in-selectbox-editing"
                                               class="ts-label"><?php esc_html_e('Enable search in select-box for entry editing', 'wpdatatables'); ?></label>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!--/ .row -->

                    </div>
                    <!--/#column-editing-settings -->

                    <!-- Column conditional formatting settings -->
                    <div role="tabpanel" class="tab-pane" id="column-conditional-formatting-settings">
                        <div class="wdt-conditional-formatting-rules-container">

                        </div>
                        <!-- /.row -->

                        <div class="row">

                            <div class="col-sm-12 p-l-0">
                                <button class="btn pull-left wdt-column-add-conditional-formatting-rule">
                                    <i class="wpdt-icon-plus-thin"></i> <?php esc_html_e('Add New Rule', 'wpdatatables'); ?>
                                </button>
                            </div>

                        </div>

                    </div>
                    <!--/#column-conditional-formatting-settings -->

                    <!-- Column transform value settings -->
                    <div role="tabpanel" class="tab-pane" id="column-transform-value">
                        <div class="wdt-column-transform-value-container"> </div>
                        <div class="row">
                            <div class="col-sm-12 column-tranform-vaule-textarea">
                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Transform value of any column into valid html code ', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-popover-content="#column-transform-value-hint"
                                       data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                                </h4>
                                <!-- Hidden popover with image hint -->
                                <div class="hidden" id="column-transform-value-hint">
                                    <div class="popover-heading">
                                        <?php esc_html_e('Transform value', 'wpdatatables'); ?>
                                    </div>

                                    <div class="popover-body">
                                        <?php esc_html_e('You can transform column cells value into an email, link, image, or any specific table column.', 'wpdatatables'); ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="fg-line">
                                        <textarea type="text" class="form-control input-sm"
                                                  id="wdt-column-transform-value" value=""></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="alert alert-info alert-dismissible hidden" role="alert" style="margin-top: 40px;margin-bottom: 0px;width: 97%;margin-left: 15px;">
                                <i class="wpdt-icon-info-circle-full"></i>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
                                </button>
                                <span class="wdt-alert-title f-600" style="font-size: 14px"><?php esc_html_e('Column types such as email, link, image, and attachment are already HTML links and will be added like that when you are using them as a transform value column. ', 'wpdatatables'); ?></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-7 transform-value-columns">
                                <p class="title"><?php esc_html_e('Column name :', 'wpdatatables'); ?></p>
                                <div class="transform-value-container">
                                    <!-- Columns will be added here -->
                                </div>
                            </div>
                            <div class="col-sm-5 transform-value-shortcodes">
                                <p class="title"><?php esc_html_e('Generate shortcodes :', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin" data-popover-content="#column-transform-value-generate-shortcodes-hint"
                                       data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                                </p>
                                <div class="hidden" id="column-transform-value-generate-shortcodes-hint">
                                    <div class="popover-heading">
                                        <?php esc_html_e('Generate shortcodes', 'wpdatatables'); ?>
                                    </div>

                                    <div class="popover-body">
                                        <?php esc_html_e('Fields are clickable, just click on the column that you want to generate as a transform value.', 'wpdatatables'); ?>
                                    </div>
                                </div>
                                <div class="transform-value-shortcodes-container">
                                    <!-- Columns will be added here -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--/#column-transform-value -->

                </div>
                <!-- /.tab-content -->
            </div>
            <!--/.tabpanel -->

        </div>
        <!-- /.wpDataTableContainer -->

        <div class="row">

            <div class="col-md-12 p-l-20 p-r-20" id="wdt-column-settings-buttons">
                <hr>
                <button class="btn btn-default btn-icon-text wdt-documentation"
                        data-doc-page="column_settings">
                    <i class="wpdt-icon-file-thin"></i> <?php esc_html_e('View Documentation', 'wpdatatables'); ?>
                </button>

                <div class="pull-right">
                    <button class="btn btn-danger btn-icon-text wdt-cancel-column-settings">
                        <?php esc_html_e('Cancel', 'wpdatatables'); ?>
                    </button>
                    <button class="btn btn-primary btn-icon-text wdt-column-apply">
                        <i class="wpdt-icon-save"></i> <?php esc_html_e('Save Changes', 'wpdatatables'); ?>
                    </button>
                </div>
            </div>
            <!-- /.col-md-12.p-l-20.p-r-20 -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.card-body -->

</div>
<!-- /.card  .column-settings-panel -->
