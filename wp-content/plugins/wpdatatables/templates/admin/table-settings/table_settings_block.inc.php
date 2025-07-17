<?php
/**
 * Template for Table Settings widget
 * @author Alexander Gilmanov
 * @since 13.10.2016
 */

defined('ABSPATH') or die('Access denied.');
?>

<div class="card wdt-table-settings">

    <?php
    $wpRoles = new WP_Roles();
    $wdtUserRoles = $wpRoles->get_names();
    $globalAutoUpdateOption = get_option('wdtAutoUpdateOption');
    $connection_type = '';
    // set connection if $connection is not set with GET parameter
    if ($connection === null) {
        if (Connection::enabledSeparate()) {
            foreach (Connection::getAll() as $wdtSeparateConnection) {
                if ($wdtSeparateConnection['default']) {
                    $connection = $wdtSeparateConnection['id'];
                    $connection_type = $wdtSeparateConnection['vendor'];
                }
            }
        } else {
            $connection = '';
        }
    }
    ?>

    <input type="hidden" id="wdt-table-connection" data-vendor="<?php echo esc_attr($connection_type); ?>"
           value="<?php echo esc_attr($connection); ?>"/>

    <!-- Preloader -->
    <?php include WDT_TEMPLATE_PATH . 'admin/common/preloader.inc.php'; ?>
    <!-- /Preloader -->

    <div class="card-header wdt-admin-card-header ch-alt ">
        <img id="wpdt-inline-logo"
             src="<?php echo WDT_ROOT_URL; ?>assets/img/logo.svg"/>
        <h2 class="pull-left">
            <div class="fg-line wdt-table-name">
                <input type="text" class="form-control input-sm" value="New wpDataTable" id="wdt-table-title-edit">
                <i class="wpdt-icon-pen"></i>
            </div>

            <small class="m-t-5 m-l-5"><?php esc_html_e('wpDataTable name, click to edit', 'wpdatatables'); ?></small>
        </h2>
        <button class="btn hidden" id="wdt-table-id">[wpdatatable id=23]
        </button>
        <div class="clear"></div>
        <h2 class="pull-left">
            <div class="col-sm-2-4 ">
                <textarea class="form-control" value="Insert wpDataTable description" id="wdt-table-description-edit"
                          placeholder="<?php esc_attr_e('Insert description of your wpDataTable', 'wpdatatables'); ?>"></textarea>
                <!--                <i class="wpdt-icon-pen"></i>-->
            </div>
        </h2>
        <div class="clear"></div>
        <ul id="wdt-tour-actions" class="actions p-t-5">
            <li>
                <button class="btn wdt-collapse-table-settings <?php if (isset($_GET['collapsed'])) { ?>collapsed <?php } else { ?>expanded <?php } ?>">
                    <?php esc_html_e('Settings', 'wpdatatables'); ?>
                    <i style="color: #008CFF;"
                       class="wpdt-icon-angle-<?php if (isset($_GET['collapsed'])) { ?>down <?php } else { ?>up <?php } ?>"></i>
                </button>
            </li>
            <li>
                <button class="btn wdt-backend-close">
                    <?php esc_html_e('Cancel', 'wpdatatables'); ?>
                </button>
            </li>
            <li>
                <button class="btn wdt-reset-customize-options" style="display: none"
                        title="<?php esc_attr_e('Reset customize options to default', 'wpdatatables'); ?>"
                        data-toggle="tooltip">
                    <?php esc_html_e('Reset options', 'wpdatatables'); ?>
                </button>
            </li>
            <li>
                <button disabled="disabled"
                        class="btn btn-primary wdt-apply"
                        title="<?php esc_attr_e('Save Changes', 'wpdatatables'); ?>" data-toggle="tooltip">
                    <i class="wpdt-icon-save"></i><?php esc_html_e('Save Changes', 'wpdatatables'); ?>
                </button>
            </li>
        </ul>
    </div>
    <!-- /.card-header -->
    <div class="card-body card-padding" <?php if (isset($_GET['collapsed'])) { ?> style="display: none" <?php } ?>>
        <div role="tabpanel">
            <ul class="tab-nav wdt-main-menu" role="tablist">
                <li class="active main-table-settings-tab">
                    <a href="#main-table-settings" aria-controls="main-table-settings" role="tab"
                       data-toggle="tab"><?php esc_html_e('Data source', 'wpdatatables'); ?></a>
                </li>
                <li class="display-settings-tab hidden">
                    <a href="#display-settings" aria-controls="display-settings" role="tab"
                       data-toggle="tab"><?php esc_html_e('Display', 'wpdatatables'); ?></a>
                </li>
                <li class="table-sorting-filtering-settings-tab hidden">
                    <a href="#table-sorting-filtering-settings" aria-controls="table-sorting-filtering-settings"
                       role="tab" data-toggle="tab"><?php esc_html_e('Sorting and filtering', 'wpdatatables'); ?></a>
                </li>
                <li class="editing-settings-tab hidden">
                    <a href="#editing-settings" aria-controls="editing-settings" role="tab"
                       data-toggle="tab"><?php esc_html_e('Editing', 'wpdatatables'); ?></a>
                </li>
                <li class="table-tools-settings-tab hidden">
                    <a href="#table-tools-settings" aria-controls="table-tools-settings" role="tab"
                       data-toggle="tab"><?php esc_html_e('Table Tools', 'wpdatatables'); ?></a>
                </li>
                <li class="placeholders-settings-tab hidden">
                    <a href="#placeholders-settings" aria-controls="placeholders-settings" role="tab"
                       data-toggle="tab"><?php esc_html_e('Placeholders', 'wpdatatables'); ?></a>
                </li>
                <li class="customize-table-settings-tab hidden">
                    <a href="#customize-table-settings" aria-controls="customize-table-settings" role="tab"
                       data-toggle="tab"><?php esc_html_e(' Customize', 'wpdatatables'); ?>
                    </a>
                </li>
                <li class="advanced-table-settings-tab hidden">
                    <a href="#advanced-table-settings" aria-controls="advanced-table-settings" role="tab"
                       data-toggle="tab"><strong
                                style="color: #ef8137"><?php esc_html_e('NEW!', 'wpdatatables'); ?></strong> <?php esc_html_e(' Advanced ', 'wpdatatables'); ?>
                    </a>
                </li>

                <?php do_action_deprecated('wdt_add_table_configuration_tab', array(), WDT_INITIAL_STARTER_VERSION, 'wpdatatables_add_table_configuration_tab'); ?>
                <?php do_action('wpdatatables_add_table_configuration_tab'); ?>

            </ul>
            <!-- /ul .tab-nav -->

            <div class="tab-content wdt-main">
                <!-- Main table settings -->
                <div role="tabpanel" class="tab-pane active" id="main-table-settings">

                    <div class="row">

                        <?php if (isset($tableData)) do_action('wpdatatables_add_data_source_tab', $tableData->table->table_type); ?>

                        <div class="col-sm-6 wdt-input-data-source-type">
                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Input data source type', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php esc_attr_e('Please choose a type of the input data source - it can be a MySQL query, a file, or an URL. Only MySQL query-based tables can use server-side processing', 'wpdatatables'); ?>"></i>
                            </h4>
                            <!-- input source type selection -->
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="selectpicker" id="wdt-table-type">
                                            <option value=""><?php esc_html_e('Select a data source type', 'wpdatatables'); ?></option>
                                            <option value="mysql"><?php esc_html_e('SQL query', 'wpdatatables'); ?></option>
                                            <option value="csv"><?php esc_html_e('CSV file', 'wpdatatables'); ?></option>
                                            <option value="xls"><?php esc_html_e('Excel file', 'wpdatatables'); ?></option>
                                            <option value="google_spreadsheet"><?php esc_html_e('Google Spreadsheet', 'wpdatatables'); ?></option>
                                            <option value="xml"><?php esc_html_e('XML file', 'wpdatatables'); ?></option>
                                            <option value="json"><?php esc_html_e('JSON file', 'wpdatatables'); ?></option>
                                            <option value="nested_json"><?php esc_html_e('Nested JSON', 'wpdatatables'); ?></option>
                                            <option value="serialized"><?php esc_html_e('Serialized PHP array', 'wpdatatables'); ?></option>
                                            <?php do_action_deprecated('wdt_add_table_type_option', array(), WDT_INITIAL_STARTER_VERSION, 'wpdatatables_add_table_type_option'); ?>
                                            <?php do_action('wpdatatables_add_table_type_option'); ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- /input source type selection -->
                        </div>

                        <div class="col-sm-4 wdt-file-location hidden">
                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('File location', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php esc_attr_e('Please choose a file location (WordPress Media Library or URL from any domain) for CSV or Excel files. Default option is WordPress Media Library.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <!-- input source type selection -->
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="selectpicker" id="wdt-file-location">
                                            <option value="wp_media_lib"><?php esc_html_e('WordPress Media Library', 'wpdatatables'); ?></option>
                                            <option value="wp_any_url"><?php esc_html_e('URL from any domain', 'wpdatatables'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- /input source type selection -->
                        </div>

                        <div class="col-sm-6 input-path-block hidden" id="wdt-input-path-block">
                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Input file path or URL', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php esc_attr_e('Upload your file or provide the full URL here. For CSV or Excel input sources only URLs or paths from same domain are supported. For Google Spreadsheets: please do not forget to publish the spreadsheet before pasting the URL.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <!-- input URL or path -->
                            <div class="form-group">
                                <div class="fg-line col-sm-9 p-0">
                                    <input type="text" id="wdt-input-url" class="form-control input-sm"
                                           placeholder="<?php esc_attr_e('Paste URL or path, or click Browse to choose', 'wpdatatables'); ?>">
                                </div>
                                <div class="col-sm-3">
                                    <button class="btn btn-primary" id="wdt-browse-button">
                                        <?php esc_html_e('Browse...', 'wpdatatables'); ?>
                                    </button>
                                </div>
                            </div>
                            <!-- /input URL or path -->
                        </div>

                        <div class="col-sm-6 input-nested-json-url-block hidden">
                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Input JSON URL', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php esc_attr_e('Insert JSON URL. Please note that you are able to use dynamic Placeholders for this input like https://api.com/v1/data/%VAR1%/', 'wpdatatables'); ?>"></i>
                            </h4>
                            <!-- input JSON URL -->
                            <div class="form-group">
                                <div class="fg-line col-sm-9 p-0">
                                    <input type="text" id="wdt-nested-json-url" class="form-control input-sm"
                                           placeholder="<?php esc_attr_e('Insert or paste JSON URL', 'wpdatatables'); ?>">
                                </div>
                                <div class="col-sm-3 p-r-0">
                                    <button class="btn btn-primary" id="wdt-get-nested-json-roots">
                                        <?php esc_html_e('Get JSON roots', 'wpdatatables'); ?>
                                    </button>
                                </div>
                            </div>
                            <!-- /input JSON URL -->
                        </div>

                        <?php do_action_deprecated('wdt_add_data_source_elements', array(), WDT_INITIAL_STARTER_VERSION, 'wpdatatables_add_data_source_elements'); ?>
                        <?php do_action('wpdatatables_add_data_source_elements'); ?>

                        <div class="col-sm-6 hidden wdt-server-side-processing">
                            <!-- Server side processing toggle -->
                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Server-side processing', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php esc_attr_e('If it is turned on, all sorting, filtering, pagination and other data interaction will be done by MySQL server. This feature is recommended if you have more than 2000-3000 rows. Mandatory for editable tables.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-server-side" class="wdt-server-side" type="checkbox" checked="checked">
                                <label for="wdt-server-side"
                                       class="ts-label"><?php esc_html_e('Enable server-side processing', 'wpdatatables'); ?></label>
                            </div>
                            <!-- /Server side processing toggle -->
                        </div>

                    </div>
                    <!-- /.row -->

                    <div class="row">

                        <?php do_action('wpdatatables_add_mysql_settings_block', $connection); ?>

                        <div class="col-sm-6 hidden wdt-auto-refresh wdt-add-data-source-change-field">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Auto-refresh', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php esc_attr_e('If you enter a non-zero value, table will auto-refresh to show actual data with a given interval of seconds. Leave zero or empty not to use auto-refresh.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line wdt-custom-number-input">
                                    <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                            data-type="minus" data-field="wdt-auto-refresh">
                                        <i class="wpdt-icon-minus"></i>
                                    </button>
                                    <input type="number" name="wdt-auto-refresh" min="0"
                                           class="form-control input-sm input-number"
                                           id="wdt-auto-refresh"
                                           placeholder="<?php esc_attr_e('Auto-refresh interval in seconds (zero or blank to disable)', 'wpdatatables'); ?>">
                                    <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                            data-type="plus" data-field="wdt-auto-refresh">
                                        <i class="wpdt-icon-plus-full"></i>
                                    </button>
                                </div>
                            </div>

                        </div>

                        <?php do_action('wpdatatables_add_data_from_source_file_block'); ?>

                    </div>

                    <!-- Block for Nested JSON options -->
                    <div class="row hidden" id="wdt-nested-json-block">
                        <!-- Choose method -->
                        <div class="col-sm-3 nested-json-get-method">
                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Choose method', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php esc_attr_e('Choose method GET or POST for getting data. GET is set by default.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <!-- select JSON HTTP method -->
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="selectpicker" id="wdt-nested-json-get-type">
                                            <option value="get"><?php esc_html_e('GET', 'wpdatatables'); ?></option>
                                            <option value="post"><?php esc_html_e('POST', 'wpdatatables'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- /select JSON HTTP method -->
                        </div>
                        <!-- /Choose method -->

                        <!-- JSON authentication -->
                        <div class="col-sm-3 nested-json-auth-options">
                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('JSON authentication', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php esc_attr_e('Set JSON authentication option. You can choose "Basic Authentication" with username and password or "No Auth" option. By default is set to "No Auth".', 'wpdatatables'); ?>"></i>
                            </h4>
                            <!-- select JSON Auth option -->
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="selectpicker" id="wdt-nested-json-auth-option">
                                            <option value=""><?php esc_html_e('No Auth', 'wpdatatables'); ?></option>
                                            <option value="basic_auth"><?php esc_html_e('Basic Authentication', 'wpdatatables'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- /select JSON Auth option -->
                        </div>
                        <!-- /JSON authentication -->

                        <!-- Basic Authentication Credentials -->
                        <div class="col-sm-6 nested-json-basic-auth-inputs hidden">
                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Basic Authentication Credentials', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php esc_attr_e('Credentials for Basic Authentication ex. Username and Password', 'wpdatatables'); ?>"></i>
                            </h4>
                            <!-- input Username and Password -->
                            <div class="form-group">
                                <div class="col-sm-6 p-l-0">
                                    <input type="text" id="wdt-nested-json-username" class="form-control input-sm"
                                           placeholder="<?php esc_attr_e('ex. Username', 'wpdatatables'); ?>">
                                </div>
                                <div class="col-sm-6 p-0">
                                    <input type="password" id="wdt-nested-json-password" class="form-control input-sm"
                                           placeholder="<?php esc_attr_e('ex. Password', 'wpdatatables'); ?>">
                                </div>
                            </div>
                            <!-- input Username and Password -->
                        </div>
                        <!-- /Basic Authentication Credentials -->
                    </div>
                    <!-- /Block for Nested JSON options -->

                    <!-- Block for Nested JSON additional options -->
                    <div class="row hidden" id="wdt-nested-json-additional-block">
                        <!-- Custom headers option-->
                        <div class="col-sm-6 json-custom-headers">
                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Custom headers', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php esc_attr_e('Headers are a key–value pair in clear-text string format. Add custom headers for API like key value pairs, ex. for key name insert apiKey, and for key value apiKeyValue.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <!-- inputs for custom headers: Key Name and Key Value-->
                            <div class="wdt-nested-json-custom-headers-container">
                                <div class="row wdt-custom-headers-row-rule">
                                    <div class="col-sm-6 wdt-custom-header-key-name">
                                        <div class="form-group m-b-10">
                                            <input placeholder="<?php esc_attr_e('Insert key name', 'wpdatatables'); ?>"
                                                   type="text"
                                                   class="form-control input-sm custom-header-key-name-value" value="">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 wdt-custom-header-key-value">
                                        <div class="form-group m-b-10">
                                            <textarea
                                                    placeholder="<?php esc_attr_e('Insert key value', 'wpdatatables'); ?>"
                                                    type="text"
                                                    class="form-control input-sm custom-header-key-value-value"
                                                    value=""></textarea>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <!-- /inputs for custom headers: Key Name and Key Value-->

                            <!-- Add row button for custom headers -->
                            <div class="row">
                                <div class="col-sm-12">
                                    <button class="btn pull-left m-t-10 wdt-add-nested-json-custom-headers-row">
                                        <i class="wpdt-icon-plus-thin"></i> <?php esc_html_e('Add Row', 'wpdatatables'); ?>
                                    </button>
                                </div>
                            </div>
                            <!-- /Add row button for custom headers -->
                        </div>
                        <!-- /Custom headers option-->

                        <!-- JSON root-->
                        <div class="col-sm-6 nested-json-roots hidden">
                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Choose JSON root', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php esc_attr_e('Here will be listed all roots from JSON endpoint. Every key that is array or object will be treated as separate root path. Choose root path where is your data.', 'wpdatatables'); ?>"></i>
                            </h4>
                            <!-- select JSON root -->
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="selectpicker" id="wdt-nested-json-root">
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- /select JSON root -->
                        </div>
                        <!-- /JSON root-->
                    </div>
                    <!-- Block for Nested JSON additional options -->

                    <div class="row" id="wdt-cache-block">
                        <div class="col-sm-3 m-b-16 hidden cache-settings-block">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Cache data', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#cache-source-data-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="cache-source-data-hint">
                                <div class="popover-heading">
                                    <?php esc_html_e('Cache data from source', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <?php esc_html_e('Enable this to cache data from source file. Available for tables created from existing data source like Excel, CSV, JSON, Nested JSON, Google Spredsheet and PHP array.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wpdt-cache-source-data" type="checkbox">
                                <label for="wpdt-cache-source-data"
                                       class="ts-label"><?php esc_html_e('Cache data from source', 'wpdatatables'); ?></label>
                            </div>

                        </div>
                        <?php if ($globalAutoUpdateOption) { ?>
                            <div class="col-sm-3 m-b-16 hidden auto-update-cache-block">

                                <h4 class="c-title-color m-b-2">
                                    <?php esc_html_e('Auto update cache', 'wpdatatables'); ?>
                                    <i class=" wpdt-icon-info-circle-thin"
                                       data-popover-content="#auto-update-cache-hint"
                                       data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                                </h4>

                                <!-- Hidden popover with image hint -->
                                <div class="hidden" id="auto-update-cache-hint">
                                    <div class="popover-heading">
                                        <?php esc_html_e('Auto update cache from source', 'wpdatatables'); ?>
                                    </div>

                                    <div class="popover-body">
                                        <?php esc_html_e('Enable this to auto update cache from source file.', 'wpdatatables'); ?>
                                    </div>
                                </div>
                                <!-- /Hidden popover with image hint -->

                                <div class="toggle-switch" data-ts-color="blue">
                                    <input id="wpdt-auto-update-cache" type="checkbox">
                                    <label for="wpdt-auto-update-cache"
                                           class="ts-label"><?php esc_html_e('Auto update cache from source', 'wpdatatables'); ?></label>
                                </div>

                            </div>
                        <?php } ?>
                    </div>
                </div>
                <!-- /Main table settings -->

                <!-- Table display settings -->
                <div role="tabpanel" class="tab-pane fade" id="display-settings">

                    <div class="row">
                        <div class="col-sm-3 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Table title', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#table-title-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="table-title-hint">
                                <div class="popover-heading">
                                    <?php esc_html_e('Show table title', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/table_title.png"/>
                                    </div>
                                    <?php esc_html_e('Enable this to show the table title in a h3 block above the table, disable to hide.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-show-title" type="checkbox" checked="checked">
                                <label for="wdt-show-title"
                                       class="ts-label"><?php esc_html_e('Show table title on the page', 'wpdatatables'); ?></label>
                            </div>

                        </div>
                        <div class="col-sm-3 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Table description', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#table-description-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="table-description-hint">
                                <div class="popover-heading">
                                    <?php esc_html_e('Show table description', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <?php esc_html_e('Enable this to show the table descrtiption, disable to hide.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-show-description" type="checkbox">
                                <label for="wdt-show-description"
                                       class="ts-label"><?php esc_html_e('Show table description on the page', 'wpdatatables'); ?></label>
                            </div>
                        </div>


                        <div class="col-sm-3 m-b-16 wdt-responsive-block">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Responsiveness', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#table-responsive-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="table-responsive-hint">
                                <div class="popover-heading">
                                    <?php esc_html_e('Responsive design', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/responsive.png"/>
                                    </div>
                                    <?php esc_html_e('Enable this to allow responsiveness in the table.', 'wpdatatables'); ?>
                                    <strong><?php esc_html_e('Please do not forget to define which columns will be hidden on mobiles and tablets in the column settings!', 'wpdatatables'); ?></strong>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-responsive" type="checkbox" checked="checked">
                                <label for="wdt-responsive"
                                       class="ts-label"><?php esc_html_e('Allow collapsing on mobiles and tablets', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                        <div class="col-sm-3 m-b-16 responsive-action-block hidden">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Responsive action', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php esc_attr_e('Choose between different options when responsive is turned on.', 'wpdatatables'); ?>"></i>
                            </h4>

                            <div class="select">
                                <select class="form-control selectpicker"
                                        id="wdt-responsive-action">
                                    <option value="icon"><?php esc_html_e('Icon', 'wpdatatables'); ?></option>
                                    <option value="cell"><?php esc_html_e('Cell', 'wpdatatables'); ?></option>
                                    <option value="row"><?php esc_html_e('Row', 'wpdatatables'); ?></option>
                                </select>
                            </div>

                        </div>

                        <div class="col-sm-3 m-b-16 wdt-hide-until-load-block">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Hide until loaded', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php esc_attr_e('Enable to make whole table hidden until it is initialized to prevent unformatted data flashing', 'wpdatatables'); ?>"></i>
                            </h4>
                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-hide-until-loaded" type="checkbox" checked="checked">
                                <label for="wdt-hide-until-loaded"
                                       class="ts-label"><?php esc_html_e('Hide the table before it is fully loaded', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                    </div>
                    <!-- /.row -->

                    <div class="row">

                        <div class="col-sm-3 wdt-default-rows-per-page">
                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Default rows per page', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#rows-per-page-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="rows-per-page-hint">
                                <div class="popover-heading">
                                    <?php esc_html_e('Rows per page', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/rows_per_page.png"/>
                                    </div>
                                    <?php esc_html_e('How many rows to show per page by default?', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <!-- Rows per page selection -->
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="form-control selectpicker" id="wdt-rows-per-page"
                                                data-original-data-length="1,5,10,25,50,100,-1">
                                            <option value="1">1</option>
                                            <option value="5">5</option>
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                            <option value="-1"><?php esc_html_e('All', 'wpdatatables'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- /rows per page selection -->

                        </div>
                        <div class="col-sm-3 m-b-16 custom-rows-per-page-block">
                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Custom rows per page', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#custom-rows-per-page-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="custom-rows-per-page-hint">
                                <div class="popover-heading">
                                    <?php esc_html_e('Custom rows per page', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <?php esc_html_e('Enter number of rows you want to display. To choose the number of rows you want to display in the table, you can input a specific sequence. ', 'wpdatatables'); ?>
                                    <br>
                                    <strong> <?php esc_html_e('The format for the sequence should be 1,3,7,15,45,200 - each number followed by a comma.', 'wpdatatables'); ?></strong>
                                    <br>
                                    <strong> <?php esc_html_e('You need to add -1 to the sequence to display all rows.', 'wpdatatables'); ?></strong>
                                    <br>
                                    <?php esc_html_e('This sequence will serve as the options for selecting the number of rows you want to be shown.', 'wpdatatables'); ?>
                                    <br>
                                    <br>
                                    <strong> <?php esc_html_e('Examples: ', 'wpdatatables'); ?></strong>
                                    <br>
                                    <?php esc_html_e(' 1,3,7,15,45,200', 'wpdatatables'); ?>
                                    <br>
                                    <?php esc_html_e(' 5,12,25,100,-1', 'wpdatatables'); ?>
                                    <br>
                                    <?php esc_html_e(' 1,100,200,300', 'wpdatatables'); ?>
                                    <br>
                                    <?php esc_html_e(' 20,40,60,80,-1', 'wpdatatables'); ?>
                                    <br>
                                    <br>
                                    <?php esc_html_e('After selecting a custom number of rows per page, please make sure to also choose how this selection will appear in the default rows per page option. ', 'wpdatatables'); ?>
                                    <strong> <?php esc_html_e('If you don\'t make a choice, the first option from the custom display row will be automatically selected, and you can change it afterwards.', 'wpdatatables'); ?></strong>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="fg-line form-group m-b-0">
                                <input id="wdt-custom-rows-per-page" type="text" class="form-control input-sm"
                                       placeholder="<?php esc_attr_e('1,3,7,15,45,200', 'wpdatatables'); ?>">
                            </div>

                        </div>
                        <div class="col-sm-3 m-b-16 wdt-rows-per-page-block">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Rows per page', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#show-rows-per-page-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="show-rows-per-page-hint">
                                <div class="popover-heading">
                                    <?php esc_html_e('Show X entries', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/rows_per_page.png"/>
                                    </div>
                                    <?php esc_html_e('Enable/disable this to show/hide "Show X entries" per page dropdown on the frontend.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-show-rows-per-page" type="checkbox" checked="checked">
                                <label for="wdt-show-rows-per-page"
                                       class="ts-label"><?php esc_html_e('Show "Show X entries" dropdown', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                        <div class="col-sm-3 m-b-16 wdt-scrollable-block">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Scrollable', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#scrollable-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="scrollable-hint">
                                <div class="popover-heading">
                                    <?php esc_html_e('Scrollable table', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/scrollable.png"/>
                                    </div>
                                    <?php esc_html_e('Enable this to enable a horizontal scrollbar below the table.', 'wpdatatables'); ?>
                                    <strong><?php esc_html_e('This should be turned off if you want to set columns width manually.', 'wpdatatables'); ?></strong>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-scrollable" type="checkbox">
                                <label for="wdt-scrollable"
                                       class="ts-label"><?php esc_html_e('Show a horizontal scrollbar', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                    </div>
                    <!-- /.row -->

                    <div class="row">

                        <div class="col-sm-3 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Info block', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#info-block-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="info-block-hint">
                                <div class="popover-heading">
                                    <?php esc_html_e('Info block', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/info_block.png"/>
                                    </div>
                                    <?php esc_html_e('Enable to show a block of information about the number of records below the table.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-info-block" type="checkbox" checked="checked">
                                <label for="wdt-info-block"
                                       class="ts-label"><?php esc_html_e('Show information block below the table', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                        <div class="col-sm-3 m-b-16 limit-table-width-settings-block">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Limit table width', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#limit-width-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="limit-width-hint">
                                <div class="popover-heading">
                                    <?php esc_html_e('Limit table width', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/limit_width.png"/>
                                    </div>
                                    <?php esc_html_e('Enable this to restrict table width to page width.', 'wpdatatables'); ?>
                                    <strong><?php esc_html_e('This should be turned on if you want to set columns width manually. Should be on to use word wrapping.', 'wpdatatables'); ?></strong>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-limit-layout" type="checkbox">
                                <label for="wdt-limit-layout"
                                       class="ts-label"><?php esc_html_e('Limit table width to page width', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                        <div class="col-sm-3 m-b-16 word-wrap-settings-block hidden">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Word wrap', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#word-wrap-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="word-wrap-hint">
                                <div class="popover-heading">
                                    <?php esc_html_e('Word wrap', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/word_wrap.png"/>
                                    </div>
                                    <?php esc_html_e('Enable this to wrap long strings into multiple lines and stretch the cells height.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-word-wrap" type="checkbox">
                                <label for="wdt-word-wrap"
                                       class="ts-label"><?php esc_html_e('Wrap words to newlines', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                        <div class="col-sm-3 m-b-16 pagination-on-top">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Return to table top', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#pagination-top-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="pagination-top-hint">
                                <div class="popover-heading">
                                    <?php esc_html_e('Return to table top', 'wpdatatables'); ?>
                                </div>
                                <div class="popover-body">
                                    <?php esc_html_e('Activate this option to automatically scroll users to the top of the table after the pagination button clicks.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-pagination-top" type="checkbox">
                                <label for="wdt-pagination-top"
                                       class="ts-label"><?php esc_html_e('Return to table top after pagination click', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                    </div>
                    <!-- /.row -->

                    <div class="row">

                        <div class="col-sm-3 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Pagination', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#pagination"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="pagination">
                                <div class="popover-heading">
                                    <?php esc_html_e('Pagination', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <?php esc_html_e('Enable to show a pagination', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-pagination" type="checkbox" checked="checked">
                                <label for="wdt-pagination"
                                       class="ts-label"><?php esc_html_e('Show pagination block below the table', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                        <div class="col-sm-3 m-b-16 pagination-align-settings-block">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Pagination Alignment', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#pagination-align"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="pagination-align">
                                <div class="popover-heading">
                                    <?php esc_html_e('Pagination Alignment', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <?php esc_html_e('Here you can set pagination position: right, center or left.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="select">
                                <select class="form-control selectpicker"
                                        id="wdt-pagination-align">
                                    <option value="right"><?php esc_html_e('Right', 'wpdatatables'); ?></option>
                                    <option value="center"><?php esc_html_e('Center', 'wpdatatables'); ?></option>
                                    <option value="left"><?php esc_html_e('Left', 'wpdatatables'); ?></option>
                                </select>
                            </div>

                        </div>

                        <div class="col-sm-3 m-b-16 pagination-layout-settings-block">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Pagination Layout', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#pagination-layout"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="pagination-layout">
                                <div class="popover-heading">
                                    <?php esc_html_e('Pagination Layout', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <?php esc_html_e('Here you can choose between different pagination layout.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="select">
                                <select class="form-control selectpicker"
                                        id="wdt-pagination-layout">
                                    <option value="full_numbers"><?php esc_html_e('"First", "Previous", "Next" and "Last" buttons, plus page numbers', 'wpdatatables'); ?></option>
                                    <option value="simple"><?php esc_html_e('"Previous" and "Next" buttons only', 'wpdatatables'); ?></option>
                                    <option value="simple_numbers"><?php esc_html_e('"Previous" and "Next" buttons, plus page numbers', 'wpdatatables'); ?></option>
                                    <option value="full"><?php esc_html_e('"First", "Previous", "Next" and "Last" buttons', 'wpdatatables'); ?></option>
                                    <option value="numbers"><?php esc_html_e('Page number buttons only', 'wpdatatables'); ?></option>
                                    <option value="first_last_numbers"><?php esc_html_e('"First" and "Last" buttons, plus page numbers', 'wpdatatables'); ?></option>
                                </select>
                            </div>

                        </div>
                        <div class="col-sm-3 m-b-16 pagination-layout-mobile-settings-block">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Pagination Layout for mobile', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#pagination-layout-mobile"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="pagination-layout-mobile">
                                <div class="popover-heading">
                                    <?php esc_html_e('Pagination Layout for mobile devices', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <?php esc_html_e('Here you can choose between different pagination layout for mobile devices.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="select">
                                <select class="form-control selectpicker"
                                        id="wdt-pagination-layout-mobile">
                                    <option value="full_numbers"><?php esc_html_e('"First", "Previous", "Next" and "Last" buttons, plus page numbers', 'wpdatatables'); ?></option>
                                    <option value="simple"><?php esc_html_e('"Previous" and "Next" buttons only', 'wpdatatables'); ?></option>
                                    <option value="simple_numbers"><?php esc_html_e('"Previous" and "Next" buttons, plus page numbers', 'wpdatatables'); ?></option>
                                    <option value="full"><?php esc_html_e('"First", "Previous", "Next" and "Last" buttons', 'wpdatatables'); ?></option>
                                    <option value="numbers"><?php esc_html_e('Page number buttons only', 'wpdatatables'); ?></option>
                                    <option value="first_last_numbers"><?php esc_html_e('"First" and "Last" buttons, plus page numbers', 'wpdatatables'); ?></option>
                                </select>
                            </div>

                        </div>

                    </div>
                    <!-- /.row -->

                </div>
                <!-- /Table display settings -->

                <!-- Table sorting and filtering settings -->
                <div role="tabpanel" class="tab-pane fade" id="table-sorting-filtering-settings">
                    <!-- .row -->
                    <div class="row">

                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Advanced column filters', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#advanced-filter-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="advanced-filter-hint">
                                <div class="popover-heading">
                                    <?php esc_html_e('Advanced filter', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/advanced_filter.png"/>
                                    </div>
                                    <?php esc_html_e('Enable to show an advanced filter for each of the columns, filters can be shown in table footer, header or in a separate form.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-advanced-filter" type="checkbox" checked="checked">
                                <label for="wdt-advanced-filter"
                                       class="ts-label"><?php esc_html_e('Enable advanced column filters', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Sorting', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#sorting-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="sorting-hint">
                                <div class="popover-heading">
                                    <?php esc_html_e('Sorting', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/sorting.png"/>
                                    </div>
                                    <?php esc_html_e('If this is enabled, each column header will be clickable; clicking will sort the whole table by the content of this column cells ascending or descending.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-global-sorting" type="checkbox" checked="checked">
                                <label for="wdt-global-sorting"
                                       class="ts-label"><?php esc_html_e('Allow sorting for the table', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Main search block', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#global-search-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="global-search-hint">
                                <div class="popover-heading">
                                    <?php esc_html_e('Global search', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/global_search.png"/>
                                    </div>
                                    <?php esc_html_e('If this is enabled, a search block will be displayed on the top right of the table, allowing to search through whole table with a single input.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-global-search" type="checkbox">
                                <label for="wdt-global-search"
                                       class="ts-label"><?php esc_html_e('Enable search block', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                    </div>
                    <!-- /.row -->

                    <!-- row -->
                    <div class="row">

                        <div class="col-sm-4 m-b-16 filtering-form-block">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Filters in a form', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#filter-in-form-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>
                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="filter-in-form-hint">
                                <div class="popover-heading">
                                    <?php esc_html_e('Filter in form', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/filter_in_form.png"/>
                                    </div>
                                    <?php esc_html_e('Enable to show the advanced column filter in a form above the table, instead of showing in the table footer/header.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-filter-in-form" type="checkbox">
                                <label for="wdt-filter-in-form"
                                       class="ts-label"><?php esc_html_e('Show filters in a form above the table', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16 wdt-clear-filters-block filtering-form-block">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Clear filters button', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#wdt-clear-filters-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>
                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="wdt-clear-filters-hint">
                                <div class="popover-heading">
                                    <?php esc_html_e('Clear filters', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <?php esc_html_e('Enable to show the clear filters button.', 'wpdatatables'); ?>
                                    <br/><br/>
                                    <?php esc_html_e('If filter in form is enabled, clear button will be rendered after the last filter.', 'wpdatatables'); ?>
                                    <br/>
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/clear_filters_1.png"/>
                                    </div>
                                    <?php esc_html_e('Otherwise, clear filter button will be rendered above the table next to "Table Tools" buttons.', 'wpdatatables'); ?>
                                    <br/><br/>
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/clear_filters_2.png"/>
                                    </div>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-clear-filters" type="checkbox">
                                <label for="wdt-clear-filters"
                                       class="ts-label"><?php esc_html_e('Show clear filters button', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                        <?php do_action_deprecated('wdt_add_sorting_and_filtering_element', array(), WDT_INITIAL_STARTER_VERSION, 'wpdatatables_add_sorting_and_filtering_element'); ?>
                        <?php do_action('wpdatatables_add_sorting_and_filtering_element'); ?>

                    </div>
                    <!-- /.row -->

                </div>
                <!-- /Table sorting and filtering settings -->

                <!-- Table editing settings -->
                <div role="tabpanel" class="tab-pane fade" id="editing-settings">

                    <?php do_action('wpdatatables_add_table_editing_elements'); ?>

                </div>
                <!-- /Table editing settings -->

                <!-- Table tools settings -->
                <div role="tabpanel" class="tab-pane fade" id="table-tools-settings">

                    <!-- .row -->
                    <div class="row">

                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Table Tools', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#table-tools-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="table-tools-hint">
                                <div class="popover-heading">
                                    <?php esc_html_e('Table tools', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <div class="thumbnail">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/hint-pictures/table_tools_hint.png"/>
                                    </div>
                                    <?php esc_html_e('If this is enabled, a toolbar with useful tools will be shown above the table', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-table-tools" type="checkbox">
                                <label for="wdt-table-tools"
                                       class="ts-label"><?php esc_html_e('Enable Table Tools', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16 table-tools-settings-block hidden">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Buttons', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php esc_attr_e('Choose which buttons to show in the Table Tools block.', 'wpdatatables'); ?>"></i>
                            </h4>

                            <div class="select">
                                <select class="form-control selectpicker" multiple="multiple"
                                        id="wdt-table-tools-config">
                                    <option value="columns"><?php esc_html_e('Columns visibility', 'wpdatatables'); ?></option>
                                    <option value="print"><?php esc_html_e('Print', 'wpdatatables'); ?></option>
                                    <option value="excel"><?php esc_html_e('Excel', 'wpdatatables'); ?></option>
                                    <option value="csv"><?php esc_html_e('CSV', 'wpdatatables'); ?></option>
                                    <option value="copy"><?php esc_html_e('Copy', 'wpdatatables'); ?></option>
                                    <option value="pdf"><?php esc_html_e('PDF', 'wpdatatables'); ?></option>
                                </select>
                            </div>

                        </div>

                        <div class="col-sm-4 m-b-16 table-tools-include-html-block hidden">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Include HTML', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#table-tools-html-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="table-tools-html-hint">
                                <div class="popover-heading">
                                    <?php esc_html_e('Include HTML', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <?php esc_html_e(' If this is enabled, columns that contain HTML (such as link, image, email, attachment, or HTML) will be rendered as HTML in the exported files of the CSV, Excel, Print and Copy options.', 'wpdatatables'); ?>
                                    <br><br>
                                    <?php esc_html_e('NOTICE: Please note that you should check this functionality on the front-end since if you use this option in the back-end the buttons in headers will be rendered as HTML as well.', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-table-tools-include-html" type="checkbox">
                                <label for="wdt-table-tools-include-html"
                                       class="ts-label"><?php esc_html_e('Enable HTML in export files (CSV, Excel, Print and Copy)', 'wpdatatables'); ?></label>
                            </div>

                        </div>

                    </div>
                    <!-- /.row -->

                    <!-- .row -->
                    <div class="row pdf-export-options hidden">
                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('PDF Paper Size', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php esc_attr_e('Choose between different paper sizes for the created PDF.', 'wpdatatables'); ?>"></i>
                            </h4>

                            <div class="select">
                                <select class="form-control selectpicker"
                                        id="wdt-pdf-paper-size">
                                    <option value="A3"><?php esc_html_e('A3', 'wpdatatables'); ?></option>
                                    <option value="A4" selected><?php esc_html_e('A4', 'wpdatatables'); ?></option>
                                    <option value="A5"><?php esc_html_e('A5', 'wpdatatables'); ?></option>
                                    <option value="LEGAL"><?php esc_html_e('Legal', 'wpdatatables'); ?></option>
                                    <option value="LETTER"><?php esc_html_e('Letter', 'wpdatatables'); ?></option>
                                    <option value="TABLOID"><?php esc_html_e('Tabloid', 'wpdatatables'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-4 m-b-16">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Page orientation', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php esc_attr_e('Choose a paper orientation for the created PDF.', 'wpdatatables'); ?>"></i>
                            </h4>

                            <div class="select">
                                <select class="form-control selectpicker"
                                        id="wdt-pdf-page-orientation">
                                    <option value="portrait"
                                            selected><?php esc_html_e('Portrait', 'wpdatatables'); ?></option>
                                    <option value="landscape"><?php esc_html_e('Landscape', 'wpdatatables'); ?></option>
                                </select>
                            </div>
                        </div>

                    </div>
                    <!-- /.row -->
                    <div class="row">
                        <div class="col-sm-4 m-b-16 table-tools-include-title-block hidden">

                            <h4 class="c-title-color m-b-2">
                                <?php esc_html_e('Include Table title', 'wpdatatables'); ?>
                                <i class=" wpdt-icon-info-circle-thin" data-popover-content="#table-tools-title-hint"
                                   data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
                            </h4>

                            <!-- Hidden popover with image hint -->
                            <div class="hidden" id="table-tools-title-hint">
                                <div class="popover-heading">
                                    <?php esc_html_e('Include Table title', 'wpdatatables'); ?>
                                </div>

                                <div class="popover-body">
                                    <?php esc_html_e(' If this is enabled, table title will be shown in first row  in the exported files of the Excel and Copy options.(CSV is not supported and on Print and PDF is included.)', 'wpdatatables'); ?>
                                </div>
                            </div>
                            <!-- /Hidden popover with image hint -->

                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-table-tools-include-title" type="checkbox">
                                <label for="wdt-table-tools-include-title"
                                       class="ts-label"><?php esc_html_e('Show table title in export files (Excel and Copy)', 'wpdatatables'); ?></label>
                            </div>

                        </div>
                    </div>

                </div>
                <!-- /Table tools settings -->

                <!-- Placeholders settings -->
                <div role="tabpanel" class="tab-pane fade" id="placeholders-settings">

                    <?php do_action('wpdatatables_add_table_placeholders_elements'); ?>

                </div>
                <!-- /Placeholders settings -->

                <!-- Customize table settings -->
                <div role="tabpanel" class="tab-pane fade" id="customize-table-settings">

                    <div role="tabpanel">
                        <!--                        <small>-->
                        <?php //esc_html_e('Now you can customize each table with different skin, font, background , colors and lot more. Checkout new table customize settings below.', 'wpdatatables'); ?><!--</small>-->
                        <ul class="tab-nav customize-table-settings-ul" role="tablist">
                            <li class="active main-customize-table-settings-tab">
                                <a href="#main-customize-table-settings" aria-controls="main-customize-table-settings"
                                   role="tab"
                                   data-toggle="tab"><?php esc_html_e('Main', 'wpdatatables'); ?></a>
                            </li>
                            <li class="font-settings-tab">
                                <a href="#font-settings" aria-controls="font-settings" role="tab"
                                   data-toggle="tab"><?php esc_html_e('Font', 'wpdatatables'); ?></a>
                            </li>
                            <li class="header-settings-tab">
                                <a href="#header-settings" aria-controls="header-settings" role="tab"
                                   data-toggle="tab"><?php esc_html_e('Header', 'wpdatatables'); ?></a>
                            </li>
                            <li class="table-border-settings-tab">
                                <a href="#table-border-settings" aria-controls="table-border-settings" role="tab"
                                   data-toggle="tab"><?php esc_html_e('Table border', 'wpdatatables'); ?></a>
                            </li>
                            <li class="row-color-settings-tab">
                                <a href="#row-color-settings" aria-controls="row-color-settings" role="tab"
                                   data-toggle="tab"><?php esc_html_e('Row color', 'wpdatatables'); ?></a>
                            </li>
                            <li class="cell-color-settings-tab">
                                <a href="#cell-color-settings" aria-controls="cell-color-settings" role="tab"
                                   data-toggle="tab"><?php esc_html_e('Cell color', 'wpdatatables'); ?></a>
                            </li>
                            <li class="pagination-settings-tab">
                                <a href="#pagination-settings" aria-controls="pagination-settings" role="tab"
                                   data-toggle="tab"><?php esc_html_e('Pagination', 'wpdatatables'); ?></a>
                            </li>
                            <li class="custom-css-settings-tab">
                                <a href="#custom-css-settings" aria-controls="custom-css-settings" role="tab"
                                   data-toggle="tab"><?php esc_html_e('Custom CSS', 'wpdatatables'); ?></a>
                            </li>
                            <li class="loader-settings-tab">
                                <a href="#loader-settings" aria-controls="loader-settings"
                                   role="tab"
                                   data-toggle="tab"><?php esc_html_e('Loader', 'wpdatatables'); ?></a>
                            </li>
                            <?php do_action_deprecated('wdt_add_customize_table_configuration_tab', array(), WDT_INITIAL_STARTER_VERSION, 'wpdatatables_add_customize_table_configuration_tab'); ?>
                            <?php do_action('wpdatatables_add_customize_table_configuration_tab'); ?>

                        </ul>
                        <!-- /ul .tab-nav -->

                        <div class="tab-content wdt-main-child">
                            <!-- Main table settings -->
                            <div role="tabpanel" class="tab-pane active main-customize-table-settings"
                                 id="main-customize-table-settings">

                                <div class="row">
                                    <div class="col-sm-4 table-interface-language">
                                        <h4 class="c-title-color m-b-2">
                                            <?php esc_html_e('Interface language', 'wpdatatables'); ?>
                                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('Pick the language which will be used in tables interface.', 'wpdatatables'); ?>"></i>
                                        </h4>
                                        <div class="form-group">
                                            <div class="fg-line">
                                                <div class="select">
                                                    <select class="selectpicker" id="wdt-table-interface-language">
                                                        <option value=""><?php esc_html_e('English (default)', 'wpdatatables'); ?></option>
                                                        <?php foreach (WDTSettingsController::getInterfaceLanguages() as $language) { ?>
                                                            <option value="<?php echo esc_attr($language['file']) ?>">
                                                                <?php echo esc_html($language['name']); ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 wdt-table-base-skin">
                                        <h4 class="c-title-color m-b-2">
                                            <?php esc_html_e('Base skin', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('Choose the base skin for the plugin.', 'wpdatatables'); ?>"></i>
                                        </h4>
                                        <div class="form-group">
                                            <div class="fg-line">
                                                <div class="select">
                                                    <select class="selectpicker" name="wdt-table-base-skin"
                                                            id="wdt-table-base-skin">
                                                        <option value="material"><?php esc_html_e('Material', 'wpdatatables'); ?></option>
                                                        <option value="light"><?php esc_html_e('Light', 'wpdatatables'); ?></option>
                                                        <option value="graphite"><?php esc_html_e('Graphite', 'wpdatatables'); ?></option>
                                                        <option value="aqua"><?php esc_html_e('Aqua', 'wpdatatables'); ?></option>
                                                        <option value="purple"><?php esc_html_e('Purple', 'wpdatatables'); ?></option>
                                                        <option value="dark"><?php esc_html_e('Dark', 'wpdatatables'); ?></option>
                                                        <option value="raspberry-cream"><?php esc_html_e('Raspberry Cream', 'wpdatatables'); ?></option>
                                                        <option value="mojito"><?php esc_html_e('Mojito', 'wpdatatables'); ?></option>
                                                        <option value="dark-mojito"><?php esc_html_e('Dark Mojito', 'wpdatatables'); ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 wdt-table-wcag">
                                        <h4 class="c-title-color m-b-2">
                                            <?php esc_html_e('WCAG compatibility for table skin', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin"
                                               data-placement="right" data-popover-content="#table-wcag-hint"
                                               data-toggle="html-popover" data-trigger="hover"></i>
                                        </h4>

                                        <div class="hidden" id="table-wcag-hint">
                                            <div class="popover-heading">
                                                <?php esc_html_e('Web Content Accessibility Guidelines', 'wpdatatables'); ?>
                                            </div>

                                            <div class="popover-body">
                                                <?php esc_html_e('Enable this option to ensure table skin is WCAG compatible.', 'wpdatatables'); ?>
                                                <br>
                                                <br>
                                                <?php esc_html_e('If you decide to enable this option, you might notice some changes in the appearance of the table. This is because we have fixed issues with low contrast or focus that were present before.', 'wpdatatables'); ?>
                                            </div>
                                        </div>
                                        <div class="toggle-switch" data-ts-color="blue">
                                            <input id="wdt-wcag" type="checkbox">
                                            <label for="wdt-wcag"
                                                   class="ts-label"><?php esc_html_e('Make table skin WCAG compatible', 'wpdatatables'); ?></label>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- /Main table settings -->

                            <!-- Font settings -->
                            <div role="tabpanel" class="tab-pane fade" id="font-settings">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <h5 class="c-title-color m-b-2">
                                            <?php esc_html_e('Font', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('This font will be used in rendered tables. Leave blank not to override default theme settings', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="form-group">
                                            <div class="fg-line">
                                                <div class="select">
                                                    <select id="wdt-table-font" data-name="wdtTableFont"
                                                            class="selectpicker"
                                                            title="Choose font for the table">
                                                        <option value=""></option>
                                                        <?php foreach (WDTSettingsController::wdtGetSystemFonts() as $font) { ?>
                                                            <option value="<?php echo esc_attr($font) ?>"><?php echo esc_html($font) ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <h5 class="c-title-color m-b-2">
                                            <?php esc_html_e('Font size', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('Define the font size', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="form-group">
                                            <div class="fg-line">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="fg-line wdt-custom-number-input">
                                                            <button type="button"
                                                                    class="btn btn-default wdt-btn-number wdt-button-minus"
                                                                    data-type="minus" data-field="wdt-table-font-size">
                                                                <i class="wpdt-icon-minus"></i>
                                                            </button>
                                                            <input type="text" name="wdt-table-font-size" min="8"
                                                                   value=""
                                                                   class="form-control cp-value input-sm input-number"
                                                                   data-name="wdtTableFontSize"
                                                                   id="wdt-table-font-size">
                                                            <button type="button"
                                                                    class="btn btn-default wdt-btn-number wdt-button-plus"
                                                                    data-type="plus" data-field="wdt-table-font-size">
                                                                <i class="wpdt-icon-plus-full"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <h5 class="c-title-color m-b-2">
                                            <?php esc_html_e('Font color', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('This color is used for the main font in table cells.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-font-color"
                                                               data-name="wdtTableFontColor"
                                                               class="form-control cp-value wdt-add-picker" value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- /Font settings -->

                            <!-- Header settings -->
                            <div role="tabpanel" class="tab-pane fade" id="header-settings">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php esc_html_e('Background color', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('The color is used for background of the table header.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-header-base-color"
                                                               data-name="wdtTableHeaderBaseColor"
                                                               class="form-control cp-value wdt-add-picker" value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php esc_html_e('Border color', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('This color is used for the border in the table header.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-header-border-color"
                                                               data-name="wdtTableHeaderBorderColor"
                                                               class="form-control cp-value wdt-add-picker"
                                                               value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php esc_html_e('Font color', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('This color is used for the font in the table header.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-header-font-color"
                                                               data-name="wdtTableHeaderFontColor"
                                                               class="form-control cp-value wdt-add-picker" value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php esc_html_e('Active and hover color	', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('This color is used when you hover the mouse above the table header, or when you choose a column.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-header-active-color"
                                                               data-name="wdtTableHeaderActiveColor"
                                                               class="form-control cp-value wdt-add-picker"
                                                               value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- /Header settings -->

                            <!-- Table border settings -->
                            <div role="tabpanel" class="tab-pane fade" id="table-border-settings">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php esc_html_e('Inner border', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('This color is used for the inner border in the table between cells.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-inner-border-color"
                                                               data-name="wdtTableInnerBorderColor"
                                                               class="form-control cp-value wdt-add-picker"
                                                               value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php esc_html_e('Outer border', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('This color is used for the outer border of the whole table body.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-outer-border-color"
                                                               data-name="wdtTableOuterBorderColor"
                                                               class="form-control cp-value wdt-add-picker"
                                                               value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 wdt-remove-borders">
                                        <h5 class="c-title-color m-b-2">
                                            <?php esc_html_e('Table borders', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('When this is checked, borders in table will be removed ', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="toggle-switch" data-ts-color="blue">
                                            <input type="checkbox" name="wdt-table-remove-borders"
                                                   id="wdt-table-remove-borders"/>
                                            <label for="wdt-table-remove-borders"
                                                   class="ts-label"><?php esc_html_e('Remove borders in table', 'wpdatatables'); ?></label>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 wdt-remove-borders-header">
                                        <h5 class="c-title-color m-b-2">
                                            <?php esc_html_e('Header border', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('When this is checked,borders in header will be removed  ', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="toggle-switch" data-ts-color="blue">
                                            <input type="checkbox" name="wdt-table-remove-borders-header"
                                                   id="wdt-table-remove-borders-header"/>
                                            <label for="wdt-table-remove-borders-header"
                                                   class="ts-label"><?php esc_html_e('Remove borders in table header', 'wpdatatables'); ?></label>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- /Table border settings -->

                            <!-- Row color settings -->
                            <div role="tabpanel" class="tab-pane fade" id="row-color-settings">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php esc_html_e('Even row background', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('This color is used for for background in even rows.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-even-row-color"
                                                               data-name="wdtTableEvenRowColor"
                                                               class="form-control cp-value wdt-add-picker" value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php esc_html_e('Odd row background', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('This color is used for for background in odd rows.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-odd-row-color"
                                                               data-name="wdtTableOddRowColor"
                                                               class="form-control cp-value wdt-add-picker" value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php esc_html_e('Hover row', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('This color is used for to highlight the row when you hover your mouse above it.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-hover-row-color"
                                                               data-name="wdtTableHoverRowColor"
                                                               class="form-control cp-value wdt-add-picker" value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php esc_html_e('Background for selected rows', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('This color is used for background in selected rows.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-selected-row-color"
                                                               data-name="wdtTableSelectedRowColor"
                                                               class="form-control cp-value wdt-add-picker"
                                                               value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- /Row color settings -->

                            <!-- Cell color settings -->
                            <div role="tabpanel" class="tab-pane fade" id="cell-color-settings">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php esc_html_e('Sorted columns, even rows', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('This color is used for background in cells which are in the active columns (columns used for sorting) in even rows.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-active-even-cell-color"
                                                               data-name="wdtTableActiveEvenCellColor"
                                                               class="form-control cp-value wdt-add-picker"
                                                               value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php esc_html_e('Sorted columns, odd rows', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('This color is used for background in cells which are in the active columns (columns used for sorting) in odd rows.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-active-odd-cell-color"
                                                               data-name="wdtTableActiveOddCellColor"
                                                               class="form-control cp-value wdt-add-picker"
                                                               value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /Cell color settings -->

                            <!-- Pagination color settings -->
                            <div role="tabpanel" class="tab-pane fade" id="pagination-settings">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php esc_html_e('Background color', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('This color is used for the background of the pagination', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-pagination-background-color"
                                                               data-name="wdtTablePaginationBackgroundColor"
                                                               class="form-control cp-value wdt-add-picker"
                                                               value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php esc_html_e('Color', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('This color is used for the color of the links in the pagination.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-pagination-color"
                                                               data-name="wdtTablePaginationColor"
                                                               class="form-control cp-value wdt-add-picker"
                                                               value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php esc_html_e('Current page background color', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('The color is used for background of the current page', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text"
                                                               id="wdt-table-pagination-current-background-color"
                                                               data-name="wdtTablePaginationCurrentBackgroundColor"
                                                               class="form-control cp-value wdt-add-picker" value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php esc_html_e('Current page color', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('This color is used for the color of the current page.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-pagination-current-color"
                                                               data-name="wdtTablePaginationCurrentColor"
                                                               class="form-control cp-value wdt-add-picker"
                                                               value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php esc_html_e('Other pages hover background color', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('This background color is used when you hover the mouse above the other pages', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text"
                                                               id="wdt-table-pagination-hover-background-color"
                                                               data-name="wdtTablePaginationHoverBackgroundColor"
                                                               class="form-control cp-value wdt-add-picker" value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <h5 class="c-title-color m-b-2">
                                            <?php esc_html_e('Other pages hover color', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('This color is used when you hover the mouse above the other pages.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-pagination-hover-color"
                                                               data-name="wdtTablePaginationHoverColor"
                                                               class="form-control cp-value wdt-add-picker"
                                                               value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /Pagination color settings -->

                            <!-- Custom CSS settings -->
                            <div role="tabpanel" class="tab-pane fade" id="custom-css-settings">
                                <div class="row">
                                    <div class="col-sm-6 custom-css">
                                        <h4 class="c-title-color m-b-2">
                                            <?php esc_html_e('Custom wpDataTables CSS', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('This CSS will be inserted as an inline style block on every page that has this wpDataTable.', 'wpdatatables'); ?>"></i>
                                        </h4>
                                        <div class="form-group">
                                            <div class="fg-line">
                                                <pre id="wdt-table-custom-css" class="m-0"
                                                     style="width: 100%; height: 300px"></pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /Custom CSS settings -->

                            <!-- Loader settings -->
                            <div role="tabpanel" class="tab-pane fade" id="loader-settings">

                                <div class="row">
                                    <div class="col-sm-3 wdt-table-loader">
                                        <h4 class="c-title-color m-b-2">
                                            <?php esc_html_e('Loader visibility', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin"
                                               data-placement="right" data-popover-content="#loader-hint"
                                               data-toggle="html-popover" data-trigger="hover"></i>
                                        </h4>

                                        <div class="hidden" id="loader-hint">
                                            <div class="popover-heading">
                                                <?php esc_html_e('Manage loader visibility while tables load', 'wpdatatables'); ?>
                                            </div>

                                            <div class="popover-body">
                                                <?php esc_html_e('Enable this option to ensure loader is visible.', 'wpdatatables'); ?>
                                            </div>
                                        </div>
                                        <div class="toggle-switch" data-ts-color="blue">
                                            <input id="wdt-loader-visibility" type="checkbox">
                                            <label for="wdt-loader-visibility"
                                                   class="ts-label"><?php esc_html_e('Enable table loader', 'wpdatatables'); ?></label>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 wdt-loader-color">
                                        <h5 class="c-title-color m-b-2">
                                            <?php esc_html_e( 'Loader Color', 'wpdatatables' ); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('This color is used for loader.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-loader-color"
                                                               data-name="wdtLoaderColor"
                                                               class="form-control cp-value wdt-add-picker"
                                                               value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 wdt-loader-color-background">
                                        <h5 class="c-title-color m-b-2">
                                            <?php esc_html_e( 'Loader Animation Color', 'wpdatatables' ); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e( 'This color is used for loaders animation.', 'wpdatatables' ); ?>"></i>
                                        </h5>
                                        <div class="cp-container">
                                            <div class="form-group">
                                                <div class="fg-line dropdown">
                                                    <div id="cp"
                                                         class="input-group wdt-color-picker">
                                                        <input type="text" id="wdt-table-loader-background-color"
                                                               data-name="wdtLoaderBackgroundColor"
                                                               class="form-control cp-value wdt-add-picker"
                                                               value=""/>
                                                        <span class="input-group-addon wpcolorpicker-icon"><i></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 opacity-5 wdt-loader-style">
                                        <h5 class="c-title-color m-b-2">
                                            <i class="wpdt-icon-star-full" style="color: #FFC078;"></i>
                                            <?php esc_html_e('Loader Style', 'wpdatatables'); ?>
                                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip"
                                               data-placement="right"
                                               title="<?php esc_attr_e('Custom style for loader.', 'wpdatatables'); ?>"></i>
                                        </h5>
                                        <div class="fg-line form-group m-b-0">
                                            <input type="text" class="form-control input-sm" disabled
                                                   placeholder="<?php esc_attr_e('Coming Soon...', 'wpdatatables'); ?>">
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- /Loader settings -->
                            <?php do_action_deprecated('wdt_add_customize_table_configuration_tabpanel', array(), WDT_INITIAL_STARTER_VERSION, 'wpdatatables_add_customize_table_configuration_tabpanel'); ?>
                            <?php do_action('wpdatatables_add_customize_table_configuration_tabpanel'); ?>

                        </div>
                        <!-- /.tab-content - end of table settings tabs -->

                    </div>

                </div>
                <!-- /Customize table settings -->

                <?php do_action_deprecated('wdt_add_table_configuration_tabpanel', array(), WDT_INITIAL_STARTER_VERSION, 'wpdatatables_add_table_configuration_tabpanel'); ?>
                <?php do_action('wpdatatables_add_table_configuration_tabpanel'); ?>

            </div>
            <!-- /.tab-content - end of table settings tabs -->

            <div class="row m-l-0 m-r-0">

                <div class="col-md-12 p-l-0  wdt-table-settings-col">
                    <button class="btn btn-default btn-icon-text wdt-documentation wdt-table-settings"
                            data-doc-page="table_settings">
                        <i class="wpdt-icon-file-thin"></i><?php esc_html_e('View Documentation', 'wpdatatables'); ?>
                    </button>
                </div>
                <!-- /.col-md-12 -->
            </div>
            <!-- /.row -->
        </div>
        <!--/div role="tabpanel" -->
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card /.wdt-table-settings -->

<!-- .row -->
<div class="row">
    <div class="col-sm-12 editing-warning-settings-block hidden">
        <div class="alert alert-warning alert-dismissible wdt-editable-table-alert" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
            <p><strong><?php esc_html_e('Please note that enabling the "Allow front-end editing" option for tables, along with setting the "Editor roles" to "Everyone", 
                                grants all users, both authenticated and unauthenticated, the ability to manipulate table data on front-end pages or posts.', 'wpdatatables'); ?></strong>
            </p>
            <p>
                <strong><?php esc_html_e('It is recommended to selectively designate which Editor roles are permitted to modify table data for enhanced control and security.', 'wpdatatables'); ?></strong>
            </p>
        </div>
    </div>
</div>
<!-- /.row -->
