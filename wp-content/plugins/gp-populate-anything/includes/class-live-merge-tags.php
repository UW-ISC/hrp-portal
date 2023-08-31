<?php

/* @todo add abspath check on all PHP files */

/**
 * Class GP_Populate_Anything_Live_Merge_Tags
 */
class GP_Populate_Anything_Live_Merge_Tags {

	private static $instance = null;

	private $_live_attrs_on_page            = array();
	private $_escapes                       = array();
	private $_current_live_merge_tag_values = array();
	private $_lmt_whitelist                 = array();

	public $checkable_input_types = array( 'checkbox', 'radio' );

	public $live_merge_tag_regex_option_placeholder = '/(<option.*?class=\'gf_placeholder\'>)(.*?)<\/option>/';
	public $live_merge_tag_regex_option_choice      = '/(<option.*>)(.*?@({.*?:?.+?}).*?)<\/option>/';
	public $live_merge_tag_regex_textarea           = '/(<textarea.*>)([\S\s]*?@({.*?:?.+?})[\S\s]*?)<\/textarea>/';
	public $live_merge_tag_regex                    = '/@({((.*?):?(.+?))})/';
	public $merge_tag_regex                         = '/{((.*?)(?::([0-9]+?\.?[0-9]*?))?(:(.+?))?)}/';
	public $live_merge_tag_regex_attr               = '/([a-zA-Z-]+)=([\'"]([^\'"]*@{.*?:?.+?}[^\'"]*)(?<!\\\)[\'"])/';
	public $value_attr                              = '/value=\'/';
	public $script_regex                            = '/<script[\s\S]*?<\/script>/';

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function __construct() {
		add_filter( 'gform_admin_pre_render', array( $this, 'populate_lmt_whitelist' ), 5 );
		add_filter( 'gform_pre_render', array( $this, 'populate_lmt_whitelist' ), 5 );
		add_filter( 'gform_pre_render', array( $this, 'replace_lmts_in_checkable_choices' ), 7 );
		add_filter( 'gform_pre_render', array( $this, 'reset_gf_is_field_hidden_cache' ), 15 );
		add_filter( 'gform_before_resend_notifications', array( $this, 'populate_lmt_whitelist' ), 5 );
		add_filter( 'gform_pre_submission_filter', array( $this, 'populate_lmt_whitelist' ), 5 );

		add_filter( 'gform_field_choice_markup_pre_render', array( $this, 'replace_live_merge_tag_select_field_option' ), 10, 4 );

		/**
		 * Prepare fields for LMTs such as adding data attributes and so on. Anything that is scoped to a specific
		 * input should be done here.
		 */
		foreach ( array( 'gform_field_content', 'gppa_hydrate_input_html' ) as $field_filter ) {
			add_filter( $field_filter, array( $this, 'replace_live_merge_tag_select_placeholder' ), 99, 2 );
			add_filter( $field_filter, array( $this, 'replace_live_merge_tag_textarea_default_value' ), 99, 2 );
			add_filter( $field_filter, array( $this, 'add_live_value_attr' ), 99, 2 );
			add_filter( $field_filter, array( $this, 'add_live_value_attr_textarea' ), 99, 2 );
			add_filter( $field_filter, array( $this, 'add_live_value_attr_checkable_choice' ), 99, 2 );
			add_filter( $field_filter, array( $this, 'add_select_default_value_attr' ), 99, 2 );
		}

		/**
		 * After the fields/inputs have been prepared, we can process the entire form (or hydrated HTML) for LMTs.
		 */
		foreach ( array( 'gform_get_form_filter', 'gppa_hydrate_field_html' ) as $wrapper_filter ) {
			add_filter( $wrapper_filter, array( $this, 'preserve_scripts' ), 98, 2 );
			add_filter( $wrapper_filter, array( $this, 'preserve_product_field_label' ), 98, 2 );
			add_filter( $wrapper_filter, array( $this, 'replace_live_merge_tag_attr' ), 99, 2 );
			add_filter( $wrapper_filter, array( $this, 'replace_live_merge_tag_non_attr' ), 99, 2 );
			add_filter( $wrapper_filter, array( $this, 'unescape_live_merge_tags' ), 99 );
			add_filter( $wrapper_filter, array( $this, 'add_localization_attr_variable' ), 99, 2 );
			add_filter( $wrapper_filter, array( $this, 'restore_escapes' ), 100, 2 );
		}

		add_filter( 'gppa_hydrate_field_html', array( $this, 'replace_live_merge_tag_textarea_default_value_hydrate_field' ), 99, 4 );

		add_filter( 'gform_pre_replace_merge_tags', array( $this, 'replace_live_merge_tags_static' ), 15, 3 ); // Give time for other plugins like GV Entry Revisions to do their replacements.
		add_filter( 'gform_replace_merge_tags', array( $this, 'replace_live_merge_tags_static' ), 10, 7 );
		add_filter( 'gform_admin_pre_render', array( $this, 'replace_field_label_live_merge_tags_static' ) );

		add_filter( 'gform_order_summary', array( $this, 'replace_live_merge_tags_static' ), 10, 3 );
		add_filter( 'gform_entry_field_value', array( $this, 'replace_entry_field_value_live_merge_tags' ), 10, 4 );

		add_filter( 'gform_merge_tag_filter', array( $this, 'prevent_missing_filter_text_from_being_tag_value' ), 10, 5 );

		add_filter( 'gpnf_all_entries_nested_entry_markup', array( $this, 'replace_live_merge_tags_gpnf_all_entries' ), 10, 5 );

		/**
		 * Security
		 */
		// Prevent things like <script> from being output in Live Merge Tags to protect against XSS.
		// @todo this is stripping things like background-repeat from CSS.
		add_filter( 'gppa_live_merge_tag_value', 'wp_kses_post' );

		/**
		 * Prevent replacement of Live Merge Tags in Preview Submission.
		 */
		add_filter( 'gpps_pre_replace_merge_tags', array( $this, 'escape_live_merge_tags' ) );
		add_filter( 'gpps_post_replace_merge_tags', array( $this, 'unescape_live_merge_tags' ) );

		/**
		 * Replace Live Merge Tags in labels of fields when printing.
		 */
		add_action( 'gform_print_entry_header', array( $this, 'add_printing_hooks' ) );
	}


