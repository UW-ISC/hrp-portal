<?php
/*
Plugin Name: Upload Prefilter Hook Example
Description: Alters the name of a file during upload
Author: David Lingren
Version: 1.00
*/
class UploadPrefilterHookExample {
	public static function initialize() {
		add_filter( 'wp_handle_upload_prefilter', 'UploadPrefilterHookExample::upload_prefilter', 10, 1 );
	}

	public static function upload_prefilter( $file ) {
		error_log( 'UploadPrefilterHookExample::upload_prefilter $_REQUEST = ' . var_export( $_REQUEST, true ), 0 );
		if ( ! empty( $_REQUEST['post'] ) ) {
			// Gutenberg REST request
			$parent = get_post( (int) $_REQUEST['post'] );
			$post_name = $parent->post_title;
		} elseif ( ! empty( $_REQUEST['post_id'] ) ) {
			// Media Manager AJAX request
			$parent = get_post( (int) $_REQUEST['post_id'] );
			$post_name = $parent->post_title;
		} else {
			$post_name = 'No parent';
		}
		
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			$user_name = $user->display_name;
		} else {
			$user_name = 'Not logged in';
		}
		
		$pathinfo = pathinfo( $file['name'] );
		$file['name'] = $user_name . ' - ' . $post_name . '.' . $pathinfo['extension'];
		return $file;
	} // upload_prefilter
} //UploadPrefilterHookExample
add_action('init', 'UploadPrefilterHookExample::initialize');
?>