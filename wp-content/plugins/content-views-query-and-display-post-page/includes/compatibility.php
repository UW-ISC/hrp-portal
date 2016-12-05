<?php
/**
 * Resolved conflict with other plugins
 *
 * @package   PT_Content_Views
 * @author    PT Guy <http://www.contentviewspro.com/>
 * @license   GPL-2.0+
 * @link      http://www.contentviewspro.com/
 * @copyright 2016 PT Guy
 */
# Autoptimize: Disable "Force JavaScript in <head>"
add_filter( 'autoptimize_filter_js_defer', 'cv_filter_js_defer', 10, 1 );
if ( !function_exists( 'cv_filter_js_defer' ) ) {
	function cv_filter_js_defer( $defer ) {
		$defer = "defer ";
		return $defer;
	}

}

# Page Builder by SiteOrigin: incorrect excerpt
add_filter( 'pt_cv_field_content_excerpt', 'cv_field_content_excerpt_siteorigin', 9, 3 );
if ( !function_exists( 'cv_field_content_excerpt_siteorigin' ) ) {
	function cv_field_content_excerpt_siteorigin( $args, $fargs, $this_post ) {
		// Prevent recursive call
		if ( empty( $fargs ) ) {
			return $args;
		}

		if ( function_exists( 'siteorigin_panels_filter_content' ) ) {
			$args = siteorigin_panels_filter_content( $args );
		}

		return $args;
	}

}

# FacetWP: Fix View does not exist", missing posts in output when access page with parameters 'fwp_*' of FacetWP plugin
add_filter( 'facetwp_is_main_query', 'cv_facetwp_is_main_query', 999, 2 );
if ( !function_exists( 'cv_facetwp_is_main_query' ) ) {
	function cv_facetwp_is_main_query( $is_main_query, $query ) {
		if ( (!empty( $query->query_vars[ 'post_type' ] ) && $query->query_vars[ 'post_type' ] === 'pt_view') || !empty( $query->query_vars[ 'by_contentviews' ] ) ) {
			$is_main_query = false;
		}

		return $is_main_query;
	}

}