	/**
	 * Signal to the front end that a Live Merge Tag exists on the page so the frontend knows what elements to
	 * search for
	 *
	 * @param string|number $form_id
	 * @param string $live_merge_tag
	 */
	public function register_lmt_on_page( $form_id, $live_merge_tag ) {
		if ( ! isset( $this->_live_attrs_on_page[ $form_id ] ) ) {
			$this->_live_attrs_on_page[ $form_id ] = array();
		}

		$this->_live_attrs_on_page[ $form_id ][] = $live_merge_tag;
		$this->_live_attrs_on_page[ $form_id ]   = array_unique( $this->_live_attrs_on_page[ $form_id ] );
	}

	/**
	 * Add current live merge tag value to handle coupling of inputs on the frontend on initial load.
	 *
	 * @param string|number $form_id
	 * @param string $live_merge_tag
	 * @param string $live_merge_tag_value
	 */
	public function add_current_lmt_value( $form_id, $live_merge_tag, $live_merge_tag_value ) {
		if ( ! isset( $this->_current_live_merge_tag_values[ $form_id ] ) ) {
			$this->_current_live_merge_tag_values[ $form_id ] = array();
		}

		$this->_current_live_merge_tag_values[ $form_id ][ $live_merge_tag ] = $this->prepare_for_lmt_comparison( $live_merge_tag_value );
	}

	/**
	 * Check whether or not a form contains Live Merge Tags
	 *
	 * @param string|number $form_id ID of form to check for Live Merge Tags.
	 * @return boolean
	 */
	public function form_has_lmts( $form_id ) {
		return count( rgar( $this->_lmt_whitelist, $form_id, array() ) ) > 0;
	}

	/**
	 * Get LMTs that have been set in the default value to whitelist the merge tags that can be used in a particular
	 * form.
	 *
	 * This helps prevent abuse with entry editing flows.
	 *
	 * @param $form
	 *
	 * @return array
	 */
	public function populate_lmt_whitelist( $form ) {

		if ( ! is_array( $form ) || empty( $form['id'] ) ) {
			return $form;
		}

		/* Do not populate if already populated. */
		if ( isset( $this->_lmt_whitelist[ $form['id'] ] ) ) {
			return $form;
		}

		$single_dimension_form = $this->flatten_multi_dimensional_array_to_index_array( $form );

		$this->_lmt_whitelist[ $form['id'] ] = array();

		foreach ( $single_dimension_form as $value ) {
			preg_match_all(
				$this->live_merge_tag_regex,
				$value,
				$merge_tag_matches,
				PREG_SET_ORDER
			);

			foreach ( $merge_tag_matches as $match ) {
				$merge_tag = html_entity_decode( preg_replace( '/^@/', '', $match[0] ), ENT_QUOTES );

				if ( isset( $this->_lmt_whitelist[ $form['id'] ][ $merge_tag ] ) ) {
					continue;
				}

				$nonce_action                                      = 'gppa-lmt-' . $form['id'] . '-' . $merge_tag;
				$this->_lmt_whitelist[ $form['id'] ][ $merge_tag ] = wp_create_nonce( $nonce_action );
			}
		}

		return $form;
	}

	/**
	 * Helper method to flatten multi-dimensional arrays into single-dimension arrays that can
	 * be looped through to populate the Live Merge Tag whitelist.
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public function flatten_multi_dimensional_array_to_index_array( $array ) {
		$return = array();

		foreach ( $array as $key => $value ) {
			if ( is_array( $value ) || $value instanceof ArrayAccess ) {
				$return = array_merge( array_values( $return ), $this->flatten_multi_dimensional_array_to_index_array( $value ) );
			} elseif ( is_string( $value ) ) {
				$return[] = $value;
			}
		}

		return $return;
	}


	/**
	 * Resets the GF cache's is hidden keys after the form is mostly processed (priority 15).
	 *
	 * The reason for this is to ensure GFFormsModel::is_field_hidden() returns the most accurate value possible when
	 * getting Live Merge Tag values.
	 *
	 * @param $form
	 *
	 * @return array
	 */
	public function reset_gf_is_field_hidden_cache( $form ) {
		// Re-fetch the form as GP_Populate_Anything::ajax_get_batch_field_html() will pare down the fields to only
		// those that need updating.
		$full_form = GFAPI::get_form( $form['id'] );

		if ( ! empty( $full_form['fields'] ) && is_array( $full_form['fields'] ) ) {
			foreach ( $full_form['fields'] as $field ) {
				GFCache::delete( 'GFFormsModel::is_field_hidden_' . $full_form['id'] . '_' . $field->id );
			}
		}

		return $form;
	}

	public function get_lmt_whitelist( $form ) {
		if ( gf_apply_filters( array( 'gppa_allow_all_lmts', $form['id'] ), false, $form ) ) {
			return null;
		}

		/**
		 * Filter the whitelist of Live Merge Tags for the current form.
		 *
		 * @since 1.0-beta-4.45
		 *
		 * @param array    $value The Live Merge Tag whitelist array. Each key is a Live Merge Tag, the values are nonces.
		 * @param array    $form  The current form.
		 */
		return gf_apply_filters( array( 'gppa_lmt_whitelist', $form['id'] ), rgar( $this->_lmt_whitelist, $form['id'] ), $form );
	}

	/**
	 * Gravity Forms outputs scripts in the form markup for things like conditional logic. Sometimes field settings
	 * such as the default value are included. Without intervention, the regular expressions in this class will match
	 * the Live Merge tags inside the JavaScript thus wreaking havoc and causing JavaScript errors.
	 *
	 * The easiest workaround is to shelve the JavaScript, run our replacements, and then re-add the JavaScript.
	 *
	 * @param $form_string
	 * @param $form
	 *
	 * @return string
	 */
	public function preserve_scripts( $form_string, $form ) {

		preg_match_all( $this->script_regex, $form_string, $matches, PREG_SET_ORDER );

		if ( ! $matches ) {
			return $form_string;
		}

		foreach ( $matches as $index => $match ) {
			$placeholder = "%%SCRIPT_FORM_{$form['id']}_{$index}%%";

			$this->_escapes[ $placeholder ] = $match[0];
			$form_string                    = str_replace( $match[0], $placeholder, $form_string );
		}

		return $form_string;

	}

