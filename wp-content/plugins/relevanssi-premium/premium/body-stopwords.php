<?php
/**
 * /premium/body-stopwords.php
 *
 * @package Relevanssi
 * @author  Mikko Saari
 * @license https://wordpress.org/about/gpl/ GNU General Public License
 * @see     https://www.relevanssi.com/
 */

/**
 * Adds a stopword to the list of stopwords.
 *
 * @param string  $term    The stopword that is added.
 * @param boolean $verbose If true, print out notice. If false, be silent. Default
 * true.
 *
 * @return boolean True, if success; false otherwise.
 */
function relevanssi_add_body_stopword( $term, $verbose = true ) {
	if ( empty( $term ) ) {
		return;
	}

	$n = 0;
	$s = 0;

	$terms = explode( ',', $term );
	if ( count( $terms ) > 1 ) {
		foreach ( $terms as $term ) {
			$n++;
			$term    = trim( $term );
			$success = relevanssi_add_single_body_stopword( $term );
			if ( $success ) {
				$s++;
			}
		}
		if ( $verbose ) {
			// translators: %1$d is the successful entries, %2$d is the total entries.
			printf( "<div id='message' class='updated fade'><p>%s</p></div>", sprintf( esc_html__( 'Successfully added %1$d/%2$d terms to content stopwords!', 'relevanssi' ), intval( $s ), intval( $n ) ) );
		}
	} else {
		// Add to stopwords.
		$success = relevanssi_add_single_body_stopword( $term );

		$term = stripslashes( $term );
		$term = esc_html( $term );
		if ( $verbose ) {
			if ( $success ) {
				// Translators: %s is the stopword.
				printf( "<div id='message' class='updated fade'><p>%s</p></div>", sprintf( esc_html__( "Term '%s' added to content stopwords!", 'relevanssi' ), esc_html( stripslashes( $term ) ) ) );
			} else {
				// Translators: %s is the stopword.
				printf( "<div id='message' class='updated fade'><p>%s</p></div>", sprintf( esc_html__( "Couldn't add term '%s' to content stopwords!", 'relevanssi' ), esc_html( stripslashes( $term ) ) ) );
			}
		}
	}

	return $success;
}

/**
 * Adds a single stopword to the stopword table.
 *
 * @global object $wpdb                 The WP database interface.
 * @global array  $relevanssi_variables The global Relevanssi variables.
 *
 * @param string $term The term to add.
 *
 * @return boolean True if success, false if not.
 */
