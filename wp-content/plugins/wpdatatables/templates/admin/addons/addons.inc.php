<?php defined('ABSPATH') or die('Access denied.'); ?>

<div class="wrap wdt-datatables-admin-wrap">
    <?php do_action('wpdatatables_admin_before_addons'); ?>

    <!-- .container -->
    <div class="container">

        <!-- .row -->
        <div class="row">

            <div class="card wdt-addons">

                <!-- Preloader -->
                <?php include WDT_TEMPLATE_PATH . 'admin/common/preloader.inc.php'; ?>
                <!-- /Preloader -->

                <div class="card-header wdt-admin-card-header ch-alt">
                    <img id="wpdt-inline-logo"
                         src="<?php echo WDT_ROOT_URL; ?>assets/img/logo.svg"/>
                    <h2>
                        <span style="display: none">wpDataTables Addons</span>
                        <?php _e('Addons', 'wpdatatables'); ?>
                    </h2>

                </div>

                <p class="wdt-addons-intro"><?php _e('While wpDataTables itself provides quite a large amount of features and unlimited customisation flexibility, you can achieve even more with our premium addons. Each addon brings you some unique extension to the core functionality. There will be more addons developed over time by wpDataTables creators and 3rd party developers, so stay tuned.', 'wpdatatables'); ?></p>

                <div class="card-body card-padding wpdt-add-ons-card">

                    <div class="row">

                        <div class="col-sm-4">
                            <div class="thumbnail">
                                <div class="ribbon"><span><?php _e('NEW', 'wpdatatables'); ?></span></div>
                                <div class="wpdt-addons-desc">
                                    <img class="img-responsive"
                                         src="<?php echo WDT_ASSETS_PATH; ?>img/addons/master-detail-logo.png" alt="">
                                    <h4><?php _e('Master Detail Tables for wpDataTables', 'wpdatatables'); ?></h4>
                                </div>
                                <div class="caption">
                                    <p><?php _e('A wpDataTables addon which allows showing additional details for a specific row in a popup or a separate page or post. Handy when you would like to keep fewer columns in the table, while allowing user to access full details of particular entries.', 'wpdatatables'); ?></p>
                                </div>
                                <div class="wdt-addons-links">
                                    <?php if (!defined('WDT_MD_ROOT_PATH')) { ?>
                                        <a href="https://wpdatatables.com/documentation/addons/master-detail-tables/?utm_source=wpdt-admin&medium=addons&campaign=addons"
                                           target="_blank"
                                           class="wdt-addons-find-out-more"
                                           role="button"><?php _e('Learn more ', 'wpdatatables'); ?>
                                        </a>
                                        <button id="wdt-master-detail"
                                                class="btn btn-sm btn-icon-text btn-primary wdt-checkout-button tms-store-checkout">
                                            <i class="wpdt-icon-shopping-bag"></i><?php _e('Purchase', 'wpdatatables'); ?>
                                        </button>
                                        <div class="clear"></div>
                                    <?php } else { ?>
                                        <button class="wdt-plugin-installed btn btn-icon-text btn-primary">
                                            <i class="wpdt-icon-check-full"></i>
                                            <?php _e('Installed', 'wpdatatables'); ?>
                                        </button>
                                        <div class="clear"></div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="thumbnail">
                                <div class="ribbon"><span><?php _e('NEW', 'wpdatatables'); ?></span></div>
                                <div class="wpdt-addons-desc">

                                    <img class="img-responsive"
                                         src="<?php echo WDT_ASSETS_PATH; ?>img/addons/powerful-filters-logo.png"
                                         alt="">
                                    <h4><?php _e('Powerful Filters for wpDataTables', 'wpdatatables'); ?></h4>
                                </div>
                                <div class="caption">
                                    <p><?php _e('An add-on for wpDataTables that provides powerful filtering features: cascade filtering, applying filters on button click, show only filter without the table before user defines the search values.', 'wpdatatables'); ?></p>
                                </div>
                                <div class="wdt-addons-links">
                                    <?php if (!defined('WDT_PF_ROOT_PATH')) { ?>
                                    <a href="https://wpdatatables.com/powerful-filtering/?utm_source=wpdt-admin&medium=addons&campaign=addons"
                                       target="_blank"
                                       class="wdt-addons-find-out-more"
                                       role="button"><?php _e('Learn more ', 'wpdatatables'); ?>
                                    </a>
                                    <button id="wdt-powerful-filters"
                                            class="btn btn-sm btn-icon-text btn-primary wdt-checkout-button tms-store-checkout">
                                        <i class="wpdt-icon-shopping-bag"></i><?php _e('Purchase', 'wpdatatables'); ?>
                                    </button>
                                    <div class="clear"></div>
                                    <?php } else { ?>
                                    <button class="wdt-plugin-installed btn btn-icon-text btn-primary">
                                        <i class="wpdt-icon-check-full"></i>
                                        <?php _e('Installed', 'wpdatatables'); ?>
                                    </button>
                                    <div class="clear"></div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <!-- /.col-sm-4 -->
                        <div class="col-sm-4">
                            <div class="thumbnail">
                                <div class="wpdt-addons-desc">
                                    <img class="img-responsive"
                                         src="<?php echo WDT_ASSETS_PATH; ?>img/addons/report-builder-logo.png" alt="">
                                    <h4><?php _e('Report Builder', 'wpdatatables'); ?></h4>
                                </div>
                                <div class="caption">
                                    <p><?php _e('A unique tool that allows you to generate almost any Word DOCX and Excel XLSX documents filled in with actual data from your database.', 'wpdatatables'); ?></p>
                                </div>
                                <div class="wdt-addons-links">
                                    <?php if (!defined('WDT_RB_ROOT_PATH')) { ?>
                                    <a href="http://wpreportbuilder.com?utm_source=wpdt" target="_blank"
                                       class="wdt-addons-find-out-more"
                                       role="button"><?php _e('Learn more ', 'wpdatatables'); ?>
                                    </a>
                                    <button id="reportbuilder"
                                            class="btn btn-sm btn-icon-text btn-primary wdt-checkout-button tms-store-checkout">
                                        <i class="wpdt-icon-shopping-bag"></i><?php _e('Purchase', 'wpdatatables'); ?>
                                    </button>
                                    <div class="clear"></div>
                                    <?php } else { ?>
                                    <button class="wdt-plugin-installed btn btn-icon-text btn-primary">
                                        <i class="wpdt-icon-check-full"></i>
                                        <?php _e('Installed', 'wpdatatables'); ?>
                                    </button>
                                    <div class="clear"></div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <!-- /.col-sm-4 -->

                    </div>
                    <!-- /.row -->

                    <!-- .row -->
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="thumbnail">
                                <div class="wpdt-addons-desc">
                                    <img class="img-responsive wdt-formidable-img"
                                         src="<?php echo WDT_ASSETS_PATH; ?>img/addons/formidable-forms-logo.png"
                                         alt="">

                                    <h4><?php _e('Formidable Forms integration for wpDataTables', 'wpdatatables'); ?></h4>
                                </div>
                                <div class="caption">
                                    <p><?php _e('Tool that adds "Formidable Form" as a new table type and allows you to create wpDataTables from Formidable Forms entries data.', 'wpdatatables'); ?></p>
                                </div>
                                <div class="wdt-addons-links">
                                    <?php if (!defined('WDT_FF_ROOT_PATH')) { ?>
                                    <a href="https://wpdatatables.com/documentation/addons/formidable-forms-integration/?utm_source=wpdt-admin&medium=addons&campaign=addons"
                                       target="_blank"
                                       class="wdt-addons-find-out-more"
                                       role="button"><?php _e('Learn more ', 'wpdatatables'); ?>
                                    </a>
                                    <button id="wdt-formidable-integration"
                                            class="btn btn-sm btn-icon-text btn-primary wdt-checkout-button tms-store-checkout">
                                        <i class="wpdt-icon-shopping-bag"></i><?php _e('Purchase', 'wpdatatables'); ?>
                                    </button>
                                    <div class="clear"></div>
                                    <?php } else { ?>
                                    <button class="wdt-plugin-installed btn btn-icon-text btn-primary">
                                        <i class="wpdt-icon-check-full"></i>
                                        <?php _e('Installed', 'wpdatatables'); ?>
                                    </button>
                                    <div class="clear"></div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <!-- /.col-sm-4 -->

                        <div class="col-sm-4">
                            <div class="thumbnail">
                                <div class="wpdt-addons-desc">
                                    <img class="img-responsive wdt-gravity-img" src="<?php echo WDT_ASSETS_PATH; ?>img/addons/gravity-forms-logo.png" alt="">
                                    <h4><?php _e('Gravity Forms integration for wpDataTables', 'wpdatatables'); ?></h4>
                                </div>
                                <div class="caption">
                                    <p><?php _e('Tool that adds "Gravity Form" as a new table type and allows you to create wpDataTables from Gravity Forms entries data.', 'wpdatatables'); ?></p>
                                </div>
                                <div class="wdt-addons-links">
                                    <?php if (!defined('WDT_GF_ROOT_PATH')) { ?>
                                    <a href="https://wpdatatables.com/documentation/addons/gravity-forms-integration/?utm_source=wpdt-admin&medium=addons&campaign=addons"
                                       target="_blank"
                                       class="wdt-addons-find-out-more"
                                       role="button"><?php _e('Learn more ', 'wpdatatables'); ?>
                                    </a>
                                    <button id="wdt-gravity-integration"
                                            class="btn btn-sm btn-icon-text btn-primary wdt-checkout-button tms-store-checkout">
                                        <i class="wpdt-icon-shopping-bag"></i><?php _e('Purchase', 'wpdatatables'); ?>
                                    </button>
                                    <div class="clear"></div>
                                    <?php } else { ?>
                                    <button class="wdt-plugin-installed btn btn-icon-text btn-primary">
                                        <i class="wpdt-icon-check-full"></i>
                                        <?php _e('Installed', 'wpdatatables'); ?>
                                    </button>
                                    <div class="clear"></div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <!-- /.col-sm-4 -->
                    </div>
                    <!-- /.row -->
                </div>
            </div>
            <!-- /.row -->
        </div>

    </div>
    <!-- /.container -->

</div>
<!-- /.wdt-datatables-admin-wrap .wrap -->