	/**
	 * Gravity Forms validates Product fields using hashing and if the product name doesn't match due to a LMT on
	 * the Product field's label, it will fail validation.
	 *
	 * We need to escape the LMT on the hidden input that contains the product name.
	 *
	 * See ticket #13740
	 *
	 * @param $form_string
	 * @param $form
	 *
	 * @return string
	 */
	public function preserve_product_field_label( $form_string, $form ) {

		preg_match_all( '/ginput_container_singleproduct\'>[.\s]*?<input type=\'hidden\' name=\'input_\d+\.\d+\' value=\'(.*)?\' class=\'gform_hidden\' \/>/', $form_string, $matches, PREG_SET_ORDER );

		if ( ! $matches ) {
			return $form_string;
		}

		foreach ( $matches as $index => $match ) {
			$placeholder = "%%PRODUCT_NAME_{$form['id']}_{$index}%%";

			/**
			 * $search and $replace are needed since we're only replacing $match[0] inside of $match[1]
			 *
			 * Without this, it can get a bit aggressive and replace the LMT in other locations.
			 */
			$search  = $match[0];
			$replace = str_replace( $match[1], $placeholder, $search );

			$this->_escapes[ $placeholder ] = $match[1];
			$form_string                    = str_replace( $search, $replace, $form_string );
		}

		return $form_string;

	}

	public function restore_escapes( $form_string, $form ) {

		foreach ( $this->_escapes as $placeholder => $script ) {
			$form_string = str_replace( $placeholder, $script, $form_string );
		}

		return $form_string;

	}

	public function replace_live_merge_tag_attr( $form_string, $form ) {

		preg_match_all( $this->live_merge_tag_regex_attr, $form_string, $matches, PREG_SET_ORDER );

		if ( ! $matches ) {
			return $form_string;
		}

		foreach ( $matches as $match ) {
			$full_match = $match[0];
			$merge_tag  = $match[3];

			$output = $this->get_live_merge_tag_value( $merge_tag, $form );

			$replaced_attr = $match[1] . '="' . esc_attr( $output ) . '"';

			if ( strpos( $match[1], 'data-gppa-live-merge-tag' ) === 0 ) {
				continue;
			}

			$data_attr_name  = 'data-gppa-live-merge-tag-' . $match[1];
			$data_attr_value = $this->escape_live_merge_tags( $match[3] );
			$data_attr       = $data_attr_name . '="' . esc_attr( $data_attr_value ) . '"';

			$this->register_lmt_on_page( $form['id'], 'data-gppa-live-merge-tag-' . $match[1] );
			$this->add_current_lmt_value( $form['id'], $merge_tag, $output );

			$form_string = str_replace( $full_match, $replaced_attr . ' ' . $data_attr, $form_string );
		}

		return $form_string;

	}

	public function replace_live_merge_tag_select_placeholder( $content, $field ) {

		// First check if the select contains a placeholder option at all.
		preg_match_all( $this->live_merge_tag_regex_option_placeholder, $content, $matches, PREG_SET_ORDER );

		if ( ! $matches ) {
			return $content;
		}

		$form = GFAPI::get_form( $field->formId );

		/**
		 * $match[0] = Entire <option>...</option> string
		 * $match[1] = Starting tag and attributes
		 * $match[2] = Inner HTML of option
		 */
		foreach ( $matches as $match ) {

			list( $full_match, $option_tag, $text ) = $match;

			// Ensure that our text has a live merge tag before proceeding.
			preg_match_all( $this->live_merge_tag_regex, $text, $live_merge_tags, PREG_SET_ORDER );
			if ( empty( $live_merge_tags ) ) {
				continue;
			}

			$output    = $this->get_live_merge_tag_value( $text, $form );
			$data_attr = 'data-gppa-live-merge-tag-innerHtml="' . esc_attr( $this->escape_live_merge_tags( $text ) ) . '"';

			$class_string = "class='gf_placeholder'";

			$full_match_replacement = str_replace( $text, $output, $full_match );
			$full_match_replacement = str_replace( $class_string, $class_string . ' ' . $data_attr, $full_match_replacement );

			$this->register_lmt_on_page( $form['id'], 'data-gppa-live-merge-tag-innerHtml' );
			$this->add_current_lmt_value( $form['id'], $text, $output );

			$content = str_replace( $full_match, $full_match_replacement, $content );
		}

		return $content;

	}

	public function replace_live_merge_tag_textarea_default_value_hydrate_field( $content, $form, $result, $field ) {
		return $this->replace_live_merge_tag_textarea_default_value( $content, $field );
	}

	public function replace_live_merge_tag_textarea_default_value( $content, $field ) {

		preg_match_all( $this->live_merge_tag_regex_textarea, $content, $matches, PREG_SET_ORDER );

		if ( ! $matches || ! $field ) {
			return $content;
		}

		$form = GFAPI::get_form( $field->formId );

		/**
		 * $match[0] = Entire <textarea>...</textarea> string
		 * $match[1] = Starting tag and attributes
		 * $match[2] = Inner HTML of textarea
		 * $match[3] = First live merge tag that's seen
		 */
		foreach ( $matches as $match ) {

			$full_match = $match[0];

			$output    = $this->get_live_merge_tag_value( $match[2], $form );
			$data_attr = 'data-gppa-live-merge-tag-innerHtml="' . esc_attr( $this->escape_live_merge_tags( $match[2] ) ) . '"';

			$full_match_replacement = str_replace( $match[2], $output, $full_match );
			$full_match_replacement = str_replace( '<textarea ', '<textarea ' . $data_attr . ' ', $full_match_replacement );

			$this->register_lmt_on_page( $form['id'], 'data-gppa-live-merge-tag-innerHtml' );
			$this->add_current_lmt_value( $form['id'], $match[2], $output );

			$content = str_replace( $full_match, $full_match_replacement, $content );
		}

		return $content;

	}

