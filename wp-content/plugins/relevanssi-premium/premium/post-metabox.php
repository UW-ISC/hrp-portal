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
	if ( in_array( $post->post_type, array(
		'acf', // Advanced Custom Fields fields.
		'acf-field-group', // Advanced Custom Fields field groups.
		'cpt_staff_lst_item', // Staff List.
		'cpt_staff_lst', // Staff List.
	), true ) ) {
		return;
	}
	add_meta_box(
		'relevanssi_hidebox',
		__( 'Relevanssi', 'relevanssi' ),
		'relevanssi_post_metabox',
		array( $post->post_type, 'edit-category' ),
		'side',
		'default',
		array( '__block_editor_compatible_meta_box' => true )
	);
	add_thickbox(); // Make sure Thickbox is enabled.
}

/**
 * Prints out the Relevanssi Post Controls meta box.
 *
 * Prints out the Relevanssi Post Controls meta box that is displayed on the post edit pages.
 *
 * @global array  $relevanssi_variables The Relevanssi global variables array, used to get the file name for nonce.
 * @global object $wpdb                 The WordPress database interface.
 * @global object $post                 The global post object.
 */
function relevanssi_post_metabox() {
	global $relevanssi_variables, $wpdb, $post;
	wp_nonce_field( plugin_basename( $relevanssi_variables['file'] ), 'relevanssi_hidepost' );

	$hide_post   = checked( 'on', get_post_meta( $post->ID, '_relevanssi_hide_post', true ), false );
	$pin_for_all = checked( 'on', get_post_meta( $post->ID, '_relevanssi_pin_for_all', true ), false );

	$pins = get_post_meta( $post->ID, '_relevanssi_pin', false );
	$pin  = implode( ', ', $pins );

	$unpins = get_post_meta( $post->ID, '_relevanssi_unpin', false );
	$unpin  = implode( ', ', $unpins );

	$terms_list = $wpdb->get_results(
		$wpdb->prepare( 'SELECT * FROM ' . $relevanssi_variables['relevanssi_table'] . ' WHERE doc = %d',
		$post->ID ), OBJECT
	); // WPCS: unprepared SQL ok, Relevanssi database table name.

	$terms['content']     = array();
	$terms['title']       = array();
	$terms['comment']     = array();
	$terms['tag']         = array();
	$terms['link']        = array();
	$terms['author']      = array();
	$terms['category']    = array();
	$terms['excerpt']     = array();
	$terms['taxonomy']    = array();
	$terms['customfield'] = array();
	$terms['mysql']       = array();

	foreach ( $terms_list as $row ) {
		if ( $row->content > 0 ) {
			$terms['content'][] = $row->term;
		}
		if ( $row->title > 0 ) {
			$terms['title'][] = $row->term;
		}
		if ( $row->comment > 0 ) {
			$terms['comment'][] = $row->term;
		}
		if ( $row->tag > 0 ) {
			$terms['tag'][] = $row->term;
		}
		if ( $row->link > 0 ) {
			$terms['link'][] = $row->term;
		}
		if ( $row->author > 0 ) {
			$terms['author'][] = $row->term;
		}
		if ( $row->category > 0 ) {
			$terms['category'][] = $row->term;
		}
		if ( $row->excerpt > 0 ) {
			$terms['excerpt'][] = $row->term;
		}
		if ( $row->taxonomy > 0 ) {
			$terms['taxonomy'][] = $row->term;
		}
		if ( $row->customfield > 0 ) {
			$terms['customfield'][] = $row->term;
		}
		if ( $row->mysqlcolumn > 0 ) {
			$terms['mysql'][] = $row->term;
		}
	}

	$content_terms     = implode( ' ', $terms['content'] );
	$title_terms       = implode( ' ', $terms['title'] );
	$comment_terms     = implode( ' ', $terms['comment'] );
	$tag_terms         = implode( ' ', $terms['tag'] );
	$link_terms        = implode( ' ', $terms['link'] );
	$author_terms      = implode( ' ', $terms['author'] );
	$category_terms    = implode( ' ', $terms['category'] );
	$excerpt_terms     = implode( ' ', $terms['excerpt'] );
	$taxonomy_terms    = implode( ' ', $terms['taxonomy'] );
	$customfield_terms = implode( ' ', $terms['customfield'] );
	$mysql_terms       = implode( ' ', $terms['mysql'] );

	// The actual fields for data entry.
?>
	<input type="hidden" id="relevanssi_metabox" name="relevanssi_metabox" value="true" />

	<p><strong><a name="<?php esc_html_e( 'How Relevanssi sees this post', 'relevanssi' ); ?>" href="#TB_inline?width=800&height=600&inlineId=relevanssi_sees_container" class="thickbox"><?php esc_html_e( 'How Relevanssi sees this post', 'relevanssi' ); ?></a></strong></p>

	<p><strong><?php esc_html_e( 'Pin this post', 'relevanssi' ); ?></strong></p>
	<p><?php esc_html_e( 'A comma-separated list of single word keywords or multi-word phrases. If any of these keywords are present in the search query, this post will be moved on top of the search results.', 'relevanssi' ); ?></p>
	<textarea id="relevanssi_pin" name="relevanssi_pin" cols="30" rows="2"><?php echo esc_html( $pin ); ?></textarea/>

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
	<textarea id="relevanssi_unpin" name="relevanssi_unpin" cols="30" rows="2"><?php echo esc_html( $unpin ); ?></textarea>

	<p><input type="checkbox" id="relevanssi_hide_post" name="relevanssi_hide_post" <?php echo esc_attr( $hide_post ); ?> />
	<label for="relevanssi_hide_post">
		<?php esc_html_e( 'Exclude this post or page from the index.', 'relevanssi' ); ?>
	</label></p>

	<?php
	$related_posts_settings = get_option( 'relevanssi_related_settings', relevanssi_related_default_settings() );
	if ( isset( $related_posts_settings['enabled'] ) && 'on' === $related_posts_settings['enabled'] ) {
		relevanssi_related_posts_metabox( $post->ID );
	}
	?>

	<div id="relevanssi_sees_container" style="display: none">
	<?php
	if ( ! empty( $title_terms ) ) {
	?>
		<h3><?php esc_html_e( 'Post title', 'relevanssi' ); ?>:</h3>
		<p><?php echo esc_html( $title_terms ); ?></p>
	<?php
	}
	if ( ! empty( $content_terms ) ) {
		?>
		<h3><?php esc_html_e( 'Post content', 'relevanssi' ); ?>:</h3>
		<p><?php echo esc_html( $content_terms ); ?></p>
		<?php
	}
	if ( ! empty( $comment_terms ) ) {
		?>
	<h3><?php esc_html_e( 'Comments', 'relevanssi' ); ?>:</h3>
	<p><?php echo esc_html( $comment_terms ); ?></p>
	<?php
	}
	if ( ! empty( $tag_terms ) ) {
		?>
	<h3><?php esc_html_e( 'Tags', 'relevanssi' ); ?>:</h3>
	<p><?php echo esc_html( $tag_terms ); ?></p>
	<?php
	}
	if ( ! empty( $category_terms ) ) {
		?>
	<h3><?php esc_html_e( 'Categories', 'relevanssi' ); ?>:</h3>
	<p><?php echo esc_html( $category_terms ); ?></p>
	<?php
	}
	if ( ! empty( $taxonomy_terms ) ) {
		?>
	<h3><?php esc_html_e( 'Other taxonomies', 'relevanssi' ); ?>:</h3>
	<p><?php echo esc_html( $taxonomy_terms ); ?></p>
	<?php
	}
	if ( ! empty( $link_terms ) ) {
		?>
	<h3><?php esc_html_e( 'Links', 'relevanssi' ); ?>:</h3>
	<p><?php echo esc_html( $link_terms ); ?></p>
	<?php
	}
	if ( ! empty( $author_terms ) ) {
		?>
	<h3><?php esc_html_e( 'Authors', 'relevanssi' ); ?>:</h3>
	<p><?php echo esc_html( $author_terms ); ?></p>
	<?php
	}
	if ( ! empty( $excerpt_terms ) ) {
		?>
	<h3><?php esc_html_e( 'Excerpt', 'relevanssi' ); ?>:</h3>
	<p><?php echo esc_html( $excerpt_terms ); ?></p>
	<?php
	}
	if ( ! empty( $customfield_terms ) ) {
		?>
	<h3><?php esc_html_e( 'Custom fields', 'relevanssi' ); ?>:</h3>
	<p><?php echo esc_html( $customfield_terms ); ?></p>
	<?php
	}
	if ( ! empty( $mysql_terms ) ) {
		?>
	<h3><?php esc_html_e( 'MySQL content', 'relevanssi' ); ?>:</h3>
	<p><?php echo esc_html( $mysql_terms ); ?></p>
	<?php
	}
	?>
	</div>
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

	$no_append = '';
	if ( isset( $post['relevanssi_related_no_append'] ) && 'on' === $post['relevanssi_related_no_append'] ) {
		$no_append = 'on';
	}

	if ( 'on' === $no_append ) {
		update_post_meta( $post_id, '_relevanssi_related_no_append', $no_append );
	} else {
		delete_post_meta( $post_id, '_relevanssi_related_no_append' );
	}

	$not_related = '';
	if ( isset( $post['relevanssi_related_not_related'] ) && 'on' === $post['relevanssi_related_not_related'] ) {
		$not_related = 'on';
	}

	if ( 'on' === $not_related ) {
		update_post_meta( $post_id, '_relevanssi_related_not_related', $not_related );
	} else {
		delete_post_meta( $post_id, '_relevanssi_related_not_related' );
	}

	if ( isset( $post['relevanssi_related_keywords'] ) ) {
		delete_post_meta( $post_id, '_relevanssi_related_keywords' );
		$keywords = sanitize_text_field( $post['relevanssi_related_keywords'] );
		if ( $keywords ) {
			add_post_meta( $post_id, '_relevanssi_related_keywords', $keywords );
		}
	} else {
		delete_post_meta( $post_id, '_relevanssi_related_keywords' );
	}

	if ( isset( $post['relevanssi_related_include_ids'] ) ) {
		delete_post_meta( $post_id, '_relevanssi_related_include_ids' );
		$include_ids_array = explode( ',', $post['relevanssi_related_include_ids'] );
		$valid_ids         = array();
		foreach ( $include_ids_array as $id ) {
			$id = (int) trim( $id );
			if ( is_int( $id ) ) {
				if ( get_post( $id ) ) {
					$valid_ids[] = $id;
				}
			}
		}
		if ( ! empty( $valid_ids ) ) {
			$id_string = implode( ',', $valid_ids );
			add_post_meta( $post_id, '_relevanssi_related_include_ids', $id_string );
		}
	} else {
		delete_post_meta( $post_id, '_relevanssi_related_include_ids' );
	}

	if ( isset( $post['relevanssi_related_exclude_ids'] ) ) {
		delete_post_meta( $post_id, 'relevanssi_related_exclude_ids' );
		$exclude_ids_array = explode( ',', $post['relevanssi_related_exclude_ids'] );
		$valid_ids         = array();
		foreach ( $exclude_ids_array as $id ) {
			$id = (int) trim( $id );
			if ( is_int( $id ) ) {
				$valid_ids[] = $id;
			}
		}
		if ( ! empty( $valid_ids ) ) {
			$id_string = implode( ',', $valid_ids );
			add_post_meta( $post_id, '_relevanssi_related_exclude_ids', $id_string );
		}
	} else {
		delete_post_meta( $post_id, '_relevanssi_related_exclude_ids' );
	}

	// Clear the related posts cache for this post.
	delete_post_meta( $post_id, '_relevanssi_related_posts' );
}

