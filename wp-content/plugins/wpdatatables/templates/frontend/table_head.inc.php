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
$dataRows = $this->getDataRows();
?>
<thead>
<?php if ($advancedFilterPosition === 'header' && !empty($dataRows) && !$this->getFilteringForm() && $this->advancedFilterEnabled()) { ?>
    <tr>
        <?php do_action('wpdatatables_before_header', $this->getWpId()); ?>
        <?php $expandShown = false; ?>
        <?php foreach ($this->getColumns() as $dataColumn) { ?>
            <th
                <?php if (!$expandShown && $dataColumn->isVisibleOnMobiles()){ ?>data-class="expand"<?php $expandShown = true;
            } ?>
                <?php if ($dataColumn->getHiddenAttr()) { ?>data-hide="<?php echo $dataColumn->getHiddenAttr() ?>"<?php } ?>
                class="<?php
                if ($dataColumn->getHiddenAttr()) {
                    echo $dataColumn->getHiddenAttr();
                }
                if ($dataColumn->getSorting()) { ?>sort <?php }
                echo $dataColumn->getCSSClasses(); ?>"
                style="<?php echo $dataColumn->getCSSStyle(); ?>"><?php echo ($dataColumn->getFilterType() != 'null') ? $dataColumn->getTitle() : '' ?>
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
        <?php if ($dataColumn->getHiddenAttr()) { ?>data-hide="<?php echo $dataColumn->getHiddenAttr() ?>"<?php } ?>
        class="<?php if ($dataColumn->getHiddenAttr()) {
            echo $dataColumn->getHiddenAttr();
        } ?> wdtheader <?php if ($dataColumn->getSorting()) { ?>sort <?php } ?><?php echo $dataColumn->getCSSClasses(); ?>"
        style="<?php echo $dataColumn->getCSSStyle(); ?>"><?php echo $dataColumn->getTitle() ?></th><?php } ?>
    <?php do_action('wpdatatables_after_header', $this->getWpId()); ?>
</tr>
</thead>
