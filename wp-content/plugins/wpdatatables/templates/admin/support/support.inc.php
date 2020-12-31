<?php defined('ABSPATH') or die('Access denied.'); ?>

<!-- .wdt-datatables-admin-wrap .wrap -->
<div class="wrap wdt-datatables-admin-wrap">
    <!-- Preloader -->
    <?php include WDT_TEMPLATE_PATH . 'admin/common/preloader.inc.php'; ?>
    <!-- /Preloader -->
    <!-- .container -->
    <div class="container wdt-support">

        <!-- .row -->
        <div class="row">

            <div class="card card-head m-b-0">
                <?php wp_nonce_field('wdtSupportNonce', 'wdtNonce'); ?>
                <div class="card-header wdt-admin-card-header ch-alt">
                    <img id="wpdt-inline-logo"
                         src="<?php echo WDT_ROOT_URL; ?>assets/img/logo.svg"/>
                    <h2>
                        <a href="<?php echo admin_url('admin.php?page=wpdatatables-dashboard'); ?>">
                            <i class="wpdt-icon-chevron-left"></i>
                        </a>
                        <span style="display: none">wpDataTables Support Center</span>
                        <?php _e('Support Center', 'wpdatatables'); ?>
                    </h2>
                    <ul class="actions">
                        <li>
                            <button class="btn btn-default btn-icon-text wdt-documentation"
                                    data-doc-page="support_page">
                                <i class="wpdt-icon-file-thin"></i>
                                <?php _e('View Documentation', 'wpdatatables'); ?>
                            </button>
                        </li>
                    </ul>
                </div>

            </div>

        </div>
        <!-- /.row -->
        <div class="row">
            <div class="col-sm-6 card-columns p-r-12 p-l-0">
                <div class="card m-b-16">
                    <div class="card-body card-padding">
                        <h5 class="m-t-0 card-title"><?php _e('Documentation', 'wpdatatables'); ?></h5>
                        <p class="card-text"><?php _e('Get started with detailed explanation of every plugin feature.', 'wpdatatables'); ?></p>
                        <a href="https://wpdatatables.com/documentation/general/features-overview/" target="_blank" class="btn btn-primary"><i class="wpdt-icon-file"></i><?php _e('View Documentation', 'wpdatatables'); ?></a>
                    </div>
                </div>
                <div class="card m-b-16">
                    <div class="card-body card-padding">
                        <h5 class="m-t-0 card-title"><?php _e('FAQ', 'wpdatatables'); ?></h5>
                        <p class="card-text"><?php _e('Find a list of frequently asked questions about wpDataTables functionality.', 'wpdatatables'); ?></p>
                        <a href="https://wpdatatables.com/help/faq/" target="_blank" class="btn btn-primary"><i class="wpdt-icon-question"></i><?php _e('Visit FAQ', 'wpdatatables'); ?></a>
                    </div>
                </div>
                <div class="card m-b-16">
                    <div class="card-body card-padding">
                        <h5 class="m-t-0 card-title"><?php _e('Feature suggestion', 'wpdatatables'); ?></h5>
                        <p class="card-text"><?php _e('Great opportunity to our users to submit ideas for new features.', 'wpdatatables'); ?></p>
                        <a href="https://wpdatatables.com/documentation/feature-suggestion/" target="_blank" class="btn btn-primary"><i class="wpdt-icon-lightbulb"></i><?php _e('Suggest a Feature', 'wpdatatables'); ?></a>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 card-columns p-l-12 p-r-0">
                <div class="card m-b-16">
                    <div class="card-body card-padding wdt-support-img">
                        <h5 class="wdt-large-title m-t-0 card-title"><?php _e('Still need help?', 'wpdatatables'); ?></h5>
                        <p class="card-text m-b-28"><?php _e('We provde proffesional support to all our users via our tiketing system.', 'wpdatatables'); ?></p>
                        <a href="https://tmsplugins.ticksy.com/" target="_blank" rel=”nofollow” class="btn btn-primary"><i class="wpdt-icon-envelope"></i><?php _e('Submit a ticket', 'wpdatatables'); ?></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <h6 class="text-center wdt-footer-title">
                <?php _e('Made by', 'wpdatatables'); ?>
                <a href="https://tms-outsource.com/?utm_source=full&utm_medium=plugin&utm_campaign=wpdtful" target="_blank">
                    <img src="<?php echo WDT_ASSETS_PATH; ?>img/TMS-Black.svg" alt="" style="width: 66px">
                </a>
            </h6>
            <ul class="wpdt-footer-links text-center">
                <li><a href="https://wpdatatables.com/?utm_source=full&utm_medium=plugin&utm_campaign=wpdtful" target="_blank">wpDataTables.com</a></li>
                <li>|</li>
                <li><a href="https://wpdatatables.com/documentation/general/features-overview/" target="_blank"> <?php _e('Documentation', 'wpdatatables'); ?></a>
                </li>
                <li>|</li>
                <li><a href="<?php echo admin_url('admin.php?page=wpdatatables-support'); ?>">
                        <?php _e('Support Center', 'wpdatatables'); ?></a></li>
            </ul>
        </div>
    </div>
    <!-- /.container -->

</div>
<!-- /.wdt-datatables-admin-wrap .wrap -->
