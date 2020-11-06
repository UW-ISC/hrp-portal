<?php
/**
 * /premium/interface.php
 *
 * @package Relevanssi_Premium
 * @author  Mikko Saari
 * @license https://wordpress.org/about/gpl/ GNU General Public License
 * @see     https://www.relevanssi.com/
 */

/**
 * Adds the Premium page actions.
 *
 * Adds the Premium contextual help in the load-{page} hook and the Premium
 * admin JS to admin_footer-{page} hook.
 *
 * @param string $plugin_page The plugin page name for the hooks.
 */
function relevanssi_premium_plugin_page_actions( $plugin_page ) {
	require_once 'contextual-help.php';
	add_action( 'load-' . $plugin_page, 'relevanssi_premium_admin_help' );
	add_action( 'admin_footer-' . $plugin_page, 'relevanssi_pdf_action_javascript' );
}

/**
 * Prints out the form fields for entering the API key.
 *
 * Prints out table rows and form fields for entering the API key, or if API key
 * is set, controls to remove it.
 *
 * @param string $context The context for the form. Default null.
 *
 * @since 2.0.0
 */
function relevanssi_form_api_key( $context = null ) {
	$api_key = get_option( 'relevanssi_api_key' );
	if ( 'network' === $context ) {
		$api_key = get_network_option( null, 'relevanssi_api_key' );
	}
	if ( ! empty( $api_key ) ) :
		?>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'API key', 'relevanssi' ); ?>
		</th>
		<td>
			<strong><?php esc_html_e( 'API key is set', 'relevanssi' ); ?></strong>.<br />
			<input type='checkbox' id='relevanssi_remove_api_key' name='relevanssi_remove_api_key' /> <label for='relevanssi_remove_api_key'><?php esc_html_e( 'Remove the API key.', 'relevanssi' ); ?></label>
			<p class="description"><?php esc_html_e( 'A valid API key is required to use the automatic update feature and the PDF indexing. Otherwise the plugin will work just fine without an API key. Get your API key from Relevanssi.com.', 'relevanssi' ); ?></p>
		</td>
	</tr>
		<?php
	else :
		?>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'API key', 'relevanssi' ); ?>
		</th>
		<td>
			<label for='relevanssi_api_key'><?php esc_html_e( 'Set the API key:', 'relevanssi' ); ?>
			<input type='text' id='relevanssi_api_key' name='relevanssi_api_key' value='' /></label>
			<p class="description"><?php esc_html_e( 'A valid API key is required to use the automatic update feature and the PDF indexing. Otherwise the plugin will work just fine without an API key. Get your API key from Relevanssi.com.', 'relevanssi' ); ?></p>
		</td>
	</tr>
		<?php
	endif;
}

/**
 * Prints out the form fields for blocking update requests.
 *
 * @since 2.1.8
 */
function relevanssi_form_do_not_call_home() {
	$option          = get_option( 'relevanssi_do_not_call_home' );
	$can_i_call_home = relevanssi_check( $option );
	?>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Disable outside connections', 'relevanssi' ); ?>
		</th>
		<td>
			<label for='relevanssi_do_not_call_home'>
				<input type='checkbox' name='relevanssi_do_not_call_home' id='relevanssi_do_not_call_home' <?php echo esc_attr( $can_i_call_home ); ?> />
				<?php esc_html_e( 'Disable update version checking and attachment indexing', 'relevanssi' ); ?>
			</label>
		<p class="description"><?php esc_html_e( "If you check this box, Relevanssi will stop all outside connections. This means the plugin won't check for updates from Relevanssi.com and won't read attachment contents using Relevanssiservices.com attachment reader (using custom attachment reader is still allowed). Do not check this box unless you know what you're doing, because this will disable Relevanssi updates.", 'relevanssi' ); ?></p>
		</td>
		</td>
	</tr>
	<?php
}

/**
 * Prints out the form fields for controlling internal links.
 *
 * Prints out the form fields that control how the internal links are handled in
 * indexing.
 */
function relevanssi_form_internal_links() {
	$internal_links            = get_option( 'relevanssi_internal_links' );
	$internal_links_strip      = relevanssi_select( $internal_links, 'strip' );
	$internal_links_dont_strip = relevanssi_select( $internal_links, 'nostrip' );
	$internal_links_noindex    = relevanssi_select( $internal_links, 'noindex' );

	?>
	<tr>
		<th scope="row">
			<label for='relevanssi_internal_links'><?php esc_html_e( 'Internal links', 'relevanssi' ); ?></label>
		</th>
		<td>
			<select name='relevanssi_internal_links' id='relevanssi_internal_links'>
				<option value='noindex' <?php echo esc_attr( $internal_links_noindex ); ?>><?php esc_html_e( 'No special processing for internal links', 'relevanssi' ); ?></option>
				<option value='strip' <?php echo esc_attr( $internal_links_strip ); ?>><?php esc_html_e( 'Index internal links for target documents only', 'relevanssi' ); ?></option>
				<option value='nostrip' <?php echo esc_attr( $internal_links_dont_strip ); ?>><?php esc_html_e( 'Index internal links for both target and source', 'relevanssi' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'Internal link anchor tags can be indexed for target document, both target and source or source only. See Help for more details.', 'relevanssi' ); ?></p>
		</td>
	</tr>
	<?php
}

/**
 * Prints out the form fields for hiding post controls.
 *
 * Prints out the form fields that hide the post controls on edit pages, or
 * allow them for admins.
 *
 * @since 2.0.0
 */
function relevanssi_form_hide_post_controls() {
	$hide_post_controls = get_option( 'relevanssi_hide_post_controls' );
	$show_post_controls = get_option( 'relevanssi_show_post_controls' );

	$hide_post_controls = relevanssi_check( $hide_post_controls );
	$show_post_controls = relevanssi_check( $show_post_controls );

	$show_post_controls_class = 'screen-reader-text';
	if ( ! empty( $hide_post_controls ) ) {
		$show_post_controls_class = '';
	}
	?>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Hide Relevanssi', 'relevanssi' ); ?>
		</th>
		<td>
			<label for='relevanssi_hide_post_controls'>
				<input type='checkbox' name='relevanssi_hide_post_controls' id='relevanssi_hide_post_controls' <?php echo esc_attr( $hide_post_controls ); ?> />
				<?php esc_html_e( 'Hide Relevanssi on edit pages', 'relevanssi' ); ?>
			</label>
			<p class="description"><?php esc_html_e( 'Enabling this option hides Relevanssi on all post edit pages.', 'relevanssi' ); ?></p>
		</td>
	</tr>
	<tr id="show_post_controls" class="<?php echo esc_attr( $show_post_controls_class ); ?>">
		<th scope="row">
			<label for='relevanssi_show_post_controls'><?php esc_html_e( 'Show Relevanssi for admins', 'relevanssi' ); ?></label>
		</th>
		<td>
		<fieldset>
			<legend class="screen-reader-text"><?php esc_html_e( 'Show Relevanssi for admins on edit pages', 'relevanssi' ); ?></legend>
			<label for='relevanssi_show_post_controls'>
				<input type='checkbox' name='relevanssi_show_post_controls' id='relevanssi_show_post_controls' <?php echo esc_attr( $show_post_controls ); ?> />
				<?php esc_html_e( 'Show Relevanssi on edit pages for admins', 'relevanssi' ); ?>
			</label>
		</fieldset>
		<?php /* translators: first placeholder has the capability used for determining admins, second has the filter hook name to change that */ ?>
		<p class="description"><?php printf( esc_html__( 'If Relevanssi is hidden on post edit pages, enabling this option will show Relevanssi features for admin-level users. Admin-level users are those with %1$s capabilities, but if you want to use a different capability, you can use the %2$s filter to modify that.', 'relevanssi' ), '<code>manage_options</code>', '<code>relevanssi_options_capability</code>' ); ?></p>
		</td>
	</tr>
	<?php
}

