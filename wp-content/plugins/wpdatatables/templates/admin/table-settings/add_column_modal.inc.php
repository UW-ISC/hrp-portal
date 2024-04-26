<?php defined('ABSPATH') or die('Access denied.'); ?>

<!-- .wdt-add-column-modal -->
<div class="modal fade" id="wdt-add-column-modal" data-backdrop="static" data-keyboard="false" tabindex="-1"
     role="dialog" aria-hidden="true">

    <!-- .modal-dialog -->
    <div class="modal-dialog">

        <!-- .modal-content -->
        <div class="modal-content">
            <!-- Preloader -->
            <?php include WDT_TEMPLATE_PATH . 'admin/common/preloader.inc.php'; ?>
            <!-- /Preloader -->

            <!-- .modal-header -->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="wpdt-icon-times-full"></i></span>
                </button>
                <h4 class="modal-title"><?php esc_html_e('Add Column', 'wpdatatable'); ?></h4>
            </div>
            <!--/ .modal-header -->

            <!-- .modal-body -->
            <div class="modal-body">

                <!-- .wdt-add-column-modal-block -->
                <div class="wdt-add-column-modal-block">

                    <div class="row">

                        <div class="col-xs-12">
                            <h5 class="c-black m-b-10">
                                <?php esc_html_e('Column header', 'wpdatatables'); ?>
                            </h5>
                            <div class="form-group">
                                <div class="fg-line">
                                    <input class="form-control input-sm" id="wdt-add-column-column-header" type="text"
                                           value=""/>
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12">
                            <h5 class="c-black m-b-10">
                                <?php esc_html_e('Type', 'wpdatatables'); ?>
                            </h5>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="selectpicker wdt-add-column-column-type">
                                            <?php foreach (WDTTools::getPossibleColumnTypes() as $columnTypeKey => $columnTypeName) { ?>
                                                <option value="<?php echo esc_attr($columnTypeKey) ?>"><?php echo esc_html($columnTypeName) ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12">
                            <h5 class="c-black m-b-10">
                                <?php esc_html_e('Insert after', 'wpdatatables'); ?>
                            </h5>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="wdt-add-column-insert-after selectpicker">
                                            <option value="%%beginning%%"><?php esc_html_e('Beginning of table', 'wpdatatables'); ?></option>
                                            <option value="%%end%%"><?php esc_html_e('End of table', 'wpdatatables'); ?></option>
                                            <?php if (isset($tableData)){
                                                foreach ($tableData->table->columns as $column) { ?>
                                                <option value="<?php echo esc_attr($column->orig_header); ?>"><?php echo esc_html($column->display_header); ?></option>
                                                <?php } ?>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-6">
                            <h5 class="c-black m-b-10">
                                <?php esc_html_e('Type in database', 'wpdatatables'); ?>
                            </h5>
                            <div class="form-group">
                                <div class="fg-line">
                                    <div class="select">
                                        <select class="selectpicker wdt-default-add-column-db-type">
                                            <option value="VARCHAR"><?php esc_html_e('VARCHAR', 'wpdatatables'); ?></option>
                                            <option value="TEXT"><?php esc_html_e('TEXT', 'wpdatatables'); ?></option>
                                            <option value="TINYINT"><?php esc_html_e('TINYINT', 'wpdatatables'); ?></option>
                                            <option value="SMALLINT"><?php esc_html_e('SMALLINT', 'wpdatatables'); ?></option>
                                            <option value="INT"><?php esc_html_e('INT', 'wpdatatables'); ?></option>
                                            <option value="MEDIUMINT"><?php esc_html_e('MEDIUMINT', 'wpdatatables'); ?></option>
                                            <option value="BIGINT"><?php esc_html_e('BIGINT', 'wpdatatables'); ?></option>
                                            <option value="DECIMAL"><?php esc_html_e('DECIMAL', 'wpdatatables'); ?></option>
                                            <option value="DATE"><?php esc_html_e('DATE', 'wpdatatables'); ?></option>
                                            <option value="DATETIME"><?php esc_html_e('DATETIME', 'wpdatatables'); ?></option>
                                            <option value="TIME"><?php esc_html_e('TIME', 'wpdatatables'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-6" id="wdt-add-column-db-type-value">
                            <h5 class="c-black m-b-10">
                                <?php esc_html_e('Type value', 'wpdatatables'); ?>
                            </h5>
                            <div class="form-group">
                                <div class="fg-line">
                                    <input type="number" pattern = '/^[0-9,]+$'  onkeypress="return event.charCode >= 48 && event.charCode <= 57 || event.charCode == 44" class="form-control input-sm wdt-default-add-column-db-type-value" value="255" placeholder="<?php esc_attr_e('Enter type value','wpdatatables'); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12">
                        <div class="alert alert-info alert-dismissible" role="alert">
                            <i class="wpdt-icon-info-circle-full"></i>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
                            </button>
                            <span class="wdt-alert-title f-600"><?php esc_html_e('Options Type in database and Type value are enabling you to choose how to save your data in the DB.', 'wpdatatables'); ?><br></span>
                            <ul class="wdt-alert-subtitle" style="list-style-type: disc;font-size: 13px;margin-top: 5px; color:red !important;">
                                <li> <?php esc_html_e('If you are not familiar with types in the database, just select the wpdatatables column type, like you did in previous versions, and it will be filled by default.', 'wpdatatables'); ?></li>
                            </ul>
                        </div>
                        </div>
                        <div class="col-xs-12 wdt-add-column-possible-values-block" style="display: none;">
                            <h5 class="c-black m-b-10">
                                <?php esc_html_e('Possible values', 'wpdatatables'); ?>
                            </h5>
                            <div class="form-group">
                                <div class="fg-line">
                                    <input class="form-control input-sm wdt-add-column-possible-values" value=""/>
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 wdt-add-column-default-value-block">
                            <h5 class="c-black m-b-10">
                                <?php esc_html_e('Editor predefined value', 'wpdatatables'); ?>
                            </h5>
                            <div class="form-group">
                                <div class="fg-line">
                                    <input type="text" class="form-control input-sm wdt-add-column-default-value"
                                           value="">
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 wdt-add-hidden-default-value-block" hidden="hidden">
                            <h5 class="c-black m-b-10">
                                <?php esc_html_e('Dynamic editor predefined value', 'wpdatatables'); ?>
                            </h5>
                            <div class="form-group">
                                <div class="fg-line">
                                    <select class="selectpicker wdt-add-hidden-default-value">
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

                        <div class="col-xs-12 wdt-add-hidden-query-param-value-block" hidden="hidden">
                            <h5 class="c-black m-b-10">
                                <?php esc_html_e('Query Parameter Key', 'wpdatatables'); ?>
                            </h5>
                            <div class="form-group">
                                <div class="fg-line">
                                    <input type="text" class="form-control input-sm wdt-add-hidden-query-param-value"
                                            placeholder="<?php esc_html_e('E.g query_parameter_key', 'wpdatatables'); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 wdt-add-hidden-post-meta-value-block" hidden="hidden">
                            <h5 class="c-black m-b-10">
                                <?php esc_html_e('Post Meta Key', 'wpdatatables'); ?>
                            </h5>
                            <div class="form-group">
                                <div class="fg-line">
                                    <input type="text" class="form-control input-sm wdt-add-hidden-post-meta-value"
                                           placeholder="<?php esc_html_e('E.g meta_key', 'wpdatatables'); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 wdt-add-hidden-acf-data-value-block" hidden="hidden">
                            <h5 class="c-black m-b-10">
                                <?php esc_html_e('ACF Key', 'wpdatatables'); ?>
                            </h5>
                            <div class="form-group">
                                <div class="fg-line">
                                    <input type="text" class="form-control input-sm wdt-add-hidden-acf-data-value"
                                           placeholder="<?php esc_html_e('E.g acf_key', 'wpdatatables'); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12">
                            <div class="toggle-switch" data-ts-color="blue">
                                <input id="wdt-add-column-fill-with-default" type="checkbox">
                                <label for="wdt-add-column-fill-with-default"
                                       class="ts-label"><?php esc_html_e('Fill with default value', 'wpdatatables'); ?></label>
                            </div>
                        </div>

                    </div>

                </div>
                <!--/ .wdt-add-column-modal-block -->
            </div>
            <!--/ .modal-body -->

            <!-- .modal-footer -->
            <div class="modal-footer">
                <hr>
                <button class="btn btn-danger btn-icon-text" data-toggle="modal"
                        data-target="#wdt-add-column-modal">
                    <?php esc_html_e('Cancel', 'wpdatatables'); ?>
                </button>
                <button class="btn btn-primary btn-icon-text" id="wdt-add-column-submit">
                    <i class="wpdt-icon-save"></i>
                    <?php esc_html_e('Save', 'wpdatatables'); ?>
                </button>
            </div>
            <!--/ .modal-footer -->
        </div>
        <!--/ .modal-content -->
    </div>
    <!--/ .modal-dialog -->
</div>
<!--/ .wdt-add-column-modal -->
