<?php defined('ABSPATH') or die('Access denied.'); ?>
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
                        <option value="hidden"><?php esc_html_e('Hidden (dynamic)', 'wpdatatables'); ?></option>
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
                    <select class="selectpicker" id="wdt-editing-default-value-selectpicker"
                            data-live-search="true">

                    </select>
                </div>
            </div>
        </div>

    </div>
    <div class="col-sm-6 wdt-dynamic-editing-predefined-block" hidden="hidden">
        <h4 class="c-title-color m-b-2">
            <?php esc_html_e('Dynamic predefined value(s)', 'wpdatatables'); ?>
            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
               title="<?php esc_attr_e('Here you can choose which dynamic value you need.', 'wpdatatables'); ?>"></i>
        </h4>

        <div class="form-group wdt-editing-hidden-default-value-selectpicker-block">
            <div class="fg-line">
                <div class="select">
                    <select class="selectpicker wdt-editing-hidden-default-value-select"
                            id="wdt-editing-hidden-default-value-selectpicker">
                        <optgroup label="<?php esc_html_e('Current User', 'wpdatatables'); ?>">
                            <option value="user-id"><?php esc_html_e('Current User ID', 'wpdatatables'); ?></option>
                            <option value="user-display-name"><?php esc_html_e('Current User Display Name', 'wpdatatables'); ?></option>
                            <option value="user-first-name"><?php esc_html_e('Current User First Name', 'wpdatatables'); ?></option>
                            <option value="user-last-name"><?php esc_html_e('Current User Last Name', 'wpdatatables'); ?></option>
                            <option value="user-email"><?php esc_html_e('Current User Email', 'wpdatatables'); ?></option>
                            <option value="user-login"><?php esc_html_e('Current User Login', 'wpdatatables'); ?></option>
                            <option value="user-ip"><?php esc_html_e('Current User IP Address', 'wpdatatables'); ?></option>
                        </optgroup>
                        <optgroup label="<?php esc_html_e('Current Date/Time', 'wpdatatables'); ?>">
                            <option value="date"><?php esc_html_e('Current Date', 'wpdatatables'); ?></option>
                            <option value="datetime"><?php esc_html_e('Current Datetime', 'wpdatatables'); ?></option>
                            <option value="time"><?php esc_html_e('Current Time', 'wpdatatables'); ?></option>
                        </optgroup>
                        <optgroup label="<?php esc_html_e('Placeholders', 'wpdatatables'); ?>">
                            <option value="p-var1"><?php esc_html_e('Placeholder %VAR1%', 'wpdatatables'); ?></option>
                            <option value="p-var2"><?php esc_html_e('Placeholder %VAR2%', 'wpdatatables'); ?></option>
                            <option value="p-var3"><?php esc_html_e('Placeholder %VAR3%', 'wpdatatables'); ?></option>
                            <option value="p-var4"><?php esc_html_e('Placeholder %VAR4%', 'wpdatatables'); ?></option>
                            <option value="p-var5"><?php esc_html_e('Placeholder %VAR5%', 'wpdatatables'); ?></option>
                            <option value="p-var6"><?php esc_html_e('Placeholder %VAR6%', 'wpdatatables'); ?></option>
                            <option value="p-var7"><?php esc_html_e('Placeholder %VAR7%', 'wpdatatables'); ?></option>
                            <option value="p-var8"><?php esc_html_e('Placeholder %VAR8%', 'wpdatatables'); ?></option>
                            <option value="p-var9"><?php esc_html_e('Placeholder %VAR9%', 'wpdatatables'); ?></option>
                        </optgroup>
                        <optgroup label="<?php esc_html_e('Post Data', 'wpdatatables'); ?>">
                            <option value="post-id"><?php esc_html_e('Post ID', 'wpdatatables'); ?></option>
                            <option value="post-title"><?php esc_html_e('Post Title', 'wpdatatables'); ?></option>
                            <option value="post-author"><?php esc_html_e('Post Author ID', 'wpdatatables'); ?></option>
                            <option value="post-type"><?php esc_html_e('Post Type', 'wpdatatables'); ?></option>
                            <option value="post-status"><?php esc_html_e('Post Status', 'wpdatatables'); ?></option>
                            <option value="post-parent"><?php esc_html_e('Post Parent ID', 'wpdatatables'); ?></option>
                            <option value="post-url"><?php esc_html_e('Post URL', 'wpdatatables'); ?></option>
                            <option value="post-meta"><?php esc_html_e('Post Meta Value as string', 'wpdatatables'); ?></option>
                            <option value="acf-data"><?php esc_html_e('ACF Data', 'wpdatatables'); ?></option>
                        </optgroup>
                        <optgroup label="<?php esc_html_e('HTTP Data', 'wpdatatables'); ?>">
                            <option value="user-agent"><?php esc_html_e('HTTP User Agent', 'wpdatatables'); ?></option>
                            <option value="refer-url"><?php esc_html_e('HTTP Refer URL', 'wpdatatables'); ?></option>
                            <option value="query-param"><?php esc_html_e('Query Parameter (GET)', 'wpdatatables'); ?></option>
                        </optgroup>
                    </select>
                </div>
            </div>
        </div>

    </div>
    <div class="col-sm-6 wdt-dynamic-editing-predefined-block wdt-editing-hidden-query-param-value-block"
         hidden="hidden">
        <h4 class="c-title-color m-b-2">
            <?php esc_html_e('Query Parameter Key', 'wpdatatables'); ?>
            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
               title="<?php esc_attr_e('If you would like to have some values from GET params, please insert GET parameter key here.', 'wpdatatables'); ?>"></i>
        </h4>

        <div class="form-group">
            <div class="fg-line">
                <input type="text" class="form-control input-sm wdt-editing-hidden-query-param-value" value=""
                       placeholder="<?php esc_attr_e('E.g query_parameter_key', 'wpdatatables'); ?>">
            </div>
        </div>
    </div>
    <div class="col-sm-6 wdt-dynamic-editing-predefined-block wdt-editing-hidden-post-meta-value-block" hidden="hidden">
        <h4 class="c-title-color m-b-2">
            <?php esc_html_e('Post Meta Key ', 'wpdatatables'); ?>
            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
               title="<?php esc_attr_e('If you would like to have some values from post meta, please insert post meta key here.', 'wpdatatables'); ?>"></i>
        </h4>

        <div class="form-group">
            <div class="fg-line">
                <input type="text" class="form-control input-sm wdt-editing-hidden-post-meta-value" value=""
                       placeholder="<?php esc_attr_e('E.g meta_key', 'wpdatatables'); ?>">
            </div>
        </div>
    </div>
    <div class="col-sm-6 wdt-dynamic-editing-predefined-block wdt-editing-hidden-acf-data-value-block" hidden="hidden">
        <h4 class="c-title-color m-b-2">
            <?php esc_html_e('ACF Key ', 'wpdatatables'); ?>
            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
               title="<?php esc_attr_e('If you would like to have some values from ACF fields, please insert ACF meta key here.', 'wpdatatables'); ?>"></i>
        </h4>

        <div class="form-group">
            <div class="fg-line">
                <input type="text" class="form-control input-sm wdt-editing-hidden-acf-data-value" value=""
                       placeholder="<?php esc_attr_e('E.g acf_key', 'wpdatatables'); ?>">
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