/**
 * Prints out the metabox part for related posts.
 *
 * @param int $post_id The post ID.
 */
function relevanssi_related_posts_metabox( $post_id ) {
	$related     = get_post_meta( $post_id, '_relevanssi_related_keywords', true );
	$include_ids = get_post_meta( $post_id, '_relevanssi_related_include_ids', true );
	$no_append   = checked( 'on', get_post_meta( $post_id, '_relevanssi_related_no_append', true ), false );
	$not_related = checked( 'on', get_post_meta( $post_id, '_relevanssi_related_not_related', true ), false );

	if ( '0' === $include_ids ) {
		$include_ids = '';
	}
?>
	<p><strong><?php esc_html_e( 'Related Posts', 'relevanssi' ); ?></strong></p>

	<p><label><input type="checkbox" name="relevanssi_related_no_append" id="relevanssi_related_no_append" <?php echo esc_attr( $no_append ); ?>/>
	<?php esc_html_e( "Don't append the related posts to this page.", 'relevanssi' ); ?></label></p>

	<p><label><input type="checkbox" name="relevanssi_related_not_related" id="relevanssi_related_not_related" <?php echo esc_attr( $not_related ); ?>/>
	<?php esc_html_e( "Don't show this as a related post for any post.", 'relevanssi' ); ?></label></p>

	<p><strong><?php esc_html_e( 'Related Posts keywords', 'relevanssi' ); ?></strong></p>
	<p><?php esc_html_e( 'A comma-separated list of keywords to use for the Related Posts feature. Anything entered here will used when searching for related posts. Using phrases with quotes is allowed, but will restrict the related posts to posts including that phrase.', 'relevanssi' ); ?></p>
	<p><textarea id="relevanssi_related_keywords" name="relevanssi_related_keywords" cols="30" rows="2"><?php echo esc_html( $related ); ?></textarea></p>

	<p><?php esc_html_e( 'A comma-separated list of post IDs to use as related posts for this post', 'relevanssi' ); ?>:</p>
	<p><input type="text" id="relevanssi_related_include_ids" name="relevanssi_related_include_ids" value="<?php echo esc_html( $include_ids ); ?>"/></p>

	<p><?php esc_html_e( 'These are the related posts Relevanssi currently will show for this post:', 'relevanssi' ); ?></p>

	<input type="hidden" id="this_post_id" value="<?php echo esc_attr( $post_id ); ?>" />
	<ol id='related_posts_list'>
	<?php
	echo relevanssi_generate_related_list( $post_id ); // WPCS: XSS ok.
	?>
	</ol>

	<p><?php esc_html_e( 'These posts are excluded from related posts for this post', 'relevanssi' ); ?>:</p>
	<ul id='excluded_posts_list'>
	<?php
	echo relevanssi_generate_excluded_list( $post_id ); // WPCS: XSS ok.
	?>
	</ul>
	<?php
}

