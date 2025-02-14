<?php defined('ABSPATH') or die('Access denied.'); ?>
<?php
/** @var $this WPDataTable */
$displayLengthHelper = $this->getDisplayLength() > count($this->getDataRows());
$displayLength = $this->getDisplayLength() + 1;

if ($this->getDisplayLength() == -1 || $displayLengthHelper) {
    $displayLength = count($this->getDataRows()) + 1;
}

$isLoaderVisible = $this->isLoaderVisible();
$isHideBeforeFiltering = $this->hideTableBeforeFiltering;

$style = '';

if (!$isLoaderVisible || $isHideBeforeFiltering) {
    $style = "display: none;";
}
?>
<div data-id="<?php echo esc_attr($this->getWpId()) ?>"
     class="wdt-timeline-item wdt-timeline-<?php echo esc_attr($this->getId()); ?>"
     style="<?php echo esc_attr($style); ?>">
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