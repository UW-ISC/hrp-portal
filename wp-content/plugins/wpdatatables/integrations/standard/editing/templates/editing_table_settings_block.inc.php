<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
$wpRoles = new WP_Roles();
$wdtUserRoles = $wpRoles->get_names();
?>
<div class="row">

    <div class="col-sm-4 m-b-16">

        <h4 class="c-title-color m-b-2">
            <?php esc_html_e('Allow editing', 'wpdatatables'); ?>
            <i class=" wpdt-icon-info-circle-thin" data-popover-content="#front-end-editing-hint"
               data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
        </h4>

        <!-- Hidden popover with image hint -->
        <div class="hidden" id="front-end-editing-hint">
            <div class="popover-heading">
                <?php esc_html_e('Front-end editing', 'wpdatatables'); ?>
            </div>

            <div class="popover-body">
                <div class="thumbnail">
                    <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/front_end_editing.png"/>
                </div>
                <?php esc_html_e('Allow editing the table from the front-end.', 'wpdatatables'); ?>
            </div>
        </div>
        <!-- /Hidden popover with image hint -->

        <div class="toggle-switch" data-ts-color="blue">
            <input id="wdt-editable" type="checkbox">
            <label for="wdt-editable"
                   class="ts-label"><?php esc_html_e('Allow front-end editing', 'wpdatatables'); ?></label>
        </div>

    </div>

    <div class="col-sm-4 m-b-16 editing-settings-block hidden">

        <h4 class="c-title-color m-b-2">
            <?php esc_html_e('Popover edit block', 'wpdatatables'); ?>
            <i class=" wpdt-icon-info-circle-thin" data-popover-content="#popover-tools-hint"
               data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
        </h4>

        <!-- Hidden popover with image hint -->
        <div class="hidden" id="popover-tools-hint">
            <div class="popover-heading">
                <?php esc_html_e('Popover tools', 'wpdatatables'); ?>
            </div>

            <div class="popover-body">
                <div class="thumbnail">
                    <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/popover_tools_hint.png"/>
                </div>
                <?php esc_html_e('If this is enabled, the New, Edit and Delete buttons will appear in a popover when you click on any row, instead of Table Tools block above the table.', 'wpdatatables'); ?>
            </div>
        </div>
        <!-- /Hidden popover with image hint -->

        <div class="toggle-switch" data-ts-color="blue">
            <input id="wdt-popover-tools" type="checkbox">
            <label for="wdt-popover-tools"
                   class="ts-label"><?php esc_html_e('Editing buttons in a popover', 'wpdatatables'); ?></label>
        </div>

    </div>

    <div class="col-sm-4 m-b-16 editing-settings-block hidden">

        <h4 class="c-title-color m-b-2">
            <?php esc_html_e('In-line editing', 'wpdatatables'); ?>
            <i class=" wpdt-icon-info-circle-thin" data-popover-content="#inline-editing-hint"
               data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
        </h4>

        <!-- Hidden popover with image hint -->
        <div class="hidden" id="inline-editing-hint">
            <div class="popover-heading">
                <?php esc_html_e('In-line editing', 'wpdatatables'); ?>
            </div>

            <div class="popover-body">
                <div class="thumbnail">
                    <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/inline_editing_hint.png"/>
                </div>
                <?php esc_html_e('If this is enabled, front-end users will be able to edit cells by double-clicking them, not only with the editor dialog.', 'wpdatatables'); ?>
            </div>
        </div>
        <!-- /Hidden popover with image hint -->

        <div class="toggle-switch" data-ts-color="blue">
            <input id="wdt-inline-editable" type="checkbox">
            <label for="wdt-inline-editable"
                   class="ts-label"><?php esc_html_e('Allow in-line editing', 'wpdatatables'); ?></label>
        </div>

    </div>


</div>
<!-- /.row -->

<!-- .row -->
<div class="row">

    <div class="col-sm-4 m-b-16 editing-settings-block hidden">

        <h4 class="c-title-color m-b-2">
            <?php esc_html_e('MySQL table name for editing', 'wpdatatables'); ?>
            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="bottom"
               title="<?php esc_attr_e('Name of the MySQL table which will be updated when edited from front-end.', 'wpdatatables'); ?>"></i>
        </h4>

        <div class="fg-line">
            <input type="text" class="form-control"
                   placeholder="<?php esc_attr_e('MySQL table name', 'wpdatatables'); ?>"
                   id="wdt-mysql-table-name">
        </div>

    </div>

    <div class="col-sm-4 m-b-16 editing-settings-block hidden">

        <h4 class="c-title-color m-b-2">
            <?php esc_html_e('ID column for editing', 'wpdatatables'); ?>
            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
               title="<?php esc_attr_e('Choose the column values from which will be used as row identifiers. MUST be a unique auto-increment integer on MySQL side so insert/edit/delete would work correctly! wpDataTables will guess the correct column if it is called "id" or "ID" on MySQL side.', 'wpdatatables'); ?>"></i>
        </h4>

        <div class="select">
            <select class="form-control selectpicker" id="wdt-id-editing-column">

            </select>
        </div>

    </div>

    <div class="col-sm-4 m-b-16 editing-settings-block hidden">

        <h4 class="c-title-color m-b-2">
            <?php esc_html_e('Editor roles', 'wpdatatables'); ?>
            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
               title="<?php esc_attr_e('If you want only specific user roles to be able to edit the table, choose in this dropdown. Leave unchecked to allow editing for everyone.', 'wpdatatables'); ?>"></i>
        </h4>

        <div class="select">
            <select class="form-control selectpicker" multiple="multiple"
                    title="<?php esc_attr_e('Everyone', 'wpdatatables'); ?>"
                    id="wdt-editor-roles">
                <?php foreach ($wdtUserRoles as $wdtUserRole) {
                    /** @noinspection $wdtUserRoles */ ?>
                    <option value="<?php echo esc_attr($wdtUserRole) ?>"><?php echo esc_html($wdtUserRole) ?></option>
                <?php } ?>
            </select>
        </div>

    </div>

