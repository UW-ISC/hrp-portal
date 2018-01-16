<?php
/**
 * Provides custom prefixes that return links to Tag or Category archive pages from terms assigned to an item
 *
 * In this example:
 *     - a "term_links:" prefix converts assigned terms to hyperlinks of the form:
 *       <a href="http://cousin-collector.com/blog/?tag=dahlstrom-surname" rel="tag"> for taxonomy=post_tag
 *       <a title="Photos" class="photos" href="http://cousin-collector.com/blog/?cat=232"> for taxonomy=category
 *
 * Created for support topic "Show Post Kicker in Media Library"
 * opened on 4/8/2017 by "ellsinore":
 * https://wordpress.org/support/topic/show-post-kicker-in-media-library/
 *
 * @package MLA Term Links Example
 * @version 1.00
 */

/*
Plugin Name: MLA Term Links Example
Plugin URI: http://fairtradejudaica.org/media-library-assistant-a-wordpress-plugin/
Description: Adds "tag_links:" and "category_links:" Field-level Substitution Parameters
Author: David Lingren
Version: 1.00
Author URI: http://fairtradejudaica.org/our-story/staff/

Copyright 2017 David Lingren

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You can get a copy of the GNU General Public License by writing to the
	Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110, USA
*/

/**
 * Class MLA Term Links Example hooks four of the filters provided
 * by the "Field-level substitution parameter filters (Hooks)"
 *
 * Call it anything you want, but give it an unlikely and hopefully unique name. Hiding
 * everything else inside a class means this is the only name you have to worry about.
 *
 * @package MLA Term Links Example
 * @since 1.00
 */
class MLATermLinksExample {
	/**
	 * Initialization function, similar to __construct()
	 *
	 * Installs filters and actions that handle the MLA hooks for the
	 * "Field-level substitution parameters"
	 *
	 * @since 1.00
	 *
	 * @return	void
	 */
	public static function initialize() {
		/*
		 * Defined in /media-library-assistant/includes/class-mla-data.php
		 */
		add_filter( 'mla_expand_custom_prefix', 'MLATermLinksExample::mla_expand_custom_prefix', 10, 8 );
	} // initialize

	/**
	 * Evaluate parent_terms: or page_terms: values
	 *
	 * @since 1.00
	 *
	 * @param	mixed	String or array - initial value
	 * @param	integer	The Post ID of the new/updated attachment
	 * @param	string	Taxonomy slug
	 * @param	string	Format/option; text,single,export,unpack,array
	 *
	 * @return	mixed	String or array 
	 */
	private static function _evaluate_terms( $custom_value, $post_id, $taxonomy, $option ) {
		// Only two taxonomies are supported
		if ( ! in_array( $taxonomy, array( 'post_tag', 'category' ) ) ) {
			return $custom_value;
		}
	
		$terms = get_object_term_cache( $post_id, $taxonomy );
		if ( false === $terms ) {
			$terms = wp_get_object_terms( $post_id, $taxonomy );
			wp_cache_add( $post_id, $terms, $taxonomy . '_relationships' );
		}

		if ( 'array' == $option ) {
			$custom_value = array();
		} else {
			$custom_value = '';
		}

		if ( is_wp_error( $terms ) ) {
			$custom_value = implode( ',', $terms->get_error_messages() );
		} elseif ( ! empty( $terms ) ) {
			if ( 'single' == $option || 1 == count( $terms ) ) {
				reset( $terms );
				$term = current( $terms );
				$term_name = sanitize_term_field( 'name', $term->name, $term->term_id, $taxonomy, 'display' );
				
				if ( 'post_tag' === $taxonomy ) {
					$custom_value = sprintf( '<a href="http://cousin-collector.com/blog/?tag=%1$s" rel="tag">%2$s</a>', $term->slug, $term_name );
				} else {
					$custom_value = sprintf( '<a title="%1$s" class="%2$s" href="http://cousin-collector.com/blog/?cat=%3$d">%1$s</a>', $term_name, $term->slug, $term->term_id );
				}
			} else {
				foreach ( $terms as $term ) {
					$term_name = sanitize_term_field( 'name', $term->name, $term->term_id, $taxonomy, 'display' );

					if ( 'post_tag' === $taxonomy ) {
						$field_value = sprintf( '<a href="http://cousin-collector.com/blog/?tag=%1$s" rel="tag">%2$s</a>', $term->slug, $term_name );
					} else {
						$field_value = sprintf( '<a title="%1$s" class="%2$s" href="http://cousin-collector.com/blog/?cat=%3$d">%1$s</a>', $term_name, $term->slug, $term->term_id );
					}
					
					if ( 'array' == $option ) {
						$custom_value[] = $field_value;
					} else {
						$custom_value .= strlen( $custom_value ) ? ', ' . $field_value : $field_value;
					}
				}
			}
		}
		
		//error_log( __LINE__ . " MLATermLinksExample::_evaluate_terms( {$post_id}, {$taxonomy}, {$option} ) custom_value = " . var_export( $custom_value, true ), 0 );
		return $custom_value;
	} // _evaluate_terms

	/**
	 * MLA Expand Custom Prefix Filter
	 *
	 * Gives you an opportunity to generate your custom data value when a parameter's prefix value is not recognized.
	 *
	 * @since 1.00
	 *
	 * @param	string	NULL, indicating that by default, no custom value is available
	 * @param	string	the data-source name 
	 * @param	array	data-source components; prefix (empty), value, option, format and args (if present)
	 * @param	array	values from the query, if any, e.g. shortcode parameters
	 * @param	array	item-level markup template values, if any
	 * @param	integer	attachment ID for attachment-specific values
	 * @param	boolean	for option 'multi', retain existing values
	 * @param	string	default option value
	 */
	public static function mla_expand_custom_prefix( $custom_value, $key, $value, $query, $markup_values, $post_id, $keep_existing, $default_option ) {
		//error_log( __LINE__ . " MLATermLinksExample::mla_expand_custom_prefix( {$key}, {$post_id}, {$keep_existing}, {$default_option} ) value = " . var_export( $value, true ), 0 );
		//error_log( __LINE__ . " MLATermLinksExample::mla_expand_custom_prefix( {$key}, {$post_id} ) query = " . var_export( $query, true ), 0 );
		//error_log( __LINE__ . " MLATermLinksExample::mla_expand_custom_prefix( {$key}, {$post_id} ) markup_values = " . var_export( $markup_values, true ), 0 );

		// $post_id is required
		if ( 0 == absint( $post_id ) ) {
			return $custom_value;
		}

		if ( 'term_links' == $value['prefix'] ) {
			$custom_value = self::_evaluate_terms( $custom_value, $post_id, $value['value'], $value['option'] );
		}

		return $custom_value;
	} // mla_expand_custom_prefix
} //MLATermLinksExample

/*
 * Install the filters at an early opportunity
 */
add_action('init', 'MLATermLinksExample::initialize');
?>