<?php defined('ABSPATH') or die('Access denied.'); ?>

<!-- #wdt-backend-save-modal -->
<div class="modal fade" id="wdt-backend-save-modal" data-backdrop="static" data-keyboard="false" tabindex="-1"
     role="dialog" aria-hidden="true">

    <!-- .modal-dialog -->
    <div class="modal-dialog">

        <!-- .modal-content -->
        <div class="modal-content">

            <!-- .modal-header -->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="wpdt-icon-times-full"></i></span>
                </button>
                <h4 class="modal-title"><?php _e('Are you sure?', 'wpdatatables') ?></h4>
            </div>
            <!--/ .modal-header -->

            <!-- .modal-body -->
            <div class="modal-body">
                <!-- .row -->
                <div class="row">
                    <div class="col-sm-12" id="wdt-save-table-message">
                    </div>
                </div>
                <!--/ .row -->

                <!-- .modal-footer -->
                <div class="modal-footer">
                    <hr>
                    <button type="button" class="btn btn-icon-text"
                            id="wdt-backend-save-button"><?php _e('Yes, save table', 'wpdatatables'); ?></button>
                    <button type="button" class="btn btn-danger btn-icon-text" data-dismiss="modal"
                            id="wdt-backend-cancel-save-button"><i class="wpdt-icon-times-full"></i><?php _e('No, cancel', 'wpdatatables'); ?></button>
                </div>
                <!--/ .modal-footer -->
            </div>
            <!--/ .modal-content -->
        </div>
        <!--/ .modal-dialog -->
    </div>
    <!--/ #wdt-backend-close-modal -->
<!--/ #wdt-backend-close-modal -->