	/**
	 * In some cases such as using a multi-page form, Gravity Forms will supply GPPA with form values which will overwrite
	 * the values that were initially LMTs. Because of this, LMTs won't be detected by the broad form filters that
	 * add in the data attr's for the LMTs.
	 *
	 * To get around this, we check if there are LMTs in the value and if not we re-add the data attr as long as there
	 * are LMTs in the field's default value.
	 *
	 * @param $content
	 * @param $field
	 *
	 * @return mixed
	 * @see GP_Populate_Anything_Live_Merge_Tags::add_live_value_attr_checkable_choice() for choices
	 *
	 * @see GP_Populate_Anything_Live_Merge_Tags::add_live_value_attr_textarea() for textareas
	 */
	public function add_live_value_attr( $content, $field ) {

		preg_match_all( '/value=([\'"]([^\'"]*@{.*?:?.+?}[^\'"]*)(?<!\\\)[\'"])/', $content, $matches, PREG_SET_ORDER );

		/**
		 * If there are already LMTs in the value, then bail out since the filters for the entry form string will
		 * add in the data attrs.
		 */
		if ( count( $matches ) ) {
			return $content;
		}

		if ( ! is_string( $field->defaultValue ) || ! preg_match( '/@{.*?:?.+?}/', $field->defaultValue ) ) {
			return $content;
		}

		$merge_tag_value = $this->get_live_merge_tag_value( $field->defaultValue, GFAPI::get_form( $field->formId ) );

		$this->register_lmt_on_page( $field->formId, 'data-gppa-live-merge-tag-value' );

		if ( $merge_tag_value ) {
			$this->add_current_lmt_value( $field->formId, $field->defaultValue, $merge_tag_value );
		}

		// No need to add data attributes for select fields as this
		// will modify the enclosed <option> elements causing hydration to fail. See HS#24437
		if ( $field->get_input_type() === 'select' ) {
			return $content;
		}

		$data_attr = 'data-gppa-live-merge-tag-value="' . esc_attr( $this->escape_live_merge_tags( $field->defaultValue ) ) . '"';

		$content = str_replace( ' value=\'', ' ' . $data_attr . ' value=\'', $content );

		/*
		 * Support Live Merge Tags for Date Picker Date fields. By default, the date will fail to parse so the value
		 * will be "//" which then causes the field to be decoupled and not update correctly.
		 */
		if ( $field->get_input_type() === 'date' && rgar( $field, 'dateType' ) === 'datepicker' ) {
			$content = str_replace( 'value=\'//\'', 'value=\'' . esc_attr( $merge_tag_value ) . '\'', $content );
		}

		return $content;
	}

	/**
	 * In some cases such as using a multi-page form or nested form, Gravity Forms will supply GPPA with form values
	 * which will overwrite the values that were initially LMTs. Because of this, LMTs won't be detected by the broad
	 * form filters that add in the data attr's for the LMTs.
	 *
	 * To get around this, we check if there are LMTs in the value and if not we re-add the data attr as long as there
	 * are LMTs in the field's default value.
	 *
	 * @param $content
	 * @param $field
	 *
	 * @return mixed
	 * @see GP_Populate_Anything_Live_Merge_Tags::add_live_value_attr_checkable_choice() for choices
	 *
	 * @see GP_Populate_Anything_Live_Merge_Tags::add_live_value_attr() for other inputs
	 */
	public function add_live_value_attr_textarea( $content, $field ) {

		preg_match_all( '/<textarea.*>([\s\S]*?)<\/textarea>/', $content, $matches, PREG_SET_ORDER );

		/**
		 * Skip if this field does not contain a textarea or the default value does NOT contain an LMT.
		 */
		if (
			( ! count( $matches ) )
			|| ! preg_match( '/@{.*?:?.+?}/', $field->defaultValue )
		) {
			return $content;
		}

		/**
		 * If there are already LMTs in the value, bail out since the filters for the entry form string will
		 * add in the data attrs.
		 */
		if ( preg_match( '/@{.*?:?.+?}/', $matches[0][1] ) ) {
			return $content;
		}

		$merge_tag_value = $this->get_live_merge_tag_value( $field->defaultValue, GFAPI::get_form( $field->formId ) );

		$this->register_lmt_on_page( $field->formId, 'data-gppa-live-merge-tag-innerHtml' );

		if ( $merge_tag_value ) {
			$this->add_current_lmt_value( $field->formId, $field->defaultValue, $merge_tag_value );
		}

		$data_attr = 'data-gppa-live-merge-tag-innerHtml="' . esc_attr( $this->escape_live_merge_tags( $field->defaultValue ) ) . '"';

		return str_replace( '<textarea ', '<textarea ' . $data_attr, $content );

	}

	/**
	 * Add in value LMT data attr if the value for a specific choice has already had its Live Merge Tags parsed.
	 * @see GP_Populate_Anything_Live_Merge_Tags::replace_live_merge_tags_in_radio_choice_value()
	 *
	 * @see GP_Populate_Anything_Live_Merge_Tags::add_live_value_attr() for other inputs
	 * @see GP_Populate_Anything_Live_Merge_Tags::add_live_value_attr_textarea() for textareas
	 *
	 * For additional context, see ticket #20452.
	 *
	 * @param $content
	 * @param $field GF_Field
	 *
	 * @return mixed
	 */
	public function add_live_value_attr_checkable_choice( $content, $field ) {
		if ( ! $field->choices || ! in_array( $field->get_input_type(), $this->checkable_input_types, true ) ) {
			return $content;
		}

		foreach ( $field->choices as $choice_index => $choice ) {
			if (
				isset( $choice['gppaOriginalValue'] )
				&& $this->has_live_merge_tag( $choice['gppaOriginalValue'] )
				&& strpos( $content, "value='{$choice['gppaOriginalValue']}'" ) === false
			) {
				// The index of checkboxes decided to start at 1 instead of 0 somewhere along the line.
				if ( $field->get_input_type() === 'checkbox' && version_compare( GFForms::$version, '2.5-beta-1', '>=' ) ) {
					$choice_index++;
				}

				$id_attr   = "id='choice_{$field->formId}_{$field->id}_{$choice_index}'";
				$data_attr = 'data-gppa-live-merge-tag-value="' . esc_attr( $this->escape_live_merge_tags( $choice['gppaOriginalValue'] ) ) . '"';
				$content   = str_replace( $id_attr, $id_attr . ' ' . $data_attr, $content );

				$this->register_lmt_on_page( $field->formId, 'data-gppa-live-merge-tag-value' );
			}
		}

		return $content;
	}