/**
 * Generates a list of related posts for the related posts metabox.
 *
 * @param int $post_id The post ID.
 */
function relevanssi_generate_related_list( $post_id ) {
	$list          = '';
	$related_posts = get_post_meta( $post_id, '_relevanssi_related_posts', true );
	if ( empty( $related_posts ) ) {
		relevanssi_related_posts( $post_id );
		$related_posts = get_post_meta( $post_id, '_relevanssi_related_posts', true );
	}
	$related_array = explode( ',', $related_posts );
	foreach ( $related_array as $related_post_id ) {
		$title = get_the_title( $related_post_id );
		$link  = get_permalink( $related_post_id );
		$list .= '<li><a href="' . esc_attr( $link ) . '">' . esc_html( $title ) . '</a> (<button type="button" class="removepost" data-removepost="' . esc_attr( $related_post_id ) . '">' . esc_html__( 'not this', 'relevanssi' ) . '</button>)</li>';
	}
	return $list;
}

/**
 * Generates a list of excluded posts for the related posts metabox.
 *
 * @param int $post_id The post ID.
 */
function relevanssi_generate_excluded_list( $post_id ) {
	$list           = '';
	$excluded_posts = get_post_meta( $post_id, '_relevanssi_related_exclude_ids', true );
	if ( $excluded_posts ) {
		$excluded_array = explode( ',', $excluded_posts );
		foreach ( $excluded_array as $excluded_post_id ) {
			$title = get_the_title( $excluded_post_id );
			$link  = get_permalink( $excluded_post_id );
			$list .= '<li><a href="' . esc_attr( $link ) . '">' . esc_html( $title ) . '</a> (<button type="button" class="returnpost" data-returnpost="' . esc_attr( $excluded_post_id ) . '">' . esc_html__( 'use this', 'relevanssi' ) . '</button>)</li>';
		}
	} else {
		$list .= '<li>' . esc_html__( 'Nothing excluded.', 'relevanssi' ) . '</li>';
	}
	return $list;
}
