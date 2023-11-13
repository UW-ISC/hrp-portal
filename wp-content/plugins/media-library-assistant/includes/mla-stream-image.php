<?php
/**
 * Stand-alone stream image handler for the mla_viewer
 *
 * @package Media Library Assistant
 * @since 2.10
 */
//@ini_set('error_log','C:\Program Files\Apache Software Foundation\Apache24\logs\php-errors.log');

require_once( pathinfo( __FILE__, PATHINFO_DIRNAME ) . '/class-mla-image-processor.php' );

MLAImageProcessor::$mla_debug = isset( $_REQUEST['mla_debug'] ) && 'log' == $_REQUEST['mla_debug'];

if ( isset( $_REQUEST['mla_stream_file'] ) ) {
	$file = $_REQUEST['mla_stream_file']; // phpcs:ignore

	if ( 0 === strpos( $file, 'file://' ) ) {
		$file = substr( $file, 7 );

		if ( false === strpos( $file, '://' ) ) {
			if ( ! in_array( strtolower( pathinfo( $file, PATHINFO_EXTENSION ) ), array( 'ai', 'eps', 'pdf', 'ps' ) ) ) {
				MLAImageProcessor::mla_image_processor_die( 'unsupported file type', __LINE__, 500 );
			}

			$_REQUEST['mla_stream_file'] = $file;
			MLAImageProcessor::mla_process_stream_image();
		}
	}

	MLAImageProcessor::mla_image_processor_die( 'invalid mla_stream_file', __LINE__, 500 );
}

MLAImageProcessor::mla_image_processor_die( 'mla_stream_file not set', __LINE__, 500 );
?>