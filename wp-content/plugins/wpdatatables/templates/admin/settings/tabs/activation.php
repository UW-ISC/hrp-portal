<?php defined('ABSPATH') or die('Access denied.'); ?>

<!-- Tab panel -->
<div role="tabpanel" class="tab-pane" id="wdt-activation">

    <div class="row">
        <div class="col-sm-12">
            <div class="alert alert-info alert-dismissible" role="alert">
                <i class="wpdt-icon-info-circle-full"></i>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true"><i
                                class="wpdt-icon-times-full"></i></span></button>
                <span class="wdt-alert-title f-600">License activation<br></span>
                <span class="wdt-alert-subtitle">
               <p><?php _e('Here you need to activate your licence by activate it with your Envato account (if you purchase our plugins over Envato market) or enter the purchase code that you have received in your email after the purchase from our store.</br> If you cannot find this email please follow the next instructions:.', 'wpdatatables'); ?></p>

               <ul class="m-l-20" style="list-style: disc;">
                   <li><?php _e('Access the store page ', 'wpdatatables'); ?> <a href="https://store.tms-plugins.com/login"
                                                                                 target="_blank"><?php _e('here', 'wpdatatables'); ?></a>,</li>
                <li><?php _e('Choose Forgot Password option,', 'wpdatatables'); ?></li>
                <li><?php _e('Enter the email that you have used during the purchase and click Send recovery email,', 'wpdatatables'); ?></li>
                <li><?php _e('Use the password and email to log in to the store page,', 'wpdatatables'); ?></li>
                <li><?php _e('Copy the purchase code from your dashboard page on the store and paste it below.', 'wpdatatables'); ?></li>
                <li><?php _e('If you still cannot find the purchase code please contact our support ', 'wpdatatables'); ?> <a
                            href="https://tmsplugins.ticksy.com/" target="_blank"><?php _e('here.', 'wpdatatables'); ?></a></li>
               </ul>
            </span>
            </div>
        </div>
    </div>

    <!-- Row -->
    <div class="row">

        <!-- Panel Group -->
        <div class="col-sm-6 m-b-30">

            <div class="wdt-activation-section">

                <div class="wpdt-plugins-desc">
                    <img class="img-responsive" src="<?php echo WDT_ASSETS_PATH; ?>img/logo-large.png" alt="">
                    <h4>wpDataTables</h4>
                </div>

                <!-- Panel Body -->
                <div class="panel-body">

                    <!-- TMS Store Purchase Code -->
                    <div class="col-sm-10 wdt-purchase-code p-l-0">

                        <!-- TMS Store Purchase Code Heading-->
                        <h4 class="c-title-color m-b-2 m-t-0">
                            <?php _e('TMS Store Purchase Code', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('If your brought the plugin directly on our website or in the Lite version, enter TMS Store purchase code to enable auto updates.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <!-- /TMS Store Purchase Code Heading -->

                        <!-- TMS Store Purchase Code Form -->
                        <div class="form-group m-b-0">
                            <div class="row">

                                <!-- TMS Store Purchase Code Input -->
                                <div class="col-sm-11 p-r-0">
                                    <div class="fg-line">
                                        <input type="text" name="wdt-purchase-code-store"
                                               id="wdt-purchase-code-store"
                                               class="form-control input-sm"
                                               placeholder="<?php _e('Please enter your wpDataTables TMS Store Purchase Code', 'wpdatatables'); ?>"
                                               value=""
                                        />
                                    </div>
                                </div>
                                <!-- TMS Store Purchase Code Input -->

                                <!-- TMS Store Purchase Code Activate Button -->
                                <div class="col-sm-1">
                                    <button class="btn btn-primary wdt-store-activate-plugin" id="wdt-activate-plugin">
                                        <i class="wpdt-icon-check-circle-full"></i><?php _e('Activate ', 'wpdatatables'); ?>
                                    </button>
                                </div>
                                <!-- /TMS Store Purchase Code Activate Button -->

                            </div>
                        </div>
                        <!-- /TMS Store Purchase Code Form -->

                    </div>
                    <!-- /TMS Store Purchase Code -->

                    <!-- Envato API -->
                    <div class="col-sm-10 wdt-envato-activation wdt-envato-activation-wpdatatables p-l-0">

                        <!-- Envato API Heading-->
                        <h4 class="c-title-color m-b-2 m-t-0">
                            <?php _e('Envato API', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php _e('If you bought the plugin on the Envato (CodeCanyon) activate the plugin using Envato API to enable auto updates.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <!-- /Envato API Heading -->

                        <!-- Envato API Form -->
                        <div class="form-group m-b-0">
                            <div class="row m-l-0">

                                <!-- Envato API Button -->
                                <button class="btn wdt-envato-activation-button"
                                        id="wdt-envato-activation-wpdatatables">
                                    <div id="wdt-envato-div">
                                        <img src="<?php echo WDT_ASSETS_PATH ?>img/envato.svg"
                                             class="wdt-envato-activation-logo"
                                        >
                                    </div>
                                    <span>
                                    <?php _e('Activate with Envato', 'wpdatatables'); ?>
                                </span>
                                </button>
                                <!-- /Envato API Button -->

                                <button class="btn btn-danger wdt-envato-deactivation-button"
                                        style="display: none;" id="wdt-envato-deactivation-wpdatatables">
                                    <i class="wpdt-icon-times-circle-full"></i><?php _e('Deactivate ', 'wpdatatables'); ?>
                                </button>

                            </div>
                        </div>
                        <!-- /Envato API Form -->

                    </div>
                    <!-- /Envato API -->

                </div>
                <!-- /Panel Body -->
            </div>

        </div>
        <!-- /Panel Group -->

        <?php do_action('wdt_add_activation'); ?>

    </div>
    <!-- /Row -->

</div>
<!-- /Tab panel -->
