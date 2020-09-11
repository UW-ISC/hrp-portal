<?php defined('ABSPATH') or die('Access denied.'); ?>

<!-- #wdt-configure-foreign-key-modal -->
<div class="modal fade" id="wdt-configure-foreign-key-modal" data-backdrop="static" data-keyboard="false"
     tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Preloader -->
            <?php include WDT_TEMPLATE_PATH . 'admin/common/preloader.inc.php'; ?>
            <!-- /Preloader -->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true"><i class="wpdt-icon-times-full"></i></span></button>
                <h4 class="modal-title"><?php _e('Use values from another wpDataTable', 'wpdatatables'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Choose a source wpDataTable', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('Please choose a remote wpDataTable which will be used as the data source.', 'wpdatatables'); ?>"></i>
                        </h4>

                        <div class="form-group">
                            <div class="fg-line">
                                <div class="select">
                                    <select class="selectpicker" id="wdt-column-foreign-table" data-live-search="true">
                                        <option value=0><?php _e('Pick a table...', 'wpdatatables'); ?></option>
                                        <?php foreach (WPDataTable::getAllTables() as $wdt) {
                                            if ($connection === $wdt['connection']) {?>
                                            <option value="<?php echo $wdt['id']; ?>"><?php echo $wdt['title']; ?>
                                                (id: <?php echo $wdt['id']; ?>)
                                            </option>
                                        <?php }
                                            } ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
                <!-- /.row -->

                <div class="row">

                    <div class="col-sm-12">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Display value', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('Please choose which column values will be shown to the front-end user (e.g. Name).', 'wpdatatables'); ?>"></i>
                        </h4>

                        <div class="form-group">
                            <div class="fg-line">
                                <div class="select">
                                    <select class="selectpicker" id="wdt-foreign-column-display-value">
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
                <!-- /.row -->


                <div class="row">

                    <div class="col-sm-12">
                        <h4 class="c-title-color m-b-2">
                            <?php _e('Store value', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('Please choose which column values will be stored in the table for reference - by default wdt_ID, or ID', 'wpdatatables'); ?>"></i>
                        </h4>

                        <div class="form-group">
                            <div class="fg-line">
                                <div class="select">
                                    <select class="selectpicker" id="wdt-foreign-column-store-value">
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
                <!-- /.row -->

            </div>
            <div class="modal-footer">
                <hr>
                <button type="button" class="btn btn-danger btn-icon-text wdt-foreign-key-close"
                        data-dismiss="modal">
                    <?php _e('Close', 'wpdatatables'); ?>
                </button>
                <button type="button" class="btn btn-primary btn-icon-text wdt-save-foreign-key-rule">
                    <i class="wpdt-icon-save"></i>
                    <?php _e('Save', 'wpdatatables'); ?>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- /#wdt-configure-foreign-key-modal -->
