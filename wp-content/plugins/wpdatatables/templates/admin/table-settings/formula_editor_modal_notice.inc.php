<?php
/**
 * Template for Formula Editor poopup/modal notice
 */

defined('ABSPATH') or die('Access denied.');
?>
<!-- #wdtFormulaEditorModal -->
<div class="modal fade" id="wdt-formula-editor-modal" data-backdrop="static" data-keyboard="false"
     tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Preloader -->
            <?php include WDT_TEMPLATE_PATH . 'admin/common/preloader.inc.php'; ?>
            <!-- /Preloader -->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="wpdt-icon-times-full"></i></span>
                </button>
                <h4 class="modal-title"><?php esc_html_e('Formula Editor', 'wpdatatables'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row-notice text-center">
                    <i class="wpdt-icon-star-full m-r-5 m-t-5"
                       style="color: #091D70;"></i><strong><?php esc_html_e('Available from Standard license', 'wpdatatables'); ?></strong>

                    <p class="m-b-0 m-t-5"><?php esc_html_e('The formula column feature in tables allows users to dynamically compute values based on other columns\' cell data, enabling tasks like calculating VAT tax or performing custom calculations.', 'wpdatatables'); ?></p>
                    <p class="m-b-5"><?php esc_html_e('It\'s particularly useful when datasets lack certain information or when complex computations are needed to derive specific insights within wpDataTables.', 'wpdatatables'); ?></p>
                    <p class="m-b-5">
                        <a href="https://wpdatatables.com/pricing/?utm_source=wpdt-admin&utm_medium=formula-column&utm_campaign=wpdt&utm_content=wpdt"
                           rel="nofollow" class="btn btn-primary wdt-upgrade-btn"
                           target="_blank"><?php esc_html_e('Upgrade', 'wpdatatables'); ?></a>
                    </p>
                </div>
                <div class="row notice-images">
                    <div class="wpdt-custom-center-flex" style="align-items: center;">
                        <img style="height: 350px;"
                             src="<?php echo WDT_ASSETS_PATH ?>img/feature-preview-notice/starter/formula-column.gif"
                             alt="Edit popover image notice"/>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
            </div>

        </div>
    </div>
</div>
<!-- /#wdtFormulaEditorModal -->