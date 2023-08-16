<?php

/**
 * Class GPNF_Entry
 *
 * Provides an interface for interacting with GPNF functions relating to Gravity Forms entries.
 */
class GPNF_Entry {

	const ENTRY_PARENT_KEY            = 'gpnf_entry_parent';
	const ENTRY_PARENT_FORM_KEY       = 'gpnf_entry_parent_form';
	const ENTRY_NESTED_FORM_FIELD_KEY = 'gpnf_entry_nested_form_field';
	const ENTRY_EXP_KEY               = '_gpnf_expiration';

	protected $_entry;
	protected $_entry_id;

	public function __construct( $entry ) {

		if ( is_array( $entry ) ) {
			$this->_entry    = $entry;
			$this->_entry_id = $entry['id'];
		} else {
			$this->_entry_id = $entry;
		}

	}

	public function __get( $name ) {

		if ( ! empty( $this->_entry ) && isset( $this->_entry[ $name ] ) ) {
			return $this->_entry[ $name ];
		}

		// phpcs:ignore PHPCompatibility.FunctionUse.ArgumentFunctionsReportCurrentValue.NeedsInspection
		$trace = debug_backtrace();
		trigger_error( sprintf( 'Undefined property via __get(): %s in %s on line %s', $name, $trace[0]['file'], $trace[0]['line'] ), E_USER_NOTICE );

		return null;
	}

	public function trash_children() {
		$child_entries = $this->get_child_entries();
		foreach ( $child_entries as $child_entry ) {
			$this->update_status( 'trash', $child_entry['id'] );
		}
	}

	public function untrash_children() {
		$child_entries = $this->get_child_entries();
		foreach ( $child_entries as $child_entry ) {
			$this->update_status( 'active', $child_entry['id'] );
		}
	}

	public function delete_children() {
		$child_entries = $this->get_child_entries();
		foreach ( $child_entries as $child_entry ) {
			GFFormsModel::delete_lead( $child_entry['id'] );
		}
	}

	/**
	 * Duplicates the children for the current parent entry. Note, it is expected that
	 * the entry that GPNF_Entry is instantiated with is the already-duplicated parent entry.
	 *
	 * The child entries duplicated will remain unaffected on the original parent entry.
	 */
	public function duplicate_children() {
		$child_entries = $this->get_child_entries();

		/** @var array<int, int[]> */
		$duplicated_child_entries = array();

		foreach ( $child_entries as $child_entry ) {
			$nested_form_field_id   = $child_entry[ GPNF_Entry::ENTRY_NESTED_FORM_FIELD_KEY ];
			$duplicated_child_entry = GFAPI::add_entry( $child_entry );

			gform_update_meta( $duplicated_child_entry, GPNF_Entry::ENTRY_PARENT_KEY, $this->_entry_id );

			if ( ! isset( $duplicated_child_entries[ $nested_form_field_id ] ) ) {
				$duplicated_child_entries[ $nested_form_field_id ] = array();
			}

			$duplicated_child_entries[ $nested_form_field_id ][] = $duplicated_child_entry;
		}

		/**
		 * Update Nested Form Field values on parent form to use the newly duplicated child entries.
		 */
		foreach ( $duplicated_child_entries as $nested_form_field_id => $new_child_entries ) {
			$this->_entry[ $nested_form_field_id ] = implode( ',', $new_child_entries );
		}

		GFAPI::update_entry( $this->_entry, $this->_entry_id );
	}

	public function update_status( $status, $entry_id = null ) {
		if ( ! $entry_id ) {
			$entry_id = $this->_entry_id;
		}
		GFAPI::update_entry_property( $entry_id, 'status', $status );
	}

	public function has_children() {

		$entry = $this->get_entry();

		if ( is_wp_error( $entry ) ) {
			return false;
		}

		$form              = GFAPI::get_form( $entry['form_id'] );
		$has_nested_fields = gp_nested_forms()->has_nested_form_field( $form );

		if ( $has_nested_fields === false ) {
			return false;
		}

		$child_entries = $this->get_child_entries();
		if ( empty( $child_entries ) ) {
			return false;
		}

		return true;
	}

	public function get_child_entries( $field_id = false ) {

		$entry  = $this->get_entry();
		$form   = GFAPI::get_form( $entry['form_id'] );
		$fields = GFCommon::get_fields_by_type( $form, 'form' );

		$child_entries = array();
		foreach ( $fields as $field ) {

			if ( $field_id && $field->id != $field_id ) {
				continue;
			}

			$child_entry_ids = gp_nested_forms()->get_child_entry_ids_from_value( rgar( $entry, $field->id ) );
			if ( empty( $child_entry_ids ) ) {
				continue;
			}

			foreach ( $child_entry_ids as $child_entry_id ) {
				$child_entry = GFAPI::get_entry( $child_entry_id );
				if ( ! is_wp_error( $child_entry ) ) {
					$child_entry[ GPNF_Entry::ENTRY_NESTED_FORM_FIELD_KEY ] = $field->id;
					$child_entries[]                                        = $child_entry;
				}
			}
		}

		return $child_entries;

	}

	public function get_entry() {

		if ( empty( $this->_entry ) ) {
			$this->_entry = GFAPI::get_entry( $this->_entry_id );
		}

		return $this->_entry;
	}

	/**
	 * @return int
	 * @deprecated 1.0.23
	 *
	 */
	public function set_parent_form( $parent_form_id, &$parent_entry_id = false ) {
		return $this->set_parent_meta( $parent_form_id, $parent_entry_id );
	}

