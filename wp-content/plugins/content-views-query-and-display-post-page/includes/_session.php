<?php
/**
 * Session management
 * @since 1.9.0
 */
if ( !class_exists( 'CV_Session' ) ) {

	class CV_Session {

		static function start() {
			if ( !headers_sent() && !session_id() && @is_writable( session_save_path() ) ) {
				session_start();
			}
		}

		static function get( $key, $default = false ) {
			return isset( $_SESSION[ $key ] ) ? $_SESSION[ $key ] : $default;
		}

		static function set( $key, $val ) {
			if ( self::is_valid() ) {
				$_SESSION[ $key ] = $val;
			}
		}

		static function is_valid() {
			$sid = session_id();
			if ( $sid === "" ) {
				return false;
			} else {
				return preg_match( '/[^-,a-zA-Z0-9]+/', $sid ) === 0;
			}
		}

	}

}


