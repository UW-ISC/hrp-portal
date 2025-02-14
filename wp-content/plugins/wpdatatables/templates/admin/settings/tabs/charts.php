<?php defined('ABSPATH') or die('Access denied.'); ?>

<div role="tabpanel" class="tab-pane" id="wdt-charts">
    <div class="row">
        <div id="wdt-google-stable-tag" class="col-sm-4 stable-tag">
            <h4 class="c-title-color m-b-2">
                <?php esc_html_e('Use stable GoogleCharts version', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php esc_attr_e('Choose weather to use the chart engine library directly from the CDN (as they get updated, some features may break), or use the latest version wpDataTables has been tested with. Leaving this option unchecked means the code is pulled from the CDN.', 'wpdatatables'); ?>"></i>
            </h4>

            <div class="fg-line">
                <div class="toggle-switch" data-ts-color="blue">
                    <input id="wdt-use-google-stable-version" type="checkbox">
                    <label for="wdt-use-google-stable-version"
                           class="ts-label form-control"><?php esc_html_e('Use stable version', 'wpdatatables'); ?></label>
                </div>
            </div>
        </div>

        <?php do_action('wpdatatables_add_chart_stable_tag_option'); ?>

        <div class="row">
            <div id="wdt-googlechart-mapkey-tag" class="col-sm-4 stable-tag googlechart-mapkey">
                <h4 class="c-title-color m-b-2">
                    <?php esc_html_e('Google Maps API key', 'wpdatatables'); ?>
                    <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                       title="<?php esc_attr_e('Insert Google Maps API key', 'wpdatatables'); ?>"></i>
                </h4>

                <div class="fg-line">
                    <input type="text" name="wdt-googlechart-mapkey"
                           id="wdt-googlechart-mapkey"
                           class="form-control input-sm"
                           placeholder="<?php esc_html_e('Please enter your Google Maps API key', 'wpdatatables'); ?>"
                           value="" autocomplete="off"
                    />
                </div>
                <div class="col-sm-14 p-r-0 wdt-security-massage-wrapper hidden">
                    <div class="fg-line">
                        <div class="alert alert-info" role="alert">
                            <i class="wpdt-icon-info-circle-full"></i>
                            <span class="wdt-alert-title f-600">
                                        <?php esc_html_e('Your Google Maps API key has been hidden for security reasons. You can find it on your', 'wpdatatables'); ?>
                                        <a href="https://console.cloud.google.com/apis/credentials"
                                           target="_blank"><?php esc_html_e('api page', 'wpdatatables'); ?></a>.
                                    </span>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <button id="wdt-validate-googlechart-mapkey"
                        class="btn m-l-15 btn-primary wdt-validate-googlegeochart-mapkey"><?php esc_html_e('Validate & Save', 'wpdatatables'); ?></button>
            </div>
        </div>
        <div class="col-sm-4 wdt-global-loder">
            <h4 class="c-title-color m-b-2">
                <?php esc_html_e('Loader visibility', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php esc_attr_e('Enable this option to display a loader for all charts while they are loading.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="toggle-switch" data-ts-color="blue">
                <input type="checkbox" name="wdt-global-chart-loader" id="wdt-global-chart-loader" checked="checked"/>
                <label for="wdt-global-chart-loader"
                       class="ts-label"><?php esc_html_e('Enable chart loaders', 'wpdatatables'); ?></label>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12" style="margin-top: 12px;">
            <div class="alert alert-info alert-dismissible" role="alert">
                <i class="wpdt-icon-info-circle-full"></i>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                            aria-hidden="true">×</span>
                </button>
                <span class="wdt-alert-title wpdttypecolumn f-600"><?php esc_html_e('Specify a Google Maps API key that you can use with Geocharts.', 'wpdatatables'); ?><br></span>
                <ul class="wdt-alert-subtitle" style="list-style-type: disc;font-size: 13px;margin-top: 5px;">
                    <li> <?php esc_html_e('To begin, you must generate a Google Maps API key. For detailed instructions on how to do so, please follow the instructions provided', 'wpdatatables'); ?>
                        <a href="https://wpdatatables.com/documentation/wpdatacharts/set-up-google-maps-api-key/"
                           rel="nofollow" target="_blank"><?php esc_html_e('here.', 'wpdatatables'); ?></a></li>
                    <li> <?php esc_html_e('To use Google Geocharts, make sure to activate both the Maps JavaScript API and Geocoding API and ensure that you have billing set up for them. For detailed instructions on how to do so, please follow the instructions provided', 'wpdatatables'); ?>
                        <a href="https://wpdatatables.com/documentation/wpdatacharts/set-up-google-maps-api-key/"
                           rel="nofollow" target="_blank"><?php esc_html_e('here.', 'wpdatatables'); ?></a></li>
                    <li style="color:red !important;"> <?php esc_html_e('Our suggestion is to limit the usage of the key, such as using it only for this particular website. Please follow the instructions provided', 'wpdatatables'); ?>
                        <a href="https://wpdatatables.com/documentation/wpdatacharts/set-up-google-maps-api-key/"
                           rel="nofollow" target="_blank"><?php esc_html_e('here.', 'wpdatatables'); ?></a></li>
                    <li style="color:red !important;"> <?php esc_html_e('To use the Maps JavaScript API and Geocoding API, make sure your API key is enabled for both and that billing is enabled on your Google Cloud Project.', 'wpdatatables'); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>