function relevanssi_add_single_body_stopword( $term ) {
	if ( empty( $term ) ) {
		return false;
	}

	$term = stripslashes( $term );

	$body_stopwords  = get_option( 'relevanssi_body_stopwords', '' );
	$body_stopwords .= ',' . $term;
	$success         = update_option( 'relevanssi_body_stopwords', $body_stopwords );

	if ( $success ) {
		global $wpdb, $relevanssi_variables;

		// Remove from index.
		$wpdb->query(
			$wpdb->prepare(
				'UPDATE ' . $relevanssi_variables['relevanssi_table'] . ' SET content = 0 WHERE term=%s', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$term
			)
		);
		// Remove all lines with all zeros, ie. no matches.
		$wpdb->query( 'DELETE FROM ' . $relevanssi_variables['relevanssi_table'] . ' WHERE content + title + comment + tag + link + author + category + excerpt + taxonomy + customfield + mysqlcolumn = 0' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return true;
	} else {
		return false;
	}
}

/**
 * Removes all content stopwords.
 *
 * Empties the relevanssi_body_stopwords option.
 */
function relevanssi_remove_all_body_stopwords() {
	$success = update_option( 'relevanssi_body_stopwords', '' );

	if ( $success ) {
		printf( "<div id='message' class='updated fade'><p>%s</p></div>", esc_html__( 'All content stopwords removed! Remember to re-index.', 'relevanssi' ) );
	} else {
		printf( "<div id='message' class='updated fade'><p>%s</p></div>", esc_html__( "There was a problem, and content stopwords couldn't be removed.", 'relevanssi' ) );
	}
}

/**
 * Removes a single content stopword.
 *
 * @param string  $term    The stopword to remove.
 * @param boolean $verbose If true, print out a notice. Default true.
 *
 * @return boolean True if success, false if not.
 */
function relevanssi_remove_body_stopword( $term, $verbose = true ) {
	$body_stopwords = get_option( 'relevanssi_body_stopwords', '' );

	$stopwords_array = explode( ',', $body_stopwords );
	$stopwords_array = array_filter(
		$stopwords_array,
		function( $v ) use ( $term ) {
			return $v !== $term;
		}
	);

	$body_stopwords = implode( ',', $stopwords_array );
	$success        = update_option( 'relevanssi_body_stopwords', $body_stopwords );

	if ( $success ) {
		if ( $verbose ) {
			// Translators: %s is the stopword.
			printf( "<div id='message' class='updated fade'><p>%s</p></div>", sprintf( esc_html__( "Term '%s' removed from content stopwords! Re-index to get it back to index.", 'relevanssi' ), esc_html( stripslashes( $term ) ) ) );
		}
		return true;
	} else {
		if ( $verbose ) {
			// Translators: %s is the stopword.
			printf( "<div id='message' class='updated fade'><p>%s</p></div>", sprintf( esc_html__( "Couldn't remove term '%s' from content stopwords!", 'relevanssi' ), esc_html( stripslashes( $term ) ) ) );
		}
		return false;
	}
}

/**
 * Fetches the list of content stopwords.
 *
 * Gets the list of content stopwords from the options.
 *
 * @return array An array of stopwords.
 */
function relevanssi_fetch_body_stopwords() {
	$body_stopwords = get_option( 'relevanssi_body_stopwords', '' );
	$stopword_list  = explode( ',', $body_stopwords );

	return $stopword_list;
}

/**
 * Displays a list of body stopwords.
 *
 * Displays the list of body stopwords and gives the controls for adding new stopwords.
 *
 * @global object $wpdb                 The WP database interface.
 * @global array  $relevanssi_variables The global Relevanssi variables array.
 */
function relevanssi_show_body_stopwords() {
	printf( '<p>%s</p>', esc_html__( 'Post content stopwords are like stopwords, but they are only applied to the post content. These words can be used for searching and will be found in post titles, custom fields and other indexed content – just not in the post body content. Sometimes a word can be very common, but also have a more specific meaning and use on your site, and making it a content stopword will make it easier to find the specific use cases.', 'relevanssi' ) );
	?>
<table class="form-table">
<tr>
	<th scope="row">
		<label for="addbodystopword"><p><?php esc_html_e( 'Content stopword(s) to add', 'relevanssi' ); ?>
	</th>
	<td>
		<textarea name="addbodystopword" id="addbodystopword" rows="2" cols="80"></textarea>
		<p><input type="submit" value="<?php esc_attr_e( 'Add', 'relevanssi' ); ?>" class='button' /></p>
	</td>
</tr>
</table>
<p><?php esc_html_e( "Here's a list of content stopwords in the database. Click a word to remove it from content stopwords. You need to reindex the database to get the words back in to the index.", 'relevanssi' ); ?></p>

<table class="form-table">
<tr>
	<th scope="row">
		<?php esc_html_e( 'Current content stopwords', 'relevanssi' ); ?>
	</th>
	<td>
		<ul>
	<?php
	$results    = get_option( 'relevanssi_body_stopwords', '' );
	$results    = explode( ',', $results );
	$exportlist = array();
	foreach ( $results as $stopword ) {
		if ( empty( $stopword ) ) {
			continue;
		}
		$sw = stripslashes( $stopword );
		printf( '<li style="display: inline;"><input type="submit" name="removebodystopword" value="%s"/></li>', esc_attr( $sw ) );
		array_push( $exportlist, $sw );
	}

	$exportlist = htmlspecialchars( implode( ', ', $exportlist ) );
	?>
	</ul>
	<p><input type="submit" id="removeallbodystopwords" name="removeallbodystopwords" value="<?php esc_attr_e( 'Remove all content stopwords', 'relevanssi' ); ?>" class='button' /></p>
	</td>
</tr>
<tr>
	<th scope="row">
		<?php esc_html_e( 'Exportable list of content stopwords', 'relevanssi' ); ?>
	</th>
	<td>
		<label for="bodystopwords" class="screen-reader-text"><?php esc_html_e( 'Exportable list of content stopwords', 'relevanssi' ); ?></label>
		<textarea name="bodystopwords" id="bodystopwords" rows="2" cols="80"><?php echo esc_textarea( $exportlist ); ?></textarea>
		<p class="description"><?php esc_html_e( 'You can copy the list of content stopwords from here if you want to back up the list, copy it to a different blog or otherwise need the list.', 'relevanssi' ); ?></p>
	</td>
</tr>
</table>

	<?php
}