/**
 * Prints out the form field for link weight boost.
 *
 * Prints out the form field for adjusting the link weight.
 */
function relevanssi_form_link_weight() {
	$link_boost = get_option( 'relevanssi_link_boost' );
	?>
	<tr>
		<td>
			<label for="relevanssi_link_boost"><?php esc_html_e( 'Internal links', 'relevanssi' ); ?></label>
		</td>
		<td class="col-2">
			<input type='text' id='relevanssi_link_boost' name='relevanssi_link_boost' size='4' value='<?php echo esc_attr( $link_boost ); ?>' />
		</td>
	</tr>
	<?php
}

/**
 * Prints out the form fields for post type weights.
 *
 * Prints out the form fields for adjusting the post type weights. Automatically
 * skips post types blocked by relevanssi_get_forbidden_post_types().
 *
 * @see relevanssi_get_forbidden_post_types
 */
function relevanssi_form_post_type_weights() {
	$post_type_weights = get_option( 'relevanssi_post_type_weights' );

	$post_types = get_post_types();
	foreach ( $post_types as $type ) {
		if ( in_array( $type, relevanssi_get_forbidden_post_types(), true ) ) {
			continue;
		}
		if ( isset( $post_type_weights[ $type ] ) ) {
			$value = $post_type_weights[ $type ];
		} else {
			$value = 1;
		}
		/* translators: name of the post type */
		$label = sprintf( __( "Post type '%s':", 'relevanssi' ), $type );

		?>
	<tr>
		<td>
			<label for="relevanssi_weight_<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $label ); ?></label>
		</td>
		<td class="col-2">
			<input type='text' id='relevanssi_weight_<?php echo esc_attr( $type ); ?>' name='relevanssi_weight_<?php echo esc_attr( $type ); ?>' size='4' value='<?php echo esc_attr( $value ); ?>' />
		</td>
	</tr>
		<?php
	}
}

/**
 * Prints out the form fields for taxonomy weights.
 *
 * Prints out the form fields for adjusting the taxonomy weights. Automatically
 * skips forbidden taxonomies.
 */
function relevanssi_form_taxonomy_weights() {
	$taxonomy_weights = get_option( 'relevanssi_post_type_weights' );

	$taxonomies = get_taxonomies( '', 'names' );
	foreach ( $taxonomies as $type ) {
		if ( in_array( $type, relevanssi_get_forbidden_taxonomies(), true ) ) {
			continue;
		}
		if ( isset( $taxonomy_weights[ 'post_tagged_with_' . $type ] ) ) {
			$value = $taxonomy_weights[ 'post_tagged_with_' . $type ];
		} elseif ( isset( $taxonomy_weights[ $type ] ) ) {
			// Legacy code, this changed in 2.1.8. Remove eventually.
			$value = $taxonomy_weights[ $type ];
		} else {
			$value = 1;
		}

		/* translators: name of the taxonomy */
		$label = sprintf( __( "Posts tagged with taxonomy '%s':", 'relevanssi' ), $type );

		?>
	<tr>
	<td>
		<label for="relevanssi_taxonomy_weight_<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $label ); ?></label>
	</td>
	<td class="col-2">
		<input type='text' id='relevanssi_taxonomy_weight_<?php echo esc_attr( $type ); ?>' name='relevanssi_taxonomy_weight_<?php echo esc_attr( $type ); ?>' size='4' value='<?php echo esc_attr( $value ); ?>' />
	</td>
</tr>
		<?php
	}

	$index_taxonomies = get_option( 'relevanssi_index_taxonomies' );
	if ( 'on' === $index_taxonomies ) {
		foreach ( $taxonomies as $type ) {
			if ( in_array( $type, relevanssi_get_forbidden_taxonomies(), true ) ) {
				continue;
			}
			if ( isset( $taxonomy_weights[ 'taxonomy_term_' . $type ] ) ) {
				$value = $taxonomy_weights[ 'taxonomy_term_' . $type ];
			} elseif ( isset( $taxonomy_weights[ $type ] ) ) {
				// Legacy code, this changed in 2.1.8. Remove eventually.
				$value = $taxonomy_weights[ $type ];
			} else {
				$value = 1;
			}

			/* translators: name of the taxonomy */
			$label = sprintf( __( "Terms in the taxonomy '%s':", 'relevanssi' ), $type );
			?>
	<tr>
	<td>
			<label for="relevanssi_term_weight_<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $label ); ?></label>
	</td>
	<td class="col-2">
		<input type='text' id='relevanssi_term_weight_<?php echo esc_attr( $type ); ?>' name='relevanssi_term_weight_<?php echo esc_attr( $type ); ?>' size='4' value='<?php echo esc_attr( $value ); ?>' />
	</td>
</tr>
			<?php
		}
	}
}

/**
 * Prints out the form fields for recency weight.
 *
 * Prints out the form fields for adjusting the recency weight bonus.
 */
function relevanssi_form_recency_weight() {
	$recency_bonus_array = get_option( 'relevanssi_recency_bonus' );
	$recency_bonus       = $recency_bonus_array['bonus'];
	?>
		<tr>
			<td>
				<label for='relevanssi_recency_bonus'><?php esc_html_e( 'Recent posts bonus weight:', 'relevanssi' ); ?></label>
			</td>
			<td class="col-2">
				<input type='text' id='relevanssi_recency_bonus' name='relevanssi_recency_bonus' size='4' value="<?php echo esc_attr( $recency_bonus ); ?>" />
			</td>
		</tr>
	<?php
}

/**
 * Prints out the form fields for recency cutoff.
 *
 * Prints out the form fields for adjusting the recency date cutoff.
 */
