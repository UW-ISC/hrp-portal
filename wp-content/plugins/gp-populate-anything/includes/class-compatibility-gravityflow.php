<?php
class GPPA_Compatibility_GravityFlow {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function __construct() {
		add_filter( 'gravityflow_step_form', array( $this, 'populate_form' ), 10, 2 );

		/* Source form is hydrated below. Target form is hydrated via "gform_form_pre_update_entry" in GPPA proper. */
		add_filter( 'gravityflowformconnector_update_entry_form', array( $this, 'populate_form' ), 10, 2 );

		// Added these at the request of Gravity Flow support. They have not been properly tested. Will revisit in the future.
		// See: https://secure.helpscout.net/conversation/1104725512/16351/
		add_filter( 'gravityflowformconnector_new_entry_form', array( $this, 'populate_form' ), 10, 2 );
		add_filter( 'gravityflowformconnector_update_field_values_form', array( $this, 'populate_form' ), 10, 2 );
	}

	public function populate_form( $form, $entry ) {
		static $populated_form_cache = array();

		/**
		 * Do not send through hydrate_initial_load if the form doesn't use dynamic population or have any
		 * Live Merge Tags.
		 */
		if ( ! gp_populate_anything()->should_enqueue_frontend_scripts( $form ) ) {
			return $form;
		}

		$cache_key = $form['id'] . '-' . rgar( $entry, 'id' );

		if ( isset( $populated_form_cache[ $cache_key ] ) ) {
			return $populated_form_cache[ $cache_key ];
		}

		$populated_form_cache[ $cache_key ] = gp_populate_anything()->populate_form( $form, false, array(), $entry );
		return $populated_form_cache[ $cache_key ];
	}

	/**
	 * @deprecated 2.0 GPPA_Compatibility_GravityFlow::populate_form()
	 *
	 * @param array $form
	 * @param array $entry
	 *
	 * @return array
	 */
	public function hydrate_form( $form, $entry ) {
		return $this->populate_form( $form, $entry );
	}

}

function gppa_compatibility_gravityflow() {
	return GPPA_Compatibility_GravityFlow::get_instance();
}
