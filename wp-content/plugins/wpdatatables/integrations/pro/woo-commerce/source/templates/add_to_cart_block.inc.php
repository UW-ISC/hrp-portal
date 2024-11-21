<?php defined('ABSPATH') or die('Access denied.');
if (!defined('WOOCOMMERCE_CART')) define('WOOCOMMERCE_CART', TRUE);
if (is_null(WC()->cart)) {
    wc_load_cart();
}
?>

<div class="wdt-woo-basket-info">
    <?php if (isset($this->_wdtNamedColumns["select"]) && $this->_wdtNamedColumns["select"]->isVisible()) : ?>
        <button id="wdt-add-to-cart-button-<?php echo $this->getWpId() ?>"
                class="button alt wdt-add-to-cart-button"
                data-value="<?php echo $this->getWpId() ?>">
            <?php esc_html_e('Add to cart', 'wpdatatables'); ?>
        </button>
    <?php endif; ?>

    <?php if ($this->getShowCartInformation()) : ?>
        <div class="wdt-woo-basket-icon">
            <a href="<?php echo apply_filters('woocommerce_get_cart_url', wc_get_page_permalink('cart')); ?>"
               class="basket-link">
                <i class="wpdt-icon-cart"></i>
                <span><?php esc_html_e('View cart', 'wpdatatables'); ?></span>
            </a>

            <?php if (WC()->cart) : ?>
                <div class="cart-info" hidden=>
                    <span class="cart-count"><?php echo WC()->cart->get_cart_contents_count(); ?> </span>
                    <span class="cart-items"> <?php esc_html_e(' items - ', 'wpdatatables'); ?></span>
                    <span class="cart-total"><?php echo WC()->cart->get_cart_total(); ?></span>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
