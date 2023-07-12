<?php

class GPNF_GravityFlow {

	private static $instance = null;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {

		if ( ! class_exists( 'Gravity_Flow' ) ) {
			return;
		}

		add_filter( 'gpnf_can_user_edit_entry', array( $this, 'can_user_edit_entry' ), 10, 3 );
		add_filter( 'gpnf_submitted_entry_ids', array( $this, 'get_submitted_entry_ids' ), 10, 3 );

	}

	/**
	 * Gets the current Gravity Flow form ID from query params.
	 *
	 * @return mixed
	 */
	public function get_current_flow_form_id() {
		return rgget( 'id' );
	}

	/**
	 * Gets the current Gravity Flow entry ID from query params.
	 *
	 * @return mixed
	 */
	public function get_current_flow_entry_id() {
		return rgget( 'lid' );
	}

	/**
	 * Ensures that the current user has permission to edit the nested form in the parent entry via workflow.
	 *
	 * @param Gravity_Flow_Step Current Step.
	 * @param int Current Nested Form Field ID being checked.
	 *
	 * @return bool
	 */
	public function can_user_edit_parent_entry( $step, $nested_form_field_id ) {
		if ( ! method_exists( $step, 'get_editable_fields' ) ) {
			return false;
		}

		return in_array( $nested_form_field_id, $step->get_editable_fields(), false );
	}

	/**
	 * Handle adding permissions to edit child entries for the current entry being processed in a workflow.
	 *
	 * @param bool     $can_user_edit_entry Can the current user edit the given entry?
	 * @param array    $entry               Current entry.
	 * @param \WP_User $user                Current user.
	 *
	 * @return bool
	 *
	 */
	public function can_user_edit_entry( $can_user_edit_entry, $entry, $user ) {
		$parent_form_id  = rgar( $entry, 'gpnf_entry_parent_form' );
		$parent_entry_id = rgar( $entry, 'gpnf_entry_parent' );

		if ( ! $parent_form_id || ! $parent_entry_id ) {
			return $can_user_edit_entry;
		}

		$parent_form  = GFAPI::get_form( $parent_form_id );
		$parent_entry = GFAPI::get_entry( $parent_entry_id );

		if ( ! $parent_form || is_wp_error( $parent_entry ) ) {
			return $can_user_edit_entry;
		}

		$current_step       = gravity_flow()->get_current_step( $parent_form, $parent_entry );
		$flow_child_entries = $this->get_current_workflow_child_entries( $parent_form, $parent_entry );

		foreach ( $flow_child_entries as $nested_form_field_id => $child_entry_ids ) {
			if ( ! $this->can_user_edit_parent_entry( $current_step, $nested_form_field_id ) ) {
				continue;
			}

			if ( in_array( $entry['id'], $child_entry_ids, false ) ) {
				return true;
			}
		}

		return $can_user_edit_entry;
	}

	/**
	 * Returns an associative array containing all the child entries associated with the parent entry going through
	 * a user input Gravity Flow workflow.
	 *
	 * @param array $parent_form  Form
	 * @param array $parent_entry Entry
	 *
	 * @return array
	 */
	public function get_current_workflow_child_entries( $parent_form, $parent_entry ) {
		$flow_child_entry_ids = array();

		if ( empty( $parent_form['fields'] ) ) {
			return $flow_child_entry_ids;
		}

		$current_step = gravity_flow()->get_current_step( $parent_form, $parent_entry );

		if ( $current_step && $current_step->get_type() === 'user_input' ) {
			foreach ( $parent_form['fields'] as $field ) {
				if ( $field->get_input_type() == 'form' ) {
					if ( ! $this->can_user_edit_parent_entry( $current_step, $field->id ) ) {
						continue;
					}

					$flow_child_entry_ids[ $field->id ] = gp_nested_forms()->get_child_entry_ids_from_value( gp_nested_forms()->get_field_value( $parent_form, $parent_entry, $field->id ) );
				}
			}
		}

		return $flow_child_entry_ids;
	}

	/**
	 * Add in the child entry IDs from the parent form that's being processed.
	 *
	 * @param array                 $entry_ids Entry IDs to populate the field with.
	 * @param array                 $form      Current form object.
	 * @param \GP_Nested_Form_Field $field     Current field object.
	 *
	 * @return array
	 */
	public function get_submitted_entry_ids( $entry_ids, $form, $field ) {
		/* Ensure workflow form ID matches the parent form ID being filtered. */
		if ( $form['id'] != $this->get_current_flow_form_id() ) {
			return $entry_ids;
		}

		if ( ! $this->get_current_flow_entry_id() ) {
			return $entry_ids;
		}

		$current_flow_entry = GFAPI::get_entry( $this->get_current_flow_entry_id() );

		if ( is_wp_error( $current_flow_entry ) ) {
			return $entry_ids;
		}

		$flow_child_entries = $this->get_current_workflow_child_entries( $form, $current_flow_entry );

		if ( empty( $flow_child_entries[ $field->id ] ) ) {
			return $entry_ids;
		}

		return array_unique( array_merge( $entry_ids, $flow_child_entries[ $field->id ] ) );
	}

}

function gpnf_gravityflow() {
	return GPNF_GravityFlow::get_instance();
}
