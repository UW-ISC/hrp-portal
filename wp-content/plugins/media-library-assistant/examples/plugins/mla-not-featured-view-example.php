<?php
/**
 * Provides an example of hooking the filters provided by the MLA_List_Table class
 *
 * This example adds a Media/Assistant submenu table view for items NOT 
 * featured in any posts/pages.
 *
 * Created for support topic "Filter by post status"
 * opened on 2/24/2015 by "milkchic".
 * https://wordpress.org/support/topic/filter-by-post-status/
 *
 * Enhanced (MMMW support) for support topic "See only “unfeatured” images in Add Media modal?"
 * opened on 3/7/2023 by "zkarj".
 * https://wordpress.org/support/topic/see-only-unfeatured-images-in-add-media-modal/
 *
 * @package MLA Not Featured View Example
 * @version 1.02
 */

/*
Plugin Name:MLA Not Featured View Example
Plugin URI: http://davidlingren.com/
Description: Adds a Media/Assistant submenu table view for items NOT featured in any posts/pages.
Author: David Lingren
Version: 1.02
Author URI: http://davidlingren.com/

Copyright 2014 - 2023 David Lingren

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
 * Class MLA Not Featured View Example hooks some of the filters provided by the MLA_List_Table class
 *
 * Call it anything you want, but give it an unlikely and hopefully unique name. Hiding everything
 * else inside a class means this is the only name you have to worry about.
 *
 * @packageMLA CustomView Example
 * @since 1.00
 */
class MLANotFeaturedViewExample {
	/**
	 * Initialization function, similar to __construct()
	 *
	 * @since 1.00
	 */
	public static function initialize() {
		// This plugin requires MLA
		if ( ! class_exists( 'MLACore', false ) ) {
			return;
		}

		// The remaining filters are only useful for the admin section; exit in the front-end posts/pages
		if ( ! is_admin() ) {
			return;
		}

		/*
		 * add_action and add_filter parameters:
		 * $tag - name of the hook you're filtering; defined by [mla_gallery]
		 * $function_to_add - function to be called when [mla_gallery] applies the filter
		 * $priority - default 10; lower runs earlier, higher runs later
		 * $accepted_args - number of arguments your function accepts
		 */

		 // Defined in /wp-admin/includes/class-wp-list-table.php
		add_filter( 'views_media_page_mla-menu', 'MLANotFeaturedViewExample::views_media_page_mla_menu', 10, 1 );

		// Defined in /media-library-assistant/includes/class-mla-list-table.php
		add_filter( 'mla_list_table_submenu_arguments', 'MLANotFeaturedViewExample::mla_list_table_submenu_arguments', 10, 2 );

		add_filter( 'mla_list_table_prepare_items_pagination', 'MLANotFeaturedViewExample::mla_list_table_prepare_items_pagination', 10, 2 );
		add_filter( 'mla_list_table_prepare_items_total_items', 'MLANotFeaturedViewExample::mla_list_table_prepare_items_total_items', 10, 2 );
		add_filter( 'mla_list_table_prepare_items_the_items', 'MLANotFeaturedViewExample::mla_list_table_prepare_items_the_items', 10, 2 );

		// Defined in /media-library-assistant/includes/class-mla-media-modal.php
		add_filter( 'mla_media_modal_settings', 'MLANotFeaturedViewExample::mla_media_modal_settings', 10, 2 );

		// Defined in /media-library-assistant/includes/class-mla-media-modal-ajax.php
		add_filter( 'mla_media_modal_query_initial_terms', 'MLANotFeaturedViewExample::mla_media_modal_query_initial_terms', 10, 2 );
		//add_filter( 'mla_media_modal_query_filtered_terms', 'MLANotFeaturedViewExample::mla_media_modal_query_filtered_terms', 10, 2 );
		add_action( 'mla_media_modal_query_items', 'MLANotFeaturedViewExample::mla_media_modal_query_items', 10, 5 );

		// Defined in /media-library-assistant/includes/class-mla-data-query.php
		//add_filter( 'mla_media_modal_query_final_terms', 'MLANotFeaturedViewExample::mla_media_modal_query_final_terms', 10, 1 );
		//add_filter( 'mla_media_modal_query_custom_items', 'MLANotFeaturedViewExample::mla_media_modal_query_custom_items', 10, 2 );
	}

