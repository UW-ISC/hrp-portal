<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
/**
 * Template file for the plain HTML table
 * wpDataTables Module
 *
 * @author cjbug@ya.ru
 * @since 10.10.2012
 *
 **/
?>
<?php if ($this->getFilteringForm()) { ?>
    <?php do_action('wpdatatables_before_filtering_form', $this->getWpId()); ?>
    <!-- Filter in form -->
    <?php do_action('wpdatatables_after_filtering_form', $this->getWpId()); ?>
<?php } ?>

<?php do_action('wpdatatables_before_table', $this->getWpId()); ?>

<?php if ((is_admin() && current_user_can('administrator') && $this->getTableType() == 'manual') || $this->isEditable()): ?>
    <?php wp_nonce_field('wdtFrontendEditTableNonce' . $this->getWpId(), 'wdtNonceFrontendEdit_' . $this->getWpId()); ?>
<?php endif; ?>
<?php if ($this->serverSide()): ?>
    <?php wp_nonce_field('wdtFrontendServerSideNonce' . $this->getWpId(), 'wdtNonceFrontendServerSide_' . $this->getWpId()); ?>
<?php endif; ?>
<input type="hidden" id="<?php echo esc_attr($this->getId()) ?>_desc"
       value='<?php echo $this->getJsonDescription(); ?>'/>

<?php if (!$this->serverSide()): ?>
    <input type="hidden" id="<?php echo esc_attr($this->getId()) ?>_data"
           value='<?php echo json_encode($this->getDataRows(), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG); ?>'/>
<?php endif; ?>
<?php if ($this->isGlobalSearch()): ?>
    <div id="<?php echo esc_attr($this->getId()) ?>_search_filter" class="wpExcelTable_search_filter">
        <label><?php esc_html_e('Search', 'wpdatatables'); ?>: <input type="search" class="" placeholder=""
                                                                      aria-controls="<?php echo esc_attr($this->getId()) ?>"></label>
    </div>
<?php endif; ?>
<div id="<?php echo esc_attr($this->getId()) ?>"
     class="<?php echo esc_attr($this->getCSSClasses()) ?> wpExcelTable wpExcelTableID-<?php echo esc_attr($this->getWpId()); ?>"
     data-described-by='<?php echo esc_attr($this->getId()) ?>_desc'
     data-wpdatatable_id="<?php echo esc_attr($this->getWpId()); ?>">
</div>

<?php do_action('wpdatatables_after_table', $this->getWpId()); ?>

<br/><br/>