	/**
	 * @param $content
	 * @param $field
	 *
	 * @return mixed
	 */
	public function add_select_default_value_attr( $content, $field ) {

		preg_match_all( '/<select name=\'input_(\d+(\.\d+)?)\'/', $content, $matches, PREG_SET_ORDER );

		if ( ! $matches ) {
			return $content;
		}

		$default_values               = ! empty( $field->inputs ) ? $this->pluck( $field->inputs, 'defaultValue', 'id' ) : array();
		$default_values[ $field->id ] = $field->defaultValue;

		$has_lmt = false;

		foreach ( array_values( $default_values ) as $default_value ) {
			// Live Merge Tags are not supported in Repeater fields yet.
			if ( is_array( $default_value ) ) {
				continue;
			}
			if ( preg_match( '/@{.*?:?.+?}/', $default_value ) ) {
				$has_lmt = true;
				break;
			}
		}

		if ( ! $has_lmt ) {
			return $content;
		}

		foreach ( $matches as $match ) {
			$input_id      = $match[1];
			$default_value = $default_values[ $input_id ];

			/**
			 * With future AJAX optimizations, we will need to output get_live_merge_tag_value for initial load.
			 */
			$data_attr = 'data-gppa-live-merge-tag-innerHtml="' . esc_attr( $this->escape_live_merge_tags( $default_value ) ) . '"';

			$this->register_lmt_on_page( $field->formId, 'data-gppa-live-merge-tag-innerHtml' );

			$content = str_replace( $match[0], $match[0] . ' ' . $data_attr, $content );
		}

		return $content;

	}

	public function pluck( $list, $field, $index_key = null ) {

		$newlist = array();

		if ( ! $index_key ) {
			/*
			 * This is simple. Could at some point wrap array_column()
			 * if we knew we had an array of arrays.
			 */
			foreach ( $list as $key => $value ) {
				if ( is_object( $value ) ) {
					$newlist[ $key ] = $value->$field;
				} else {
					$newlist[ $key ] = $value[ $field ];
				}
			}

			$list = $newlist;

			return $list;
		}

		/*
		 * When index_key is not set for a particular item, push the value
		 * to the end of the stack. This is how array_column() behaves.
		 */
		foreach ( $list as $value ) {
			if ( is_object( $value ) ) {
				if ( isset( $value->$index_key ) ) {
					$newlist[ $value->$index_key ] = $value->$field;
				} else {
					$newlist[] = $value->$field;
				}
			} else {
				if ( isset( $value[ $index_key ] ) ) {
					$newlist[ $value[ $index_key ] ] = rgar( $value, $field );
				} else {
					$newlist[] = rgar( $value, $field );
				}
			}
		}

		return $newlist;
	}

	public function replace_live_merge_tag_select_field_option( $choice_markup, $choice, $field, $value ) {

		preg_match_all( $this->live_merge_tag_regex_option_choice, $choice_markup, $matches, PREG_SET_ORDER );

		if ( ! $matches ) {
			return $choice_markup;
		}

		$form = GFAPI::get_form( $field->formId );

		/**
		 * $match[0] = Entire <option>...</option> string
		 * $match[1] = Starting tag and attributes
		 * $match[2] = Option label
		 * $match[3] = First live merge tag that's seen
		 */
		foreach ( $matches as $match ) {

			$full_match = $match[0];

			$output    = $this->get_live_merge_tag_value( $match[2], $form );
			$data_attr = 'data-gppa-live-merge-tag-innerHtml="' . esc_attr( $this->escape_live_merge_tags( $match[2] ) ) . '"';

			$full_match_replacement = str_replace( '>' . $match[2] . '</option>', '>' . $output . '</option>', $full_match );
			$full_match_replacement = str_replace( '<option ', '<option ' . $data_attr . ' ', $full_match_replacement );

			$this->register_lmt_on_page( $form['id'], 'data-gppa-live-merge-tag-innerHtml' );
			$this->add_current_lmt_value( $form['id'], $match[2], $output );

			$choice_markup = str_replace( $full_match, $full_match_replacement, $choice_markup );
			// Remove empty values to default to innerHTML
			$choice_markup = str_replace( " value=''", '', $choice_markup );

			// Ensure that we reselect selected options using LMTs as their values.
			$value_parsed = $this->get_live_merge_tag_value( $choice['value'], $form );

			if ( $value_parsed == $value ) {
				$choice_markup = str_replace( '<option ', '<option selected="selected" ', $choice_markup );
			}
		}

		return $choice_markup;

	}

	public function replace_live_merge_tag_non_attr( $form_string, $form ) {

		preg_match_all( $this->live_merge_tag_regex, $form_string, $matches, PREG_SET_ORDER );

		if ( ! $matches ) {
			return $form_string;
		}

		foreach ( $matches as $match ) {
			$full_match = $match[0];
			$merge_tag  = $match[1];

			$populated_merge_tag = $this->get_live_merge_tag_value( $merge_tag, $form );

			$span        = '<span data-gppa-live-merge-tag="' . esc_attr( $this->escape_live_merge_tags( $full_match ) ) . '">' . $populated_merge_tag . '</span>';
			$form_string = str_replace( $full_match, $span, $form_string );
		}

		return $form_string;

	}

	/**
	 * Escape live merge tags to prevent regex interference.
	 *
	 * @param $string
	 */
	public function escape_live_merge_tags( $string ) {
		return preg_replace( $this->live_merge_tag_regex, '#!GPPA!!$2!!GPPA!#', $string );
	}

	public function unescape_live_merge_tags( $form_string ) {
		return preg_replace( '/#!GPPA!!((.*?):?(.+?))!!GPPA!#/', '@{$1}', $form_string );
	}

	public function add_localization_attr_variable( $form_string, $form ) {
		if ( ! empty( $this->_live_attrs_on_page[ $form['id'] ] ) ) {
			gp_populate_anything()->add_js_variable( "GPPA_LIVE_ATTRS_FORM_{$form['id']}", array_values( array_unique( $this->_live_attrs_on_page[ $form['id'] ] ) ) );
		}

		if ( ! empty( $this->_current_live_merge_tag_values[ $form['id'] ] ) ) {
			/**
			 * We explicitly add this to the form string to add support for Live Merge Tags when editing nested entries
			 * with GP Nested Forms.
			 */
			$form_string .= '<script type="text/javascript">
				var GPPA_CURRENT_LIVE_MERGE_TAG_VALUES_FORM_' . $form['id'] . ' = ' . json_encode( $this->_current_live_merge_tag_values[ $form['id'] ] ) . ';
			</script>';
		}

		if ( $this->get_lmt_whitelist( $form ) ) {
			gp_populate_anything()->add_js_variable( "GPPA_LMT_WHITELIST_{$form['id']}", $this->get_lmt_whitelist( $form ) );
		}

		return $form_string;
	}