</div>
<!-- /.row -->

<!-- .row -->
<div class="row">

    <div class="col-sm-4 m-b-16 editing-settings-block hidden">

        <h4 class="c-title-color m-b-2">
            <?php esc_html_e('Users see and edit only own data', 'wpdatatables'); ?>
            <i class=" wpdt-icon-info-circle-thin" data-popover-content="#own-rows-hint"
               data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
        </h4>

        <!-- Hidden popover with image hint -->
        <div class="hidden" id="own-rows-hint">
            <div class="popover-heading">
                <?php esc_html_e('Users see and edit only their own data', 'wpdatatables'); ?>
            </div>

            <div class="popover-body">
                <div class="thumbnail">
                    <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/own_rows_hint.png"/>
                </div>
                <?php esc_html_e('If this is enabled, users will see and edit only the rows that are related to them or were created by them (associated using the User ID column).', 'wpdatatables'); ?>
            </div>
        </div>
        <!-- /Hidden popover with image hint -->

        <div class="toggle-switch" data-ts-color="blue">
            <input id="wdt-edit-only-own-rows" type="checkbox">
            <label for="wdt-edit-only-own-rows"
                   class="ts-label"><?php esc_html_e('Limit editing to own data only', 'wpdatatables'); ?></label>
        </div>

    </div>

    <div class="col-sm-4 m-b-16 own-rows-editing-settings-block hidden">

        <h4 class="c-title-color m-b-2">
            <?php esc_html_e('User ID column', 'wpdatatables'); ?>
            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
               title="<?php esc_attr_e('Choose the column values from which will be used as User identifiers. References the ID from WordPress Users table (wp_users), MUST be defined as an integer on MySQL side.', 'wpdatatables'); ?>"></i>
        </h4>

        <div class="select">
            <select class="form-control selectpicker" id="wdt-user-id-column">

            </select>
        </div>

    </div>

    <div class="col-sm-4 m-b-16 show-all-rows-editing-settings-block hidden">

        <h4 class="c-title-color m-b-2">
            <?php esc_html_e('Show all rows in back-end', 'wpdatatables'); ?>
            <i class="wpdt-icon-info-circle-thin" data-popover-content="#show-all-rows-hint"
               data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
        </h4>

        <!-- Hidden popover with image hint -->
        <div class="hidden" id="show-all-rows-hint">
            <div class="popover-heading">
                <?php esc_html_e('Show all rows in back-end', 'wpdatatables'); ?>
            </div>

            <div class="popover-body">
                <?php esc_html_e('If this is enabled, users will see all data for this table in admin area.', 'wpdatatables'); ?>
            </div>
        </div>
        <!-- /Hidden popover with image hint -->

        <div class="toggle-switch" data-ts-color="blue">
            <input id="wdt-show-all-rows" type="checkbox">
            <label for="wdt-show-all-rows"
                   class="ts-label"><?php esc_html_e('Show all rows for this table in admin area', 'wpdatatables'); ?></label>
        </div>

    </div>

    <!-- .row -->
    <div class="col-sm-4 m-b-16 editing-settings-block hidden">

        <h4 class="c-title-color m-b-2">
            <?php esc_html_e('Edit buttons to be displayed on the front-end', 'wpdatatables'); ?>
            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
               title="<?php esc_attr_e('If you want to include only certain editing buttons on the front-end, select them from this dropdown. Leave unchecked to show all buttons.', 'wpdatatables'); ?>"></i>
        </h4>

        <div class="select">
            <select class="form-control selectpicker" multiple="multiple"
                    title="<?php esc_attr_e('All', 'wpdatatables'); ?>"
                    id="wdt-edit-buttons-displayed">
                <?php $wdtEditButtonsDisplayed = array('New Entry', 'Edit', 'Delete');
                if (isset($tableData) && $tableData->table->enableDuplicateButton) {
                    $wdtEditButtonsDisplayed[] = 'Duplicate';
                }
                foreach ($wdtEditButtonsDisplayed as $wdtEditButtonDisplayed) {
                    /** @noinspection $wdtEditButtonsDisplayed */ ?>
                    <option value="<?php echo esc_attr(str_replace(' ', '_', strtolower($wdtEditButtonDisplayed))) ?>"><?php echo esc_html($wdtEditButtonDisplayed) ?></option>
                <?php } ?>
            </select>
        </div>

    </div>

    <div class="col-sm-4 m-b-16 editing-settings-block
                            <?php if (defined('WDT_GF_VERSION') &&
        version_compare(WDT_GF_VERSION, "1.6.3", '<=')) { ?> hideDuplicateForGF<?php } ?>">
        <h4 class="c-title-color m-b-4">
            <?php esc_html_e('Show duplicate button', 'wpdatatables'); ?>
            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
               title="<?php esc_attr_e('Enable the duplicate button in Editing buttons', 'wpdatatables'); ?>"></i>
        </h4>
        <div class="toggle-switch" data-ts-color="blue">
            <input id="wdt-enable-duplicate-button" type="checkbox">
            <label for="wdt-enable-duplicate-button"
                   class="ts-label"><?php esc_html_e('Enable duplicate button', 'wpdatatables'); ?></label>
        </div>
    </div>

    <!-- /.row -->

</div>
<!-- /.row -->