	public function set_parent_meta( $parent_form_id, $parent_entry_id = false ) {
		/**
		 * Filter parent entry ID
		 *
		 * @param string $parent_entry_id Parent entry ID to link child entries to
		 *
		 * @since 1.0-beta-9.10
		 *
		 */
		$parent_entry_id = gf_apply_filters( array( 'gpnf_set_parent_entry_id', $parent_form_id ), $parent_entry_id );

		// If parent entry ID not passed, get the temporary hash from the session.
		if ( ! $parent_entry_id ) {

			$session = new GPNF_Session( $parent_form_id );
			if ( ! $session->has_data() ) {
				return;
			}

			$parent_entry_id = $session->get( 'hash' );

		}

		// Set either temporary parent hashcode or actual parent entry ID
		gform_update_meta( $this->_entry_id, self::ENTRY_PARENT_KEY, $parent_entry_id );
		$this->_entry[ self::ENTRY_PARENT_KEY ] = $parent_entry_id;

		// Set the parent form ID during child submission.
		gform_update_meta( $this->_entry_id, self::ENTRY_PARENT_FORM_KEY, $parent_form_id );
		$this->_entry[ self::ENTRY_PARENT_FORM_KEY ] = $parent_form_id;

		return $parent_entry_id;
	}

	public function set_nested_form_field( $nested_form_field_id ) {
		// Add nested form field ID to allow finding child entries by the Nested Form field from which they were submitted.
		gform_update_meta( $this->_entry_id, self::ENTRY_NESTED_FORM_FIELD_KEY, $nested_form_field_id );
		$this->_entry[ self::ENTRY_NESTED_FORM_FIELD_KEY ] = $nested_form_field_id;
	}

	/**
	 * Set expiration meta for the child entry. Daily cron will clear expired entries.
	 */
	public function set_expiration() {
		gform_update_meta( $this->_entry_id, self::ENTRY_EXP_KEY, time() + self::get_expiration_modifier() );
	}

	/**
	 * Delete expiration meta.
	 *
	 * The clean-up cron will trash entries rather than delete them. If the entry is ever restored from the trash it
	 * should not expire again.
	 */
	public function delete_expiration() {
		gform_delete_meta( $this->_entry_id, self::ENTRY_EXP_KEY );
	}

	public function set_total() {

		$form = GFAPI::get_form( $this->_entry['form_id'] );

		// Force GFCommon::get_order_total() to get an un-cached total.
		gform_delete_meta( $this->_entry['id'], 'gform_product_info__' );

		$total        = GFCommon::get_order_total( $form, $this->_entry );
		$this->_total = $total;

	}

	public function set_created_by( $parent_entry_id ) {

		// Always fetch fresh entry as data may have changed since start of submission.
		$parent_entry = GFAPI::get_entry( $parent_entry_id );
		if ( is_wp_error( $parent_entry ) ) {
			return false;
		}

		if ( rgar( $parent_entry, 'created_by' ) ) {
			GFAPI::update_entry_property( $this->id, 'created_by', $parent_entry['created_by'] );
		}

	}

	public static function get_expiration_modifier() {
		/**
		 * Modify how long entries submitted from a nested form should be saved before being moved to the trash.
		 *
		 * @param int $seconds The number of seconds until the entry should be "expired".
		 *
		 * @since 1.0
		 *
		 */
		return apply_filters( 'gpnf_expiration_modifier', WEEK_IN_SECONDS );
	}

	/**
	 * Checks if user can edit/delete a child entry. If the user is logged in and they are the creator then they will
	 * be able to edit/delete the child entry.
	 *
	 * If the user is not logged in, they will be validated by a cookie.
	 *
	 * @param $entry
	 *
	 * @return bool
	 */
	public static function can_current_user_edit_entry( $entry ) {

		$can_user_edit_entry = false;

		$current_user   = get_current_user_id();
		$parent_form_id = gform_get_meta( $entry['id'], self::ENTRY_PARENT_FORM_KEY );

		if ( GFCommon::current_user_can_any( 'gravityforms_edit_entries' ) ) {
			$can_user_edit_entry = true;
		}

		if ( $current_user > 0 && $current_user == $entry['created_by'] ) {
			$can_user_edit_entry = true;
		}

		$parent_entry_id      = gform_get_meta( $entry['id'], self::ENTRY_PARENT_KEY );
		$parent_form_field_id = gform_get_meta( $entry['id'], self::ENTRY_NESTED_FORM_FIELD_KEY );

		$session = new GPNF_Session( $parent_form_id );
		$hash    = $session->get( 'hash' );

		$save_and_continue_entry_ids = gp_nested_forms()->get_save_and_continue_child_entry_ids( $parent_form_id, $parent_form_field_id );

		if ( $parent_entry_id == $hash ) {
			$can_user_edit_entry = true;
		}
		/**
		 * In some cases, the session cookie may not be available in which case we need to pull the child entry list
		 * again and bypass the permissions.
		 */
		elseif ( count( $save_and_continue_entry_ids ) && in_array( $entry['id'], $save_and_continue_entry_ids ) ) {
			$can_user_edit_entry = true;
		}
		/**
		 * With Partial Entries, child entries are adopted prior to parent form submission. In this case, we associate the
		 * child entries with the current session via the session hash saved to the parent entry.
		 */
		elseif ( gform_get_meta( $parent_entry_id, GPNF_Session::SESSION_HASH_META_KEY ) == $hash ) {
			$can_user_edit_entry = true;
		}

		/**
		 * Filter whether the current user has permission to edit the given entry.
		 *
		 * @param bool $can_user_edit_entry Can the current user edit the given entry?
		 * @param array $entry Current entry.
		 * @param \WP_User $user Current user.
		 *
		 * @since 1.0
		 *
		 */
		$can_user_edit_entry = apply_filters( 'gpnf_can_user_edit_entry', $can_user_edit_entry, $entry, $current_user );

		return $can_user_edit_entry;

	}

}