function relevanssi_form_recency_cutoff() {
	$recency_bonus_array = get_option( 'relevanssi_recency_bonus' );
	$recency_bonus_days  = $recency_bonus_array['days'];
	?>
	<tr>
		<th scope="row">
			<label for='relevanssi_recency_days'><?php esc_html_e( 'Recent posts bonus cutoff', 'relevanssi' ); ?></label>
		</th>
		<td>
			<input type='text' id='relevanssi_recency_days' name='relevanssi_recency_days' size='4' value="<?php echo esc_attr( $recency_bonus_days ); ?>" /> <?php esc_html_e( 'days', 'relevanssi' ); ?>
			<p class="description"><?php esc_html_e( 'Posts newer than the day cutoff specified here will have their weight multiplied with the bonus above.', 'relevanssi' ); ?></p>
		</td>
	</tr>
	<?php
}

/**
 * Prints out the form fields for hiding Relevanssi branding.
 *
 * Prints out the form fields for hiding the Relevanssi branding on user
 * searches screen.
 */
function relevanssi_form_hide_branding() {
	$hide_branding = get_option( 'relevanssi_hide_branding' );
	$hide_branding = relevanssi_check( $hide_branding );
	?>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Hide Relevanssi branding', 'relevanssi' ); ?>
		</th>
		<td>
		<fieldset>
			<?php /* translators: title of the User Searches page */ ?>
			<legend class="screen-reader-text"><?php printf( esc_html__( "Don't show Relevanssi branding on the '%s' screen.", 'relevanssi' ), esc_html__( 'User Searches', 'relevanssi' ) ); ?></legend>
			<label for='relevanssi_hide_branding'>
				<input type='checkbox' name='relevanssi_hide_branding' id='relevanssi_hide_branding' <?php echo esc_html( $hide_branding ); ?> />
				<?php /* translators: title of the User Searches page */ ?>
				<?php printf( esc_html__( "Don't show Relevanssi branding on the '%s' screen.", 'relevanssi' ), esc_html__( 'User Searches', 'relevanssi' ) ); ?>
			</label>
		</fieldset>
		</td>
	</tr>
	<?php
}

/**
 * Prints out the form fields for thousand separator.
 *
 * Prints out the form fields for adjusting the thousands separator in indexing.
 */
function relevanssi_form_thousands_separator() {
	$thousand_separator = get_option( 'relevanssi_thousand_separator' );
	?>
	<tr>
		<th scope="row">
			<label for='relevanssi_thousand_separator'><?php esc_html_e( 'Thousands separator', 'relevanssi' ); ?></label>
		</th>
		<td>
			<input type='text' name='relevanssi_thousand_separator' id='relevanssi_thousand_separator' size='3' value='<?php echo esc_attr( $thousand_separator ); ?>' />
			<p class="description"><?php esc_html_e( "If Relevanssi sees this character between numbers, it'll stick the numbers together no matter how the character would otherwise be handled. Especially useful if a space is used as a thousands separator.", 'relevanssi' ); ?></p>
		</td>
	</tr>
	<?php
}

/**
 * Prints out the form fields for disabling shortcodes.
 *
 * Prints out the form fields for adjusting the disabled shortcodes setting.
 */
function relevanssi_form_disable_shortcodes() {
	$disable_shortcodes = get_option( 'relevanssi_disable_shortcodes' );
	?>
	<tr>
		<th scope="row">
			<label for='relevanssi_disable_shortcodes'><?php esc_html_e( 'Disable these shortcodes', 'relevanssi' ); ?></label>
		</th>
		<td>
			<input type='text' name='relevanssi_disable_shortcodes' id='relevanssi_disable_shortcodes' size='60' value='<?php echo esc_attr( $disable_shortcodes ); ?>' />
			<p class="description"><?php esc_html_e( 'Enter a comma-separated list of shortcodes. These shortcodes will not be expanded if expand shortcodes above is enabled. This is useful if a particular shortcode is causing problems in indexing.', 'relevanssi' ); ?></p>
		</td>
	</tr>
	<?php
}

/**
 * Prints out the form fields for indexing MySQL columns.
 *
 * Prints out the form fields for adjusting the MySQL column indexing setting.
 *
 * @global $wpdb The WordPress database interface.
 */
