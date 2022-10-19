<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
/** @var WDTColumn $dataColumn */
?>
<tbody>
<?php do_action('wpdatatables_before_first_row', $this->getWpId()); ?>
<?php if(!$this->serverSide() || ($this->serverSide() && ($this->getDisplayLength() !== -1 || count($this->getDataRows()) < 2000))) {?>
<?php foreach ($this->getDataRows() as $wdtRowIndex => $wdtRowDataArr) { ?>
    <?php do_action('wpdatatables_before_row', $this->getWpId(), $wdtRowIndex); ?>
        <tr id="table_<?php echo esc_attr($this->getWpId()) ?>_row_<?php echo $wdtRowIndex; ?>">
            <?php foreach ($this->getColumnsByHeaders() as $dataColumnHeader => $dataColumn) { ?>
                <td style="<?php echo esc_attr($dataColumn->getCSSStyle()); ?>"><?php echo isset($wdtRowDataArr[$dataColumnHeader]) ? apply_filters('wpdatatables_filter_cell_output', $this->returnCellValue($wdtRowDataArr[$dataColumnHeader], $dataColumnHeader), $this->getWpId(), $dataColumnHeader) : ''; ?></td>
            <?php } ?>
        </tr>
    <?php do_action('wpdatatables_after_row', $this->getWpId(), $wdtRowIndex); ?>
<?php } ?>
<?php } ?>
<?php do_action('wpdatatables_after_last_row', $this->getWpId()); ?>
</tbody>