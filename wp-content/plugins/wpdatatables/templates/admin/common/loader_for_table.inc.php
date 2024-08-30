<?php defined('ABSPATH') or die('Access denied.'); ?>
<?php
/** @var $this WPDataTable */
$displayLengthHelper = $this->getDisplayLength() > count($this->getDataRows());
$displayLength = $this->getDisplayLength();

$tableIDcounter = WPDataTable::$wdt_internal_idcount;

if ($this->getDisplayLength() == -1 || $displayLengthHelper) {
    $displayLength = count($this->getDataRows());
}
?>
<div data-id="<?php echo esc_attr($this->getWpId())?>" class="wdt-timeline-item wdt-timeline-table_<?php echo esc_attr($tableIDcounter); ?>">
    <div class="wdt-table-loader">
        <div class="wdt-table-loader-row wdt-table-loader-header">
            <div class="wdt-table-loader-header-cell wdt-animated-background"></div>
            <div class="wdt-table-loader-header-cell wdt-animated-background"></div>
            <div class="wdt-table-loader-header-cell wdt-animated-background"></div>
        </div>
        <?php for ($i = 0; $i < $displayLength; $i++): ?>
            <div class="wdt-table-loader-row">
                <div class="wdt-table-loader-cell wdt-animated-background"></div>
                <div class="wdt-table-loader-cell wdt-animated-background"></div>
                <div class="wdt-table-loader-cell wdt-animated-background"></div>
            </div>
        <?php endfor; ?>
    </div>
</div>