function relevanssi_form_mysql_columns() {
	global $wpdb;
	$mysql_columns = get_option( 'relevanssi_mysql_columns' );
	$column_list   = wp_cache_get( 'relevanssi_column_list' );
	if ( false === $column_list ) {
		$column_list = $wpdb->get_results( "SHOW COLUMNS FROM $wpdb->posts" );
		wp_cache_set( 'relevanssi_column_list', $column_list );
	}
	$columns = array();
	foreach ( $column_list as $column ) {
		array_push( $columns, $column->Field ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}
	$columns = implode( ', ', $columns );

	?>
	<tr>
		<th scope="row">
			<label for='relevanssi_mysql_columns'><?php esc_html_e( 'MySQL columns', 'relevanssi' ); ?></label>
		</th>
		<td>
			<input type='text' name='relevanssi_mysql_columns' id='relevanssi_mysql_columns' size='60' value='<?php echo esc_attr( $mysql_columns ); ?>' />
			<p class="description">
			<?php
				/* translators: the placeholder has the wp_posts table name */
				printf( esc_html__( 'A comma-separated list of %s MySQL table columns to include in the index. Following columns are available: ', 'relevanssi' ), '<code>wp_posts</code>' );
				echo esc_html( $columns );
			?>
			</p>
		</td>
	</tr>
	<?php
}

/**
 * Prints out the form fields for searchblogs setting.
 *
 * Prints out the form fields for adjusting the global searchblogs setting.
 */
function relevanssi_form_searchblogs_setting() {
	if ( is_multisite() ) :
		$searchblogs     = get_option( 'relevanssi_searchblogs' );
		$searchblogs_all = get_option( 'relevanssi_searchblogs_all' );
		$searchblogs_all = relevanssi_check( $searchblogs_all );

		?>
	<tr>
		<th scope="row">
			<label for='relevanssi_searchblogs_all'><?php esc_html_e( 'Search all subsites', 'relevanssi' ); ?></label>
		</th>
		<td>
			<fieldset>
				<legend class="screen-reader-text"><?php esc_html_e( 'Search all subsites.', 'relevanssi' ); ?></legend>
				<label for='relevanssi_searchblogs_all'>
					<input type='checkbox' name='relevanssi_searchblogs_all' id='relevanssi_searchblogs_all' <?php echo esc_attr( $searchblogs_all ); ?> />
					<?php esc_html_e( 'Search all subsites', 'relevanssi' ); ?>
				</label>
				<p class="description"><?php esc_html_e( 'If this option is checked, multisite searches will include all subsites. Warning: if you have dozens of sites in your network, the searches may become too slow. This can be overridden from the search form.', 'relevanssi' ); ?></p>
			</fieldset>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for='relevanssi_searchblogs'><?php esc_html_e( 'Search some subsites', 'relevanssi' ); ?></label>
		</th>
		<td>
			<input type='text' name='relevanssi_searchblogs' id='relevanssi_searchblogs' size='60' value='<?php echo esc_attr( $searchblogs ); ?>'
			<?php
			if ( ! empty( $searchblogs_all ) ) {
				echo 'disabled';
			}
			?>
			/>
			<p class="description"><?php esc_html_e( 'Add a comma-separated list of blog ID values to have all search forms on this site search these multisite subsites. This can be overridden from the search form.', 'relevanssi' ); ?></p>
		</td>
	</tr>
		<?php
	endif;
}

/**
 * Prints out the form fields for indexing user profiles.
 *
 * Prints out the form fields for adjusting the user indexing settings.
 */
function relevanssi_form_index_users() {
	$index_users       = get_option( 'relevanssi_index_users' );
	$index_user_fields = get_option( 'relevanssi_index_user_fields' );
	$index_subscribers = get_option( 'relevanssi_index_subscribers' );
	$index_users       = relevanssi_check( $index_users );
	$index_subscribers = relevanssi_check( $index_subscribers );

	$fields_display = 'class="screen-reader-text"';
	if ( ! empty( $index_users ) ) {
		$fields_display = '';
	}
	?>
	<h2><?php esc_html_e( 'Indexing user profiles', 'relevanssi' ); ?></h2>

	<table class="form-table">
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Index user profiles', 'relevanssi' ); ?>
		</th>
		<td>
		<fieldset>
			<legend class="screen-reader-text"><?php esc_html_e( 'Index user profiles.', 'relevanssi' ); ?></legend>
			<label for='relevanssi_index_users'>
				<input type='checkbox' name='relevanssi_index_users' id='relevanssi_index_users' <?php echo esc_attr( $index_users ); ?> />
				<?php esc_html_e( 'Index user profiles.', 'relevanssi' ); ?>
			</label>
			<p class="description"><?php esc_html_e( 'Relevanssi will index user profiles. This includes first name, last name, display name and user description.', 'relevanssi' ); ?></p>
			<p class="description important screen-reader-text" id="user_profile_notice"><?php esc_html_e( 'This may require changes to search results template, see the contextual help.', 'relevanssi' ); ?></p>
		</fieldset>
		</td>
	</tr>
	<tr id="index_subscribers" <?php echo $fields_display; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<th scope="row">
			<?php esc_html_e( 'Index subscribers', 'relevanssi' ); ?>
		</th>
		<td>
		<fieldset>
			<legend class="screen-reader-text"><?php esc_html_e( 'Index also subscriber profiles.', 'relevanssi' ); ?></legend>
			<label for='relevanssi_index_subscribers'>
				<input type='checkbox' name='relevanssi_index_subscribers' id='relevanssi_index_subscribers' <?php echo esc_attr( $index_subscribers ); ?> />
				<?php esc_html_e( 'Index also subscriber profiles.', 'relevanssi' ); ?>
			</label>
			<p class="description"><?php esc_html_e( 'By default, Relevanssi indexes authors, editors, contributors and admins, but not subscribers. You can change that with this option.', 'relevanssi' ); ?></p>
		</fieldset>
		</td>
	</tr>

	<tr id="user_extra_fields" <?php echo $fields_display; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<th scope="row">
			<label for='relevanssi_index_user_fields'><?php esc_html_e( 'Extra fields', 'relevanssi' ); ?></label>
		</th>
		<td>
			<input type='text' name='relevanssi_index_user_fields' id='relevanssi_index_user_fields' size='60' value='<?php echo esc_attr( $index_user_fields ); ?>' /></label><br />
			<p class="description"><?php esc_html_e( 'A comma-separated list of extra user fields to include in the index. These can be user fields or user meta.', 'relevanssi' ); ?></p>
		</td>
	</tr>
	</table>
	<?php
}

/**
 * Prints out the form fields for indexing synonyms.
 *
 * Prints out the form fields for adjusting the synonym indexing settings.
 */
function relevanssi_form_index_synonyms() {
	$index_synonyms = get_option( 'relevanssi_index_synonyms' );
	$index_synonyms = relevanssi_check( $index_synonyms );
	?>
	<h3><?php esc_html_e( 'Indexing synonyms', 'relevanssi' ); ?></h3>
	<table class="form-table">
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Index synonyms', 'relevanssi' ); ?>
		</th>
		<td>
		<fieldset>
			<legend class="screen-reader-text"><?php esc_html_e( 'Index synonyms for AND searches.', 'relevanssi' ); ?></legend>
			<label for='relevanssi_index_synonyms'>
				<input type='checkbox' name='relevanssi_index_synonyms' id='relevanssi_index_synonyms' <?php echo esc_attr( $index_synonyms ); ?> />
				<?php esc_html_e( 'Index synonyms for AND searches.', 'relevanssi' ); ?>
			</label>
		</fieldset>
		<p class="description">
		<?php
			_e( 'If checked, Relevanssi will use the synonyms in indexing. If you add <code>dog = hound</code> to the synonym list and enable this feature, every time the indexer sees <code>hound</code> in post content or post title, it will index it as <code>hound dog</code>. Thus, the post will be found when searching with either word. This makes it possible to use synonyms with AND searches, but will slow down indexing, especially with large databases and large lists of synonyms. This only works for post titles and post content. You can use multi-word keys and values, but phrases do not work.', 'relevanssi' ); // phpcs:ignore WordPress.Security.EscapeOutput.UnsafePrintingFunction
		?>
		</p>
		</td>
	</tr>
	</table>

	<br /><br />
	<?php
}

/**
 * Prints out the form fields for indexing PDF content.
 *
 * Prints out the form fields for adjusting the way the PDF content is indexed for parent posts.
 */
function relevanssi_form_index_pdf_parent() {
	$index_pdf_parent = get_option( 'relevanssi_index_pdf_parent' );
	$index_pdf_parent = relevanssi_check( $index_pdf_parent );
	$index_post_types = get_option( 'relevanssi_index_post_types', array() );
	?>
	<h2><?php esc_html_e( 'Indexing PDF content', 'relevanssi' ); ?></h2>

	<table class="form-table" role="presentation">
	<tr>
	<th scope="row">
		<?php esc_html_e( 'Index for parent', 'relevanssi' ); ?>
	</th>
	<td>
		<label for='relevanssi_index_pdf_parent'>
			<input type='checkbox' name='relevanssi_index_pdf_parent' id='relevanssi_index_pdf_parent' <?php echo esc_attr( $index_pdf_parent ); ?> />
			<?php esc_html_e( 'Index PDF contents for parent post', 'relevanssi' ); ?>
		</label>
		<?php /* translators: name of the attachment post type */ ?>
		<p class="description"><?php printf( esc_html__( 'If checked, Relevanssi indexes the PDF content both for the attachment post and the parent post. You can control the attachment post visibility by indexing or not indexing the post type %s.', 'relevanssi' ), '<code>attachment</code>' ); ?></p>
		<?php if ( ! in_array( 'attachment', $index_post_types, true ) && empty( $index_pdf_parent ) ) : ?>
			<?php /* translators: name of the attachment post type */ ?>
		<p class="description important"><?php printf( esc_html__( "You have not chosen to index the post type %s. You won't see any PDF content in the search results, unless you check this option.", 'relevanssi' ), '<code>attachment</code>' ); ?></p>
		<?php endif; ?>
		<?php if ( in_array( 'attachment', $index_post_types, true ) && ! empty( $index_pdf_parent ) ) : ?>
			<?php /* translators: name of the attachment post type */ ?>
		<p class="description important"><?php printf( esc_html__( 'Searching for PDF contents will now return both the attachment itself and the parent post. Are you sure you want both in the results?', 'relevanssi' ), '<code>attachment</code>' ); ?></p>
		<?php endif; ?>
	</td>
	</tr>
	</table>
	<?php
}

/**
 * Prints out the form fields for indexing taxonomy terms.
 *
 * Prints out the form fields for choosing which taxonomies are indexed.
 */
function relevanssi_form_index_taxonomies() {
	$index_taxonomies       = get_option( 'relevanssi_index_taxonomies' );
	$index_taxonomies       = relevanssi_check( $index_taxonomies );
	$index_these_taxonomies = get_option( 'relevanssi_index_terms', array() );

	$fields_display = 'class="screen-reader-text"';
	if ( ! empty( $index_taxonomies ) ) {
		$fields_display = '';
	}

	?>
	<h2><?php esc_html_e( 'Indexing taxonomy terms', 'relevanssi' ); ?></h2>

	<table class="form-table" role="presentation">
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Index taxonomy terms', 'relevanssi' ); ?>
		</th>
		<td>
			<label for='relevanssi_index_taxonomies'>
				<input type='checkbox' name='relevanssi_index_taxonomies' id='relevanssi_index_taxonomies' <?php echo esc_attr( $index_taxonomies ); ?> />
				<?php esc_html_e( 'Index taxonomy terms.', 'relevanssi' ); ?>
			</label>
			<p class="description"><?php esc_html_e( 'Relevanssi will index taxonomy terms (categories, tags and custom taxonomies). Searching for taxonomy term name will return the taxonomy term page.', 'relevanssi' ); ?></p>
		</td>
	</tr>
	<tr id="taxonomies" <?php echo $fields_display; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<th scope="row">
			<?php esc_html_e( 'Taxonomies', 'relevanssi' ); ?>
		</th>
		<td>
			<table class="widefat" id="index_terms_table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Taxonomy', 'relevanssi' ); ?></th>
					<th><?php esc_html_e( 'Index', 'relevanssi' ); ?></th>
					<th><?php esc_html_e( 'Public?', 'relevanssi' ); ?></th>
				</tr>
			</thead>
	<?php
	$taxos = get_taxonomies( '', 'objects' );
	foreach ( $taxos as $taxonomy ) {
		if ( in_array( $taxonomy->name, relevanssi_get_forbidden_taxonomies(), true ) ) {
			continue;
		}
		if ( in_array( $taxonomy->name, $index_these_taxonomies, true ) ) {
			$checked = 'checked';
		} else {
			$checked = '';
		}

		if ( $taxonomy->public ) {
			$public = __( 'yes', 'relevanssi' );
		} else {
			$public = __( 'no', 'relevanssi' );
		}

		// Translators: %s is the post type name.
		$screen_reader_label = sprintf( __( 'Index terms for taxonomy %s', 'relevanssi' ), $taxonomy->name );
		$public              = __( 'no', 'relevanssi' );
		// Translators: %s is the post type name.
		$screen_reader_public = sprintf( __( 'Taxonomy %s is not public', 'relevanssi' ), $taxonomy->name );
		if ( $taxonomy->public ) {
			$public = __( 'yes', 'relevanssi' );
			// Translators: %s is the post type name.
			$screen_reader_public = sprintf( __( 'Taxonomy %s is public', 'relevanssi' ), $taxonomy->name );
		}
		?>
	<tr>
		<th scope="row">
			<label class="screen-reader-text" for="relevanssi_index_terms_<?php echo esc_attr( $taxonomy->name ); ?>">
			<?php echo esc_html( $screen_reader_label ); ?></label>
			<?php echo esc_html( $taxonomy->name ); ?>
		</th>
		<td>
			<input type='checkbox' name='relevanssi_index_terms_<?php echo esc_attr( $taxonomy->name ); ?>'
			id='relevanssi_index_terms_<?php echo esc_attr( $taxonomy->name ); ?>' <?php echo esc_attr( $checked ); ?> />
		</td>
		<td>
			<span aria-hidden="true"><?php echo esc_html( $public ); ?></span>
			<span class="screen-reader-text"><?php echo esc_html( $screen_reader_public ); ?></span>
		</td>
	</tr>
		<?php
	}
	?>
			</table>
		</td>
	</tr>
	</table>
	<?php
}

/**
 * Prints out the form fields for indexing post type archives.
 *
 * Prints out the form fields for choosing which post types are indexed.
 */
function relevanssi_form_index_post_type_archives() {
	$index_post_type_archives = get_option( 'relevanssi_index_post_type_archives' );
	$index_post_type_archives = relevanssi_check( $index_post_type_archives );

	$fields_display = 'class="screen-reader-text"';
	if ( ! empty( $index_post_type_archives ) ) {
		$fields_display = '';
	}

	?>
	<h2><?php esc_html_e( 'Indexing post type archives', 'relevanssi' ); ?></h2>

	<table class="form-table">
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Index post type archives', 'relevanssi' ); ?>
		</th>
		<td>
		<fieldset>
			<legend class="screen-reader-text"><?php esc_html_e( 'Index post type archives.', 'relevanssi' ); ?></legend>
			<label for='relevanssi_index_post_type_archives'>
				<input type='checkbox' name='relevanssi_index_post_type_archives' id='relevanssi_index_post_type_archives' <?php echo esc_attr( $index_post_type_archives ); ?> />
				<?php esc_html_e( 'Index post type archives.', 'relevanssi' ); ?>
			</label>
			<?php // Translators: %s is the name of filter hook. ?>
			<p class="description"><?php printf( esc_html__( 'Relevanssi will index post type archive pages. By default Relevanssi indexes the post type label and the description set when the post type is registered. If you want to index some other content, you can use the %s filter hook to adjust the content.', 'relevanssi' ), '<code>relevanssi_post_type_additional_content</code>' ); ?></p>
		</fieldset>
		</td>
	</tr>
	<tr id="posttypearchives" <?php echo $fields_display; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<th scope="row">
			<?php esc_html_e( 'Post types indexed', 'relevanssi' ); ?>
		</th>
		<td>
			<?php
			$post_types = relevanssi_get_indexed_post_type_archives();
			echo '<p>' . esc_html( implode( ', ', $post_types ) ) . '</p>';
			?>
			<?php // Translators: %1$s is 'has_archive', %2$s is 'relevanssi_indexed_post_type_archives' . ?>
			<p class="description"><?php printf( esc_html__( 'This list includes all post types that are not built in and have %1$s set to true. If you want to adjust the list, you can use the %2$s filter hook.', 'relevanssi' ), '<code>has_archive</code>', '<code>relevanssi_indexed_post_type_archives</code>' ); ?></p>
		</td>
	</tr>
	</table>
	<?php
}

/**
 * Adds admin PDF scripts for Relevanssi Premium.
 *
 * Adds the admin-side Javascript for Relevanssi Premium PDF controls and
 * includes some script localizations.
 *
 * @global Object $post The global post object.
 *
 * @param string $hook The current page hook.
 */
function relevanssi_premium_add_admin_scripts( $hook ) {
	global $relevanssi_variables;

	$plugin_dir_url = plugin_dir_url( $relevanssi_variables['file'] );

	// These are the only page hooks Relevanssi admin scripts will hook into.
	$post_hooks = array( 'post.php', 'post-new.php' );
	if ( in_array( $hook, $post_hooks, true ) ) {
		global $post;
		// Only add the scripts for attachment posts.
		if ( 'attachment' === $post->post_type ) {
			$api_key = get_network_option( null, 'relevanssi_api_key' );
			if ( ! $api_key ) {
				$api_key = get_option( 'relevanssi_api_key' );
			}
			wp_enqueue_script(
				'relevanssi_admin_pdf_js',
				$plugin_dir_url . 'premium/admin_pdf_scripts.js',
				array( 'jquery' ),
				$relevanssi_variables['plugin_version'],
				true
			);
			wp_localize_script(
				'relevanssi_admin_pdf_js',
				'admin_pdf_data',
				array(
					'send_pdf_nonce' => wp_create_nonce( 'relevanssi_send_pdf' ),
				)
			);
		}
		wp_enqueue_script(
			'relevanssi_metabox_js',
			$plugin_dir_url . 'premium/admin_metabox_scripts.js',
			array( 'jquery' ),
			$relevanssi_variables['plugin_version'],
			true
		);
		wp_localize_script(
			'relevanssi_metabox_js',
			'relevanssi_metabox_data',
			array(
				'metabox_nonce' => wp_create_nonce( 'relevanssi_metabox_nonce' ),
			)
		);
		wp_enqueue_style(
			'relevanssi_metabox_css',
			$plugin_dir_url . 'premium/metabox_styles.css',
			array(),
			$relevanssi_variables['plugin_version']
		);

	}

	$nonce = array(
		'taxonomy_indexing_nonce'          => wp_create_nonce( 'relevanssi_taxonomy_indexing_nonce' ),
		'user_indexing_nonce'              => wp_create_nonce( 'relevanssi_user_indexing_nonce' ),
		'indexing_nonce'                   => wp_create_nonce( 'relevanssi_indexing_nonce' ),
		'post_type_archive_indexing_nonce' => wp_create_nonce( 'relevanssi_post_type_archive_indexing_nonce' ),
		'searching_nonce'                  => wp_create_nonce( 'relevanssi_admin_search_nonce' ),
	);

	wp_localize_script( 'relevanssi_admin_js_premium', 'nonce', $nonce );
}

/**
 * Imports Relevanssi Premium options.
 *
 * Takes the options array and does the actual updating of options using
 * update_options().
 *
 * @param array $options Key has the option name, value the option value.
 */
function relevanssi_import_options( $options ) {
	$unserialized = json_decode( stripslashes( $options ) );
	foreach ( $unserialized as $key => $value ) {
		if ( in_array(
			$key,
			array(
				'relevanssi_post_type_weights',
				'relevanssi_recency_bonus',
				'relevanssi_punctuation',
				'relevanssi_related_style',
				'relevanssi_related_settings',
			),
			true
		) ) {
			// The options are associative arrays that are translated to objects in JSON and need to be changed back to arrays.
			$value = (array) $value;
		}
		if ( 'relevanssi_redirects' === $key ) {
			$value = json_decode( wp_json_encode( $value ), true );
		}
		update_option( $key, $value );
	}

	echo "<div id='relevanssi-warning' class='updated fade'>" . esc_html__( 'Options updated!', 'relevanssi' ) . '</div>';
}

/**
 * Updates Relevanssi Premium options.
 *
 * @global array $relevanssi_variables Relevanssi global variables, used to
 * access the plugin file name.
 *
 * Reads in the options from $_REQUEST and updates the correct options,
 * depending on which tab has been active.
 */
function relevanssi_update_premium_options() {
	global $relevanssi_variables;
	check_admin_referer( plugin_basename( $relevanssi_variables['file'] ), 'relevanssi_options' );

	$request = $_REQUEST; // WPCS: Input var okay.
	if ( ! isset( $request['tab'] ) ) {
		$request['tab'] = '';
	}

	if ( isset( $request['relevanssi_link_boost'] ) ) {
		$boost = floatval( $request['relevanssi_link_boost'] );
		update_option( 'relevanssi_link_boost', $boost );
	}

	if ( empty( $request['relevanssi_api_key'] ) ) {
		unset( $request['relevanssi_api_key'] );
	}

	if ( 'overview' === $request['tab'] ) {
		if ( ! isset( $request['relevanssi_hide_post_controls'] ) ) {
			$request['relevanssi_hide_post_controls'] = 'off';
		}
		if ( ! isset( $request['relevanssi_show_post_controls'] ) ) {
			$request['relevanssi_show_post_controls'] = 'off';
		}
		if ( ! isset( $request['relevanssi_do_not_call_home'] ) ) {
			$request['relevanssi_do_not_call_home'] = 'off';
		}
	}

	if ( 'indexing' === $request['tab'] ) {
		if ( ! isset( $request['relevanssi_index_users'] ) ) {
			$request['relevanssi_index_users'] = 'off';
		}

		if ( ! isset( $request['relevanssi_index_synonyms'] ) ) {
			$request['relevanssi_index_synonyms'] = 'off';
		}

		if ( ! isset( $request['relevanssi_index_taxonomies'] ) ) {
			$request['relevanssi_index_taxonomies'] = 'off';
		}

		if ( ! isset( $request['relevanssi_index_subscribers'] ) ) {
			$request['relevanssi_index_subscribers'] = 'off';
		}

		if ( ! isset( $request['relevanssi_index_post_type_archives'] ) ) {
			$request['relevanssi_index_post_type_archives'] = 'off';
		}

		if ( ! isset( $request['relevanssi_index_pdf_parent'] ) ) {
			$request['relevanssi_index_pdf_parent'] = 'off';
		}
	}

	if ( 'attachments' === $request['tab'] ) {
		if ( ! isset( $request['relevanssi_read_new_files'] ) ) {
			$request['relevanssi_read_new_files'] = 'off';
		}

		if ( ! isset( $request['relevanssi_send_pdf_files'] ) ) {
			$request['relevanssi_send_pdf_files'] = 'off';
		}

		if ( ! isset( $request['relevanssi_link_pdf_files'] ) ) {
			$request['relevanssi_link_pdf_files'] = 'off';
		}
	}

	if ( 'searching' === $request['tab'] ) {
		if ( isset( $request['relevanssi_recency_bonus'] ) && isset( $request['relevanssi_recency_days'] ) ) {
			$relevanssi_recency_bonus          = array();
			$relevanssi_recency_bonus['bonus'] = floatval( $request['relevanssi_recency_bonus'] );
			$relevanssi_recency_bonus['days']  = intval( $request['relevanssi_recency_days'] );
			update_option( 'relevanssi_recency_bonus', $relevanssi_recency_bonus );
		}

		if ( ! isset( $request['relevanssi_searchblogs_all'] ) ) {
			$request['relevanssi_searchblogs_all'] = 'off';
		}
	}

	if ( 'logging' === $request['tab'] ) {
		if ( ! isset( $request['relevanssi_hide_branding'] ) ) {
			$request['relevanssi_hide_branding'] = 'off';
		}
	}

	if ( 'related' === $request['tab'] ) {
		$settings = get_option( 'relevanssi_related_settings', relevanssi_related_default_settings() );

		$settings['enabled'] = 'off';
		if ( isset( $request['relevanssi_related_enabled'] ) && 'off' !== $request['relevanssi_related_enabled'] ) {
			$settings['enabled'] = 'on';
		}
		if ( isset( $request['relevanssi_related_number'] ) ) {
			$settings['number'] = intval( $request['relevanssi_related_number'] );
		}
		if ( isset( $request['relevanssi_related_months'] ) ) {
			$settings['months'] = intval( $request['relevanssi_related_months'] );
		}
		if ( isset( $request['relevanssi_related_nothing'] ) ) {
			$settings['nothing'] = 'nothing';
			if ( in_array( $request['relevanssi_related_nothing'], array( 'random', 'random_cat' ), true ) ) {
				$settings['nothing'] = $request['relevanssi_related_nothing'];
			}
		}
		if ( isset( $request['relevanssi_related_notenough'] ) ) {
			$settings['notenough'] = 'nothing';
			if ( in_array( $request['relevanssi_related_notenough'], array( 'random', 'random_cat' ), true ) ) {
				$settings['notenough'] = $request['relevanssi_related_notenough'];
			}
		}
		$settings['append'] = '';
		if ( isset( $request['relevanssi_related_append'] ) && is_array( $request['relevanssi_related_append'] ) ) {
			$settings['append'] = implode( ',', $request['relevanssi_related_append'] );
		}
		$settings['post_types'] = '';
		if ( isset( $request['relevanssi_related_post_types'] ) && is_array( $request['relevanssi_related_post_types'] ) ) {
			$settings['post_types'] = implode( ',', $request['relevanssi_related_post_types'] );
			if ( false !== stripos( $settings['post_types'], 'matching_post_type' ) ) {
				$settings['post_types'] = 'matching_post_type';
			}
		}
		$settings['keyword'] = '';
		if ( isset( $request['relevanssi_related_keyword'] ) && is_array( $request['relevanssi_related_keyword'] ) ) {
			$settings['keyword'] = implode( ',', $request['relevanssi_related_keyword'] );
		}
		$settings['restrict'] = '';
		if ( isset( $request['relevanssi_related_restrict'] ) && is_array( $request['relevanssi_related_restrict'] ) ) {
			$settings['restrict'] = implode( ',', $request['relevanssi_related_restrict'] );
		}
		$settings['cache_for_admins'] = 'off';
		if ( isset( $request['relevanssi_related_cache_for_admins'] ) && 'off' !== $request['relevanssi_related_cache_for_admins'] ) {
			$settings['cache_for_admins'] = 'on';
		}

		update_option( 'relevanssi_related_settings', $settings );

		if ( isset( $request['relevanssi_flush_related_cache'] ) && 'off' !== $request['relevanssi_flush_related_cache'] ) {
			relevanssi_flush_related_cache();
		}

		$style = get_option( 'relevanssi_related_style', relevanssi_related_default_styles() );

		if ( isset( $request['relevanssi_related_width'] ) ) {
			$style['width'] = intval( $request['relevanssi_related_width'] );
		}
		$style['excerpts'] = 'off';
		if ( isset( $request['relevanssi_related_excerpts'] ) && 'off' !== $request['relevanssi_related_excerpts'] ) {
			$style['excerpts'] = 'on';
		}
		$style['titles'] = 'off';
		if ( isset( $request['relevanssi_related_titles'] ) && 'off' !== $request['relevanssi_related_titles'] ) {
			$style['titles'] = 'on';
		}
		$style['thumbnails'] = 'off';
		if ( isset( $request['relevanssi_related_thumbnails'] ) && 'off' !== $request['relevanssi_related_thumbnails'] ) {
			$style['thumbnails'] = 'on';
		}
		if ( isset( $request['relevanssi_default_thumbnail'] ) ) {
			$style['default_thumbnail'] = intval( $request['relevanssi_default_thumbnail'] );
		}
		if ( isset( $request['relevanssi_remove_default_thumbnail'] ) && 'off' !== $request['relevanssi_remove_default_thumbnail'] ) {
			$style['default_thumbnail'] = 0;
		}
		update_option( 'relevanssi_related_style', $style );
	}

	if ( isset( $request['relevanssi_remove_api_key'] ) ) {
		update_option( 'relevanssi_api_key', '', false );
	}
	if ( isset( $request['relevanssi_api_key'] ) ) {
		$value = sanitize_text_field( wp_unslash( $request['relevanssi_api_key'] ) );
		update_option( 'relevanssi_api_key', $value, false );
	}
	if ( isset( $request['relevanssi_index_synonyms'] ) ) {
		$value = 'off';
		if ( 'off' !== $request['relevanssi_index_synonyms'] ) {
			$value = 'on';
		}
		update_option( 'relevanssi_index_synonyms', $value, false );
	}
	if ( isset( $request['relevanssi_index_post_type_archives'] ) ) {
		$value = 'off';
		if ( 'off' !== $request['relevanssi_index_post_type_archives'] ) {
			$value = 'on';
		}
		update_option( 'relevanssi_index_post_type_archives', $value, false );
	}
	if ( isset( $request['relevanssi_index_users'] ) ) {
		$value = 'off';
		if ( 'off' !== $request['relevanssi_index_users'] ) {
			$value = 'on';
		}
		update_option( 'relevanssi_index_users', $value, false );
	}
	if ( isset( $request['relevanssi_index_subscribers'] ) ) {
		$value = 'off';
		if ( 'off' !== $request['relevanssi_index_subscribers'] ) {
			$value = 'on';
		}
		update_option( 'relevanssi_index_subscribers', $value, false );
	}
	if ( isset( $request['relevanssi_index_user_fields'] ) ) {
		$value = sanitize_text_field( wp_unslash( $request['relevanssi_index_user_fields'] ) );
		update_option( 'relevanssi_index_user_fields', $value, false );
	}
	if ( isset( $request['relevanssi_internal_links'] ) ) {
		$value = sanitize_text_field( wp_unslash( $request['relevanssi_internal_links'] ) );
		update_option( 'relevanssi_internal_links', $value, false );
	}
	if ( isset( $request['relevanssi_hide_branding'] ) ) {
		$value = 'off';
		if ( 'off' !== $request['relevanssi_hide_branding'] ) {
			$value = 'on';
		}
		update_option( 'relevanssi_hide_branding', $value, false );
	}
	if ( isset( $request['relevanssi_hide_post_controls'] ) ) {
		$value = 'off';
		if ( 'off' !== $request['relevanssi_hide_post_controls'] ) {
			$value = 'on';
		}
		update_option( 'relevanssi_hide_post_controls', $value, false );
	}
	if ( isset( $request['relevanssi_show_post_controls'] ) ) {
		$value = 'off';
		if ( 'off' !== $request['relevanssi_show_post_controls'] ) {
			$value = 'on';
		}
		update_option( 'relevanssi_show_post_controls', $value, false );
	}
	if ( isset( $request['relevanssi_do_not_call_home'] ) ) {
		$value = 'off';
		if ( 'off' !== $request['relevanssi_do_not_call_home'] ) {
			$value = 'on';
		}
		update_option( 'relevanssi_do_not_call_home', $value, false );
	}
	if ( isset( $request['relevanssi_index_taxonomies'] ) ) {
		$value = 'off';
		if ( 'off' !== $request['relevanssi_index_taxonomies'] ) {
			$value = 'on';
		}
		update_option( 'relevanssi_index_taxonomies', $value, false );
	}
	if ( isset( $request['relevanssi_thousand_separator'] ) ) {
		$value = sanitize_text_field( wp_unslash( $request['relevanssi_thousand_separator'] ) );
		update_option( 'relevanssi_thousand_separator', $value );
	}
	if ( isset( $request['relevanssi_disable_shortcodes'] ) ) {
		$value = sanitize_text_field( wp_unslash( $request['relevanssi_disable_shortcodes'] ) );
		update_option( 'relevanssi_disable_shortcodes', $value );
	}
	if ( isset( $request['relevanssi_mysql_columns'] ) ) {
		$value = sanitize_text_field( wp_unslash( $request['relevanssi_mysql_columns'] ) );
		update_option( 'relevanssi_mysql_columns', $value, false );
	}
	if ( isset( $request['relevanssi_searchblogs'] ) ) {
		$value = sanitize_text_field( wp_unslash( $request['relevanssi_searchblogs'] ) );
		update_option( 'relevanssi_searchblogs', $value );
	}
	if ( isset( $request['relevanssi_searchblogs_all'] ) ) {
		$value = 'off';
		if ( 'off' !== $request['relevanssi_searchblogs_all'] ) {
			$value = 'on';
		}
		update_option( 'relevanssi_searchblogs_all', $value );
	}
	if ( isset( $request['relevanssi_read_new_files'] ) ) {
		$value = 'off';
		if ( 'off' !== $request['relevanssi_read_new_files'] ) {
			$value = 'on';
		}
		update_option( 'relevanssi_read_new_files', $value, false );
	}
	if ( isset( $request['relevanssi_send_pdf_files'] ) ) {
		$value = 'off';
		if ( 'off' !== $request['relevanssi_send_pdf_files'] ) {
			$value = 'on';
		}
		update_option( 'relevanssi_send_pdf_files', $value, false );
	}
	if ( isset( $request['relevanssi_index_pdf_parent'] ) ) {
		$value = 'off';
		if ( 'off' !== $request['relevanssi_index_pdf_parent'] ) {
			$value = 'on';
		}
		update_option( 'relevanssi_index_pdf_parent', $value, false );
	}
	if ( isset( $request['relevanssi_link_pdf_files'] ) ) {
		$value = 'off';
		if ( 'off' !== $request['relevanssi_link_pdf_files'] ) {
			$value = 'on';
		}
		update_option( 'relevanssi_link_pdf_files', $value );
	}
	if ( isset( $request['relevanssi_server_location'] ) ) {
		$value = 'us';
		if ( 'eu' === $request['relevanssi_server_location'] ) {
			$value = 'eu';
		}
		update_option( 'relevanssi_server_location', $value, false );
	}

	if ( 'redirects' === $request['tab'] ) {
		$value = relevanssi_process_redirects( $request );
		update_option( 'relevanssi_redirects', $value );
	}
}

/**
 * Adds Relevanssi Premium tabs to Relevanssi settings page.
 *
 * @global array $relevanssi_variables Used for the plugin path.
 *
 * @param array $tabs The array of tab items.
 *
 * @return array The tab array with Premium tabs added.
 */
function relevanssi_premium_add_tabs( $tabs ) {
	global $relevanssi_variables;

	$slugs          = wp_list_pluck( $tabs, 'slug' );
	$redirects_id   = array_search( 'redirects', $slugs, true );
	$attachments_id = array_search( 'attachments', $slugs, true );

	$tabs[ $redirects_id ]   = array(
		'slug'     => 'redirects',
		'name'     => __( 'Redirects', 'relevanssi' ),
		'require'  => dirname( $relevanssi_variables['file'] )
					. '/premium/tabs/redirects-tab.php',
		'callback' => 'relevanssi_redirects_tab',
		'save'     => true,
	);
	$tabs[ $attachments_id ] = array(
		'slug'     => 'attachments',
		'name'     => __( 'Attachments', 'relevanssi' ),
		'require'  => dirname( $relevanssi_variables['file'] )
					. '/premium/tabs/attachments-tab.php',
		'callback' => 'relevanssi_attachments_tab',
		'save'     => true,
	);

	$tabs[] = array(
		'slug'     => 'importexport',
		'name'     => __( 'Import / Export options', 'relevanssi' ),
		'require'  => dirname( $relevanssi_variables['file'] )
					. '/premium/tabs/import-export-tab.php',
		'callback' => 'relevanssi_import_export_tab',
		'save'     => true,
	);
	$tabs[] = array(
		'slug'     => 'related',
		'name'     => __( 'Related', 'relevanssi' ),
		'require'  => dirname( $relevanssi_variables['file'] )
			. '/premium/tabs/related-tab.php',
		'callback' => 'relevanssi_related_tab',
		'save'     => true,
	);

	return $tabs;
}
