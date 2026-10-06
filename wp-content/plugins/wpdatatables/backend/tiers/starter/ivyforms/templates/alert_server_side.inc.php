<?php
/**
 * SSP notice for IvyForms tables.
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="alert alert-info wdt-ivyforms-ssp-alert">
	<?php esc_html_e('Server-side processing reads live data from IvyForms on each request. Sorting and column filters work for entry metadata and form field columns. Compound fields (name, address) and file/signature columns may sort or filter less predictably because values are stored as plain text.', 'wpdatatables'); ?>
</div>
