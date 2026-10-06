<div class="wpdt-c">
    <div id="wdt_search_all_tables_<?php echo esc_attr($unique_id); ?>" class="wdt-filter-all-tables">
        <label for="wdt_search_all_<?php echo esc_attr($unique_id); ?>" class="wpdt-visually-hidden">Search
            table</label>
        <span class="wpdt-c wpdt-visually-hidden"><?php esc_html_e('Search table', 'wpdatatables'); ?></span>
        <span class="wdt-search-icon"></span>
        <input id="wdt_search_all_<?php echo esc_attr($unique_id); ?>" type="search"
               class="form-control <?php echo esc_attr($class); ?><?php if ($use_global_search_only) { ?> wdt-use-global-only <?php } ?>"
               placeholder="<?php echo esc_attr($placeholder); ?>"/>
        <?php if ($use_button) { ?>
            <button class="button-search-all-tables <?php echo esc_attr($button_class); ?>"><?php echo esc_html($button_placeholder); ?></button>
        <?php } ?>
    </div>
</div>
<br>
