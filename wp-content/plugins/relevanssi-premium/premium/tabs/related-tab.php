<?php
/**
 * /premium/tabs/related-tab.php
 *
 * Prints out the Premium Related tab in Relevanssi settings.
 *
 * @package Relevanssi_Premium
 * @author  Mikko Saari
 * @license https://wordpress.org/about/gpl/ GNU General Public License
 * @see     https://www.relevanssi.com/
 */

/**
 * Prints out the Premium Related posts tab in Relevanssi settings.
 */
function relevanssi_related_tab() {
	global $relevanssi_variables;

	wp_enqueue_media();
	add_action( 'admin_footer', 'relevanssi_media_selector_print_scripts' );

	$settings = get_option( 'relevanssi_related_settings', array() );
	if ( empty( $settings ) ) {
		$settings = relevanssi_related_default_settings();
		update_option( 'relevanssi_related_settings', $settings );
	}

	$enabled          = $settings['enabled'];
	$append           = $settings['append'];
	$number           = $settings['number'];
	$keyword          = $settings['keyword'];
	$nothing          = $settings['nothing'];
	$notenough        = $settings['notenough'];
	$post_types       = $settings['post_types'];
	$cache_for_admins = $settings['cache_for_admins'];

	$keyword_sources = explode( ',', $keyword );

	$nothing_selected = '';
	$random_selected  = '';

	if ( 'nothing' === $nothing ) {
		$nothing_selected = 'selected="selected"';
	}
	if ( 'random' === $nothing ) {
		$random_selected = 'selected="selected"';
	}

	$dontfillup_selected   = '';
	$randomfillup_selected = '';

	if ( 'nothing' === $notenough ) {
		$dontfillup_selected = 'selected="selected"';
	}
	if ( 'random' === $notenough ) {
		$randomfillup_selected = 'selected="selected"';
	}

	$append_array    = explode( ',', $append );
	$post_type_array = explode( ',', $post_types );

	$matching_checked = '';
	if ( 'matching_post_type' === $post_types ) {
		$matching_checked = 'checked="checked"';
	}

	$enabled          = relevanssi_check( $settings['enabled'] );
	$cache_for_admins = relevanssi_check( $settings['cache_for_admins'] );

	$disabled = '';
	if ( empty( $enabled ) ) {
		$disabled = 'disabled="disabled"';
	}

	$style = get_option( 'relevanssi_related_style', relevanssi_related_default_styles() );

	$width        = $style['width'];
	$titles       = relevanssi_check( $style['titles'] );
	$excerpts     = relevanssi_check( $style['excerpts'] );
	$thumbnails   = relevanssi_check( $style['thumbnails'] );
	$thumbnail_id = $style['default_thumbnail'];

	$display_default_thumbnail        = '';
	$display_remove_default_thumbnail = '';
	if ( empty( $thumbnails ) ) {
		$display_default_thumbnail        = 'class="screen-reader-text"';
		$display_remove_default_thumbnail = 'class="screen-reader-text"';
	}

	$display_thumbnail_preview = '';
	if ( ! $thumbnail_id ) {
		$display_thumbnail_preview = 'style="display: none"';
	}
?>
<h2 id="options"><?php esc_html_e( 'Related Posts', 'relevanssi' ); ?></h2>

<p><?php esc_html_e( "Relevanssi Related Posts feature shows related posts on posts pages, based on keywords like post title, tags and categories. This feature uses the Relevanssi index to find the best-matching related posts. All results are cached, so your site performance won't suffer.", 'relevanssi' ); ?></p>

<?php // Translators: %s is the WP CLI command. ?>
<p><?php printf( esc_html__( 'A pro tip: you can regenerate related posts for all posts with the WP CLI command %s.', 'relevanssi' ), '<code>wp relevanssi regenerate_related</code>' ); ?></p>

<h3><?php esc_html_e( 'Displaying the related posts', 'relevanssi' ); ?></h3>

<table class="form-table">
	<tr>
		<th scope="row">
			<label for='relevanssi_related_enabled'><?php esc_html_e( 'Enable related posts', 'relevanssi' ); ?></label>
		</th>
		<td>
		<fieldset>
			<legend class="screen-reader-text"><?php esc_html_e( 'Enable related posts', 'relevanssi' ); ?></legend>
			<label >
				<input type='checkbox' name='relevanssi_related_enabled' id='relevanssi_related_enabled' <?php echo esc_html( $enabled ); ?> />
				<?php esc_html_e( 'If this is unchecked, related posts will be completely disabled.', 'relevanssi' ); ?>
			</label>
		</fieldset>
		</td>
	</tr>
	<tr id="tr_relevanssi_related_append">
		<th scope="row"><label for="relevanssi_related_append"><?php esc_html_e( 'Automatically add to these post types', 'relevanssi' ); ?></label></th>
		<td>
			<?php
			$args       = array(
				'public' => true,
			);
			$post_types = get_post_types( $args, 'objects' );
			foreach ( $post_types as $post_type ) {
				$checked = '';
				if ( in_array( $post_type->name, $append_array, true ) ) {
					$checked = 'checked="checked"';
				}
				if ( in_array( $post_type->name, relevanssi_get_forbidden_post_types(), true ) ) {
					continue;
				}
				printf( '<p><input type="checkbox" name="relevanssi_related_append[]" value="%1$s" %2$s %4$s>%3$s</p>', esc_attr( $post_type->name ), $checked, esc_html( $post_type->labels->singular_name ), $disabled ); // WPCS: XSS ok. Known values.
			}
			?>
			<?php // Translators: %1$s is the_content, %2$s is relevanssi_related_priority. ?>
			<p class="description"><?php printf( esc_html__( 'The related posts will be automatically displayed for these post types. The element is added using %1$s filter hook with priority 99 (you can adjust that with the %2$s filter hook).', 'relevanssi' ), '<code>the_content</code>', '<code>relevanssi_related_priority</code>' ); ?></p>
			<p class="description"><?php printf( esc_html__( "If you don't choose to display the related posts automatically, you need to add them manually to your template. You can use the template function %1\$s or the shortcode %2\$s to display the related posts.", 'relevanssi' ), '<code>relevanssi_related_posts( $post_id )</code>', '<code>[relevanssi_related_posts]</code>' ); ?></p>
		</td>
	</tr>
</table>

<h3><?php esc_html_e( 'Choosing the related posts', 'relevanssi' ); ?></h3>

<table class="form-table">
	<tr id="tr_relevanssi_related_keyword">
		<th scope="row"><label for="relevanssi_related_keyword"><?php esc_html_e( 'Keyword sources', 'relevanssi' ); ?></label></th>
		<td>
<?php
$title_object               = new stdClass();
$title_object->name         = 'title';
$title_object->labels       = new stdClass();
$title_object->labels->name = __( 'Title', 'relevanssi' );

$taxos = get_taxonomies( '', 'objects' );
array_unshift( $taxos, $title_object );

$taxonomies_list          = array_flip( get_option( 'relevanssi_index_taxonomies_list' ) );
$taxonomies_list['title'] = true;

$not_indexed = array();

foreach ( $taxos as $taxonomy ) {
	if ( in_array( $taxonomy->name, relevanssi_get_forbidden_taxonomies(), true ) ) {
		continue;
	}
	if ( ! isset( $taxonomies_list[ $taxonomy->name ] ) ) {
		$not_indexed[] = $taxonomy->labels->name;
		continue;
	}
	$checked = '';
	if ( in_array( $taxonomy->name, $keyword_sources, true ) ) {
		$checked = 'checked="checked"';
	}
	printf( '<p><input type="checkbox" name="relevanssi_related_keyword[]" %1$s value="%2$s" %4$s/> %3$s</p>', $checked, esc_attr( $taxonomy->name ), esc_html( $taxonomy->labels->name ), $disabled ); // WPCS: XSS ok. Known values.
}

$not_indexed = implode( ', ', $not_indexed );
?>
	<p class="description"><?php esc_html_e( "The sources Relevanssi uses for related post keywords. Keywords from these sources are then used to search the Relevanssi index to find related posts. Make sure you choose something, otherwise you won't see results or will see random results. In addition of these sources, you can also define your own keywords for each post from the post edit screen.", 'relevanssi' ); ?></p>
<?php
if ( ! empty( $not_indexed ) ) {
	?>
	<p class="description">
		<?php
			esc_html_e( "These taxonomies are missing here, because Relevanssi isn't set to index them:", 'relevanssi' );
			echo ' ' . esc_html( $not_indexed ) . '.';
		?>
		</p>
	<?php
}
?>
	</td>
	</tr>
	<tr>
		<th scope="row"><label for="relevanssi_related_number"><?php esc_html_e( 'Number of posts', 'relevanssi' ); ?></label></th>
		<td>
			<input type='number' name='relevanssi_related_number' id='relevanssi_related_number' size='4' placeholder='6' value='<?php echo esc_attr( $number ); ?>' <?php echo $disabled; // WPCS: XSS ok. Known value. ?>/>
			<p class="description"><?php esc_html_e( 'The number of related posts to show.', 'relevanssi' ); ?></p>
		</td>
	</tr>
	<tr id="tr_relevanssi_related_post_types">
		<th scope="row"><label for="relevanssi_related_post_types"><?php esc_html_e( 'Post types to use', 'relevanssi' ); ?></label></th>
		<td>
			<p><input type="checkbox" class="matching" name="relevanssi_related_post_types[]" value="matching_post_type" <?php echo $matching_checked; // WPCS: XSS ok. ?>  <?php echo $disabled; // WPCS: XSS ok. Known value. ?>><?php esc_html_e( 'Matching post type', 'relevanssi' ); ?></p>
			<?php
			$post_types = get_post_types();
			foreach ( $post_types as $type ) {
				$post_type = get_post_type_object( $type );

				$checked = '';
				if ( in_array( $type, $post_type_array, true ) ) {
					$checked = 'checked="checked"';
				}
				$row_disabled = '';
				if ( $matching_checked ) {
					$row_disabled = 'disabled="disabled"';
				}
				if ( $disabled ) {
					$row_disabled = $disabled;
				}
				if ( in_array( $type, relevanssi_get_forbidden_post_types(), true ) ) {
					continue;
				}
				printf( '<p><input type="checkbox" class="nonmatching" name="relevanssi_related_post_types[]" value="%1$s" %2$s %3$s>%4$s</p>', esc_attr( $type ), $checked, $row_disabled, esc_html( $post_type->labels->singular_name ) ); // WPCS: XSS ok. Known values.
			}
			?>
			<p class="description"><?php esc_html_e( 'The post types to use for related posts. Matching post type means that for each post type, only posts from the same post type are used for related posts.', 'relevanssi' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="relevanssi_related_nothing"><?php esc_html_e( 'No related posts found', 'relevanssi' ); ?></label></th>
		<td>
			<select name="relevanssi_related_nothing" id="relevanssi_related_nothing" <?php echo $disabled; // WPCS: XSS ok. Known value. ?>>
				<option value="nothing" <?php echo esc_html( $nothing_selected ); ?>><?php esc_html_e( 'Show nothing', 'relevanssi' ); ?></option>
				<option value="random" <?php echo esc_html( $random_selected ); ?>><?php esc_html_e( 'Random posts', 'relevanssi' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'What to do when no related posts are found? The options are to show nothing, just disable the whole element, or to show random posts. Do note that the related posts are cached, so the random posts do not change on every page load.', 'relevanssi' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="relevanssi_related_notenough"><?php esc_html_e( 'Not enough related posts found', 'relevanssi' ); ?></label></th>
		<td>
			<select name="relevanssi_related_notenough" id="relevanssi_related_notenough" <?php echo $disabled; // WPCS: XSS ok. Known value. ?>>
				<option value="nothing" <?php echo esc_html( $dontfillup_selected ); ?>><?php esc_html_e( 'Show the found posts', 'relevanssi' ); ?></option>
				<option value="random" <?php echo esc_html( $randomfillup_selected ); ?>><?php esc_html_e( 'Fill with random posts', 'relevanssi' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'What to do when not enough related posts are found? The options are to show what was found, or to fill up the display with random posts. Do note that the related posts are cached, so the random posts do not change on every page load.', 'relevanssi' ); ?></p>
		</td>
	</tr>
</table>

<h3><?php esc_html_e( 'Style options', 'relevanssi' ); ?></h3>

<p><?php esc_html_e( 'When you add the related posts to your site, Relevanssi will use a template to print out the results. These settings control how that template displays the posts. If you need to modify the related posts in a way these settings do not allow, you can always create your own template.', 'relevanssi' ); ?></p>

<p><?php printf( esc_html__( "To create your own template, it's best if you begin with the default Relevanssi template, which can be found in the file %1\$s. Copy the template in the %2\$s folder in your theme and make the necessary changes. Relevanssi will then use your template file to display the related posts.", 'relevanssi' ), '<code>' . esc_html( $relevanssi_variables['plugin_dir'] ) . 'premium/templates/relevanssi-related.php</code>', '<code>' . esc_html( get_stylesheet_directory() ) . '/templates/</code>' ); ?></p>

<table class="form-table">
	<tr>
		<th scope="row">
			<label for='relevanssi_related_titles'><?php esc_html_e( 'Display titles', 'relevanssi' ); ?></label>
		</th>
		<td>
		<fieldset>
			<legend class="screen-reader-text"><?php esc_html_e( 'Display titles for related posts', 'relevanssi' ); ?></legend>
			<label>
				<input type='checkbox' name='relevanssi_related_titles' id='relevanssi_related_titles' <?php echo esc_html( $titles ); ?> <?php echo $disabled; // WPCS: XSS ok. Known value. ?>/>
				<?php esc_html_e( 'Display titles for related posts.', 'relevanssi' ); ?>
			</label>
		</fieldset>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for='relevanssi_related_thumbnails'><?php esc_html_e( 'Display thumbnails', 'relevanssi' ); ?></label>
		</th>
		<td>
		<fieldset>
			<legend class="screen-reader-text"><?php esc_html_e( 'Display thumbnails for related posts', 'relevanssi' ); ?></legend>
			<label>
				<input type='checkbox' name='relevanssi_related_thumbnails' id='relevanssi_related_thumbnails' <?php echo esc_html( $thumbnails ); ?> <?php echo $disabled; // WPCS: XSS ok. Known value. ?>/>
				<?php esc_html_e( 'Display thumbnails for related posts.', 'relevanssi' ); ?>
			</label>
		</fieldset>
		<p class="description"><?php esc_html_e( 'If enabled, this will show the featured image for the post if the post has one.', 'relevanssi' ); ?></p>
		</td>
	</tr>

	<tr id="defaultthumbnail" <?php echo $display_default_thumbnail; // WPCS: XSS ok. ?>>
		<th scope="row">
			<label><?php esc_html_e( 'Default thumbnail', 'relevanssi' ); ?></label>
		</th>
		<td>
			<div class='image-preview-wrapper' <?php echo $display_thumbnail_preview; // WPCS: XSS ok. ?>>
				<img id='image-preview' src='<?php echo esc_attr( wp_get_attachment_url( $thumbnail_id ) ); ?>' width='100' height='100' style='max-height: 100px; width: 100px;'>
			</div>
			<input id="upload_image_button" type="button" class="button" value="<?php echo esc_attr( __( 'Select image', 'relevanssi' ) ); ?>" <?php echo $disabled; // WPCS: XSS ok. Known value. ?>/>
			<input type='hidden' name='relevanssi_default_thumbnail' id='relevanssi_default_thumbnail' value='<?php echo esc_attr( $thumbnail_id ); ?>'>
			<p class="description"><?php esc_html_e( "If a post doesn't have a featured image, this image will be used instead.", 'relevanssi' ); ?></p>

			<label>
				<input type='checkbox' name='relevanssi_remove_default_thumbnail' <?php echo $disabled; // WPCS: XSS ok. ?>/>
				<?php esc_html_e( 'Check this post to remove the default thumbnail.', 'relevanssi' ); ?>
			</label>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for='relevanssi_related_excerpts'><?php esc_html_e( 'Display excerpts', 'relevanssi' ); ?></label>
		</th>
		<td>
		<fieldset>
			<legend class="screen-reader-text"><?php esc_html_e( 'Display excerpts for related posts', 'relevanssi' ); ?></legend>
			<label >
				<input type='checkbox' name='relevanssi_related_excerpts' id='relevanssi_related_excerpts' <?php echo esc_html( $excerpts ); ?> <?php echo $disabled; // WPCS: XSS ok. Known value. ?>/>
				<?php esc_html_e( 'Display excerpts for related posts.', 'relevanssi' ); ?>
			</label>
		</fieldset>
		<?php // Translators: name of the filter hook. ?>
		<p class="description"><?php printf( esc_html__( 'This uses the manually created post excerpt if one exists, otherwise the beginning of the post is used. Default length is 50 characters, use the %s filter hook to adjust that.', 'relevanssi' ), '<code>relevanssi_related_excerpt_length</code>' ); ?></p>
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="relevanssi_related_width"><?php esc_html_e( 'Minimum width', 'relevanssi' ); ?></label></th>
		<td>
			<input type='text' name='relevanssi_related_width' id='relevanssi_related_width' size='4' value='<?php echo esc_attr( $width ); ?>' <?php echo $disabled; // WPCS: XSS ok. Known value. ?>/> px
			<p class="description"><?php esc_html_e( 'The minimum width of the related post element.', 'relevanssi' ); ?></p>
		</td>
	</tr>
</table>

<h3><?php esc_html_e( 'Caching', 'relevanssi' ); ?></h3>

<p><?php esc_html_e( 'The related posts are cached using WordPress transients. The related posts for each post are stored in a transient that is stored for two weeks. The cache for each post is flushed whenever the post is saved. When a post is made non-public (returned to draft, trashed), Relevanssi automatically flushes all related post caches where that post appears.', 'relevanssi' ); ?></p>

<table class="form-table">
	<tr>
		<th scope="row">
			<label for='relevanssi_related_cache_for_admins'><?php esc_html_e( 'Use cache for admins', 'relevanssi' ); ?></label>
		</th>
		<td>
		<fieldset>
			<legend class="screen-reader-text"><?php esc_html_e( 'Use cache for admins', 'relevanssi' ); ?></legend>
			<label >
				<input type='checkbox' name='relevanssi_related_cache_for_admins' id='relevanssi_related_cache_for_admins' <?php echo esc_html( $cache_for_admins ); ?> <?php echo $disabled; // WPCS: XSS ok. Known value. ?>/>
				<?php esc_html_e( 'Use the cache for admin users.', 'relevanssi' ); ?>
			</label>
		</fieldset>
		<p class="description"><?php esc_html_e( 'Disable this option when adjusting the settings to see changes on the site.', 'relevanssi' ); ?></p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for='relevanssi_flush_related_cache'><?php esc_html_e( 'Flush cache', 'relevanssi' ); ?></label>
		</th>
		<td>
		<fieldset>
			<legend class="screen-reader-text"><?php esc_html_e( 'Flush cache', 'relevanssi' ); ?></legend>
			<label >
				<input type='checkbox' name='relevanssi_flush_related_cache' id='relevanssi_flush_related_cache' <?php echo $disabled; // WPCS: XSS ok. Known value. ?>/>
				<?php esc_html_e( 'Flush the caches.', 'relevanssi' ); ?>
			</label>
		</fieldset>
		<p class="description"><?php esc_html_e( 'Check this box to flush all related posts caches.', 'relevanssi' ); ?></p>
		</td>
	</tr>

</table>
<?php
}

/**
 * Prints the media selector scripts for the related posts tab.
 *
 * From: https://jeroensormani.com/how-to-include-the-wordpress-media-selector-in-your-plugin/
 * and https://mikejolley.com/2012/12/21/using-the-new-wordpress-3-5-media-uploader-in-plugins/
 *
 * @author Jeroen Sormani
 * @author Mike Jolly
 */
function relevanssi_media_selector_print_scripts() {
	$style = get_option( 'relevanssi_related_style',
		array(
			'width'             => 250,
			'excerpts'          => 'off',
			'thumbnails'        => 'on',
			'default_thumbnail' => '',
		)
	);

	$thumbnail_id = $style['default_thumbnail'];
	if ( empty( $thumbnail_id ) ) {
		$thumbnail_id = 0;
	}

	?>
<script type='text/javascript'>
		jQuery( document ).ready( function( $ ) {
			// Uploading files
			var file_frame;
			var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
			var set_to_post_id = <?php echo $thumbnail_id; // WPCS: XSS ok. ?>;
			console.log(set_to_post_id);
			jQuery('#upload_image_button').on('click', function( event ){
				event.preventDefault();
				// If the media frame already exists, reopen it.
				if ( file_frame ) {
					// Set the post ID to what we want
					file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
					// Open frame
					file_frame.open();
					return;
				} else {
					// Set the wp.media post id so the uploader grabs the ID we want when initialised
					wp.media.model.settings.post.id = set_to_post_id;
				}
				// Create the media frame.
				file_frame = wp.media.frames.file_frame = wp.media({
					title: 'Select a image to upload',
					button: {
						text: 'Use this image',
					},
					multiple: false	// Set to true to allow multiple files to be selected
				});
				// When an image is selected, run a callback.
				file_frame.on( 'select', function() {
					// We set multiple to false so only get one image from the uploader
					attachment = file_frame.state().get('selection').first().toJSON();
					// Do something with attachment.id and/or attachment.url here
					$( '#image-preview' ).attr( 'src', attachment.url ).css( 'width', 'auto' );
					$( '#relevanssi_default_thumbnail' ).val( attachment.id );
					// Restore the main post ID
					wp.media.model.settings.post.id = wp_media_post_id;
					$( '.image-preview-wrapper' ).show();
				});
					// Finally, open the modal
					file_frame.open();
			});
			// Restore the main ID when the add media button is pressed
			jQuery( 'a.add_media' ).on( 'click', function() {
				wp.media.model.settings.post.id = wp_media_post_id;
				$(".image-preview-wrapper").show();
			});
		});
	</script>
<?php
}
