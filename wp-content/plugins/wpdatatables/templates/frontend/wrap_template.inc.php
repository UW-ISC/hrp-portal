<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
/** @var string $tableContent */
/** @var WPDataTable $this */
?>
<div class="wpdt-c <?php echo 'wdt-skin-' . esc_attr($this->getTableSkin()) ?><?php if ($this->isTableWCAG()) { ?> wpTableWCAG<?php } ?>">
    <?php echo $tableContent; ?>
</div>