	public function extract_merge_tag_modifiers( $non_live_merge_tag ) {

		$merge_tag_parts = explode( ':', $non_live_merge_tag );

		if ( count( $merge_tag_parts ) < 3 ) {
			return array();
		}

		$modifiers       = array();
		$merge_tag_parts = array_slice( $merge_tag_parts, 2 );
		$modifiers_str   = rtrim( join( ':', $merge_tag_parts ), '}' );

		preg_match_all( '/([a-z]+)(?:(?:\[(.+?)\])|,?)/', $modifiers_str, $matches, PREG_SET_ORDER );

		foreach ( $matches as $match_group ) {
			$modifiers[ $match_group[1] ] = isset( $match_group[2] ) ? $match_group[2] : true;
		}

		return $modifiers;

	}

	/**
	 * Prepares a field value for comparison to see if it's a decoupled merge tag or not.
	 *
	 * @param $live_merge_tag_value mixed The live merge tag value.
	 *
	 * @return mixed
	 */
	public function prepare_for_lmt_comparison( $live_merge_tag_value ) {
		if ( ! is_scalar( $live_merge_tag_value ) ) {
			return $live_merge_tag_value;
		}

		return stripslashes( trim( implode( "\n", array_map( 'trim', explode( "\n", $live_merge_tag_value ) ) ) ) );
	}

	/**
	 * Check if a field has empty inputs if all are needed. Example: Date field using inputs and not all three inputs
	 * have been filled out. Without all inputs filled out, Merge Tags typically return odd values.
	 *
	 * @param $field
	 * @param $form
	 *
	 * @return bool
	 */
	public function is_value_submission_empty( $entry_value, $field, $form ) {
		$dummy_field = clone $field;

		$is_empty = $dummy_field->is_value_submission_empty( $form['id'] );

		// Only do this check if AJAX as GF_Field::is_value_submission_empty() relies on POSTed values.
		if ( wp_doing_ajax() && $is_empty ) {
			return true;
		}

		/**
		 * GF 2.5 changed the behavior of is_value_submission_empty() and it won't return false if there are missing
		 * inputs like <GF 2.5 would.
		 *
		 * Fortunately, GF_Field->validate() has been changed and it's more suitable for this use-case.
		 */
		if ( version_compare( GFForms::$version, '2.5-beta-1', '>=' ) ) {
			$dummy_field->isRequired = true;

			// Suppress any PHP notices as there may be undefined indexes.
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			@$dummy_field->validate( $entry_value, $form );

			if ( ! empty( $dummy_field->validation_message ) ) {
				return true;
			}
		}

		return false;
	}

