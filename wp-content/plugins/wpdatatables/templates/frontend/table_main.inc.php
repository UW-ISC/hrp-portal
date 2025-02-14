<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
/**
 * Template file for the plain HTML table
 * wpDataTables Module
 * @author cjbug@ya.ru
 * @since 10.10.2012
 *
 **/

/** @var $this WPDataTable */
/** @var string $advancedFilterPosition */
?>
<?php
if ($this->getFilteringForm()) {
    include WDT_TEMPLATE_PATH . 'frontend/filter_form.inc.php';
}
?>
<?php $customClasses = apply_filters_deprecated(
    'wdt_add_class_to_table_html_element',
    array(esc_attr($this->getCSSClasses()), $this->getWpId()),
    WDT_INITIAL_STARTER_VERSION,
    'wpdatatables_add_class_to_table_html_element'
); ?>
<?php $customClasses = apply_filters('wpdatatables_add_class_to_table_html_element', esc_attr($this->getCSSClasses()), $this->getWpId()) ?>
<?php do_action('wpdatatables_before_table', $this->getWpId()); ?>
<?php if ((is_admin() && current_user_can('administrator') && $this->getTableType() == 'manual') || $this->isEditable()): ?>
    <?php wp_nonce_field('wdtFrontendEditTableNonce' . $this->getWpId(), 'wdtNonceFrontendEdit_' . $this->getWpId()); ?>
<?php endif; ?>
<?php if ($this->serverSide()): ?>
    <?php wp_nonce_field('wdtFrontendServerSideNonce' . $this->getWpId(), 'wdtNonceFrontendServerSide_' . $this->getWpId()); ?>
<?php endif; ?>
    <input type="hidden" id="<?php echo esc_attr($this->getId()) ?>_desc"
           value='<?php echo $this->getJsonDescription(); ?>'/>

    <table id="<?php echo esc_attr($this->getId()) ?>"
           class="<?php if ($this->isFixedHeaders()) { ?>wpFixedHeader<?php } ?><?php if ($this->groupingEnabled()) { ?> wpdt-has-grouping<?php } ?> <?php if ($this->isScrollable()) { ?>scroll<?php } ?> <?php if ($this->isResponsive()) { ?>responsive<?php } ?> display nowrap wdt-no-display <?php echo esc_attr($this->getCssClassesArr()) ?> <?php echo esc_attr($customClasses) ?> wpDataTable wpDataTableID-<?php echo esc_attr($this->getWpId()) ?> "
           style="<?php echo esc_attr($this->getCSSStyle()) ?>"
           data-described-by='<?php echo esc_attr($this->getId()) ?>_desc'
           data-wpdatatable_id="<?php echo esc_attr($this->getWpId()); ?>
">
        <?php if (is_admin() || in_array($this->getTableSkin(), ['mojito', 'raspberry-cream', 'dark-mojito'])) { ?>
            <colgroup id="<?php echo 'colgrup-' . esc_attr($this->getId()) ?>"></colgroup>
        <?php } ?>

        <!-- Table header -->
        <?php include WDT_TEMPLATE_PATH . 'frontend/table_head.inc.php'; ?>
        <!-- /Table header -->

        <!-- Table body -->
        <?php include WDT_TEMPLATE_PATH . 'frontend/table_body.inc.php'; ?>
        <!-- /Table body -->

        <!-- Table footer -->
        <?php include WDT_TEMPLATE_PATH . 'frontend/table_footer.inc.php'; ?>
        <!-- /Table footer -->
    </table>

<?php do_action('wpdatatables_after_table', $this->getWpId()); ?>
<?php if ($this->isEditable()) {
    include WDT_TEMPLATE_PATH . 'frontend/edit_dialog.inc.php';
}