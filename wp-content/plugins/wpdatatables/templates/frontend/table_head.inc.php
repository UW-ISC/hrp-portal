<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
/**
 * Template file for the table head HTML
 * @author: Milos Timotic
 * @since: 22.4.17.
 */

/** @var string $advancedFilterPosition */
/** @var WPDataTable $this */
/** @var WDTColumn $dataColumn */
global $is_safari;
$dataRows = $this->getDataRows();
$type = $this->getTableType();
?>
<?php if ($type == 'woo_commerce') {
    include WDT_WOO_COMMERCE_PATH . 'source/templates/add_to_cart_block.inc.php';
} ?>
<thead>
<?php if ($advancedFilterPosition === 'header' && !empty($dataRows) && !$this->getFilteringForm() && $this->advancedFilterEnabled()) { ?>
    <tr>
        <?php do_action('wpdatatables_before_header', $this->getWpId()); ?>
        <?php $expandShown = false; ?>
        <?php foreach ($this->getColumns() as $dataColumn) { ?>
            <th
                <?php if (!$expandShown && $dataColumn->isVisibleOnMobiles()){ ?>data-class="expand"<?php $expandShown = true;
            } ?>
                <?php if ($dataColumn->getHiddenAttr()) { ?>data-hide="<?php echo esc_attr($dataColumn->getHiddenAttr()) ?>"<?php } ?>
                class="<?php
                if ($dataColumn->getHiddenAttr()) {
                    echo esc_attr($dataColumn->getHiddenAttr());
                }
                if ($dataColumn->getSorting()) { ?>sort <?php }
                echo esc_attr($dataColumn->getCSSClasses()); ?>"
                style="<?php echo esc_attr($dataColumn->getCSSStyle()); ?>"><?php echo ($dataColumn->getFilterType() != 'null') ? esc_attr($dataColumn->getTitle()) : '' ?>
            </th>
        <?php } ?>
        <?php do_action('wpdatatables_after_header', $this->getWpId()); ?>
    </tr>
<?php } ?>
<tr>
    <?php do_action('wpdatatables_before_header', $this->getWpId()); ?>
    <?php $expandShown = false; ?>
    <?php foreach ($this->getColumns() as $dataColumn) { ?>
        <th
        <?php if (!$expandShown && $dataColumn->isVisibleOnMobiles()){ ?>data-class="expand"<?php $expandShown = true;
    } ?>
        <?php if ($dataColumn->getHiddenAttr()) { ?>data-hide="<?php echo esc_attr($dataColumn->getHiddenAttr()) ?>"<?php } ?>
        class="<?php if ($dataColumn->getHiddenAttr()) {
            echo esc_attr($dataColumn->getHiddenAttr());
        } ?> wdtheader <?php if ($dataColumn->getSorting()) { ?>sort <?php } ?><?php echo esc_attr($dataColumn->getCSSClasses()); ?>"
        style="<?php echo esc_attr($dataColumn->getCSSStyle()); ?>"><?php if ($is_safari) { ?>
            <span><?php echo esc_attr($dataColumn->getTitle()) ?></span> <?php } ?>
        <?php if (!($is_safari)) { ?><?php echo esc_attr($dataColumn->getTitle()) ?><?php } ?></th><?php } ?>
    <?php do_action('wpdatatables_after_header', $this->getWpId()); ?>
</tr>
</thead>
