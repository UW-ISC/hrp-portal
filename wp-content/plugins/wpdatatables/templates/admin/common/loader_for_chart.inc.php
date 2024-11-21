<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
$height = esc_attr($this->getHeight());
$isLoaderVisible = (int)($this->getRenderData()['loader']);

$style = "height: {$height}px;";

if (!$isLoaderVisible) {
    $style .= " display: none;";
}
?>
<div class="wdt-wrapper-chart-loader"  data-id="<?php echo esc_attr($this->getId());?>" style="<?php echo esc_attr($style); ?>">
    <div class="wdt-main-item">
        <?php
        $roundedHeight = floor($this->getHeight() / 4);
        ?>
        <div class="wdt-chart-animated-background" style="height: <?php echo esc_attr($roundedHeight) ?>px">
            <div class="wdt-background-masker wdt-btn-divide-left wdt-chart-one"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-2"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-3"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-4 wdt-chart-three"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-5"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-6"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-7 "></div>
            <div class="wdt-background-masker wdt-btn-divide-left-8"></div>
        </div>
        <div class="wdt-chart-animated-background" style="height: <?php echo esc_attr($roundedHeight) ?>px">
            <div class="wdt-background-masker wdt-btn-divide-left"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-2 wdt-chart-two"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-3"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-4"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-5 wdt-chart-four"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-6"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-7"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-8"></div>
        </div>
        <div class="wdt-chart-animated-background" style="height: <?php echo esc_attr($roundedHeight) ?>px">
            <div class="wdt-background-masker wdt-btn-divide-left"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-2"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-3"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-4"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-5"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-6 wdt-chart-five"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-7"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-8"></div>
        </div>
        <div class="wdt-chart-animated-background" style="height: <?php echo esc_attr($roundedHeight) ?>px">
            <div class="wdt-background-masker wdt-btn-divide-left"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-2"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-3"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-4"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-5"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-6"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-7"></div>
            <div class="wdt-background-masker wdt-btn-divide-left-8"></div>
        </div>
        <div class="wdt-static-background">
            <div class="wdt-background-masker"></div>
        </div>
    </div>
</div>
