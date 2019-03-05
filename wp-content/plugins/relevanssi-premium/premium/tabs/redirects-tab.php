<?php
/**
 * /premium/tabs/redirects-tab.php
 *
 * Prints out the Premium Redirects tab in Relevanssi settings.
 *
 * @package Relevanssi_Premium
 * @author  Mikko Saari
 * @license https://wordpress.org/about/gpl/ GNU General Public License
 * @see     https://www.relevanssi.com/
 */

/**
 * Prints out the Premium Redirects tab in Relevanssi settings.
 */
function relevanssi_redirects_tab() {
	$redirects = get_option( 'relevanssi_redirects' );
?>
<h2 id="options"><?php esc_html_e( 'Redirects', 'relevanssi' ); ?></h2>

<p><?php esc_html_e( 'If you want a particular search to always lead to a specific page, you can use the redirects. Whenever the search query matches a redirect, the search is automatically bypassed and the user is redirected to the target page.', 'relevanssi' ); ?></p>

<p><?php esc_html_e( 'Enter the search term and the target URL, which may be relative to your site home page or an absolute URL. If "Partial match" is checked, the redirect happens if the query word appears anywhere in the search query, even inside a word, so use it with care. If the search query matches multiple redirections, the first one it matches will trigger.', 'relevanssi' ); ?></p>

<table class="form-table" id="redirect_table">
	<thead>
	<tr>
	<th><?php esc_html_e( 'Query', 'relevanssi' ); ?></th>
	<th><?php esc_html_e( 'Partial match', 'relevanssi' ); ?></th>
	<th><?php esc_html_e( 'URL', 'relevanssi' ); ?></th>
	</tr>
	</thead>
	<tbody>
<?php
if ( empty( $redirects ) ) {
?>
	<tr class="redirect_table_row" id="row_0">
	<td><input type="text" name="query_0" size="60" />
	<div class="row-actions">
		<span class="copy"><a href="#" class="copy"><?php esc_html_e( 'Copy', 'relevanssi' ); ?></a> |</span>
		<span class="delete"><a href="#" class="remove"><?php esc_html_e( 'Remove', 'relevanssi' ); ?></a></span>
	</div>
	</td>
	<td><input type="checkbox" name="partial_0" /></td>
	<td><input type="text" name="url_0" size="60" /></td>
	</tbody>
	</tr>
<?php
} else {
	$row      = 0;
	$site_url = site_url();
	foreach ( $redirects as $redirect ) {
		$row_id  = esc_attr( $row );
		$query   = esc_attr( $redirect['query'] );
		$partial = '';
		if ( $redirect['partial'] ) {
			$partial = 'checked="checked"';
		}
		$url = esc_attr( $redirect['url'] );
		$url = str_replace( $site_url, '', $url );
		?>
	<tr class="redirect_table_row" id="row_<?php echo $row_id; // WPCS: XSS ok. ?>">
	<td><input type="text" name="query_<?php echo $row_id; // WPCS: XSS ok. ?>" size="60" value="<?php echo $query; // WPCS: XSS ok. ?>" />
		<div class="row-actions">
			<span class="copy"><a href="#" class="copy"><?php esc_html_e( 'Copy', 'relevanssi' ); ?></a> |</span>
			<span class="delete"><a href="#" class="remove"><?php esc_html_e( 'Remove', 'relevanssi' ); ?></a></span>
	</div>
	</td>
	<td><input type="checkbox" name="partial_<?php echo $row_id; // WPCS: XSS ok. ?>" <?php echo $partial; // WPCS: XSS ok. ?> /></td>
	<td><input type="text" name="url_<?php echo $row_id; // WPCS: XSS ok. ?>" size="60" value="<?php echo $url; // WPCS: XSS ok. ?>" /></td>
	</tr>
<?php
		$row++;
	}
}
?>
	</tbody>
	</table>

	<button type="button" class="secondary" id="add_redirect"><?php esc_html_e( 'Add a redirect', 'relevanssi' ); ?></button>

	<p><?php esc_html_e( "Once you're done, remember to click the save button below!", 'relevanssi' ); ?></p>
<?php
}
