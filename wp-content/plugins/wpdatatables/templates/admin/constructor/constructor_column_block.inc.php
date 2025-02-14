<?php defined('ABSPATH') or die('Access denied.'); ?>

<script id="wdt-constructor-column-block-template" type="text/x-jsrender">
    <div class="wdt-constructor-column-block col-sm-3">

        <div class="card m-b-15 m-t-15">

            <div class="card-header col-sm-12 ch-alt p-t-10 p-b-10 p-r-0 p-l-0">

                <div class="col-sm-10">
                    <div class="fg-line">
                        <input type="text" class="form-control input-sm wdt-constructor-column-name" value="{{>name}}">
                        <i class="wpdt-icon-pen"></i>
                    </div>
                </div>

                <ul class="actions wdt-constructor-remove-column">
                    <li>
                        <a>
                            <i class="wpdt-icon-trash-reg"></i>
                        </a>
                    </li>
                </ul>

            </div>

            <div class="card-body card-padding">

                <div class="col-sm-12 p-t-5 p-0">
                    <h5 class="c-black m-b-10">
                        <?php esc_html_e('Type', 'wpdatatables'); ?>
                    </h5>
                    <div class="form-group">
                        <div class="fg-line">
                            <div class="select">
                                <select class="selectpicker wdt-constructor-column-type">
                                    <?php foreach (WDTTools::getPossibleColumnTypes() as $columnTypeKey => $columnTypeName) { ?>
                                                <option value="<?php echo esc_attr($columnTypeKey) ?>"><?php echo esc_html($columnTypeName) ?></option>
                                     <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                 <div class="col-sm-6 p-t-5 p-0">
                    <h5 class="c-black m-b-10">
                        <?php esc_html_e('Type in database', 'wpdatatables'); ?>
                    </h5>

                    <div class="form-group">
                        <div class="fg-line">
                        <div class="select">
                                 <select class="selectpicker wdt-constructor-default-column-db-type">
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
                 <div class="col-sm-4 p-t-5 p-0" id="wdt-default-column-db-type-value">
                     <h5 class="c-black m-b-10">
                        <?php esc_html_e('Type value', 'wpdatatables'); ?>
                    </h5>
                    <div class="form-group">
                        <div class="fg-line">
                           <input type="number" pattern = '/^[0-9,]+$' onkeypress='return event.charCode >= 48 && event.charCode <= 57 || event.charCode == 44' class="form-control input-sm wdt-constructor-default-column-db-type-value" value="255" placeholder="<?php esc_attr_e('Enter type value', 'wpdatatables'); ?>">
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 wdt-constructor-possible-values-block p-0" style="display: none;">
                    <h5 class="c-black m-b-10">
                        <?php esc_html_e('Possible values', 'wpdatatables'); ?>
                    </h5>
                    <div class="form-group">
                        <div class="fg-line">
                            <input class="form-control input-sm wdt-constructor-possible-values" value=""/>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12  p-0 wdt-constructor-default-value-block">
                    <h5 class="c-black m-b-10">
                        <?php esc_html_e('Editor predefined value', 'wpdatatables'); ?>
                    </h5>
                    <div class="form-group">
                        <div class="fg-line">
                            <input type="text" class="form-control input-sm wdt-constructor-default-value" value="" placeholder="<?php esc_attr_e('Enter predefined value', 'wpdatatables'); ?>">
                        </div>
                    </div>
                </div>

                <?php do_action('wpdatatables_after_constructor_column_block'); ?>

            </div>

        </div>

    </div>



</script>