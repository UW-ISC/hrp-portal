<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php foreach ($headingsArray as $header) { ?>
    <?php $header = trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $header))); ?>
    <?php if ($header !== null) { ?>
        <div class="wdt-constructor-column-block wdt-constructor-column-block-file col-sm-3">

            <div class="card m-b-15 m-t-15">

                <div class="card-header col-sm-12 ch-alt p-t-10 p-b-10 p-r-0 p-l-0">

                    <div class="col-sm-10">
                        <div class="fg-line">
                            <input type="text" class="form-control input-sm wdt-constructor-column-name"
                                   value="<?php echo esc_attr($header) ?>">
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
                                        <?php foreach ($possibleColumnTypes as $columnTypeKey => $columnTypeName) { ?>
                                            <option value="<?php echo esc_attr($columnTypeKey) ?>"
                                                    <?php if ($columnTypeKey == $columnTypeArray[$header]) { ?>selected="selected"<?php } ?> ><?php echo esc_html($columnTypeName) ?></option>
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
                                <input type="number" pattern="[0-9,]"
                                       onkeypress="return event.charCode >= 48 && event.charCode <= 57 || event.charCode == 44"
                                       class="form-control input-sm wdt-constructor-default-column-db-type-value"
                                       value="255"
                                       placeholder="<?php esc_attr_e('Enter type value', 'wpdatatables'); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12 wdt-constructor-date-input-format-block p-0" style="display: none;">
                        <h5 class="c-black m-b-10">
                            <?php esc_html_e('Date input format', 'wpdatatables'); ?>
                        </h5>

                        <div class="form-group">
                            <div class="fg-line">
                                <div class="select">
                                    <select class="selectpicker wdt-constructor-date-input-format">
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
                                        <option value="M d, Y"> Jul 15,2005 (Mon d, Y)</option>
                                        <option value="M Y"> Jul 2005 (Mon Y)</option>
                                        <option value="F Y"> July 2005 (F Y)</option>
                                        <option value="F j, Y"> July 15, 2005 (F j, Y)</option>
                                        <option value="j. F Y."> 15. July 2005. (j. F Y.)</option>
                                        <option value="j F Y"> 15 July 2005 (j F Y)</option>
                                        <option value="D, F j, Y"> Fri, July 15, 2005 (D, F j, Y)</option>
                                        <option value="D, M j, Y"> Fri, Jul 15, 2005 (D, M j, Y)</option>
                                        <option value="m/Y"> 07/2005 (m/Y)</option>
                                        <option value="Y">2005 (Y)</option>
                                    </select>
                                </div>
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
                                <input type="text" class="form-control input-sm wdt-constructor-default-value" value=""
                                       placeholder="<?php esc_attr_e('Enter predefined value', 'wpdatatables'); ?>">
                            </div>
                        </div>
                    </div>

                    <?php do_action('wpdatatables_after_constructor_column_block_preview'); ?>

                    <div class="col-sm-12 wdt-constructor-data-preview p-0">
                        <h5 class="c-black m-b-10">
                            <?php esc_html_e('Data preview', 'wpdatatables'); ?>
                        </h5>
                        <div class="form-group">
                            <div class="fg-line">
                                <table class="table table-condensed">
                                    <tbody>
                                    <?php foreach ($namedDataArray as $row) { ?>
                                        <tr>
                                            <td><?php echo esc_html($row[$header]) ?></td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    <?php } ?>
<?php } ?>