	public function get_live_merge_tag_value( $merge_tag, $form, $entry_values = null ) {

		$lmt_nonces = null;

		if ( rgar( $_REQUEST, 'lmt-nonces' ) ) {
			$lmt_nonces = rgar( $_REQUEST, 'lmt-nonces' );
		}

		if ( ! $entry_values ) {
			$entry_values = gp_populate_anything()->get_posted_field_values( $form );
		}

		/**
		 * Transform entry values for certain inputs depending on their type. This can be necessary to prevent double formatting/cleaning of numbers, etc.
		 */
		foreach ( $entry_values as $input_id => $entry_value ) {
			$field_id = (int) $input_id;
			$field    = GFAPI::get_field( $form, (int) $field_id );

			if ( ! $field ) {
				continue;
			}

			if ( $field->get_input_type() === 'number' ) {
				/** @var \GF_Field_Number $field */
				$entry_values[ $input_id ] = $field->clean_number( $entry_value );
				continue;
			}

			if ( ! in_array( $field['type'], GP_Populate_Anything::get_interpreted_multi_input_field_types(), true ) ) {
				// Convert input array to individual inputs only if they are a whole number and match the field ID.
				// See https://github.com/gravitywiz/gp-populate-anything/pull/379
				if ( ! empty( $field->inputs ) && is_array( $entry_value ) && (float) $input_id === (float) $field_id ) {
					unset( $entry_values[ $input_id ] );

					foreach ( $entry_value as $input_name => $input_value ) {
						$entry_values[ $input_name ] = $input_value;
					}

					continue;
				}

				// Convert array values to comma-separated strings.
				if ( is_array( $entry_value ) ) {
					$entry_values[ $input_id ] = implode( ', ', $entry_value );
				}

				continue;
			}

			/**
			 * The datepicker date type is not a multi-input field. Do not do
			 * anything with its value.
			 */
			if ( rgar( $field, 'dateType' ) === 'datepicker' ) {
				continue;
			}

			/**
			 * Sometimes a field (time, date dropdown) will come in with 3.1, 3.2, etc.
			 * We need to pass all of the input values in an array to get_value_save_entry() otherwise
			 * the correct value won't be returned.
			 */
			if (
				$field->type === 'time'
				|| (
					$field->type == 'date'
					&& in_array( rgar( $field, 'dateType' ), array(
						'datedropdown',
						'datefield',
					), true )
				)
			) {
				/* Our goal is to get the entry value into a scalar (string, specifically) format. If it's already converted, skip it. */
				if ( isset( $entry_values[ $field_id ] ) && is_scalar( $entry_values[ $field_id ] ) ) {
					continue;
				}

				/*
				 * When refreshing LMTs using AJAX, the entry values are already sent as an array so use the array if it's present, otherwise construct it
				 * from the inputs.
				 */
				if ( ! isset( $entry_values[ $field_id ] ) || ! is_array( $entry_values[ $field_id ] ) ) {
					$input_values = array();

					foreach ( $entry_values as $input_id_search => $input_value_search ) {
						if ( (int) $input_id_search === $field_id ) {
							$input_values[] = $input_value_search;
						}
					}
				} else {
					$input_values = $entry_values[ $field_id ];
				}

				$entry_values[ $field_id ] = $field->get_value_save_entry( $input_values, $form, $field_id, null, null );

				continue;
			}

			if ( $this->is_value_submission_empty( $entry_value, $field, $form ) ) {
				$entry_values[ $input_id ] = null;
				continue;
			}

			$save_value = $field->get_value_save_entry( $entry_value, $form, $input_id, null, null );

			if ( ! $save_value ) {
				continue;
			}

			$entry_values[ $input_id ] = $save_value;
		}

		/**
		 * Change Live Merge Tags to regular merge tags.
		 */
		$merge_tag = preg_replace( $this->live_merge_tag_regex, '$1', $merge_tag );
		$output    = $merge_tag;

		/**
		 * Sometimes one Live Merge Tag can contain multiple Merge Tags.
		 *
		 * Loop through and replace them individually to detect which are blank so we can properly use fallback
		 * modifier.
		 */
		preg_match_all( $this->merge_tag_regex, $merge_tag, $merge_tag_matches, PREG_SET_ORDER );

		foreach ( $merge_tag_matches as $merge_tag_match ) {
			$merge_tag = $merge_tag_match[0];

			/**
			 * Filter if all Live Merge Tags should be allowed. This is disabled by default for security.
			 *
			 * @since 1.0-beta-4.52
			 *
			 * @param array    $value Whether or not all Live Merge Tags are allowed
			 * @param array    $form  The current form.
			 */
			if ( ! gf_apply_filters( array( 'gppa_allow_all_lmts', $form['id'] ), false, $form ) ) {
				$_merge_tag = html_entity_decode( $merge_tag_match[0], ENT_QUOTES );
				/**
				 * Verify that LMT was supplied by trusted source and not injected.
				 */
				$nonce_action = 'gppa-lmt-' . $form['id'] . '-' . $_merge_tag;

				$lmt_whitelist = $this->get_lmt_whitelist( $form );

				if ( $lmt_nonces ) {
					if ( ! wp_verify_nonce( rgar( $lmt_nonces, $_merge_tag ), $nonce_action ) ) {
						gp_populate_anything()->log_debug( 'Live Merge Tag is not valid for merge tag: ' . $_merge_tag );
						$output = str_replace( $_merge_tag, '', $output );

						continue;
					}
				} elseif ( ! isset( $lmt_whitelist[ $_merge_tag ] ) ) {
					gp_populate_anything()->log_debug( 'Live Merge Tag nonce not found for merge tag: ' . $_merge_tag );
					$output = str_replace( $_merge_tag, '', $output );

					continue;
				}
			}

			// We probably should create a proper GF entry but for now, we'll add the currency property to prevent a
			// series of notices generated by passing the $entry_values to GFCommon::replace_variables() below when
			// {all_fields} or {pricing_fields} are used on the form.
			if ( ! isset( $entry_values['currency'] ) ) {
				$entry_values['currency'] = GFCommon::get_currency();
			}

			$merge_tag_match_value_html = GFCommon::replace_variables( $merge_tag, $form, $entry_values, false, false, false );

			/**
			 * If the merge tag is returning HTML, use it. We check if the string is actually HTML by utilizing
			 * strip_tags. This will ensure that Live Merge Tags containing {all_fields} or similar continue to work
			 * as expected.
			 *
			 * Otherwise, we need to use the merge tag result when the format is text to avoid an issue where
			 * HTML entities get escaped and break coupling/decoupling when users enter characters such as & in
			 * fields that are depended upon.
			 *
			 * <br /> is an allowed tag to improve support for linebreaks in textareas.
			 */
			if ( strip_tags( $merge_tag_match_value_html, '<br>' ) !== $merge_tag_match_value_html ) {
				$merge_tag_match_value = $merge_tag_match_value_html;
			} else {
				$merge_tag_match_value = GFCommon::replace_variables( $merge_tag, $form, $entry_values, false, false, false, 'text' );
			}

			// The Euro symbol breaks coupling and is not being parsed correctly when applied to submit buttons.
			// Decoding HTML entities here resolves the issue. HS#27761
			$merge_tag_match_value = html_entity_decode( $merge_tag_match_value );

			$merge_tag_modifiers = $this->extract_merge_tag_modifiers( $merge_tag );

			// Do not merge the value of conditionally hidden fields
			if ( isset( $merge_tag_match[3] ) && is_numeric( $merge_tag_match[3] ) ) {
				$field = GFFormsModel::get_field( $form, $merge_tag_match[3] );
				if ( GFFormsModel::is_field_hidden( $form, $field, array(), $entry_values ) ) {
					$merge_tag_match_value = '';
				}
			}

			$fallback = rgar( $merge_tag_modifiers, 'fallback' );

			if ( $fallback && ! $merge_tag_match_value ) {
				$merge_tag_match_value = $fallback;
			}

			// Return input ID for field-specific merge tags; otherwise, return generic merge tag (e.g. "all_fields").
			$field_id = rgar( $merge_tag_match, 3, $merge_tag_match[1] );
			/**
			 * Filter the live merge tag value.
			 *
			 * @since 1.0-beta-4.35
			 *
			 * @param string|int $merge_tag_match_value The value with which the live merge tag will be replaced.
			 * @param string     $merge_tag_match       The merge tag that is being replaced.
			 * @param array      $form                  The current form object.
			 * @param int        $field_id              The field ID targeted by the current merge tag.
			 * @param array      $entry_values          An array of values that should be used to determine the value with which to replace the merge tag.
			 */
			$merge_tag_match_value = gf_apply_filters( array( 'gppa_live_merge_tag_value', $form['id'], $field_id ), $merge_tag_match_value, $merge_tag, $form, $field_id, $entry_values );

			$output = str_replace( $merge_tag, $merge_tag_match_value, $output );
		}

		/**
		 * Handle recursive Live Merge Tags.
		 */
		while ( preg_match_all( $this->live_merge_tag_regex, $output, $populated_merge_tag_matches, PREG_SET_ORDER ) ) {
			$output = $this->get_live_merge_tag_value( $output, $form, $entry_values );
		}

		return $output;

	}

	public function replace_live_merge_tags( $text, $form, $entry = null ) {
		if ( ! is_string( $text ) ) {
			return $text;
		}

		preg_match_all( $this->live_merge_tag_regex, $text, $matches, PREG_SET_ORDER );

		if ( ! $matches ) {
			return $text;
		}

		foreach ( $matches as $match ) {
			$full_match = $match[0];
			$merge_tag  = $match[1];

			/**
			 * Prevent recursion.
			 */
			remove_filter( 'gform_replace_merge_tags', array( $this, 'replace_live_merge_tags_static' ), 10 );
			$populated_merge_tag = $this->get_live_merge_tag_value( $merge_tag, $form, $entry );
			add_filter( 'gform_replace_merge_tags', array( $this, 'replace_live_merge_tags_static' ), 10, 7 );

			$text = str_replace( $full_match, $populated_merge_tag, $text );
		}

		return $text;
	}

	/**
	 * In some cases, live merge tags should be replaced statically without the need to make them "live" (i.e. in field
	 * labels when rendering the {all_fields} merge tag).
	 *
	 * @return string $text
	 */
	public function replace_live_merge_tags_static( $text, $form, $entry = null, $url_encode = false, $esc_html = false, $nl2br = false, $format = 'html' ) {

		if ( ! $entry ) {
			return $text;
		}

		return $this->replace_live_merge_tags( $text, $form, $entry );

	}

