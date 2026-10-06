<?php defined('ABSPATH') or die('Access denied.'); ?>
<input type="hidden"
       value=""
       id="<?php echo esc_attr($tableSelector) ?>_<?php echo esc_attr($dataColumnKey) ?>"
       data-key="<?php echo esc_attr($dataColumnKey) ?>"
       data-value=""
       data-column_type="<?php echo esc_attr($dataColumn->getDataType()); ?>"
       data-column_header="<?php echo esc_attr($dataColumn->getTitle()); ?>"
       data-input_type="<?php echo esc_attr($dataColumn->getInputType()); ?>"
       class="form-control input-sm editDialogInput"
/>