	/**
	 * Add custom views for the Media/Assistant submenu
	 *
	 * @since 1.00
	 *
	 * @param	string	The slug for the custom view to evaluate
	 * @param	string	The slug for the current custom view, or ''
	 *
	 * @return	mixed	HTML for link to display the view, false if count = zero
	 */
	private static function _get_view( $view_slug, $current_view ) {
		global $wpdb;
		static $posts_per_view = NULL,
			$view_singular = array (),
			$view_plural = array ();

		// Calculate the common values once per page load
		if ( is_null( $posts_per_view ) ) {
			$items = (integer) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} AS item LEFT JOIN ( SELECT DISTINCT sub.meta_value FROM {$wpdb->postmeta} AS sub WHERE ( sub.meta_key = '_thumbnail_id' ) ) AS meta ON item.ID = meta.meta_value WHERE meta.meta_value IS NULL AND item.post_type = 'attachment' AND item.post_status = 'inherit'" );
			$posts_per_view = array( 'notfeatured' => $items );

			$view_singular = array (
				'notfeatured' => __( 'Not Featured', 'mla-not-featured-view-example' ),
			);
			$view_plural = array (
				'notfeatured' => __( 'Not Featured', 'mla-not-featured-view-example' ),
			);
		}

		// Make sure the slug is in our list and has posts
		if ( array_key_exists( $view_slug, $posts_per_view ) ) {
			$post_count = $posts_per_view[ $view_slug ];
			$singular = sprintf('%s <span class="count">(%%s)</span>', $view_singular[ $view_slug ] );
			$plural = sprintf('%s <span class="count">(%%s)</span>', $view_plural[ $view_slug ] );
			$nooped_plural = _n_noop( $singular, $plural, 'mla-not-featured-view-example' );
		} else {
			return false;
		}

		if ( $post_count ) {
			$query = array( 'nfve_view' => $view_slug );
			$base_url = 'upload.php?page=mla-menu';
			$class = ( $view_slug == $current_view ) ? ' class="current"' : '';

			return "<a href='" . add_query_arg( $query, $base_url ) . "'$class>" . sprintf( translate_nooped_plural( $nooped_plural, $post_count, 'mla-not-featured-view-example' ), number_format_i18n( $post_count ) ) . '</a>';
		}