	/**
	 * Same story, different signature for replacing merge tags in Entry Details for fields such as the List field.
	 *
	 * @param string   $display_value The value to be displayed.
	 * @param GF_Field $field         The Field Object.
	 * @param array    $entry         The Entry Object.
	 * @param array    $form          The Form Object.
	 *
	 * @return string $text
	 */
	public function replace_entry_field_value_live_merge_tags( $display_value, $field, $entry, $form ) {
		return $this->replace_live_merge_tags( $display_value, $form, $entry );
	}

	/**
	 * Replaces merge tags in GPNF {all_fields} content.
	 *
	 * @param $markup
	 * @param $nested_form_field
	 * @param $nested_form
	 * @param $entry
	 * @param $args
	 *
	 * @return string
	 */
	function replace_live_merge_tags_gpnf_all_entries( $markup, $nested_form_field, $nested_form, $entry, $args ) {
		$this->populate_lmt_whitelist( $nested_form );
		return $this->replace_live_merge_tags_static( $markup, $nested_form, $entry );
	}

	/**
	 * If using a Live Merge Tag pointing to a choice-based field that's reliant on field filters, by default it will
	 * try to use the "Fill Out Other Fields" text as the merge tag value as that is the choice text for the value
	 * of an empty string. This isn't ideal as it can cause uncoupling, and it's not the expected behavior especially
	 * if using Conditional Logic.
	 *
	 * @param string $value
	 * @param $input_id
	 * @param $modifier
	 * @param GF_Field $field
	 * @param string $raw_value
	 *
	 * @return string
	 */
	public function prevent_missing_filter_text_from_being_tag_value( $value, $input_id, $modifier, $field, $raw_value ) {
		if ( $value === apply_filters( 'gppa_missing_filter_text', '&ndash; ' . esc_html__( 'Fill Out Other Fields', 'gp-populate-anything' ) . ' &ndash;', $field ) ) {
			return $raw_value;
		}

		return $value;
	}

	public function replace_field_label_live_merge_tags_static( $form, $entry = null ) {
		if ( in_array( GFForms::get_page(), array( 'entry_detail', 'entry_detail_edit' ), true ) ) {
			$entry = GFAPI::get_entry( rgget( 'lid' ) );
		}

		if ( ! $entry || is_wp_error( $entry ) ) {
			return $form;
		}

		foreach ( $form['fields'] as $field ) {
			$field->label = $this->replace_live_merge_tags_static( $field->label, $form, $entry );
		}

		return $form;
	}

	public function has_live_merge_tag( $string ) {
		preg_match_all( $this->live_merge_tag_regex, $string, $matches, PREG_SET_ORDER );
		return (bool) count( $matches );
	}

	/**
	 * When using Live Merge Tags in choice values, the selected choice will be lost when navigating multi-page forms
	 * and when encountering a validation error.
	 *
	 * @param $form array Current form object.
	 */
	public function replace_lmts_in_checkable_choices( $form ) {
		/**
		 * When refreshing a field with AJAX, $form will only contain the fields being reloaded. This means LMT
		 * values may reference a field that does not exist in $form which can result in LMTs that do not have their
		 * value properly replaced.
		 */
		$form_for_lmts = wp_doing_ajax() ? GFAPI::get_form( $form['id'] ) : $form;

		/** @var \GF_Field $field */
		foreach ( $form['fields'] as &$field ) {
			if ( ! in_array( $field->get_input_type(), $this->checkable_input_types, true ) ) {
				continue;
			}

			if ( empty( $field->choices ) || ! is_array( $field->choices ) ) {
				continue;
			}

			foreach ( $field->choices as $choice_index => &$choice ) {
				$choice['gppaOriginalValue'] = trim( $choice['value'] );
				$choice['value']             = trim( $this->replace_live_merge_tags( $choice['value'], $form_for_lmts ) );

				// Registration of text/label will happen in another method.
				if ( preg_match( $this->live_merge_tag_regex, $choice['gppaOriginalValue'] ) ) {
					$this->register_lmt_on_page( $form['id'], 'data-gppa-live-merge-tag-value' );
				}

				// If the value is empty, change POST params to prevent it from becoming checked on multi-page forms.
				$input_id = sprintf( 'input_%d_%d', $field->id, $choice_index + 1 );

				if ( rgpost( 'is_submit_' . $form['id'] ) && rgar( $choice, 'value' ) === '' && rgar( $_POST, $input_id ) === '' ) {
					// An empty string will not suffice here as GFFormsModel::choice_value_match() will still check it.
					$_POST[ $input_id ] = 'gppa-unchecked';
				}
			}
		}

		return $form;
	}

	/**
	 * Adds the necessary hooks to replace Live Merge Tags in field labels when printing.
	 */
	public function add_printing_hooks() {
		remove_action( 'gform_print_entry_content', 'gform_default_entry_content' );
		remove_action( 'gform_print_entry_content', array( $this, 'print_entry_content' ) );

		add_action( 'gform_print_entry_content', array( $this, 'print_entry_content' ), 10, 3 );
	}

	/**
	 * Overrides gform_default_entry_content() to replace Live Merge Tags in field labels first.
	 */
	public function print_entry_content( $form, $entry, $entry_ids ) {
		// Reload the form each time otherwise the form object will be modified by the previous iteration.
		$form = GFAPI::get_form( $form['id'] );

		// Populate LMT whitelist to know if the form has LMTs or not to avoid unnecessary processing.
		$this->populate_lmt_whitelist( $form );

		if ( ! $this->form_has_lmts( $form['id'] ) ) {
			gform_default_entry_content( $form, $entry, $entry_ids );

			return;
		}

		// Break object references in $form['fields']
		$form['fields'] = array_map( 'unserialize', array_map( 'serialize', $form['fields'] ) );

		// Run form through gform_admin_pre_render for additional replacements.
		$form = apply_filters( 'gform_admin_pre_render', $form );

		// Replace Live Merge Tags in field labels (not done during gform_admin_pre_render).
		$form = gp_populate_anything()->live_merge_tags->replace_field_label_live_merge_tags_static( $form, $entry );

		gform_default_entry_content( $form, $entry, $entry_ids );
	}
}
