<?php
/**
 * /premium/post-metabox.php
 *
 * Relevanssi Premium post metaboxes controls.
 *
 * @package Relevanssi_Premium
 * @author  Mikko Saari
 * @license https://wordpress.org/about/gpl/ GNU General Public License
 * @see     https://www.relevanssi.com/
 */

/**
 * Adds the Relevanssi metaboxes for post edit pages.
 *
 * Adds the Relevanssi Post Controls meta box on the post edit pages. Will skip ACF pages.
 */
function relevanssi_add_metaboxes() {
	global $post;
	if ( null === $post ) {
		return;
	}
	if ( in_array( $post->post_type, array( 'acf', 'acf-field-group' ), true ) ) {
		return;
		// No metaboxes for Advanced Custom Fields pages.
	}
	add_meta_box(
		'relevanssi_hidebox',
		__( 'Relevanssi post controls', 'relevanssi' ),
		'relevanssi_post_metabox',
		array( $post->post_type, 'edit-category' )
	);
}

/**
 * Prints out the Relevanssi Post Controls meta box.
 *
 * Prints out the Relevanssi Post Controls meta box that is displayed on the post edit pages.
 *
 * @global array $relevanssi_variables The Relevanssi global variables array, used to get the file name for nonce.
 */
function relevanssi_post_metabox() {
	global $relevanssi_variables;
	wp_nonce_field( plugin_basename( $relevanssi_variables['file'] ), 'relevanssi_hidepost' );

	global $post;
	$hide_post   = checked( 'on', get_post_meta( $post->ID, '_relevanssi_hide_post', true ), false );
	$pin_for_all = checked( 'on', get_post_meta( $post->ID, '_relevanssi_pin_for_all', true ), false );

	$pins = get_post_meta( $post->ID, '_relevanssi_pin', false );
	$pin  = implode( ', ', $pins );

	$unpins = get_post_meta( $post->ID, '_relevanssi_unpin', false );
	$unpin  = implode( ', ', $unpins );

	// The actual fields for data entry.
?>
	<input type="hidden" id="relevanssi_metabox" name="relevanssi_metabox" value="true" />

	<p><strong><?php esc_html_e( 'Pin this post', 'relevanssi' ); ?></strong></p>
	<p><?php esc_html_e( 'A comma-separated list of single word keywords or multi-word phrases. If any of these keywords are present in the search query, this post will be moved on top of the search results.', 'relevanssi' ); ?></p>
	<textarea type="text" id="relevanssi_pin" name="relevanssi_pin" cols="80" rows="2"><?php echo esc_html( $pin ); ?></textarea/>

	<?php
	if ( 0 === intval( get_option( 'relevanssi_content_boost' ) ) ) {
	?>
		<p><?php esc_html_e( "NOTE: You have set the post content weight to 0. This means that keywords that don't appear elsewhere in the post won't work, because they are indexed as part of the post content. If you set the post content weight to any positive value, the pinned keywords will work again.", 'relevanssi' ); ?></p>
	<?php
	}
	?>

	<p><input type="checkbox" id="relevanssi_pin_for_all" name="relevanssi_pin_for_all" <?php echo esc_attr( $pin_for_all ); ?> />
	<label for="relevanssi_pin_for_all">
		<?php esc_html_e( 'Pin this post for all searches it appears in.', 'relevanssi' ); ?>
	</label></p>

	<p><strong><?php esc_html_e( 'Exclude this post', 'relevanssi' ); ?></strong></p>
	<p><?php esc_html_e( 'A comma-separated list of single word keywords or multi-word phrases. If any of these keywords are present in the search query, this post will be removed from the search results.', 'relevanssi' ); ?></p>
	<textarea type="text" id="relevanssi_unpin" name="relevanssi_unpin" cols="80" rows="2"><?php echo esc_html( $unpin ); ?></textarea/>

	<p><input type="checkbox" id="relevanssi_hide_post" name="relevanssi_hide_post" <?php echo esc_attr( $hide_post ); ?> />
	<label for="relevanssi_hide_post">
		<?php esc_html_e( 'Exclude this post or page from the index.', 'relevanssi' ); ?>
	</label></p>
<?php
}

/**
 * Saves Relevanssi metabox data.
 *
 * When a post is saved, this function saves the Relevanssi Post Controls metabox data.
 *
 * @param int $post_id The post ID that is being saved.
 */
function relevanssi_save_postdata( $post_id ) {
	global $relevanssi_variables;
	// Verify if this is an auto save routine.
	// If it is, our form has not been submitted, so we dont want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Verify the nonce.
	if ( isset( $_POST['relevanssi_hidepost'] ) ) { // WPCS: input var okey.
		if ( ! wp_verify_nonce( sanitize_key( $_POST['relevanssi_hidepost'] ), plugin_basename( $relevanssi_variables['file'] ) ) ) { // WPCS: input var okey.
			return;
		}
	}

	$post = $_POST; // WPCS: input var okey.

	// If relevanssi_metabox is not set, it's a quick edit.
	if ( ! isset( $post['relevanssi_metabox'] ) ) {
		return;
	}

	// Check permissions.
	if ( isset( $post['post_type'] ) ) {
		if ( 'page' === $post['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
		}
	}

	$hide = '';
	if ( isset( $post['relevanssi_hide_post'] ) && 'on' === $post['relevanssi_hide_post'] ) {
		$hide = 'on';
	}

	if ( 'on' === $hide ) {
		// Post is marked hidden, so remove it from the index.
		relevanssi_remove_doc( $post_id );
	}

	if ( 'on' === $hide ) {
		update_post_meta( $post_id, '_relevanssi_hide_post', $hide );
	} else {
		delete_post_meta( $post_id, '_relevanssi_hide_post' );
	}

	$pin_for_all = '';
	if ( isset( $post['relevanssi_pin_for_all'] ) && 'on' === $post['relevanssi_pin_for_all'] ) {
		$pin_for_all = 'on';
	}

	if ( 'on' === $pin_for_all ) {
		update_post_meta( $post_id, '_relevanssi_pin_for_all', $pin_for_all );
	} else {
		delete_post_meta( $post_id, '_relevanssi_pin_for_all' );
	}

	if ( isset( $post['relevanssi_pin'] ) ) {
		delete_post_meta( $post_id, '_relevanssi_pin' );
		$pins = explode( ',', sanitize_text_field( wp_unslash( $post['relevanssi_pin'] ) ) );
		foreach ( $pins as $pin ) {
			$pin = trim( $pin );
			add_post_meta( $post_id, '_relevanssi_pin', $pin );
		}
	} else {
		delete_post_meta( $post_id, '_relevanssi_pin' );
	}

	if ( isset( $post['relevanssi_unpin'] ) ) {
		delete_post_meta( $post_id, '_relevanssi_unpin' );
		$pins = explode( ',', sanitize_text_field( wp_unslash( $post['relevanssi_unpin'] ) ) );
		foreach ( $pins as $pin ) {
			$pin = trim( $pin );
			add_post_meta( $post_id, '_relevanssi_unpin', $pin );
		}
	} else {
		delete_post_meta( $post_id, '_relevanssi_unpin' );
	}
}