		return false;
	}

	/**
	 * Views for media page MLA Menu
	 *
	 * This filter gives you an opportunity to filter the list of available list table views.
	 *
	 * @since 1.00
	 *
	 * @param	array	$views An array of available list table views.
	 *					format: view_slug => link to the view, with count
	 *
	 * @return	array	updated list table views.
	 */
	public static function views_media_page_mla_menu( $views ) {
		// See if the current view is a custom view
		if ( isset( $_REQUEST['nfve_view'] ) ) {
			switch( $_REQUEST['nfve_view'] ) {
				case 'notfeatured':
					$current_view = 'notfeatured';
					break;
				default:
					$current_view = '';
			} // nfve_view
		} else {
			$current_view = '';
		}

		foreach ( $views as $slug => $view ) {
			// Find/update the current view
			if ( strpos( $view, ' class="current"' ) ) {
				if ( ! empty( $current_view ) ) {
					$views[ $slug ] = str_replace( ' class="current"', '', $view );
				} else {
					$current_view = $slug;
				}
			}
		} // each view

		$value = self::_get_view( 'notfeatured', $current_view );
		if ( $value ) {
			$views['notfeatured'] = $value;
		}

		return $views;
	} // views_media_page_mla_menu

	/**
	 * Filter the "sticky" submenu URL parameters
	 *
	 * This filter gives you an opportunity to filter the URL parameters that will be
	 * retained when the submenu page refreshes.
	 *
	 * @since 1.00
	 *
	 * @param	array	$submenu_arguments	Current view, pagination and sort parameters.
	 * @param	object	$include_filters	True to include "filter-by" parameters, e.g., year/month dropdown.
	 *
	 * @return	array	updated submenu_arguments.
	 */
	public static function mla_list_table_submenu_arguments( $submenu_arguments, $include_filters ) {
		// If the current view is a custom view, retain it
		if ( isset( $_REQUEST['nfve_view'] ) ) {
			$submenu_arguments['nfve_view'] = $_REQUEST['nfve_view'];
		}

		return $submenu_arguments;
	} // mla_list_table_submenu_arguments

	/**
	 * Pagination parameters for custom views
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $pagination_parameters = array(
		'per_page' => NULL,
		'current_page' => NULL,
	);

	/**
	 * Filter the pagination parameters for prepare_items()
	 *
	 * This filter gives you an opportunity to filter the per_page and current_page
	 * parameters used for the prepare_items database query.
	 *
	 * @since 1.00
	 *
	 * @param	array	$pagination		Contains 'per_page', 'current_page'.
	 * @param	object	$mla_list_table	The MLA_List_Table object, passed by reference.
	 *
	 * @return	array	updated pagination array.
	 */
	public static function mla_list_table_prepare_items_pagination( $pagination, $mla_list_table ) {
		global $wpdb;

		/*
		 * Save the parameters for the count and items filters
		 */
		self::$pagination_parameters = $pagination;
		return $pagination;
	} // mla_list_table_prepare_items_pagination

	/**
	 * Filters all clauses for shortcode queries, pre caching plugins
	 * 
	 * Modifying the query by editing the clauses in this filter ensures that all the other
	 * "List Table" parameters are retained, e.g., orderby, month, taxonomy and Search Media.
	 *
	 * @since 1.00
	 *
	 * @param	array	query clauses before modification
	 *
	 * @return	array	query clauses after modification (none)
	 */
	public static function posts_clauses( $pieces ) {
		global $wpdb;
		//error_log( __LINE__ . ' MLANotFeaturedViewExample::posts_clauses $pieces = ' . var_export( $pieces, true ), 0 );

		if ( isset( $_REQUEST['nfve_view'] ) ) {
			switch( $_REQUEST['nfve_view'] ) {
				case 'notfeatured':
					$pieces['join'] = " LEFT JOIN ( SELECT DISTINCT sub.meta_value FROM {$wpdb->postmeta} AS sub WHERE ( sub.meta_key = '_thumbnail_id' ) ) AS meta ON {$wpdb->posts}.ID = meta.meta_value" . $pieces['join'];
					$pieces['where'] = " AND meta.meta_value IS NULL" . $pieces['where'];
					break;
				default:
			} // nfve_view
		}

		return $pieces;
	} // posts_clauses

	/**
	 * Filter the total items count for prepare_items()
	 *
	 * This filter gives you an opportunity to substitute your own $total_items
	 * parameter used for the prepare_items database query.
	 *
	 * @since 1.00
	 *
	 * @param	integer	$total_items	NULL, indicating no substitution.
	 * @param	object	$mla_list_table	The MLA_List_Table object, passed by reference.
	 *
	 * @return	integer	updated total_items.
	 */
	public static function mla_list_table_prepare_items_total_items( $total_items, $mla_list_table ) {
		global $wpdb;

		if ( isset( $_REQUEST['nfve_view'] ) ) {
			switch( $_REQUEST['nfve_view'] ) {
				case 'notfeatured':
					// Defined in /wp-includes/query.php, function get_posts()
					add_filter( 'posts_clauses', 'MLANotFeaturedViewExample::posts_clauses', 10, 1 );
					$current_page = self::$pagination_parameters['current_page'];
					$per_page = self::$pagination_parameters['per_page'];
					$total_items = MLAData::mla_count_list_table_items( $_REQUEST, ( ( $current_page - 1 ) * $per_page ), $per_page );
					remove_filter( 'posts_clauses', 'MLANotFeaturedViewExample::posts_clauses', 10 );
					break;
				default:
			} // nfve_view
		}

		return $total_items;
	} // mla_list_table_prepare_items_total_items

	/**
	 * Filter the items returned by prepare_items()
	 *
	 * This filter gives you an opportunity to substitute your own items array
	 * in place of the default prepare_items database query.
	 *
	 * @since 1.00
	 *
	 * @param	array	$items			NULL, indicating no substitution.
	 * @param	object	$mla_list_table	The MLA_List_Table object, passed by reference.
	 *
	 * @return	array	updated $items array.
	 */
	public static function mla_list_table_prepare_items_the_items( $items, $mla_list_table ) {
		global $wpdb;

		if ( isset( $_REQUEST['nfve_view'] ) ) {
			switch( $_REQUEST['nfve_view'] ) {
				case 'notfeatured':
					add_filter( 'posts_clauses', 'MLANotFeaturedViewExample::posts_clauses', 10, 1 );
					$current_page = self::$pagination_parameters['current_page'];
					$per_page = self::$pagination_parameters['per_page'];
					$items = MLAData::mla_query_list_table_items( $_REQUEST, ( ( $current_page - 1 ) * $per_page ), $per_page );
					remove_filter( 'posts_clauses', 'MLANotFeaturedViewExample::posts_clauses', 10 );
					break;
				default:
			} // nfve_view
		}

		return $items;
	} // mla_list_table_prepare_items_the_items

	/**
	 * MLA Edit Media Toolbar Settings Filter
	 *
	 * Gives you an opportunity to change the content of the
	 * Media Manager Modal Window toolbar controls.
	 *
	 * @since 1.02
	 *
	 * @param	array	associative array with setting => value pairs
	 * @param	object	current post object, if available, else NULL
	 */
	public static function mla_media_modal_settings( $settings, $post ) {
		//error_log( __LINE__ . ' MLANotFeaturedViewExample::mla_media_modal_settings $settings = ' . var_export( $settings, true ), 0 );
		//error_log( __LINE__ . ' MLANotFeaturedViewExample::mla_media_modal_settings $post = ' . var_export( $post, true ), 0 );

		// Add our view to the MIME Types dropdown list
		if ( isset( $settings['mla_settings']['allMimeTypes'] ) ) {
			$settings['mla_settings']['allMimeTypes']['nfve_view'] = 'Not Featured';
		}
		
		if ( isset( $settings['mla_settings']['uploadMimeTypes'] ) ) {
			$settings['mla_settings']['uploadMimeTypes']['nfve_view'] = 'Not Featured';
		}
		
		return $settings;
	} // mla_media_modal_settings

	/**
	 * MLA Edit Media "Query Attachments" initial terms Filter
	 *
	 * Gives you an opportunity to change the terms of the 
	 * Media Manager Modal Window "Query Attachments" query
	 * before they are pre-processed by the MLA handler.
	 *
	 * @since 1.02
	 *
	 * @param	array	WP_Query terms supported for "Query Attachments"
	 * @param	array	All terms passed in the request
	 */
	public static function mla_media_modal_query_initial_terms( $query, $raw_query ) {
		//error_log( __LINE__ . ' MLANotFeaturedViewExample::mla_media_modal_query_initial_terms $query = ' . var_export( $query, true ), 0 );
		//error_log( __LINE__ . ' MLANotFeaturedViewExample::mla_media_modal_query_initial_terms $raw_query = ' . var_export( $raw_query, true ), 0 );

		if ( isset( $query['post_mime_type'] ) && ( 'nfve_view' === $query['post_mime_type'] ) ) {
			// Convert the MIME Type selection to the custom view
			//unset( $query['post_mime_type'] );
			$query['post_mime_type'] = 'image';
			$_REQUEST['nfve_view'] = 'notfeatured';
			add_filter( 'posts_clauses', 'MLANotFeaturedViewExample::posts_clauses', 10, 1 );
		}

		return $query;
	} // mla_media_modal_query_initial_terms

	/**
	 * MLA Edit Media "Query Attachments" filtered terms Filter
	 *
	 * Gives you an opportunity to change the terms of the 
	 * Media Manager Modal Window "Query Attachments" query
	 * after they are pre-processed by the Ajax handler.
	 *
	 * @since 1.02
	 *
	 * @param	array	WP_Query terms supported for "Query Attachments"
	 * @param	array	All terms passed in the request
	 */
	public static function mla_media_modal_query_filtered_terms( $query, $raw_query ) {
		//error_log( __LINE__ . ' MLANotFeaturedViewExample::mla_media_modal_query_filtered_terms $query = ' . var_export( $query, true ), 0 );
		//error_log( __LINE__ . ' MLANotFeaturedViewExample::mla_media_modal_query_filtered_terms $raw_query = ' . var_export( $raw_query, true ), 0 );

		return $query;
	} // mla_media_modal_query_filtered_terms

	/**
	 * MLA Media Modal Query Items
	 *
	 * Gives you an opportunity to Record or modify
	 * the results of the "mla_query_media_modal_items" query.
	 *
	 * @since 1.02
	 *
	 * @param	object	$attachments_query WP_Query results, passed by reference
	 * @param	array	$query query parameters passed to WP_Query
	 * @param	array	$raw_query query parameters passed in to function
	 * @param	integer	$offset parameter_name => parameter_value pairs
	 * @param	integer	$count parameter_name => parameter_value pairs
	 */
	public static function mla_media_modal_query_items( $attachments_query, $query, $raw_query, $offset, $count ) {
		//error_log( __LINE__ . " MLANotFeaturedViewExample::mla_media_modal_query_items( {$offset}, {$count} ) query = " . var_export( $query, true ), 0 );
		//error_log( __LINE__ . " MLANotFeaturedViewExample::mla_media_modal_query_items( {$offset}, {$count} ) raw_query = " . var_export( $raw_query, true ), 0 );
		//error_log( __LINE__ . " MLANotFeaturedViewExample::mla_media_modal_query_items( {$attachments_query->post_count}, {$attachments_query->found_posts} ) query_vars = " . var_export( $attachments_query->query_vars, true ), 0 );
		
		// The query is done and we no longer need our filter
		remove_filter( 'posts_clauses', 'MLANotFeaturedViewExample::posts_clauses', 10 );

		return $attachments_query;
	} // mla_media_modal_query_items

	/**
	 * MLA Edit Media "Query Attachments" final terms Filter
	 *
	 * Gives you an opportunity to change the terms of the 
	 * Media Manager Modal Window "Query Attachments" query
	 * after they are processed by the "Prepare List Table Query" handler.
	 *
	 * @since 1.02
	 *
	 * @param	array	WP_Query request prepared by "Prepare List Table Query"
	 */
	public static function mla_media_modal_query_final_terms( $request ) {
		//error_log( __LINE__ . ' MLANotFeaturedViewExample::mla_media_modal_query_final_terms $request = ' . var_export( $request, true ), 0 );

		/*
		 * MLAData::$query_parameters and MLAData::$search_parameters contain
		 * additional parameters used in some List Table queries.
		 */
		 
		/*
		 * Comment the next line out to remove items assigned to the
		 *  Att. Categories "Admin" term from the query results.
		 */
		return $request;
	} // mla_media_modal_query_final_terms

	/**
	 * MLA Edit Media "Query Attachments" custom results filter
	 *
	 * Gives you an opportunity to substitute the results of the 
	 * Media Manager Modal Window "Query Attachments" query
	 * with alternative results of your own.
	 *
	 * @since 1.02
	 *
	 * @param	object	NULL, indicating no results substitution
	 * @param	array	WP_Query request prepared by "Prepare List Table Query"
	 */
	public static function mla_media_modal_query_custom_items( $wp_query_object, $request ) {
		//error_log( __LINE__ . ' MLANotFeaturedViewExample::mla_media_modal_query_custom_items $request = ' . var_export( $request, true ), 0 );

		/*
		 * You can replace the NULL $wp_query_object with a new WP_Query( $request )
		 * object using your own $request parameters
		 */
		return $wp_query_object;
	} // mla_media_modal_query_custom_items
} // Class MLANotFeaturedViewExample

// Install the filters at an early opportunity
add_action('init', 'MLANotFeaturedViewExample::initialize');
?>