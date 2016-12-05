<?php

/**
 * Check update, do update
 *
 * @package   PT_Content_Views
 * @author    PT Guy <http://www.contentviewspro.com/>
 * @license   GPL-2.0+
 * @link      http://www.contentviewspro.com/
 * @copyright 2014 PT Guy
 */
// Compare stored version and current version
$stored_version = get_option( PT_CV_OPTION_VERSION );
if ( $stored_version ) {
	if ( version_compare( $stored_version, PT_CV_VERSION, '<' ) ) {
		update_option( PT_CV_OPTION_VERSION, PT_CV_VERSION );
	}

	// Delete view_count post meta
	if ( !get_option( 'pt_cv_version_pro' ) && version_compare( $stored_version, '1.8.8.0', '<=' ) ) {
		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $wpdb->postmeta WHERE meta_key = %s", '_' . PT_CV_PREFIX_ . 'view_count'
			)
		);
	}
}