<?php
if ( ! class_exists( 'GP_Plugin' ) ) {
	return;
}

/**
 * @todo migrate optimization in Advanced Select for FVO to all FVO values so all choices don't need to be fetched. Try
 * to benchmark it if possible
 */

class GP_Populate_Anything extends GP_Plugin {

	private static $instance = null;

	/**
	 * @var null|GP_Populate_Anything_Live_Merge_Tags
	 */
	public $live_merge_tags = null;

	public $gf_merge_tags_cache = array();

	protected $_field_objects_cache = array();

	protected $_field_choices_cache = array();


	/**
	 * Marks which scripts/styles have been localized to avoid localizing multiple times with Gravity Forms' scripts
	 * 'callback' property.
	 *
	 * @var array
	 */
	protected $_localized = array();

	protected $_version      = GPPA_VERSION;
	protected $_path         = 'gp-populate-anything/gp-populate-anything.php';
	protected $_full_path    = __FILE__;
	protected $_object_types = array();
	protected $_slug         = 'gp-populate-anything';
	protected $_title        = 'Gravity Forms Populate Anything';
	protected $_short_title  = 'Populate Anything';

	private $_getting_current_entry = false;

	/* Used for storing and passing around the $field_values passed to gform_pre_render */
	public $prepopulate_fields_values = array();

	/**
	 * @var array Hydrated fields cached _only_ during submission to reduce the time to submit.
	 */
	private $_hydrated_fields_on_submission_cache = array();

	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function minimum_requirements() {
		return array(
			'gravityforms' => array(
				'version' => '2.3-rc-1',
			),
			'wordpress'    => array(
				'version' => '4.8',
			),
			'plugins'      => array(
				'gravityperks/gravityperks.php' => array(
					'name'    => 'Gravity Perks',
					'version' => '2.2.3',
				),
			),
		);
	}

	public function init() {

		parent::init();

		load_plugin_textdomain( 'gp-populate-anything', false, basename( dirname( __file__ ) ) . '/languages/' );

		/* Form Display */
		add_filter( 'gform_pre_render', array( $this, 'field_value_js' ) );
		add_filter( 'gform_pre_render', array( $this, 'posted_value_js' ) );
		add_filter( 'gform_pre_render', array( $this, 'field_value_object_js' ) );
		add_filter( 'gform_pre_render', array( $this, 'populate_form' ), 8, 3 );

		// Remove 'object' from all field choices if it exists
		// @TODO EXTRACT INTO CALLBACK
		add_filter( 'gform_pre_render', function ( $form ) {
			if ( empty( $form['fields'] ) || ! is_array( $form['fields'] ) ) {
				return $form;
			}

			foreach ( $form['fields'] as &$field ) {
				if ( empty( $field->choices ) || ! is_array( $field->choices ) ) {
					continue;
				}

				foreach ( $field->choices as &$choice ) {
					if ( isset( $choice['object'] ) ) {
						unset( $choice['object'] );
					}
				}
			}

			return $form;
		}, PHP_INT_MAX );

		add_filter( 'gform_pre_validation', array( $this, 'override_state_validation_for_populated_fields' ) );

		add_filter( 'gform_field_input', array( $this, 'field_input_add_empty_field_value_filter' ), 10, 5 );

		add_filter( 'gform_field_content', array( $this, 'field_content_disable_if_empty_field_values' ), 10, 2 );

		add_filter( 'gppa_get_batch_field_html', array( $this, 'field_content_disable_if_empty_field_values' ), 10, 2 );
		add_filter( 'gppa_get_batch_field_html', array( $this, 'batch_field_html_maxlen_counter' ), 10, 6 );

		add_filter( 'gform_entry_field_value', array( $this, 'entry_field_value' ), 20, 4 );
		add_filter( 'gform_product_info', array( $this, 'use_choice_label_for_products' ), 10, 3 );

		add_filter( 'gform_entries_field_value', array( $this, 'entries_field_value' ), 20, 4 );

		add_action( 'gform_entry_detail_content_before', array( $this, 'field_value_js' ) );
		add_action( 'gform_entry_detail_content_before', array( $this, 'field_value_object_js' ) );

		add_filter( 'gform_post_category_choices', array( $this, 'post_category_hydrate_choices' ), 10, 3 );

		/**
		 * `gform_pre_process` priority is set to 5 to give other plugins some wiggle room after
		 * GPPA hydrates a form's fields while also avoiding any potential caching issues.
		 * An example of a caching issue here was with GPLCD and confirmation URL query strings
		 * returning IDs instead of hydrated labels. See HS#25419.
		 */
		add_filter( 'gform_pre_process', array( $this, 'populate_form' ), 5 );
		add_filter( 'gform_pre_process', array( $this, 'flush_dynamically_populated_fields_cache' ), 6 );
		add_filter( 'gform_pre_validation', array( $this, 'populate_form_pre_update_entry' ) ); // Required for Gravity View's Edit Entry view.
		add_filter( 'gform_pre_submission_filter', array( $this, 'populate_form' ) );
		add_filter( 'gform_form_pre_process_async_task', array( $this, 'populate_form' ), 10, 2 );

		add_filter( 'gform_admin_pre_render', array( $this, 'populate_form' ) );

		// Hydrate choices for Survey add-on, etc
		add_filter( 'gform_form_pre_results', array( $this, 'populate_form' ) );

		/* Permissions */
		add_filter( 'gform_form_update_meta', array( $this, 'check_gppa_settings_for_user' ), 10, 3 );

		/* Template Replacement */
		add_filter( 'gppa_process_template', array( $this, 'convert_wp_error_in_template_to_null' ), 8, 1 );
		add_filter( 'gppa_process_template', array( $this, 'maybe_convert_array_value_to_text' ), 9, 8 );
		add_filter( 'gppa_process_template', array( $this, 'replace_template_generic_gf_merge_tags' ), 15, 1 );
		add_filter( 'gppa_process_template', array( $this, 'replace_template_object_merge_tags' ), 10, 6 );
		add_filter( 'gppa_process_template', array( $this, 'replace_template_count_merge_tags' ), 10, 7 );
		add_filter( 'gppa_process_template', array( $this, 'maybe_add_currency_to_price' ), 10, 7 );
		add_filter( 'gppa_process_template', array( $this, 'maybe_convert_multiselect_text_to_array' ), 16, 8 );

		add_filter( 'gppa_no_results_value', array( $this, 'replace_no_results_template_count_merge_tags' ), 10, 4 );

		add_filter( 'gppa_array_value_to_text', array( $this, 'use_commas_for_arrays' ), 10, 6 );
		add_filter( 'gppa_array_value_to_text', array( $this, 'prepare_gf_field_array_value_to_text' ), 10, 7 );

		/* Form Submission */
		add_action( 'gform_save_field_value', array( $this, 'maybe_save_choice_label' ), 10, 4 );

		/* Field Value Parsing */
		add_filter( 'gppa_modify_field_value_date', array( $this, 'modify_field_values_date' ), 10, 2 );
		add_filter( 'gppa_modify_field_value_time', array( $this, 'modify_field_values_time' ), 10, 2 );
		add_filter( 'gppa_modify_field_value_multiselect', array( $this, 'modify_field_values_multiselect' ), 10, 2 );

		/* Field HTML when there are input field values */
		add_filter( 'gppa_field_html_empty_field_value_radio', function( $text, $field, $form_id, $field_values ) {
			return $this->radio_field_html_empty_field_value( $text, $field );
		}, 10, 4 );

		/* Conditional Logic */
		add_filter( 'gform_field_filters', array( $this, 'conditional_logic_field_filters' ), 10, 2 );
		add_action( 'admin_footer', array( $this, 'conditional_logic_use_text_field' ) );

		/* Exporting */
		add_filter( 'gform_export_field_value', array( $this, 'hydrate_export_value' ), 10, 4 );

		/**
		 * Hydrate form before updating an entry. This is particularly helpful when the form contains a Checkbox field
		 * so that dynamically populated inputs are hydrated and will be saved.
		 */
		add_filter( 'gform_form_pre_update_entry', array( $this, 'populate_form_pre_update_entry' ), 10, 2 );

		/**
		 * Exclude the "Fill Out Other Fields" choice in field map settings.
		 */
		add_filter( 'gform_field_map_choices', array( $this, 'exclude_error_choices_from_field_maps' ), 10, 4 );

		/* Globals */
		if ( ! isset( $GLOBALS['gppa-field-values'] ) ) {
			$GLOBALS['gppa-field-values'] = array();
		}

		/* Live Merge Tags */
		$this->live_merge_tags = new GP_Populate_Anything_Live_Merge_Tags();

		/* Add default object types */
		$object_types = array(
			'post'     => 'GPPA_Object_Type_Post',
			'term'     => 'GPPA_Object_Type_Term',
			'user'     => 'GPPA_Object_Type_User',
			'gf_entry' => 'GPPA_Object_Type_GF_Entry',
			'database' => 'GPPA_Object_Type_Database',
		);

		/**
		 * Filter object types GPPA will populate from.
		 *
		 * @since 1.0-beta-4.104
		 *
		 * @param array $object_types Array of GPPA object types indexed by type name.
		 *    default value: array( 'post'     => 'GPPA_Object_Type_Post',
		 *                          'term'     => 'GPPA_Object_Type_Term',
		 *                          'user'     => 'GPPA_Object_Type_User',
		 *                          'gf_entry' => 'GPPA_Object_Type_GF_Entry',
		 *                          'database' => 'GPPA_Object_Type_Database');
		 */
		$object_types = apply_filters( 'gppa_autoloaded_object_types', $object_types );
		foreach ( $object_types as $type => $class_name ) {
			$this->register_object_type( $type, $class_name );
		}

		$this->perk_compatibility();

		gppa_compatibility_gravityview();
		gppa_compatibility_gravityflow();
		gppa_compatibility_gravitypdf();
		gppa_compatibility_jetsloth_image_choices();
		gppa_compatibility_wc_product_addons();

	}

	public function pre_init() {
		parent::pre_init();

		// Must happen on pre_init to intercept the 'gform_export_form' filter.
		gppa_export();
	}

	/**
	 * Add necessary hooks to ensure compatibility with other Gravity Perks
	 */
	public function perk_compatibility() {
		/**
		 * GP Nested Forms
		 *
		 * Hydrate fields any time the nested form is fetched. One key example of this is ensuring that the label of
		 * a choice-based field is reflected in the merge value rather than the value of the option itself.
		 */
		add_filter( 'gpnf_get_nested_form', array( $this, 'populate_form' ) );

		/**
		 * Easy Passthrough
		 */
		add_filter( 'gform_gp-easy-passthrough_field_value', array( $this, 'easy_passthrough_override_field_value' ), 10, 4 );
		add_filter( 'gppa_prepopulate_field_values', array( $this, 'easy_passthrough_prepopulate_values' ), 10, 2 );
	}

	/**
	 * Hydrate dynamically populated fields with choices and inputs (currently just checkboxes).
	 *
	 * @param string $field_value The current field value.
	 * @param array  $form The current Form object.
	 * @param array  $entry The current Entry object.
	 * @param string $field_id The current field ID.
	 *
	 * @return string
	 */
	public function easy_passthrough_override_field_value( $field_value, $form, $entry, $field_id ) {
		if ( ! is_numeric( $field_id ) || intval( $field_id ) != $field_id ) {
			return $field_value;
		}

		$field = GFFormsModel::get_field( $form, absint( $field_id ) );

		if ( ! $field ) {
			return $field_value;
		}

		if ( empty( $field->choices ) || empty( $field->inputs ) || ! $this->is_field_dynamically_populated( $field ) ) {
			return $field_value;
		}

		// Hydrate field
		$form           = GFAPI::get_form( $field['formId'] );
		$hydrated_field = $this->populate_field( $field, $form, $entry );
		$field          = $hydrated_field['field'];

		return $field->get_value_export( $entry, $field_id );
	}

	/**
	 * If Easy Passthrough is in use for the current form, take the passed through values and add them to the prepopulate values which contain values from
	 * Save & Continue, dynamic population, etc, so Populate Anything can correctly make queries on the initial load.
	 *
	 * @param array $prepopulate_values The values to prepopulate into the form and any Populate Anything queries.
	 * @param array $form The current form.
	 *
	 * @return array
	 */
	public function easy_passthrough_prepopulate_values( $prepopulate_values, $form ) {
		if ( ! function_exists( 'gp_easy_passthrough' ) ) {
			return $prepopulate_values;
		}

		$gpep_values = gp_easy_passthrough()->get_field_values( $form['id'] );

		if ( ! empty( $gpep_values ) ) {
			$prepopulate_values = array_replace( $prepopulate_values, $gpep_values );

			/* Unset values for fields that have individual input values in the same array.. */
			foreach ( $prepopulate_values as $input_id => $input_value ) {
				$field_id = absint( $input_id );

				/* Check if the input ID is a float (not a field ID) and if so, clear out the field value for that field if it exists since we have the input. */
				if ( (float) $input_id !== (float) $field_id && isset( $prepopulate_values[ $field_id ] ) ) {
					unset( $prepopulate_values[ $field_id ] );
				}
			}
		}

		return $prepopulate_values;
	}

	/**
	 * Some field types such as time handle the value as a single value rather than a value for each input.
	 * GPPA needs to know what field types behave this way so it treats the value templates correctly.
	 *
	 * @return array
	 */
	public static function get_interpreted_multi_input_field_types() {
		return apply_filters(
			'gppa_interpreted_multi_input_field_types',
			array(
				'time',
				'date',
			)
		);
	}

	/**
	 * Much like the interpreted multi input fields above, some fields such as checkboxes and multiselect need to have
	 * their value handled as a singular array value rather than a value for each input (AKA choice).
	 *
	 * @see GP_Populate_Anything::get_interpreted_multi_input_field_types()
	 *
	 * @return array
	 */
	public static function get_multi_selectable_choice_field_types() {
		return apply_filters(
			'gppa_multi_selectable_choice_field_types',
			array(
				'multiselect',
				'checkbox',
			)
		);
	}

	public function init_admin() {

		parent::init_admin();

		/* Form Editor */
		add_filter( 'admin_body_class', array( $this, 'add_helper_body_classes' ) );
		add_action( 'gform_field_standard_settings_75', array( $this, 'field_standard_settings' ) );

		/* We don't change field values in admin since it can cause the value to be saved as the defaultValue setting */

		add_filter( 'gform_field_css_class', array( $this, 'add_enabled_field_class' ), 10, 3 );

	}

	public function init_ajax() {

		parent::init_ajax();

		/* Privileged */
		add_action( 'wp_ajax_gppa_get_object_type_properties', array( $this, 'ajax_get_object_type_properties' ) );
		add_action( 'wp_ajax_gppa_get_property_values', array( $this, 'ajax_get_property_values' ) );
		add_action( 'wp_ajax_gppa_get_batch_field_html', array( $this, 'ajax_get_batch_field_html' ) );
		add_action( 'wp_ajax_gppa_get_query_results', array( $this, 'ajax_get_query_results' ) );

		/* Un-Privileged */
		add_action( 'wp_ajax_nopriv_gppa_get_batch_field_html', array( $this, 'ajax_get_batch_field_html' ) );

	}

	public function scripts() {

		$scripts = array(
			array(
				'handle'    => 'gp-populate-anything-admin',
				'src'       => $this->get_base_url() . '/js/built/gp-populate-anything-admin.js',
				'version'   => $this->_version,
				'deps'      => array( 'jquery' ),
				'in_footer' => true,
				'enqueue'   => array(
					array( 'admin_page' => array( 'form_editor' ) ),
				),
				'callback'  => array( $this, 'localize_admin_scripts' ),
			),
			array(
				'handle'    => 'gp-populate-anything',
				'src'       => $this->get_base_url() . '/js/built/gp-populate-anything.js',
				'version'   => $this->_version,
				'deps'      => array( 'gform_gravityforms', 'jquery' ),
				'in_footer' => true,
				'enqueue'   => array(
					array( $this, 'should_enqueue_frontend_scripts' ),
				),
				'callback'  => array( $this, 'localize_frontend_scripts' ),
			),
		);

		return apply_filters( 'gppa_scripts', array_merge( parent::scripts(), $scripts ) );

	}

	/**
	 * @param $form array
	 * @return bool
	 */
	public function should_enqueue_frontend_scripts( $form ) {
		/* form_has_lmts() is dependent on the LMT whitelist being populated. */
		$this->live_merge_tags->populate_lmt_whitelist( $form );

		return (
			$this->form_has_dynamic_population( $form )
			|| $this->live_merge_tags->form_has_lmts( rgar( $form, 'id' ) )
		);
	}

	public function styles() {

		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

		$styles = array(
			array(
				'handle'  => 'gp-populate-anything-admin',
				'src'     => $this->get_base_url() . "/styles/gp-populate-anything-admin{$min}.css",
				'version' => $this->_version,
				'enqueue' => array(
					array( 'admin_page' => array( 'form_editor' ) ),
				),
			),
			array(
				'handle'  => 'gp-populate-anything',
				'src'     => $this->get_base_url() . "/styles/gp-populate-anything{$min}.css",
				'version' => $this->_version,
				'enqueue' => array(
					array( $this, 'should_enqueue_frontend_scripts' ),
				),
			),
		);

		return apply_filters( 'gppa_styles', array_merge( parent::styles(), $styles ) );

	}

	public function is_localized( $item ) {
		return in_array( $item, $this->_localized, true );
	}

	public function localize_admin_scripts() {

		if ( $this->is_localized( 'admin-scripts' ) ) {
			return;
		}

		$gppa_object_types = array();

		foreach ( $this->get_object_types() as $object_type_id => $object_type_instance ) {
			$gppa_object_types[ $object_type_id ] = $object_type_instance->to_simple_array();
		}

		wp_localize_script(
			'gp-populate-anything-admin',
			'GPPA_ADMIN',
			array(
				'objectTypes'                     => $gppa_object_types,
				'strings'                         => $this->get_js_strings(),
				'defaultOperators'                => $this->get_default_operators(),
				'interpretedMultiInputFieldTypes' => self::get_interpreted_multi_input_field_types(),
				'multiSelectableChoiceFieldTypes' => self::get_multi_selectable_choice_field_types(),
				'gfBaseUrl'                       => GFCommon::get_base_url(),
				'nonce'                           => wp_create_nonce( 'gppa' ),
				'isSuperAdmin'                    => is_super_admin(),
			)
		);

		$this->_localized[] = 'admin-scripts';

	}

	public function get_default_operators() {
		/**
		 * Filter the default operators for ALL properties.
		 *
		 * Note: this will impact the UI only, additional logic will be required when adding new operators such as
		 * extending Object Types to know how to query using the added operator.
		 *
		 * @since 1.0-beta-4.91
		 *
		 * @param string[] $operators The default operators for ALL properties.
		 */
		return apply_filters(
			'gppa_default_operators',
			array(
				'is',
				'isnot',
				'>',
				'>=',
				'<',
				'<=',
				'contains',
				'does_not_contain',
				'starts_with',
				'ends_with',
				'like',
			)
		);
	}

	public function localize_frontend_scripts() {

		/**
		 * If a script is enqueued in the footer with in_footer, this script will
		 * be called multiple times and we need to guard against localizing multiple times.
		 */
		if ( $this->is_localized( 'frontend-scripts' ) ) {
			return;
		}

		wp_localize_script( 'gp-populate-anything', 'GPPA', array(
			'AJAXURL'    => admin_url( 'admin-ajax.php', null ),
			'GF_BASEURL' => GFCommon::get_base_url(),
			'NONCE'      => wp_create_nonce( 'gppa' ),
			'I18N'       => $this->get_js_strings(),
		) );

		$this->_localized[] = 'frontend-scripts';

	}

	public function get_js_strings() {

		return apply_filters(
			'gppa_strings',
			array(
				'populateChoices'                   => esc_html__( 'Populate choices dynamically', 'gp-populate-anything' ),
				'populateValues'                    => esc_html__( 'Populate value dynamically', 'gp-populate-anything' ),
				'or'                                => esc_html__( 'Or', 'gp-populate-anything' ),
				'and'                               => esc_html__( 'And', 'gp-populate-anything' ),
				'filterAriaLabel'                   => esc_html__( 'Filter {0}', 'gp-populate-anything' ),
				'filterGroupAriaLabel'              => esc_html__( 'Filter Group {0}', 'gp-populate-anything' ),
				'filterGroups'                      => esc_html__( 'Filter Groups', 'gp-populate-anything' ),
				'addFilter'                         => esc_html__( 'Add Filter', 'gp-populate-anything' ),
				'addFilterGroup'                    => esc_html__( 'Add Filter Group', 'gp-populate-anything' ),
				'removeFilter'                      => esc_html__( 'Remove Filter', 'gp-populate-anything' ),
				'removeFilterAriaLabel'             => esc_html__( 'Remove Filter {0}', 'gp-populate-anything' ),
				'label'                             => esc_html__( 'Label', 'gp-populate-anything' ),
				'value'                             => esc_html__( 'Value', 'gp-populate-anything' ),
				'price'                             => esc_html__( 'Price', 'gp-populate-anything' ),
				'image'                             => esc_html__( 'Image', 'gp-populate-anything' ),
				'loadingEllipsis'                   => esc_html__( 'Loading...', 'gp-populate-anything' ),
				/**
				 * Using HTML entity (&#9998;) does not work with esc_html__ so the pencil has been pasted in directly.
				 */
				'addCustomValue'                    => esc_html__( '✎ Custom Value', 'gp-populate-anything' ),
				'standardValues'                    => esc_html__( 'Standard Values', 'gp-populate-anything' ),
				'formFieldValues'                   => esc_html__( 'Form Field Values', 'gp-populate-anything' ),
				'specialValues'                     => esc_html__( 'Special Values', 'gp-populate-anything' ),
				'valueBoolTrue'                     => esc_html__( '(boolean) true', 'gp-populate-anything' ),
				'valueBoolFalse'                    => esc_html__( '(boolean) false', 'gp-populate-anything' ),
				'valueNull'                         => esc_html__( '(null) NULL', 'gp-populate-anything' ),
				// translators: placeholder is the primary property to be selected such as a Form or Database Table
				'selectAnItem'                      => esc_html__( 'Select a %s', 'gp-populate-anything' ),
				'unique'                            => esc_html__( 'Only Show Unique Results', 'gp-populate-anything' ),
				'reset'                             => esc_html__( 'Reset', 'gp-populate-anything' ),
				'type'                              => esc_html__( 'Type', 'gp-populate-anything' ),
				'objectType'                        => esc_html__( 'Object Type', 'gp-populate-anything' ),
				'filters'                           => esc_html__( 'Filters', 'gp-populate-anything' ),
				'ordering'                          => esc_html__( 'Ordering', 'gp-populate-anything' ),
				'ascending'                         => esc_html__( 'Ascending', 'gp-populate-anything' ),
				'descending'                        => esc_html__( 'Descending', 'gp-populate-anything' ),
				'random'                            => esc_html__( 'Random', 'gp-populate-anything' ),
				'choiceTemplate'                    => esc_html__( 'Choice Template', 'gp-populate-anything' ),
				'valueTemplates'                    => esc_html__( 'Value Templates', 'gp-populate-anything' ),
				'operators'                         => array(
					'is'               => __( 'is', 'gp-populate-anything' ),
					'isnot'            => __( 'is not', 'gp-populate-anything' ),
					'>'                => __( '>', 'gp-populate-anything' ),
					'>='               => __( '>=', 'gp-populate-anything' ),
					'<'                => __( '<', 'gp-populate-anything' ),
					'<='               => __( '<=', 'gp-populate-anything' ),
					'contains'         => __( 'contains', 'gp-populate-anything' ),
					'does_not_contain' => __( 'does not contain', 'gp-populate-anything' ),
					'starts_with'      => __( 'starts with', 'gp-populate-anything' ),
					'ends_with'        => __( 'ends with', 'gp-populate-anything' ),
					'like'             => __( 'is LIKE', 'gp-populate-anything' ),
					'is_in'            => __( 'is in', 'gp-populate-anything' ),
					'is_not_in'        => __( 'is not in', 'gp-populate-anything' ),
				),
				'chosen_no_results'                 => esc_attr( gf_apply_filters( array( 'gform_dropdown_no_results_text', 0 ), __( 'No results matched', 'gp-populate-anything' ), 0 ) ),
				'restrictedObjectTypeNonPrivileged' => esc_html__( 'This field is configured to an object type for which you do not have permission to edit.', 'gp-populate-anything' ),
				'restrictedObjectTypePrivileged'    => esc_html__( 'The selected Object Type is restricted. Non-super admins will not be able to edit this field\'s GPPA settings.', 'gp-populate-anything' ),
				'tooManyPropertyValues'             => esc_html__( 'Too many values to display.', 'gp-populate-anything' ),
			)
		);

	}

	public function register_object_type( $id, $class ) {
		$this->_object_types[ $id ] = new $class( $id );
	}

	public function get_object_type( $id, $field = null ) {
		$id_parts = explode( ':', $id );

		if ( $id_parts[0] === 'field_value_object' && $field ) {
			$field = GFFormsModel::get_field( $field['formId'], $id_parts[1] );

			return $this->get_object_type( rgar( $field, 'gppa-choices-object-type' ), $field );
		}

		return rgar( $this->_object_types, $id );
	}

	public function get_object_types() {
		return apply_filters( 'gppa_object_types', $this->_object_types );
	}

	public function get_primary_property( $field, $populate ) {
		static $primary_property_cache = array();

		$cache_key = rgar( $field, 'formId' ) . '_' . rgar( $field, 'id' ) . '_' . $populate;

		if ( isset( $primary_property_cache[ $cache_key ] ) ) {
			return $primary_property_cache[ $cache_key ];
		}

		$object_type_id = rgar( $field, 'gppa-' . $populate . '-object-type' );
		$id_parts       = explode( ':', $object_type_id );

		if ( $id_parts[0] === 'field_value_object' && $field ) {
			$field = GFFormsModel::get_field( $field['formId'], $id_parts[1] );

			// This assumes that only choice-populated fields can be Field Value Objects.
			return $this->get_primary_property( $field, 'choices' );
		}

		$primary_property = rgar( $field, 'gppa-' . $populate . '-primary-property' );

		$primary_property_cache[ $cache_key ] = $primary_property;

		return $primary_property;
	}

	/**
	 * @param $object_type_instance GPPA_Object_Type
	 * @param $field GF_Field
	 * @param $paged boolean Whether pagination is being used. If it is, the filter name and default will be different.
	 *
	 * @return mixed
	 */
	public function get_query_limit( $object_type_instance, $field = null, $paged = false ) {
		$filter_name = $paged ? 'gppa_query_limit_paged' : 'gppa_query_limit';
		$default     = $paged ? 51 : 501;

		if ( ! $field ) {
			return apply_filters(
				$filter_name,
				$default,
				$object_type_instance,
				$field
			);
		}

		return gf_apply_filters(
			array( $filter_name, rgar( $field, 'formId' ), rgar( $field, 'id' ) ),
			$default,
			$object_type_instance,
			$field
		);
	}

	/*
	 * Form Display
	 */

	/**
	 * Adds JS variable to gp-populate-anything script using wp_localize_script()
	 *
	 * Workaround for scripts in GFAddOn::scripts() not being registered until GFAddOn::enqueue_scripts() is called.
	 *
	 * Handles registering gp-populate-anything if it's not already registered that way wp_localize_scripts() does
	 * not return false due to the script not being registered.
	 *
	 * @param string $object_name
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function add_js_variable( $object_name, $value ) {
		if ( ! wp_script_is( 'gp-populate-anything', 'registered' ) ) {
			$scripts = $this->scripts();
			$script  = $scripts[ array_search( 'gp-populate-anything', array_column( $scripts, 'handle' ) ) ];

			wp_register_script( $script['handle'], $script['src'], $script['deps'], $script['deps'], $script['in_footer'] );
		}

		wp_localize_script( 'gp-populate-anything', $object_name, $value );
	}

	public function field_value_js( $form ) {

		if ( ! is_array( $form ) && GFCommon::is_form_editor() ) {
			return $form;
		}

		$form_fields          = rgar( $form, 'fields', array() );
		$has_gppa_field_value = false;
		$gppa_field_value_map = array( $form['id'] => array() );

		foreach ( $form_fields as $field ) {
			if ( ! $this->is_field_dynamically_populated( $field ) ) {
				continue;
			}

			$filter_groups = array_merge( rgar( $field, 'gppa-choices-filter-groups', array() ), rgar( $field, 'gppa-values-filter-groups', array() ) );

			if ( ! is_array( $filter_groups ) || ! count( $filter_groups ) ) {
				continue;
			}

			foreach ( $filter_groups as $filter_group ) {
				foreach ( $filter_group as $filter ) {
					$filter_value_exploded = explode( ':', rgar( $filter, 'value' ) );
					$dependent_fields      = array();

					if ( $filter_value_exploded[0] === 'gf_field' ) {
						$dependent_fields[] = $filter_value_exploded[1];
					} elseif ( preg_match_all( '/{\w+:gf_field_(\d+)}/', rgar( $filter, 'value' ), $field_matches ) ) {
						if ( count( $field_matches ) && ! empty( $field_matches[1] ) ) {
							$dependent_fields = $field_matches[1];
						}
					}

					if ( empty( $dependent_fields ) ) {
						continue;
					}

					$has_gppa_field_value = true;

					if ( ! isset( $gppa_field_value_map[ $form['id'] ][ $field->id ] ) ) {
						$gppa_field_value_map[ $form['id'] ][ $field->id ] = array();
					}

					foreach ( $dependent_fields as $dependent_field_id ) {
						$gppa_field_value_map[ $form['id'] ][ $field->id ][] = array(
							'gf_field' => $dependent_field_id,
							'property' => $filter['property'],
							'operator' => $filter['operator'],
						);
					}
				}
			}
		}

		if ( $has_gppa_field_value ) {
			$this->add_js_variable( "GPPA_FILTER_FIELD_MAP_{$form['id']}", $gppa_field_value_map );
		}

		return $form;

	}

	public function posted_value_js( $form ) {

		if ( ! rgar( $_POST, 'gform_submit' ) || ! is_array( $form ) ) {
			return $form;
		}

		$posted_values = array();

		foreach ( $_POST as $input_name => $input_value ) {
			$input_name = str_replace( '_', '.', str_replace( 'input_', '', $input_name ) );
			$field_id   = absint( $input_name );

			if ( ! $input_name ) {
				continue;
			}

			$field = GFFormsModel::get_field( $form, $field_id );

			if ( ! $this->is_field_dynamically_populated( $field ) ) {
				continue;
			}

			$posted_values[ $input_name ] = $input_value;
		}

		if ( ! count( $posted_values ) ) {
			return $form;
		}

		$this->add_js_variable( "GPPA_POSTED_VALUES_{$form['id']}", $posted_values );

		return $form;

	}

	public function field_value_object_js( $form ) {

		if ( GFCommon::is_form_editor() || ! is_array( $form ) ) {
			return $form;
		}

		$form_fields            = rgar( $form, 'fields', array() );
		$has_field_value_object = false;
		$field_value_object_map = array( $form['id'] => array() );

		foreach ( $form_fields as $field ) {
			if ( ! rgar( $field, 'gppa-values-enabled' ) || strpos( rgar( $field, 'gppa-values-object-type' ), 'field_value_object' ) !== 0 ) {
				continue;
			}

			$object_type_exploded   = explode( ':', rgar( $field, 'gppa-values-object-type' ) );
			$has_field_value_object = true;

			if ( ! isset( $field_value_object_map[ $form['id'] ][ $field->id ] ) ) {
				$field_value_object_map[ $form['id'] ][ $field->id ] = array();
			}

			$field_value_object_map[ $form['id'] ][ $field->id ][] = array(
				'gf_field' => $object_type_exploded[1],
			);
		}

		if ( $has_field_value_object ) {
			$this->add_js_variable( "GPPA_FIELD_VALUE_OBJECT_MAP_{$form['id']}", $field_value_object_map );
		}

		return $form;

	}

	/**
	 * Gets the filtered query args.
	 *
	 * @param GF_Field $field Current field to populate choices for.
	 * @param array|null $field_values Field values that are currently in the form to be used for Filter Values.
	 * @param string $populate What is being populated. Either 'choices', 'values'.
	 * @param null|int $page Supply a page if paginating results. Null will result in no pagination used. Pagination will
	 *    also decrease the query limit to a lower number to improve performance.
	 */
	public function get_query_args( $field, $field_values, $populate, $page = null ) {
		$gppa_prefix          = 'gppa-' . $populate . '-';
		$templates            = rgar( $field, $gppa_prefix . 'templates' );
		$object_type          = rgar( $field, $gppa_prefix . 'object-type' );
		$unique               = rgar( $field, $gppa_prefix . 'unique-results' );
		$object_type_instance = rgar( $this->_object_types, $object_type );

		if ( $unique === null || $unique === '' ) {
			$unique = true;
		}

		if ( ! $object_type_instance ) {
			return null;
		}

		$limit = $this->get_query_limit( $object_type_instance, $field, ! ! $page );

		/**
		 * With Populate Anything 2.0, we now only query 1 object at a time when populating values.
		 * This is to improve performance, but can break some snippets that rely on the existing behavior.
		 *
		 * This filter allows you to override the limit and bring back the existing behavior to query all objects
		 * when populating values.
		 *
		 * @param bool $populate_all_objects_for_value Whether to query all objects when populating values.
		 * @param GF_Field $field Current field to populate a value for.
		 * @param array $field_values Field values that are currently in the form to be used for Filter Values.
		 * @param GPPA_Object_Type $object_type Current Object Type instance.
		 * @param array $filter_groups Filter Groups to use for filtering objects.
		 * @param string $primary_property Primary Property to use for filtering objects.
		 * @param array $templates Templates to use when populating.
		 *
		 * @since 2.0
		 */
		$query_all_value_objects = gf_apply_filters(
			array( 'gppa_query_all_value_objects', rgar( $field, 'formId' ), rgar( $field, 'id' ) ),
			false,
			$field,
			$field_values,
			$object_type_instance,
			rgar( $field, $gppa_prefix . 'filter-groups' ),
			rgar( $field, $gppa_prefix . 'primary-property' ),
			$templates
		);

		/*
		 * If we're querying for a single object to populate a value, set the limit to 1 to save on querying.
		 *
		 * If the field supports populating multiple values, then do not modify the limit.
		 */
		if (
			$populate === 'values'
			&& ! in_array( rgar( $field, 'type' ), self::get_multi_selectable_choice_field_types(), true )
			&& ! in_array( rgar( $field, 'inputType' ), self::get_multi_selectable_choice_field_types(), true )
			&& ! $this->does_field_accept_json( $field )
			&& ! $query_all_value_objects
		) {
			$limit = 1;
		}

		/**
		 * Filter the arguments used when querying an Object Type for objects.
		 *
		 * @param array             $args        Query arguments array:
		 *                                       array(
		 *                                          string populate               What is being populated. Either 'choices', 'values'.
		 *                                          array  filter_groups          Filters for querying/fetching the objects.
		 *                                          array  ordering               Ordering settings for querying/fetching (includes 'orderby' and 'order').
		 *                                          array  templates              Templates to determine how choices/values will utilize the returned objects.
		 *                                          mixed  primary_property_value Current primary property value used for querying the objects. (Not all object types use primary properties.)
		 *                                          string field_values           Current field values used in query.
		 *                                          GF_Field field                Current field.
		 *                                          bool   unique                 Return only unique results.
		 *                                          int    page                   Which page of results to query.
		 *                                          int    limit                  Maximum number of results to return.
		 *                                       )
		 * @param \GF_Field         $field       The current field having its value or choices populated.
		 * @param string            $object_type The current GPPA object type (e.g. 'gf_entry').
		 * @param \GPPA_Object_Type $object_type The current GPPA object type instance.
		 *
		 * @since 1.2.14
		 */
		return gf_apply_filters( array( 'gppa_field_objects_query_args', rgar( $field, 'formId' ), rgar( $field, 'id' ) ), array(
			'populate'               => $populate,
			'filter_groups'          => rgar( $field, $gppa_prefix . 'filter-groups' ),
			'ordering'               => array(
				'orderby' => rgar( $field, $gppa_prefix . 'ordering-property' ),
				'order'   => rgar( $field, $gppa_prefix . 'ordering-method' ),
			),
			'templates'              => $templates,
			'primary_property_value' => rgar( $field, $gppa_prefix . 'primary-property' ),
			'field_values'           => $field_values,
			'field'                  => $field,
			'unique'                 => $unique,
			'page'                   => $page,
			'limit'                  => $limit,
		), $field, $object_type, $object_type_instance );
	}

	/**
	 * Gets a cache key for query args.
	 */
	public function get_query_cache_hash( $field, $field_values, $populate, $page = null ) {
		$gppa_prefix          = 'gppa-' . $populate . '-';
		$object_type          = rgar( $field, $gppa_prefix . 'object-type' );
		$object_type_instance = rgar( $this->_object_types, $object_type );

		if ( ! $object_type_instance ) {
			return null;
		}

		$args = $this->get_query_args( $field, $field_values, $populate, $page );

		/**
		 * Filter GPPA's query cache hash.
		 *
		 * Warning: This modifies how GPPA hashes queries for all types. Incorrect hashing may result
		 * in GPPA returning incorrect or stale results.
		 *
		 * Return `null` to disable query caching.
		 *
		 * @param string|false|null $query_cache_hash  Current hash of the query GPPA is about to execute.
		 * @param string $object_type       The current GPPA object type (e.g. 'gf_entry').
		 * @param array  $args              Query arguments array:
		 *        array(
		 *          array  filter_groups          Filters for querying/fetching the objects.
		 *          array  ordering               Ordering settings for querying/fetching (includes 'orderby' and 'order').
		 *          array  templates              Templates to determine how choices/values will utilize the returned objects.
		 *          mixed  primary_property_value Current primary property value used for querying the objects. (Not all object types use primary properties.)
		 *          string field_values           Current field values used in query.
		 *          int    page                   Which page of results to query.
		 *          int    limit                  Maximum number of results to return.
		 *        )
		 *
		 * @since 1.0.18
		 *
		 */
		$filtered = apply_filters( 'gppa_query_cache_hash', false, $object_type, $args );

		if ( $filtered !== false ) {
			return $filtered;
		}

		return $object_type_instance->query_cache_hash( $args );
	}

	/**
	 * Gets the objects for populating choices or values.
	 *
	 * @param GF_Field $field Current field to populate choices for.
	 * @param array|null $field_values Field values that are currently in the form to be used for Filter Values.
	 * @param null|string $populate What is being populated. Either 'choices', 'values'.
	 * @param null|int $page Supply a page if paginating results. Null will result in no pagination used. Pagination will
	 *    also decrease the query limit to a lower number to improve performance.
	 *
	 * @return array
	 */
	function get_field_objects( $field, $field_values, $populate, $page = null ) {
		$gppa_prefix = 'gppa-' . $populate . '-';
		$templates   = rgar( $field, $gppa_prefix . 'templates' );
		$object_type = rgar( $field, $gppa_prefix . 'object-type' );
		$unique      = rgar( $field, $gppa_prefix . 'unique-results' );
		$args        = $this->get_query_args( $field, $field_values, $populate, $page );

		if ( $unique === null || $unique === '' ) {
			$unique = true;
		}

		$object_type_instance = rgar( $this->_object_types, $object_type );

		// Abort if there are any bad or deprecated object types here.
		if ( empty( $object_type_instance ) ) {
			return array();
		}

		/**
		 * Store results in query cache before making them unique and once after.
		 * This ensures that identical unique queries that target different fields in their
		 * templates do not interfere with one another.
		 *
		 * This may end up using more memory but will ensure that we're always returning the most
		 * accurate results while utilizing caching for performance.
		 */
		$query_cache_hash  = $this->get_query_cache_hash( $field, $field_values, $populate, $page );
		$unique_cache_hash = ( $query_cache_hash ) ? $query_cache_hash . '_uniq' : null;

		$return_unique_results = gf_apply_filters( array( "gppa_object_type_{$object_type}_unique", $field['formId'], $field['id'] ), $unique );

		if ( $return_unique_results ) {
			if ( isset( $this->_field_objects_cache[ $unique_cache_hash ] ) ) {
				// Return unique cached results if found
				return $this->_field_objects_cache[ $unique_cache_hash ];
			} elseif ( $query_cache_hash && isset( $this->_field_objects_cache[ $query_cache_hash ] ) ) {
				// Otherwise check full cached results before making them unique to avoid a second query()
				$results = $this->_field_objects_cache[ $query_cache_hash ];
			} else {
				// If all fails, perform the query and cache it
				$results = $object_type_instance->query( $args, $field );
				if ( $query_cache_hash ) {
					// Store all results in cache
					$this->_field_objects_cache[ $query_cache_hash ] = $results;
				}
			}

			// Make results unique
			$results = $this->make_results_unique( $results, $field, $templates, $populate );

			if ( $unique_cache_hash ) {
				// Store unique results in cache
				$this->_field_objects_cache[ $unique_cache_hash ] = $results;
			}
		} else {
			// None unique query
			if ( $query_cache_hash && isset( $this->_field_objects_cache[ $query_cache_hash ] ) ) {
				// Return cached results if found
				return $this->_field_objects_cache[ $query_cache_hash ];
			}
			$results = $object_type_instance->query( $args, $field );
			if ( $query_cache_hash ) {
				$this->_field_objects_cache[ $query_cache_hash ] = $results;
			}
		}

		return $results;

	}

	public function make_results_unique( $results, $field, $templates, $populate ) {

		$unique_results = array();
		$checked_values = array();
		$template       = ! empty( $templates['label'] ) ? 'label' : 'value';

		foreach ( $results as $result ) {

			$result_checked_value = $this->process_template( $field, $template, $result, $populate, $results );

			// String comparison should be case-insensitive.
			if ( is_string( $result_checked_value ) ) {
				$result_checked_value = strtolower( $result_checked_value );
			}

			if ( array_search( $result_checked_value, $checked_values ) !== false ) {
				continue;
			}

			$checked_values[] = $result_checked_value;
			$unique_results[] = $result;

		}

		return $unique_results;

	}

	public function process_template( $field, $template_name, $object, $populate, $objects ) {

		static $_cache;

		$object_type_id   = rgar( $field, 'gppa-' . $populate . '-object-type' );
		$object_type      = $this->get_object_type( $object_type_id, $field );
		$templates        = rgar( $field, 'gppa-' . $populate . '-templates', array() );
		$primary_property = $this->get_primary_property( $field, $populate );
		$template         = rgar( $templates, $template_name );

		if ( ! $object_type ) {
			return null;
		}

		$object_id = $object_type->get_object_id( $object, $primary_property );

		/**
		 * Modify cache key for template processing as required.
		 *
		 * In some cases, it can be advantageous to relax the cache key to improve performance.
		 *
		 * @param string $cache_key Cache key to use
		 * @param \GF_Field $field The current field
		 * @param array $object The current object being processed into the template.
		 * @param string $template Current template being processed.
		 * @param string $template_name Name of template being processed.
		 * @param mixed|null|string $object_type Object type being used for template
		 * @param mixed|null|string $primary_property Primary property for field if set
		 *
		 * @since 1.0-beta-5.3
		 *
		 */
		$cache_key = apply_filters( 'gppa_process_template_cache_key', serialize(
			array(
				$template,
				$object_id ? $object_id : '',
				rgar( $field, 'id' ),
				$populate,
			)
		), $field, $object, $template, $template_name, $object_type, $primary_property );

		if ( isset( $_cache[ $cache_key ] ) ) {
			return $_cache[ $cache_key ];
		}

		if ( strpos( $template, 'gf_custom' ) === 0 ) {

			$template_value = $this->extract_custom_value( $template );

			if ( empty( $template_value ) ) {
				return null;
			}

			$_cache[ $cache_key ] = gf_apply_filters(
				array(
					'gppa_process_template',
					$template_name,
				),
				$template_value,
				$field,
				$template_name,
				$populate,
				$object,
				$object_type,
				$objects,
				$template
			);

			return $_cache[ $cache_key ];
		}

		if ( ! $template ) {
			return null;
		}

		$value = $object_type->get_object_prop_value( $object, $template );

		try {
			$_cache[ $cache_key ] = gf_apply_filters(
				array(
					'gppa_process_template',
					$template_name,
				),
				$value,
				$field,
				$template_name,
				$populate,
				$object,
				$object_type,
				$objects,
				$template
			);

			return $_cache[ $cache_key ];
		} catch ( Exception $e ) {
			return null;
		}

	}

	public function replace_template_count_merge_tags( $template_value, $field, $template, $populate, $object, $object_type, $objects ) {

		return str_replace( '{count}', count( $objects ), $template_value );

	}

	public function replace_no_results_template_count_merge_tags( $value, $field, $form, $templates ) {
		if ( rgar( $templates, 'value' ) === 'gf_custom:{count}' ) {
			$value = 0;
		}
		return $value;
	}

	/**
	 * Convert WP_Error in templates to null to prevent downstream errors with methods such as str_replace() without
	 * needing to put an is_wp_error() check in every filter that's added to gppa_process_template
	 *
	 * WP_Error's can be returned by WordPress if trying to fetch from a taxonomy that does not
	 * exist (among other situations) which can cause the whole form to break rather than just the field.
	 */
	public function convert_wp_error_in_template_to_null( $template_value ) {
		if ( is_wp_error( $template_value ) ) {
			return null;
		}

		return $template_value;
	}

	/**
	 * Convert array values to text for value population.
	 *
	 * @param string|string[] $template_value
	 * @param \GF_Field $field
	 * @param string $template_name
	 * @param string $populate
	 * @param mixed $object
	 * @param GPPA_Object_Type $object_type
	 * @param mixed[] $objects
	 * @param string $template
	 *
	 * @return string
	 */
	public function maybe_convert_array_value_to_text( $template_value, $field, $template_name, $populate, $object, $object_type, $objects, $template ) {

		/**
		 * We only want to convert away from JSON/array if the current field can not display the data in a way that makes
		 * sense to the user.
		 *
		 * Without the conditional below, checkboxes and multi-selects may not repopulate correctly.
		 */
		if (
			(
				( isset( $field->choices ) && is_array( $field->choices ) && in_array( $field->type, self::get_multi_selectable_choice_field_types(), true ) )
				|| rgar( $field, 'storageType' ) === 'json'
			)
			&& $populate === 'values'
		) {
			return $template_value;
		}

		if ( self::is_json( $template_value ) ) {
			return apply_filters( 'gppa_array_value_to_text', $template_value, json_decode( $template_value, ARRAY_A ), $field, $object, $object_type, $objects, $template );
		}

		if ( is_array( $template_value ) ) {
			return apply_filters( 'gppa_array_value_to_text', $template_value, $template_value, $field, $object, $object_type, $objects, $template );
		}

		return $template_value;

	}

	/**
	 * If we're populating a value for a multiselect field, utilize GF_Field_MultiSelect::to_array() which has its
	 * own logic for splitting a string into an array.
	 *
	 * Throughout Populate Anything, we will convert comma+space delimited lists to arrays, but not just a comma. We do
	 * this to protect against splitting numbers.
	 *
	 * However, GF_Field_MultiSelect::to_array() is a little less picky so let's use it if the field is a multi-select
	 * to stay consistent with how the field works.
	 *
	 * @return mixed
	 */
	public function maybe_convert_multiselect_text_to_array( $template_value, $field, $template_name, $populate, $object, $object_type, $objects, $template ) {
		if ( $populate !== 'values' ) {
			return $template_value;
		}

		// Do not continue with empty array as an empty array can cause some memory issues at this point.
		if ( rgblank( $template_value ) || ( is_array( $template_value ) && empty( $template_value ) ) ) {
			return '';
		}

		if ( ! is_a( $field, 'GF_Field_MultiSelect' ) || ! method_exists( $field, 'to_array' ) ) {
			return $template_value;
		}

		if ( ! is_string( $template_value ) ) {
			return $template_value;
		}

		if ( self::is_json( $template_value ) ) {
			return $template_value;
		}

		return $field->to_array( $template_value );
	}

	/**
	 * Default callback to use for gppa_array_value_to_text filter.
	 *
	 * @param $text_value string
	 * @param $array_value array
	 * @param $field
	 * @param $object
	 * @param $object_type
	 * @param $objects
	 *
	 * @return string
	 */
	public function use_commas_for_arrays( $text_value, $array_value, $field, $object, $object_type, $objects ) {
		return implode( ', ', $array_value );
	}

	public function prepare_gf_field_array_value_to_text( $text_value, $array_value, $field, $object, $object_type, $objects, $template ) {

		if ( ! $object_type || $object_type->id !== 'gf_entry' ) {
			return $text_value;
		}

		$field = GFAPI::get_field( $object->form_id, str_replace( 'gf_field_', '', $template ) );

		if ( $field ) {
			// JSON encode $array_value for file uploads. This fixes a PHP warning (see HS#25675)
			// Encoding all fields causes the default value of name fields to be comma separated (see HS#25880)
			$value_export = $field['type'] === 'fileupload' ? $field->get_value_export( json_encode( $array_value ) ) : $field->get_value_export( $array_value );
		} else {
			$value_export = '';
		}

		if ( $value_export ) {
			$text_value = $value_export;
		}

		return apply_filters( 'gppa_prepare_gf_field_array_value_to_text', $text_value, $array_value, $field, $object, $object_type, $template );

	}

	public function replace_template_object_merge_tags( $template_value, $field, $template, $populate, $object, $object_type ) {

		if ( ! is_string( $template_value ) ) {
			return $template_value;
		}

		$object_type_ids = wp_list_pluck( $this->get_object_types(), 'id' );

		$pattern = sprintf( '/{(%s):(.+?)(:(.+))?}/', implode( '|', array_merge( array( 'object' ), $object_type_ids ) ) );

		preg_match_all( $pattern, $template_value, $matches, PREG_SET_ORDER );
		foreach ( $matches as $match ) {
			list( $search, $tag, $prop, , $modifier ) = array_pad( $match, 5, null );

			$replace = $object_type->get_object_prop_value( $object, $prop );
			$replace = apply_filters( 'gppa_object_merge_tag_replacement_value', $replace, $object, $match );

			/**
			 * Allow fetching specific keys in an associative array using a merge tag in a Choice/Value template.
			 *
			 * @example {post:meta_example:key}
			 */
			if ( $modifier ) {
				/**
				 * PHP serialized data in meta will already be deserialized but JSON data will still need to be decoded
				 * at this point.
				 */
				$replace = self::maybe_decode_json( $replace );

				$replace = rgars( $replace, implode( '/', explode( ':', $modifier ) ) );
			}

			if ( is_array( $replace ) ) {
				$replace = $this->maybe_convert_array_value_to_text(
					$replace,
					$field,
					null,
					$populate,
					$object,
					$object_type,
					array( $object ),
					$template
				);

				$template_value = str_replace( $search, $replace, $template_value );
			} else {
				$template_value = str_replace( $search, $replace, $template_value );
			}
		}

		return $template_value;

	}

	/**
	 * Replace generic merge tags from Gravity Forms that don't require an entry
	 *
	 * @param $template_value
	 *
	 * @return mixed|void
	 */
	public function replace_template_generic_gf_merge_tags( $template_value ) {

		if ( ! is_string( $template_value ) ) {
			return $template_value;
		}

		if ( isset( $this->gf_merge_tags_cache[ $template_value ] ) ) {
			return $this->gf_merge_tags_cache[ $template_value ];
		}

		/**
		 * Check for existence of merge tags prior to trying to parse as replace_variables() can be expensive if there
		 * are a lot of entries to parse.
		 */
		if ( ! preg_match( gp_populate_anything()->live_merge_tags->merge_tag_regex, $template_value ) ) {
			return $template_value;
		}

		$result = GFCommon::replace_variables_prepopulate( $template_value, false, false, true );

		$this->gf_merge_tags_cache[ $template_value ] = $result;

		return $result;

	}

	/**
	 * GF 2.5 does not run GFCommon::to_money() on the frontend so we need to convert product field's prices to
	 * be formatted numbers with currency.
	 */
	public function maybe_add_currency_to_price( $template_value, $field, $template, $populate, $object, $object_type, $objects ) {
		if ( rgar( $field, 'type' ) !== 'product' || $template !== 'price' ) {
			return $template_value;
		}

		$template_exploded = explode( '.', $template );

		/**
		 * Price input should be .2
		 */
		if ( rgar( $template_exploded, 1 ) == 2 ) {
			return GFCommon::to_money( $template_value );
		}

		return $template_value;
	}

	/**
	 * @todo change wording from "dependent" to "dependency" for accuracy.
	 *
	 * @param $field
	 * @param $populate
	 *
	 * @return array
	 */
	public function get_dependent_fields_by_filter_group( $field, $populate ) {

		$gppa_prefix = 'gppa-' . $populate . '-';

		$filter_groups    = rgar( $field, $gppa_prefix . 'filter-groups' );
		$dependent_fields = array();

		if ( ! rgar( $field, $gppa_prefix . 'enabled' ) || ! $filter_groups ) {
			return $dependent_fields;
		}

		foreach ( $filter_groups as $filter_group_index => $filters ) {
			$dependent_fields[ $filter_group_index ] = array();

			foreach ( $filters as $filter ) {
				$filter_value = rgar( $filter, 'value' );

				if ( preg_match_all( '/{\w+:gf_field_(\d+)}/', $filter_value, $field_matches ) ) {
					if ( count( $field_matches ) && ! empty( $field_matches[1] ) ) {
						$dependent_fields[ $filter_group_index ] = array_merge( $dependent_fields[ $filter_group_index ], $field_matches[1] );
					}
				} elseif ( strpos( $filter_value, 'gf_field:' ) === 0 ) {
					$dependent_fields[ $filter_group_index ][] = str_replace( 'gf_field:', '', $filter_value );
				}
			}

			if ( ! count( $dependent_fields[ $filter_group_index ] ) ) {
				unset( $dependent_fields[ $filter_group_index ] );
			}
		}

		return $dependent_fields;

	}

	public function has_empty_field_value( $field, $populate, $entry = false ) {

		$form = GFAPI::get_form( $field->formId );
		if ( ! $form ) {
			return false;
		}

		$field_values               = $entry ? $entry : $this->get_posted_field_values( $form );
		$dependency_fields_by_group = $this->get_dependent_fields_by_filter_group( $field, $populate );
		$result                     = null;

		if ( count( $dependency_fields_by_group ) === 0 ) {
			$result = false;
		}

		if ( $result === null ) {
			foreach ( $dependency_fields_by_group as $dependency_field_group_index => $dependency_field_ids ) {
				$group_requirements_met = true;

				foreach ( $dependency_field_ids as $dependency_field_id ) {
					if ( ! $this->has_field_value( $dependency_field_id, $field_values ) ) {
						$group_requirements_met = false;

						break;
					}
				}

				if ( $group_requirements_met ) {
					$result = false;
					break;
				}
			}
		}

		/**
		 * If the checks above didn't see that there are no filters or find a filter group that has all of its values,
		 * then there is a missing field filter value.
		 */
		if ( $result === null ) {
			$result = true;
		}

		/**
		 * Filter whether a field has missing field filter values.
		 *
		 * Note, this filter's name is close to `gppa_has_empty_field_value` which is for filtering the value of a field
		 * with a dynamically populated value if this method returns `true`.
		 *
		 * @param boolean $has_empty_field_filter_value Whether the current fields has missing field filter values.
		 * @param \GF_Field $field Current field.
		 * @param array $form Current form.
		 * @param array $field_values Current field values.
		 * @param array $dependency_fields_by_group Fields that are depended upon in the current field's filters.
		 *
		 * @since 1.2.20
		 */
		return gf_apply_filters( array( 'gppa_has_empty_field_filter_value', $form['id'], $field->id ), $result, $field, $form, $field_values, $dependency_fields_by_group );

	}

	public function has_field_value( $field_id, $field_values ) {
		return ! $this->is_empty( $this->get_field_value_from_field_values( $field_id, $field_values ) );
	}

	/**
	 * Get the value of a given field from the passed array of field values.
	 *
	 * Multi-input fields store each inputs value in a decimal format (e.g. 1.1, 1.2). If the target field is 1, we
	 * should return all 1.x values.
	 *
	 * @param string|float|int $field_id
	 * @param array $field_values
	 *
	 * @return bool|string
	 */
	public function get_field_value_from_field_values( $field_id, $field_values ) {

		$is_input_specific = (int) $field_id != $field_id;
		$value             = '';

		// Return input-specific values without any fanfare (e.g. 1.2).
		if ( $is_input_specific ) {
			return rgar( $field_values, $field_id, null );
		}

		// If the target field ID is for a multi-input field (e.g. Checkbox), we want to get all input values for this field.
		foreach ( $field_values as $input_id => $field_value ) {

			$input_field_id = (int) $input_id;

			if ( $input_field_id == $field_id ) {
				// If input field ID does not match the input ID, we know that the current value is for a specific-input.
				// Let's collect all input values as an array.
				if ( $input_field_id != $input_id ) {
					if ( ! is_array( $value ) ) {
						$value = array();
					}
					$value[] = $field_value;
					// Otherwise, we are targeting a single-input field's value. There should only be one field value so we can break the loop.
				} else {
					$value = $field_value;
					break;
				}
			}
		}

		return $value;
	}

	public function extract_custom_value( $value ) {
		return preg_replace( '/^gf_custom:?/', '', $value );
	}

	/**
	 * @param $value
	 *
	 * empty can't be used on its own because it's a language construct
	 *
	 * @return bool
	 */
	public function is_empty( $value ) {
		return empty( $value ) && $value !== 0 && $value !== '0';
	}

	/**
	 * Gets the choices for a dynamically populated field. Also used for GP Advanced Select AJAX results.
	 *
	 * @param GF_Field $field Current field to populate choices for.
	 * @param array|null $field_values Field values that are currently in the form to be used for Filter Values.
	 * @param boolean $include_object Whether the object associated with the choice should be included with each choice.
	 * @param null|int $page Supply a page if paginating results. Null will result in no pagination used. Pagination will
	 *    also decrease the query limit to a lower number to improve performance.
	 *
	 * @return array
	 */
	public function get_input_choices( $field, $field_values = null, $include_object = true, $page = null ) {

		$templates = rgar( $field, 'gppa-choices-templates', array() );

		if ( ! rgar( $field, 'gppa-choices-enabled' ) || ! rgar( $field, 'gppa-choices-object-type' ) || ! rgar( $templates, 'label' ) || ! rgar( $templates, 'value' ) ) {
			return $field->choices;
		}

		$cache_key = $field['formId'] . '-' . $field['id'] . '-' . $this->get_query_cache_hash( $field, $field_values, 'choices', $page );

		if ( isset( $this->_field_choices_cache[ $cache_key ] ) ) {
			return $this->_field_choices_cache[ $cache_key ];
		}

		/* Force field to use both value and text */
		$field->enableChoiceValue = true;

		if ( $this->has_empty_field_value( $field, 'choices', $field_values ) ) {
			// This seems to break placeholders when the source is CPT-UI
			// Yet doesn't seem to affect GPPA. Leaving code in for posterity.
			//$field->placeholder = null;

			return array(
				array(
					// Unchecked checkboxes need to have a non-empty value otherwise they will automatically be checked by GF.
					'value'           => apply_filters( 'gppa_missing_filter_value', $field->get_input_type() === 'checkbox', $field ),
					'text'            => apply_filters( 'gppa_missing_filter_text', '&ndash; ' . esc_html__( 'Fill Out Other Fields', 'gp-populate-anything' ) . ' &ndash;', $field ),
					/*
					 * We only want our instructive text to be selected for Drop Downs. This bit below is necessary because
					 * Product Drop Downs do not have an empty value so the first option is not selected automatically.
					 * This also overrides placeholders for any Drop Down field.
					 */
					'isSelected'      => $field->inputType === 'select',
					'gppaErrorChoice' => 'missing_filter',
					'object'          => null,
				),
			);
		}

		$objects = $this->get_field_objects( $field, $field_values, 'choices', $page );

		if ( count( $objects ) === 0 ) {

			$choices = array(
				array(
					// Unchecked checkboxes need to have a non-empty value otherwise they will automatically be checked by GF.
					'value'           => apply_filters( 'gppa_no_choices_value', $field->get_input_type() === 'checkbox', $field ),
					'text'            => apply_filters( 'gppa_no_choices_text', '&ndash; ' . esc_html__( 'No Results', 'gp-populate-anything' ) . ' &ndash;', $field ),
					'isSelected'      => false,
					'gppaErrorChoice' => 'no_choices',
					'object'          => null,
				),
			);

		} else {

			$choices = array();

			foreach ( $objects as $object_index => $object ) {
				$value = $this->process_template( $field, 'value', $object, 'choices', $objects );
				$text  = $this->process_template( $field, 'label', $object, 'choices', $objects );

				if ( rgblank( $value ) && rgblank( $text ) ) {
					continue;
				}

				$choice = array(
					'value' => $value,
					'text'  => $text,
				);

				if ( rgar( $templates, 'price' ) ) {
					$choice['price'] = $this->process_template( $field, 'price', $object, 'choices', $objects );
				}

				if ( $include_object ) {
					$choice['object'] = $object;
				}

				/**
				 * Modify the choice to be populated into the current field.
				 *
				 * @since 1.0-beta-4.116
				 *
				 * @param array     $choice  The current choice being modified.
				 * @param \GF_Field $field   The current field being populated.
				 * @param array     $object  The current object being populated into the choice.
				 * @param array     $objects An array of objects being populated as choices into the field.
				 */
				$choices[] = gf_apply_filters( array( 'gppa_input_choice', $field->formId, $field->id ), $choice, $field, $object, $objects );
			}
		}

		/**
		 * Modify the choices to be populated into the current field.
		 *
		 * @since 1.0-beta-4.36
		 *
		 * @param array     $choices An array of Gravity Forms choices.
		 * @param \GF_Field $field   The current field being populated.
		 * @param array     $objects An array of objects being populated as choices into the field.
		 */
		$choices = gf_apply_filters( array( 'gppa_input_choices', $field->formId, $field->id ), $choices, $field, $objects );

		$this->_field_choices_cache[ $cache_key ] = $choices;

		return $choices;

	}

	/**
	 * Handles marking isSelected on fields with dynamic value population where multiple choices can be selected.
	 *
	 * Trello card #626
	 * https://secure.helpscout.net/conversation/870244683/12421
	 *
	 * @param $field
	 * @param array|null $field_values
	 *
	 * @see GP_Populate_Anything::get_selected_choices()
	 *
	 * @return mixed
	 */
	public function maybe_select_choices( $field, $field_values = null ) {

		$values_to_select = $this->get_selected_choices( $field, $field_values );

		if ( $values_to_select === null ) {
			return $field->choices;
		}

		foreach ( $field->choices as &$choice ) {
			if ( in_array( $choice['value'], $values_to_select ) ) {
				$choice['isSelected'] = true;
			}
		}

		return $field->choices;

	}

	/**
	 * @param $field
	 * @param null $field_values
	 *
	 * @see GP_Populate_Anything::maybe_select_choices()
	 *
	 * @return array|null
	 */
	public function get_selected_choices( $field, $field_values = null ) {

		$templates = rgar( $field, 'gppa-values-templates', array() );

		if (
			! in_array( $field->type, self::get_multi_selectable_choice_field_types(), true )
			&& ! in_array( $field->inputType, self::get_multi_selectable_choice_field_types(), true )
		) {
			return null;
		}

		if ( ! rgar( $field, 'gppa-values-enabled' ) || ! rgar( $field, 'gppa-values-object-type' ) || ! rgar( $templates, 'value' ) ) {
			return null;
		}

		if ( strpos( rgar( $field, 'gppa-values-object-type' ), 'field_value_object' ) === 0 ) {
			$object_type_split           = explode( ':', rgar( $field, 'gppa-values-object-type' ) );
			$field_value_object_field_id = $object_type_split[1];
			$field_value_object_field    = GFFormsModel::get_field( $field->formId, $field_value_object_field_id );
			$objects                     = array();

			// If no choices are selected in the dynamically populated field
			// do not hydrate the field value object with all the entries.
			if ( ! rgblank( rgar( $field_values, $field_value_object_field_id ) ) ) {
				$choice_value_template = rgars( $field_value_object_field, 'gppa-choices-templates/value' );
				$fvo_field_value       = rgars( $GLOBALS, 'gppa-field-values/' . $field_value_object_field->formId . '/' . $field_value_object_field->id );

				$form             = GFAPI::get_form( $field->formId );
				$selected_choices = $this->get_specific_choices( $form, $field_value_object_field, $choice_value_template, $fvo_field_value, $field_values );

				$objects = wp_list_pluck( $selected_choices, 'object' );
			}
		} else {
			$objects = $this->get_field_objects( $field, $field_values, 'values' );
		}

		$values_to_select = array();

		foreach ( $objects as $object ) {
			$object_processed = $this->process_template( $field, 'value', $object, 'values', $objects );

			if ( ! is_array( $object_processed ) ) {
				// This will be an array when the top-level field is selected but it will be a string when a specific input is selected.
				$decoded = GFAddOn::maybe_decode_json( $object_processed );
				if ( $decoded !== null ) {
					$object_processed = $decoded;
				}

				/**
				 * Convert comma separated values to an array if it's still not an array. We check for a comma then
				 * space as this is what's added by GP_Populate_Anything::use_commas_for_arrays() on the
				 * `gppa_array_value_to_text` filter.
				 */
				if ( ! is_array( $object_processed ) && strpos( $object_processed, ', ' ) ) {
					$object_processed = array_map( 'trim', explode( ', ', $object_processed ) );
				}
			}

			if ( is_array( $object_processed ) ) {
				$values_to_select = array_unique( array_merge( $object_processed, $values_to_select ) );
			} else {
				$values_to_select[] = $object_processed;
			}
		}

		if ( $field->type === 'checkbox' ) {

			$values_to_select_by_input = array();
			$choice_number             = 0;

			foreach ( $field->choices as $choice ) {
				$choice_number++;

				// Hack to skip numbers ending in 0, so that 5.1 doesn't conflict with 5.10. From class-gf-field-checkbox.php
				if ( $choice_number % 10 == 0 ) {
					$choice_number ++;
				}

				$input = $field->id . '.' . $choice_number;

				if ( in_array( $choice['value'], $values_to_select ) ) {
					$values_to_select_by_input[ $input ] = $choice['value'];
				}
			}

			return $values_to_select_by_input;
		}

		return array_values( $values_to_select );

	}

	public function ajax_get_query_results() {

		if ( ! GFCommon::current_user_can_any( array( 'gravityforms_edit_forms' ) ) ) {
			wp_die( -1 );
		}

		check_ajax_referer( 'gppa', 'security' );

		global $wpdb;
		$wpdb->suppress_errors();

		$field_settings = json_decode( stripslashes( rgar( $_POST, 'fieldSettings' ) ), true );
		$template_rows  = rgar( $_POST, 'templateRows' );
		$populate       = rgar( $_POST, 'gppaPopulate' );

		$gppa_prefix          = 'gppa-' . $populate . '-';
		$object_type          = rgar( $field_settings, $gppa_prefix . 'object-type' );
		$object_type_instance = rgar( $this->_object_types, $object_type );

		if ( ! is_subclass_of( $object_type_instance, 'GPPA_Object_Type' ) ) {
			wp_die( - 1 );
		}

		if ( $object_type_instance->is_restricted() && ! is_super_admin() ) {
			wp_die( - 1 );
		}

		$objects = $this->get_field_objects( $field_settings, null, $populate );

		$preview_results = array(
			'results' => array(),
			'limit'   => gp_populate_anything()->get_query_limit( $object_type_instance, $field_settings ),
		);

		foreach ( $objects as $object_index => $object ) {
			$row = array();

			foreach ( $template_rows as $template_row ) {
				$template_label = rgar( $template_row, 'label', '(Unknown Property)' );
				$template       = rgar( $template_row, 'id' );

				if ( ! $template ) {
					continue;
				}

				$value = $this->process_template( $field_settings, $template, $object, $populate, $objects );

				if ( is_array( $value ) ) {
					$row[ $template_label ] = '<code>' . esc_html( json_encode( $value ) ) . '</code>';
				} else {
					$row[ $template_label ] = esc_html( $value );
				}
			}

			$preview_results['results'][] = $row;
		}

		if ( $wpdb->last_error ) {
			wp_send_json( array( 'error' => $wpdb->last_error ) );
		}

		wp_send_json( $preview_results );

	}

	public function get_input_values( $field, $template = 'value', $field_values = null, $lead = null, $form = null ) {
		$templates = rgar( $field, 'gppa-values-templates', array() );

		if ( ! $form ) {
			$form = GFAPI::get_form( rgar( $_REQUEST, 'form-id' ) );
		}

		if ( ! rgar( $field, 'gppa-values-enabled' ) || ! rgar( $field, 'gppa-values-object-type' ) || ! rgar( $templates, $template ) ) {
			if ( $lead ) {
				$value = RGFormsModel::get_lead_field_value( $lead, $field );

				if ( ! empty( $field->inputs ) && is_array( $value ) ) {
					$value = rgar( $value, $template );
				}

				return $value;
			}

			return null;
		}

		if ( strpos( rgar( $field, 'gppa-values-object-type' ), 'field_value_object' ) === 0 ) {
			if ( ! $form ) {
				if ( $lead ) {
					return RGFormsModel::get_lead_field_value( $lead, $field );
				}

				return null;
			}

			$object_type_split           = explode( ':', rgar( $field, 'gppa-values-object-type' ) );
			$field_value_object_field_id = $object_type_split[1];
			$field_value_object_field    = GFFormsModel::get_field( $form, $field_value_object_field_id );

			$choice_value_template = rgars( $field_value_object_field, 'gppa-choices-templates/value' );

			$current_field_value = rgar( $field_values, $field_value_object_field_id );

			/**
			 * Update $current_field_value to work in the case that the field being populated from a Product dropdown
			 * which has a pipe delimiter to also include the price (which we don't want for value comparison).
			 */
			$current_field_value = $this->maybe_extract_value_from_product( $current_field_value, $field_value_object_field );

			if ( ! $choice_value_template ) {
				return null;
			}

			$object_type          = rgar( $field_value_object_field, 'gppa-choices-object-type' );
			$object_type_instance = rgar( $this->_object_types, $object_type );

			/**
			 * Documented in GP_Populate_Anything::get_query_args()
			 */
			$query_all_value_objects = stripos( rgar( $templates, $template ), '{count}' ) !== false;

			$query_all_value_objects = gf_apply_filters(
				array( 'gppa_query_all_value_objects', rgar( $field, 'formId' ), rgar( $field, 'id' ) ),
				$query_all_value_objects,
				$field,
				$field_values,
				$object_type_instance,
				rgar( $field_value_object_field, 'gppa-choices-filter-groups' ),
				rgar( $field_value_object_field, 'gppa-choices-primary-property' ),
				$templates
			);

			if ( ! $query_all_value_objects ) {
				$choices = $this->get_specific_choices( $form, $field_value_object_field, $choice_value_template, $current_field_value, $field_values );
			} else {
				$choices = gp_populate_anything()->get_input_choices( $field_value_object_field, $field_values );
			}

			if ( ! empty( $choices ) ) {
				$objects = wp_list_pluck( $choices, 'object' );

				foreach ( $choices as $choice ) {
					if ( $choice['value'] == $current_field_value ) {
						return $this->process_template( $field, $template, $choice['object'], 'values', $objects );
					}
				}
			}

			if ( ! isset( $objects ) ) {
				$objects = array();
			}

			$objects_in_value = array();

			/**
			 * Maybe the field value object field has multiple inputs (checkbox, etc).
			 *
			 * We could check for the presence of floats in $field_values prior to the foreach, but that'd likely
			 * require a loop of some type which defeats the purpose.
			 **/
			foreach ( $field_values as $input_id => $input_value ) {
				if ( absint( $input_id ) != $field_value_object_field_id ) {
					continue;
				}

				$input_value = $this->maybe_extract_value_from_product( $input_value, $field_value_object_field );

				if ( ! isset( $values ) ) {
					$values = array();
				}

				if ( ! $query_all_value_objects ) {
					$choices = $this->get_specific_choices( $form, $field_value_object_field, $choice_value_template, $input_value, $field_values );
				} else {
					$choices = gp_populate_anything()->get_input_choices( $field_value_object_field, $field_values );
				}

				$objects = wp_list_pluck( $choices, 'object' );

				if ( ! empty( $choices ) ) {
					foreach ( $choices as $choice ) {
						if ( $choice['value'] == $current_field_value ) {
							return $this->process_template( $field, $template, $choice['object'], 'values', $objects );
						}
					}
				}

				if ( empty( $choices ) ) {
					continue;
				}

				/**
				 * Field types where the inputs are scalar. This is specifically written for checkboxes but likely
				 * handles other inputs as well.
				 */
				if ( is_scalar( $input_value ) && ! rgars( $choices, '0/gppaErrorChoice' ) ) {
					foreach ( $choices as $choice ) {
						if ( $choice['value'] == $input_value ) {
							$objects_in_value[] = $choice['object'];

							$values[] = $this->process_template(
								$field,
								$template,
								$choice['object'],
								'values',
								$objects
							);
						}
					}
					/**
					 * Loops values that are arrays like the values from Multi Select fields
					 */
				} elseif ( is_array( $input_value ) ) {
					foreach ( $input_value as $value ) {
						foreach ( $choices as $choice ) {
							if ( $choice['value'] == $value ) {
								$objects_in_value[] = $choice['object'];

								$values[] = $this->process_template(
									$field,
									$template,
									$choice['object'],
									'values',
									$objects
								);
							}
						}
					}
				}
			}

			if ( isset( $values ) && is_array( $values ) ) {
				return apply_filters( 'gppa_array_value_to_text', $values, $values, $field, $objects_in_value, $this->get_object_type( $object_type_split[0] ), $objects, rgar( $templates, $template ) );
			}

			if ( $lead ) {
				return RGFormsModel::get_lead_field_value( $lead, $field );
			}

			return null;
		}

		if ( $this->has_empty_field_value( $field, 'values', $field_values ) ) {
			/**
			 * Modify the value of an input when its value is being populated dynamically and there is a field
			 * dependency that is not filled in. This will take priority over the field's Default Value.
			 *
			 * @since 1.0-beta-4.129
			 *
			 * @param mixed $value Field value
			 * @param \GF_Field $field The field that is having its value modified
			 * @param array $form The form that is having its field's value modified
			 * @param array $templates Value templates for the current field
			 */
			return gf_apply_filters( array( 'gppa_has_empty_field_value', $field->formId, $field->id ), null, $field, $form, $templates );
		}

		$objects = $this->get_field_objects( $field, $field_values, 'values' );

		if ( count( $objects ) === 0 ) {
			if ( $lead ) {
				$value = RGFormsModel::get_lead_field_value( $lead, $field );

				// If the value template is for an input, we need to get that from $value if it's an array.
				if ( is_array( $value ) && strpos( $template, '.' ) !== false && is_numeric( $template ) ) {
					return rgar( $value, $template );
				}

				return $value;
			}

			/**
			 * Modify the value of an input when no object results have been found. Note, the field's Default Value will
			 * be used if field dependencies have not been filled in.
			 *
			 * @since 1.0-beta-4.129
			 *
			 * @param mixed $value Field value
			 * @param \GF_Field $field The field that is having its value modified.
			 * @param array $form The form that is having its field's value modified
			 * @param array $templates Value templates for the current field
			 */
			return gf_apply_filters( array( 'gppa_no_results_value', $field->formId, $field->id ), null, $field, $form, $templates );
		}

		$values = $this->process_template( $field, $template, $objects[0], 'values', $objects );

		return gf_apply_filters(
			array( 'gppa_get_input_values', $field->formId, $field->id ),
			$values,
			$field,
			$template,
			$objects
		);

	}

	/**
	 * Gravity Forms product and option fields use values like "1|1" (1 being the value, 1 being the price). With GPPA,
	 * we need to extract out only the value for dynamic population.
	 *
	 * @param $value mixed
	 * @param $field GF_Field
	 *
	 * @return mixed
	 */
	public function maybe_extract_value_from_product( $value, $field ) {
		if ( GFCommon::is_product_field( $field->type ) ) {
			if ( is_string( $value ) ) {
				$value_bits = explode( '|', $value );

				return $value_bits[0];
			} elseif ( is_array( $value ) ) {
				foreach ( $value as $input_key => $input_value ) {
					$input_value_bits = explode( '|', $input_value );

					$value[ $input_key ] = $input_value_bits[0];
				}
			}
		}

		return $value;
	}

	/**
	 * @param GF_Field $field
	 * @param null|string $populate Whether to check if the field has any dynamic population, choices, or values populated. Null to check anything.
	 *
	 * @return bool
	 */
	public function is_field_dynamically_populated( $field, $populate = null ) {
		switch ( $populate ) {
			case 'choices':
				return rgar( $field, 'gppa-choices-enabled' );

			case 'values':
				return rgar( $field, 'gppa-values-enabled' );

			default:
				return rgar( $field, 'gppa-choices-enabled' ) || rgar( $field, 'gppa-values-enabled' );
		}
	}

	/**
	 * Gets the filter groups from a field.
	 *
	 * @param GF_Field $field
	 * @param null|string $populate Which filter groups to fetch. Either 'choices', 'values', or null for all.
	 *
	 * @return array
	 */
	public function get_filter_groups( $field, $populate = null ) {
		if ( ! $this->is_field_dynamically_populated( $field ) ) {
			return array();
		}

		switch ( $populate ) {
			case 'choices':
				return rgar( $field, 'gppa-choices-filter-groups' );

			case 'values':
				return rgar( $field, 'gppa-choices-filter-groups' );

			default:
				return array_merge( rgar( $field, 'gppa-choices-filter-groups', array() ), rgar( $field, 'gppa-values-filter-groups', array() ) );
		}
	}

	/**
	 * Loop through form fields to check if any field in the form uses dynamic population powered by Populate Anything.
	 *
	 * @param $form array Form to check for dynamic population
	 * @uses GP_Populate_Anything::is_field_dynamically_populated()
	 */
	public function form_has_dynamic_population( $form ) {
		$fields = rgar( $form, 'fields' );

		if ( empty( $fields ) ) {
			return false;
		}

		foreach ( $fields as $field ) {
			if ( $this->is_field_dynamically_populated( $field ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * The meat and potatoes of Populate Anything.
	 *
	 * Handles dynamically populating a fields choices and value(s). It will also determine the value of non-dynamic
	 * fields so the values can be used in field filters and Live Merge Tags.
	 *
	 * This method also handles replacing Live Merge Tags in field values.
	 *
	 * @param array|GF_Field $field
	 * @param array $form
	 * @param array $field_values
	 * @param array $entry
	 * @param boolean $force_use_field_value
	 * @param boolean $include_html
	 * @param boolean $run_pre_render
	 *
	 * @return array
	 */
	public function populate_field( $field, $form, $field_values, $entry = null, $force_use_field_value = false, $include_html = false, $run_pre_render = false ) {

		// Re-use $field reference if it's present. Breaking the reference can cause issues with validation.
		$field = is_subclass_of( $field, 'GF_Field' ) ? $field : GF_Fields::create( $field );

		/**
		 * Filter a field prior to hydration as a way to override how a field gets hydrated.
		 *
		 * @todo document
		 */
		$pre_hydrate_field = gf_apply_filters( array( 'gppa_pre_populate_field', $form['id'], $field->id ), null, $field, $form, $field_values, $entry, $force_use_field_value, $include_html, $run_pre_render );

		if ( $pre_hydrate_field !== null ) {
			return $pre_hydrate_field;
		}

		$field       = $this->populate_field_choices( $field, $field_values, $preselected_choice_value );
		$field_value = $this->populate_field_value( $field, $field_values, $form, $entry, $force_use_field_value );

		if ( in_array( $field->type, self::get_multi_selectable_choice_field_types(), true ) ) {
			$selected_choices_value = $this->get_selected_choices( $field, $field_values );
		}

		/**
		 * We need to get *all* populated values (include GF-populated values) in order to establish the most accurate
		 * state of the form when it is loaded.
		 *
		 * For multi-input fields, the $field_value will most often default to an empty array. Our $field_values may
		 * contain an input-specific value, so let's check for it. Currently, this is limited to Single Product fields
		 * because GF-dyn-pop Quantity is not fetched correctly by the get_value_default_if_empty() below.
		 */
		if ( rgblank( $field_value ) || ( is_array( $field_value ) && count( $field_value ) === 0 ) ) {
			if ( rgar( $field, 'gppa-values-enabled' ) || rgar( $field, 'gppa-choices-enabled' ) ) {
				if ( strpos( $field->inputName, 'gppa_' ) !== 0 ) {
					$dynamic_field_value = GFFormsModel::get_field_value( $field, $field_values );
				}
			}
		}

		if ( ( isset( $dynamic_field_value ) && ! rgblank( $dynamic_field_value ) ) && ! $force_use_field_value ) {
			$field_value = $dynamic_field_value;
		}

		if ( isset( $selected_choices_value ) && ! $force_use_field_value ) {
			$field_value = $selected_choices_value;
		}

		/**
		 * If still blank after pulling in populated choices/values, fallback to field value.
		 */
		if ( rgblank( $field_value ) && ! rgar( $field, 'gppa-values-enabled' ) && ! rgar( $field, 'gppa-choices-enabled' ) ) {
			$field_value = rgar( $field_values, $field->id );
		}

		if ( rgar( $_REQUEST, 'gravityview-meta' ) && isset( $field_values[ $field->id ] ) ) {
			$field_value = rgar( $field_values, $field->id );
		}

		/**
		 * If the field is a choice-based field with dynamic choices, ensure that the value is present in the choices
		 * in case the value filters/query is different from the choices.
		 */

		/**
		 * Filter if the hydrated value should be validated against the available choices if the choices are dynamically
		 * populated.
		 *
		 * @param bool $require_value_to_be_in_dynamic_choices Whether value will be checked against available dynamic choices. (default: `true`)
		 * @param \GF_Field $field Current field being populated.
		 * @param array $form Current form.
		 *
		 * @since 1.2.26
		 */
		$require_value_to_be_in_dynamic_choices = gf_apply_filters( array( 'gppa_require_value_to_be_in_dynamic_choices', $field->formId, $field->id ), true, $field, $form );

		if ( ! empty( $field->choices ) && is_array( $field->choices ) && rgar( $field, 'gppa-choices-enabled' ) && $require_value_to_be_in_dynamic_choices ) {
			$choice_values = wp_list_pluck( $field->choices, 'value' );

			$choice_values_with_price_removed = array_map( function( $value ) use ( $field ) {
				return $this->maybe_extract_value_from_product( $value, $field );
			}, $choice_values );

			if ( is_array( $field_value ) || ( rgar( $field, 'storageType' ) === 'json' && self::is_json( $field_value ) ) ) {
				/*
				 * We need to JSON decode multi-selects for this check. Additionally, we need to keep it JSON decoded
				 * so other fields relying on this field value can properly query by it.
				 */
				$field_value = self::maybe_decode_json( $field_value );

				foreach ( $field_value as $field_value_index => $individual_field_value ) {
					if ( ! in_array( $this->maybe_extract_value_from_product( $individual_field_value, $field ), $choice_values ) ) {
						unset( $field_value[ $field_value_index ] );
					}
				}
			} else {
				if ( ! rgblank( $field_value ) && ! in_array( $this->maybe_extract_value_from_product( $field_value, $field ), $choice_values ) ) {
					$field_value = null;
				}
			}
		}

		$field_value = $field->get_value_default_if_empty( $field_value );

		// Can't always rely on Gravity Forms default value.
		switch ( $field->get_input_type() ) {
			case 'singleproduct':
			case 'hiddenproduct':
				if ( rgblank( rgar( $field_value, "{$field->id}.1" ) ) ) {
					$field_value[ "{$field->id}.1" ] = $field->label;
				}
				if ( rgblank( rgar( $field_value, "{$field->id}.3" ) ) ) {
					$quantity_field = GFCommon::get_product_fields_by_type( $form, array( 'quantity' ), $field->id );
					if ( ! count( $quantity_field ) ) {
						// GF-populated Single Product Quantity input values are not correctly fetched via get_value_default_if_empty()
						// above. Let's get them from our $field_values array.
						$field_value[ "{$field->id}.3" ] = rgar( $field_values, "{$field->id}.3", $field->disableQuantity );
					}
				}
				break;
			case 'calculation':
				if ( rgblank( $field_value[ "{$field->id}.1" ] ) ) {
					$field_value[ "{$field->id}.1" ] = $field->label;
				}
				if ( rgblank( $field_value[ "{$field->id}.3" ] ) && $field->disableQuantity ) {
					$quantity_field = GFCommon::get_product_fields_by_type( $form, array( 'quantity' ), $field->id );
					if ( ! count( $quantity_field ) ) {
						// GF-populated Single Product Quantity input values are not correctly fetched via get_value_default_if_empty()
						// above. Let's get them from our $field_values array.
						$field_value[ "{$field->id}.3" ] = rgar( $field_values, "{$field->id}.3", $field->disableQuantity );
					}
				}
				// Attempt to calculate the original calculation so it can be rendered in LMTs on load. Not 100% confident in this...
				$fake_entry                      = $field_values;
				$fake_entry['currency']          = GFCommon::get_currency();
				$fake_entry['id']                = null;
				$fake_entry['form_id']           = $form['id'];
				$field_value[ "{$field->id}.2" ] = GFCommon::calculate( $field, $form, $fake_entry );
				break;
		}

		/**
		 * current-merge-tag-values is used to see if the field is stilled coupled to the live merge tags.
		 * @todo Add suppport for fields that return an array for their default value.
		 */
		$default_value = $field->get_value_default();
		$request_val   = rgar( rgar( $_REQUEST, 'current-merge-tag-values', array() ), ! is_array( $default_value ) ? $default_value : '' );

		$field_value = is_scalar( $field_value ) ? str_replace( "\r\n", "\n", $field_value ) : $field_value;
		$request_val = $request_val ? str_replace( "\r\n", "\n", $request_val ) : null;

		/**
		 * Added trim here to improve reliability of LMTs being in textareas. There were situations where the number of
		 * line breaks would not equal and cause LMTs to stop populating.
		 */
		if ( is_string( $field_value ) && $this->live_merge_tags ) {
			if ( $this->live_merge_tags->prepare_for_lmt_comparison( $field_value ) == $this->live_merge_tags->prepare_for_lmt_comparison( $request_val ) ) {
				$field_value = $default_value;
			}
		} else {
			if ( $field_value == $request_val ) {
				$field_value = $default_value;
			}
		}

		$form_id = rgar( $form, 'id' );

		/**
		 * Filter the field object after it has been hydrated.
		 *
		 * @since 1.0-beta-4.166
		 *
		 * @param \GF_Field $field The field object that has been hydrated.
		 * @param array     $form  The current form object to which the hydrated field belongs.
		 */
		$field = gf_apply_filters( array( 'gppa_hydrated_field', $form['id'], $field['id'] ), $field, $form );

		$hydrated_value = $field_value || $field_value === '0' ? $field_value : $preselected_choice_value;

		// Store hydrated value for use in other perks (currently GPRO)
		$field->gppa_hydrated_value = $hydrated_value;

		/**
		 * Filter whether gform_pre_render should be utilized when fetching the new markup for fields when
		 * populated via AJAX.
		 *
		 * While disabling gform_pre_render during AJAX can be helpful in some cases, consider it a workaround as it
		 * can have adverse effects on certain integrations.
		 *
		 * @param boolean $run_pre_render Whether or not to use gform_pre_render filter.
		 * @param \GF_Field $field The field that is being populated.
		 * @param array $form The current form.
		 *
		 * @since 1.0.6
		 */
		if ( gf_apply_filters( array( 'gppa_run_pre_render_in_ajax', $form['id'], $field['id'] ), $run_pre_render, $field, $form ) ) {
			$field = $this->run_pre_render_on_field( $field, $form, $field_values );
		}

		$result = array(
			'field'       => $field,
			'field_value' => $hydrated_value,
			'lead_id'     => rgar( $entry, 'id' ),
			'form_id'     => $form_id,
			'form'        => $form,
		);

		/**
		 * gppa_hydrate_field_html is used as a filter to receive many of the Live Merge Tag filters like the form does
		 * on initial load.
		 */
		if ( $include_html ) {
			/**
			 * gppa_hydrate_input_html is here to provide a filter with the same signature as gform_field_content as
			 * there isn't a comparable filter for inputs.
			 */
			/**
			 * @todo deprecate this filter in favor of "populate"
			 */
			$input_html     = apply_filters( 'gppa_hydrate_input_html', GFCommon::get_field_input( $field, $result['field_value'], rgar( $entry, 'id' ), $form_id, $form ), $field );
			$result['html'] = apply_filters( 'gppa_hydrate_field_html', $input_html, $form, $result, $field );
			$default_value  = $field->get_value_default(); // Cache default value
			/**
			 * Re-add the live merge tag value data attr if the field becomes uncoupled. This will allow re-coupling.
			 */
			if ( ! is_array( $default_value ) && preg_match( $this->live_merge_tags->live_merge_tag_regex, $default_value ) && $field_value !== $default_value ) {
				$result['html'] = preg_replace( $this->live_merge_tags->value_attr, 'data-gppa-live-merge-tag-value="' . esc_attr( $default_value ) . '" $0', $result['html'] );
			}
		}

		/**
		 * Convert field value from Live Merge tag to allow chaining with Form Field values.
		 */
		if ( is_scalar( $field->get_value_default() ) && preg_match( $this->live_merge_tags->live_merge_tag_regex, $field->get_value_default() ) ) {
			$result['field_value'] = $this->live_merge_tags->get_live_merge_tag_value( $result['field_value'], $form, $field_values );

			$GLOBALS['gppa-field-values'][ $form_id ][ $field->id ] = $result['field_value'];
		}

		return $result;

	}

	/**
	 * Handles getting the choice value from a choice and concatenating the price on if it's a choice-based
	 * product or option field.
	 *
	 * @param array $choice
	 * @param GF_Field $field
	 *
	 * @return string | null
	 */
	public function get_preselected_choice_value( $choice, $field ) {
		if ( ! rgar( $choice, 'value' ) ) {
			return null;
		}

		$choice_value = $choice['value'];

		if ( GFCommon::is_product_field( $field->type ) && rgar( $field, 'enablePrice' ) ) {
			$price         = rgempty( 'price', $choice ) ? 0 : GFCommon::to_number( rgar( $choice, 'price' ) );
			$choice_value .= '|' . $price;
		}

		return $choice_value;
	}

	/**
	 * Dynamically populates a field's choices and also provide the preselected choice value.
	 *
	 * @param GF_Field $field
	 * @param array $field_values
	 * @param mixed &$preselected_choice_value Modified parameter of the preselected choice value. Based on what
	 *   choices have isSelected and if none exist, it'll be the first choice. May not match the actual field value
	 *   if the value is dynamically populated.
	 *
	 * @return GF_Field
	 */
	public function populate_field_choices( $field, $field_values, &$preselected_choice_value ) {
		$preselected_choice_value = null;

		if ( $field->choices !== '' && isset( $field->choices ) ) {

			$field->choices = $this->get_input_choices( $field, $field_values );
			$field->choices = $this->maybe_select_choices( $field, $field_values );

			$field->gppaDisable = ! empty( $field->choices[0]['gppaErrorChoice'] );

			if ( $field->get_input_type() == 'checkbox' ) {
				$inputs = array();
				$index  = 1;

				foreach ( $field->choices as $choice ) {

					if ( $index % 10 == 0 ) {
						$index++;
					}

					$inputs[] = array(
						'id'    => sprintf( '%d.%d', $field->id, $index ),
						'label' => $choice['text'],
					);

					$index++;

				}

				$field->inputs = $inputs;
			}

			/**
			 * If there's a value pre-selected, use it as the preselected choice value.
			 */
			foreach ( $field->choices as $choice_index => $choice ) {

				if ( ! rgar( $choice, 'isSelected' ) ) {
					continue;
				}

				$choice_value = $this->get_preselected_choice_value( $choice, $field );

				if ( ! rgblank( $choice_value ) ) {
					// Choice-based fields with inputs (e.g. checkboxes) use individual input values rather than
					// an array for the checked values.
					if ( $field->inputs ) {
						if ( ! $preselected_choice_value ) {
							$preselected_choice_value = array();
						}

						$preselected_choice_value[ $field->inputs[ $choice_index ]['id'] ] = $choice_value;
						// If there are multiple pre-selections, make sure we capture them all in an array
					} else {
						if ( $preselected_choice_value ) {
							$preselected_choice_value   = ( is_array( $preselected_choice_value ) ) ? $preselected_choice_value : array( $preselected_choice_value );
							$preselected_choice_value[] = $choice_value;
						} else {
							$preselected_choice_value = $choice_value;
						}
					}
				}
			}

			/**
			 * Set preselected choice value to first choice if there is not a placeholder and there isn't a pre-selected
			 * choice above.
			 */
			if ( ! $preselected_choice_value && $field->get_input_type() === 'select' && count( $field->choices ) && ! rgblank( $field->choices[0]['value'] ) && ! $field->placeholder ) {
				$preselected_choice_value = $this->get_preselected_choice_value( $field->choices[0], $field );
			}
		}

		return $field;
	}

	/**
	 * Dynamically populates a field's value(s).
	 *
	 * @param GF_Field $field
	 * @param array $field_values
	 * @param array $form
	 * @param array $entry
	 * @param boolean $force_use_field_value
	 *
	 * @return mixed
	 */
	public function populate_field_value( $field, $field_values, $form, $entry, $force_use_field_value ) {
		if (
			$field->inputs
			&& ! in_array( $field->type, self::get_interpreted_multi_input_field_types(), true )
			// Treat email fields with confirmation as interpreted multi input
			&& ! ( $field->type === 'email' && rgar( $field, 'emailConfirmEnabled' ) )
		) {
			$field_value = array();

			if ( $force_use_field_value ) {
				foreach ( $field->inputs as $input ) {
					$field_value[ $input['id'] ] = rgar( $field_values, $input['id'] );
				}
			} else {
				foreach ( $field->inputs as $input ) {
					$value = $this->get_input_values( $field, $input['id'], $field_values, $entry, $form );

					if ( $value ) {
						$field_value[ $input['id'] ] = $value;
					}
				}
			}
		} else {
			/**
			 * This is here to force using the provided field values in instances like save and continue.
			 **/
			if ( $force_use_field_value ) {
				$field_value = rgar( $field_values, $field->id );
			} else {
				$field_value = $this->get_input_values( $field, 'value', $field_values, $entry, $form );
			}

			$filter_name = 'gppa_modify_field_value_' . $field->type;

			if ( has_filter( $filter_name ) ) {
				$field_value = apply_filters( $filter_name, $field_value, $field, $field_values );
			}
		}

		return $field_value;
	}


	/**
	 * Run gform_pre_render on a field to improve compatibility with other plugins and perks such as GP Limit Choices
	 *
	 * @param GF_Field $field
	 * @param array $form
	 * @param array $field_values
	 *
	 * return GF_Field
	 */
	public function run_pre_render_on_field( $field, $form, $field_values = array() ) {
		remove_filter( 'gform_pre_render', array( $this, 'populate_form' ), 8 );

		// Ensure that GFFormDisplay is loaded for `gform_pre_render` to function correctly
		if ( ! class_exists( 'GFFormDisplay' ) ) {
			require_once( GFCommon::get_base_path() . '/form_display.php' );
		}

		/**
		 * Pass field through gform_pre_render to improve compatibility with Perks like GPLC during AJAX
		 */
		$pseudo_form = gf_apply_filters(
			array( 'gform_pre_render', $form['id'], $field['id'] ),
			array_merge(
				$form,
				array(
					'fields' => array( $field ),
				)
			),
			true,
			$field_values
		);

		add_filter( 'gform_pre_render', array( $this, 'populate_form' ), 8, 3 );

		return $pseudo_form['fields'][0];
	}


	/**
	 * @param $form
	 *
	 * @deprecated 2.0 Use GP_Populate_Anything::populate_form()
	 *
	 * @return array
	 */
	public function hydrate_fields( $form, $entry = null ) {
		return $this->populate_form( $form, false, array(), $entry );
	}

	/**
	 * Run exported values through helper method to ensure that the choice label is used instead of the value.
	 *
	 * @param $value
	 * @param $form_id
	 * @param $field_id
	 * @param $entry
	 *
	 * @return mixed|string|null
	 */
	public function hydrate_export_value( $value, $form_id, $field_id, $entry ) {
		$field = GFAPI::get_field( $form_id, $field_id );

		return $this->get_submitted_choice_label( $value, $field, $entry['id'] );
	}

	public function get_posted_field_values( $form ) {

		// Ensure that we're parsing the correct Form's posted values
		$form_id         = intval( rgar( $form, 'id', 0 ) );
		$gform_submit_id = intval( rgar( $_POST, 'gform_submit', - 1 ) );
		$ajax_id         = intval( rgar( $_POST, 'form-id', - 1 ) );
		$parse_request   = ( $form_id === $gform_submit_id || $form_id === $ajax_id );

		$field_values = $this->get_prepopulate_values( $form, rgar( $this->prepopulate_fields_values, $form_id, array() ) );
		$field_values = array_replace( $field_values, $this->get_save_and_continue_values( rgar( $_REQUEST, 'gf_token' ) ) );

		if ( isset( $GLOBALS['gppa-field-values'][ $form_id ] ) ) {
			$field_values = array_replace( $field_values, rgar( $GLOBALS['gppa-field-values'], $form_id, array() ) );
		} elseif ( isset( $_REQUEST['field-values'] ) && $parse_request ) {
			$field_values = array_replace( $field_values, $this->get_field_values_from_request() );
		}

		if ( ! empty( $form['fields'] ) && is_array( $form['fields'] ) ) {
			foreach ( $form['fields'] as $field ) {
				$field_value = null;

				/**
				 * If value is directly posted, use it.
				 */
				if ( $parse_request && isset( $_POST[ "input_{$field->id}" ] ) ) {
					$field_value = rgpost( "input_{$field->id}" );

					/**
					 * Value is cached in runtime variable
					 */
				} elseif ( isset( $field_values[ $field->id ] ) ) {
					$field_value = $field_values[ $field->id ];

					/**
					 * Check for individually posted inputs for entire field
					 */
				} else {
					foreach ( $_POST as $posted_meta_key => $posted_meta ) {
						if ( strpos( $posted_meta_key, "input_{$field->id}_" ) !== 0 ) {
							continue;
						}

						$input_id = str_replace( "input_{$field->id}_", '', $posted_meta_key );

						if ( $field_value === null ) {
							$field_value = array();
						}

						$field_value[ $field->id . '.' . $input_id ]  = $posted_meta;
						$field_values[ $field->id . '.' . $input_id ] = $posted_meta;
					}

					if ( $field_value ) {
						continue;
					}
				}

				/**
				 * Ideally we'd like to use $field->get_value_submission() but it requires the submit $_POST value to be
				 * present. Setting that will likely cause unintended side-effects.
				 */
				if ( $field_value == 'gf_other_choice' ) {
					$other = $field->id . '_other';

					$field_value = isset( $field_values[ $other ] ) ? $field_values[ $other ] : rgpost( 'input_' . $other );
				}

				if ( $field_value || $field_value === '' || $field_value === '0' ) {
					$field_values[ $field->id ] = $field_value;
				}
			}
		}

		return count( $field_values ) ? $field_values : array();
	}

	public function get_prepopulate_values( $form, $field_values = array() ) {

		$prepopulate_values = array();

		if ( empty( $form['fields'] ) ) {
			return $prepopulate_values;
		}

		foreach ( $form['fields'] as $field ) {

			$input_type = $field->get_input_type();
			$inputs     = $field->get_entry_inputs();

			/**
			 * @note GP Nested Forms sets allowsPrepopulate to true on all fields in the child form.
			 */
			if ( $field->allowsPrepopulate ) {
				/* Skip over preopulate values from Populate Anything */
				if ( strpos( $field->inputName, 'gppa_' ) === 0 ) {
					continue;
				}

				/* Skip over list fields as RGFormsModel::get_parameter_value() will recurse and try to merge values indefinitely. */
				if ( $input_type === 'list' ) {
					continue;
				}

				if ( $input_type == 'checkbox' || $input_type == 'multiselect' ) {
					$prepopulate_values[ $field->id ] = RGFormsModel::get_parameter_value( $field->inputName, $field_values, $field );

					if ( ! is_array( $prepopulate_values[ $field->id ] ) ) {
						$prepopulate_values[ $field->id ] = explode( ',', $prepopulate_values[ $field->id ] );
					}
				} elseif ( is_array( $inputs ) ) {
					foreach ( $inputs as $input ) {
						$prepopulate_values[ $input['id'] ] = RGFormsModel::get_parameter_value( rgar( $input, 'name' ), $field_values, $field );
					}
				} else {
					$prepopulate_values[ $field->id ] = RGFormsModel::get_parameter_value( $field->inputName, $field_values, $field );
				}
			}
		}

		/**
		 * Filter the values that will be used as pre-populated values for Populate Anything. Common use-cases here are pulling in values from
		 * other perks such as Easy Passthrough, using values from parameters, etc.
		 *
		 * @param array $prepopulate_values  The prepopulated values.
		 * @param array $form                The current form.
		 *
		 * @since 1.2.15
		 */
		$this->prepopulate_fields_values[ $form['id'] ] = gf_apply_filters(
			array( 'gppa_prepopulate_field_values', $form['id'] ),
			array_replace( $field_values, array_filter( $prepopulate_values ) ),
			$form
		);

		return $this->prepopulate_fields_values[ $form['id'] ];

	}

	public function field_input_add_empty_field_value_filter( $html, $field, $value, $lead_id, $form_id ) {

		if ( GFCommon::is_form_editor() || ! $field->{'gppa-choices-enabled'} || ( ! $this->has_empty_field_value( $field, 'choices' ) && ! $this->has_empty_field_value( $field, 'values' ) ) ) {
			return $html;
		}

		$field_values = $this->get_field_values_from_request();

		$field_html_empty_field_value = gf_apply_filters(
			array(
				'gppa_field_html_empty_field_value',
				$field->type,
			),
			'',
			$field,
			$form_id,
			$field_values
		);

		$entry = $this->get_current_entry(); // Current entry if on Entry Details screen

		if ( ( $this->has_empty_field_value( $field, 'choices', $entry ) || $this->has_empty_field_value( $field, 'values', $entry ) ) && $field_html_empty_field_value ) {
			return '<div class="ginput_container">' . $field_html_empty_field_value . '</div>';
		}

		return $html;

	}

	/**
	 * Disable field if there are empty dependency field values for filters.
	 *
	 * @param string $field_content
	 * @param \GF_Field|null $field
	 *
	 * @return string
	 */
	public function field_content_disable_if_empty_field_values( $field_content, $field ) {
		if ( ! $field || GFCommon::is_entry_detail() ) {
			return $field_content;
		}

		if ( ! isset( $field->gppaDisable ) || $field->gppaDisable === false ) {
			return $field_content;
		}

		/**
		 * Only disable option's if not a product option field. This is due to gformGetPrice() relying on jQuery's
		 * .val() method which will return undefined for disabled options.
		 *
		 * HS #23575
		 */
		if ( $field->type !== 'option' ) {
			// Keep "Other" radio options enabled even if there are no GPPA results
			$field_content = preg_replace( '/ value=([\'"](?!other|gf_other_choice[\'"]))/i', ' disabled="true" selected value=$1', $field_content );
		}

		$field_content = str_replace( '<select ', '<select disabled="true" ', $field_content );
		$field_content = str_replace( '<textarea ', '<textarea disabled="true" ', $field_content );

		return $field_content;

	}

	/**
	 * Output max len counter script from core when field is replaced to re-add the counter.
	 *
	 * @todo Is there a more robust way we can output any init scripts registered by a field?
	 *
	 * @param $html string
	 * @param $field GF_Field
	 * @param $form array
	 * @param $fields array
	 * @param $entry_id number
	 * @param $hydrated_field array
	 */
	public function batch_field_html_maxlen_counter( $html, $field, $form, $fields, $entry_id, $hydrated_field ) {
		if ( ! class_exists( 'GFFormDisplay' ) ) {
			require_once( GFCommon::get_base_path() . '/form_display.php' );
		}

		/**
		 * Limit Form fields to only the one being replaced.
		 */
		$form['fields'] = array_filter( $form['fields'], function ( $field ) use ( $hydrated_field ) {
			return $field->id === rgars( $hydrated_field, 'field/id' );
		} );

		$html .= '<script type="text/javascript">' . GFFormDisplay::get_counter_init_script( $form ) . '</script>';

		return $html;
	}

	public function radio_field_html_empty_field_value( $text, $field ) {
		return apply_filters( 'gppa_missing_filter_text', '<p>' . esc_html__( 'Please fill out other fields.', 'gp-populate-anything' ) . '</p>', $field );
	}

	/**
	 * Since the choices for a field do not exist on the field object after submission, we use the gppa_choices meta
	 * that's included during entry submission.
	 *
	 * This is favorable over always filtering gform_form_meta as always filtering gform_form_meta can cause
	 * unintended consequences.
	 *
	 * @param $value
	 * @param $field
	 * @param $entry_id
	 *
	 * @return mixed|string|null
	 */
	public function get_submitted_choice_label( $value, $field, $entry_id ) {
		if ( ! rgar( $field, 'gppa-choices-enabled' ) ) {
			return $value;
		}

		$meta    = gform_get_meta( $entry_id, 'gppa_choices' );
		$choices = rgar( $meta, $field['id'], array() );

		$label = rgar( $choices, $value, $value );

		/**
		 * Filter the submitted choice label.
		 *
		 * Populate Anything shows choice labels instead of values in backend contexts as dynamically populated choice
		 * values are frequently non-human-readable values such as IDs, slugs, etc.
		 *
		 * @param string $label The submitted choice label.
		 * @param string $value The submitted choice value.
		 * @param GF_Field $field The field object.
		 * @param int $entry_id The entry ID.
		 *
		 * @since 2.0.5
		 */
		return gf_apply_filters( array( 'gppa_submitted_choice_label', $field->formId, $field->id ), $label, $value, $field, $entry_id );
	}

	/**
	 * Queries for specific choices (usually 1 unless value is array) based on a definitive value.
	 *
	 * Used frequently for field value objects. We used to re-query all the choices and loop through them until there
	 * is a value match.
	 *
	 * @param array $form The current form.
	 * @param GF_Field $choice_field Field to query the choice from.
	 * @param string $property Property to search for. The operator used will be "is" for non-array and "is in" for arrays.
	 * @param mixed $value Value to search by.
	 * @param array $field_values
	 *
	 * @return array|null
	 */
	public function get_specific_choices( $form, $choice_field, $property, $value, $field_values ) {
		$modify_query_args = function ( $query_args ) use ( $form, $choice_field, $property, $value ) {
			if ( ! $property || rgblank( $value ) ) {
				return $query_args;
			}

			$explicit_match_filter = array(
				'property' => $property,
				'operator' => ! is_array( $value ) ? 'is' : 'is_in',
				'value'    => $value,
			);

			if ( ! empty( $query_args['filter_groups'] ) ) {
				foreach ( $query_args['filter_groups'] as &$filters ) {
					/*
					 * Add a new filter to each group that takes the provided property and value and searches for
					 * it using an IS operator.
					 */
					$filters[] = $explicit_match_filter;
				}
			} else {
				$query_args['filter_groups'] = array(
					array(
						$explicit_match_filter,
					),
				);
			}

			return apply_filters( 'gppa_specific_choice_query_args', $query_args, $form, $choice_field, $property, $value );
		};

		add_filter( 'gppa_field_objects_query_args', $modify_query_args );
		$choices = gp_populate_anything()->get_input_choices( $choice_field, $field_values );
		remove_filter( 'gppa_field_objects_query_args', $modify_query_args );

		if ( ! empty( $choices ) ) {
			return $choices;
		}

		return null;
	}


	public function entry_field_value( $display_value, $field, $lead, $form ) {
		return $this->get_submitted_choice_label( $display_value, $field, $lead['id'] );
	}

	public function use_choice_label_for_products( $product_info, $form, $entry ) {
		if ( empty( $product_info['products'] ) ) {
			return $product_info;
		}

		foreach ( $product_info['products'] as $field_id => &$product ) {
			$field = GFAPI::get_field( $form, $field_id );

			if ( ! rgar( $field, 'gppa-choices-enabled' ) ) {
				continue;
			}

			$entry_value    = RGFormsModel::get_lead_field_value( $entry, $field );
			list( $value, ) = explode( '|', $entry_value );

			$product['name'] = $this->get_submitted_choice_label( $value, $field, rgar( $entry, 'id' ) );
		}

		return $product_info;
	}

	public function entries_field_value( $value, $form_id, $field_id, $entry ) {
		$form  = GFAPI::get_form( $form_id );
		$field = GFFormsModel::get_field( $form, $field_id );

		return $this->get_submitted_choice_label( $value, $field, $entry['id'] );
	}

	public function maybe_save_choice_label( $value, $entry, $field, $form ) {

		if ( ! rgar( $field, 'gppa-choices-enabled' ) ) {
			return $value;
		}

		// In some cases gform_save_field_value may be called without an $entry ID present. We need the entry ID to save
		// the meta.
		if ( ! rgar( $entry, 'id' ) ) {
			return $value;
		}

		/**
		 * gppa_choices is a legacy meta_key. It's not as descriptive as the variable below but it's kept for backwards
		 * compatibility.
		 */
		$gppa_choice_labels = gform_get_meta( $entry['id'], 'gppa_choices' );

		if ( ! is_array( $gppa_choice_labels ) ) {
			$gppa_choice_labels = array();
		}

		if ( empty( $this->_hydrated_fields_on_submission_cache[ $form['id'] . '-' . $field['id'] ] ) ) {
			$this->_hydrated_fields_on_submission_cache[ $form['id'] . '-' . $field['id'] ] = $this->populate_field( $field, $form, $this->get_posted_field_values( $form ), $entry );
		}

		$hydrated_field = $this->_hydrated_fields_on_submission_cache[ $form['id'] . '-' . $field['id'] ];

		$choices = wp_list_pluck( $hydrated_field['field']->choices, 'text', 'value' );

		$option_value = $field->type === 'product' ? preg_replace( '/(\|.*?)$/', '', $value ) : $value;

		if ( ! empty( $gppa_choice_labels[ $field->id ] ) ) {
			$gppa_choice_labels[ $field->id ] = array(
				$option_value => rgar( $choices, $option_value ),
			) + $gppa_choice_labels[ $field->id ];
		} else {
			$gppa_choice_labels[ $field->id ] = array(
				$option_value => rgar( $choices, $option_value ),
			);
		}

		gform_update_meta( $entry['id'], 'gppa_choices', $gppa_choice_labels, $form['id'] );

		return $value;

	}

	/**
	 * @param $form
	 * @param $ajax
	 * @param $field_values
	 *
	 * @deprecated 2.0 Use GP_Populate_Anything::populate_form()
	 *
	 * @return array
	 */
	public function modify_admin_field_choices( $form, $ajax = false, $field_values = array() ) {
		return $this->populate_form( $form, $ajax, $field_values );
	}

	public function get_current_entry() {
		if ( ! class_exists( 'GFEntryDetail' ) || ! GFCommon::is_entry_detail_edit() ) {
			return false;
		}

		// Avoid infinite loops...
		$this->_getting_current_entry = true;
		$entry                        = GFEntryDetail::get_current_entry();
		$this->_getting_current_entry = false;
		return $entry;
	}

	public function modify_field_values_date( $value, $field ) {

		$format = empty( $field->dateFormat ) ? 'mdy' : esc_attr( $field->dateFormat );

		// If $field's value is dynamically populated from another date field, get the format from the source field.
		if ( $this->is_field_dynamically_populated( $field, 'values' ) ) {
			$object_type_id = rgar( $field, 'gppa-values-object-type' );
			$object_type    = $this->get_object_type( $object_type_id, $field );

			if ( $object_type && $object_type->id === 'gf_entry' ) {
				$primary_property = $this->get_primary_property( $field, 'values' );
				$template         = rgars( $field, 'gppa-values-templates/value' );

				if ( $template && strpos( $template, 'gf_field_' ) === 0 ) {
					$field_id     = str_replace( 'gf_field_', '', $template );
					$source_field = GFFormsModel::get_field( $primary_property, $field_id );
					$format       = empty( $source_field->dateFormat ) ? 'mdy' : esc_attr( $source_field->dateFormat );
				}
			}
		}

		if ( ! $field->inputs || ! count( $field->inputs ) ) {
			return $value;
		}

		$date_info = GFCommon::parse_date( $value, $format );

		$day_value   = esc_attr( rgget( 'day', $date_info ) );
		$month_value = esc_attr( rgget( 'month', $date_info ) );
		$year_value  = esc_attr( rgget( 'year', $date_info ) );

		// Date field inputs are always [m, d, y], no need to call $field->get_date_array_by_format() here.
		$date_array_values = array( $month_value, $day_value, $year_value );

		$value = array();

		foreach ( $field->inputs as $input_index => &$input ) {
			$value[ $input['id'] ] = $date_array_values[ $input_index ];
		}

		return $value;

	}

	public function modify_field_values_time( $value, $field ) {

		if ( ! is_string( $value ) ) {
			return $value;
		}

		preg_match( '/^(\d*):(\d*) ?(.*)$/', $value, $matches );

		if ( ! count( $matches ) ) {
			return $value;
		}

		$hour     = esc_attr( $matches[1] );
		$minute   = esc_attr( $matches[2] );
		$the_rest = strtolower( rgar( $matches, 3 ) );

		$value = array();

		$value[ $field->id . '.' . 1 ] = $hour;
		$value[ $field->id . '.' . 2 ] = $minute;

		if ( rgar( $field, 'timeFormat', 12 ) == 12 ) {
			$value[ $field->id . '.' . 3 ] = strpos( $the_rest, 'am' ) > - 1 ? 'am' : 'pm';
		}

		return $value;

	}

	public function modify_field_values_multiselect( $value, $field ) {

		if ( ! $value ) {
			return $value;
		}

		if ( is_string( $value ) && self::is_json( $value ) ) {
			$value = json_decode( $value, ARRAY_A );
		}

		if ( ! is_array( $value ) ) {
			return null;
		}

		return json_encode( array_values( $value ) );

	}

	public function should_force_use_field_value( $field, $deprecated = null ) {
		$save_and_continue_values = $this->get_save_and_continue_values( rgar( $_REQUEST, 'gf_token' ) );

		/**
		 * If a field has been requested to be updated with AJAX, then we should not be preserving its POSTed value.
		 */
		if (
			wp_doing_ajax()
			&& rgget( 'action' ) === 'gppa_get_batch_field_html'
			&& ! empty( $_POST['field-ids'] )
			&& in_array( $field->id, $_POST['field-ids'] ) ) {
			return false;
		}

		foreach ( $save_and_continue_values as $input_id => $value ) {
			if ( absint( $field->id ) === absint( $input_id ) ) {
				/**
				 * Determine whether or not a passed field value is used rather than the dynamically populated value.
				 * This can be useful for preferring dynamically populated values over values already set in an entry.
				 *
				 * @param $should_force_use_field_value boolean Whether or not to use the field value provided from an
				 *    outside source such as Save & Continue or if editing an entry.
				 * @param $field GF_Field The current field.
				 *
				 * @since 1.1.5
				 */
				return gf_apply_filters( array(
					'gppa_should_force_use_field_value',
					$field->formId,
					$field->id,
				), true, $field );
			}
		}

		if ( ! empty( $this->prepopulate_fields_values[ $field->formId ] ) ) {
			foreach ( $this->prepopulate_fields_values[ $field->formId ] as $input_id => $value ) {
				if ( absint( $field->id ) === absint( $input_id ) ) {
					return gf_apply_filters( array(
						'gppa_should_force_use_field_value',
						$field->formId,
						$field->id,
					), true, $field );
				}
			}
		}

		/*
		 * Check for posted values and use them if they're present. This will prevent blanked out values on multi-page
		 * forms from being reset back to their populated value.
		 */
		if ( (int) rgpost( 'gform_submit' ) === (int) $field->formId ) {
			foreach ( $_POST as $posted_key => $posted_value ) {
				if ( strpos( $posted_key, 'input_' ) !== 0 ) {
					continue;
				}

				$input_id = str_replace( 'input_', '', $posted_key );

				if ( absint( $field->id ) === absint( $input_id ) ) {
					return gf_apply_filters( array(
						'gppa_should_force_use_field_value',
						$field->formId,
						$field->id,
					), true, $field );
				}
			}
		}

		return gf_apply_filters( array(
			'gppa_should_force_use_field_value',
			$field->formId,
			$field->id,
		), false, $field );

	}

	/**
	 * @param $form
	 * @param $entry
	 *
	 * @deprecated 2.0 Use GP_Populate_Anything::populate_form()
	 *
	 * @return array
	 */
	public function hydrate_form( $form, $entry ) {
		return $this->populate_form( $form, false, array(), $entry );
	}

	/**
	 * Calls GP_Populate_Anything::populate_form() but with a different signature for the
	 * `gform_form_pre_update_entry` and `gform_pre_validation` filter.
	 *
	 * @param array $form
	 * @param array $entry
	 *
	 * @return array
	 */
	public function populate_form_pre_update_entry( $form, $entry = null ) {
		return $this->populate_form( $form, false, array(), $entry );
	}

	/**
	 * @param array $form
	 * @param boolean $ajax
	 * @param array $field_values
	 * @param array $entry
	 * @param boolean $hydrate_values
	 *
	 * @deprecated 2.0 Use GP_Populate_Anything::populate_form()
	 *
	 * @return array
	 */
	public function hydrate_initial_load( $form, $ajax = false, $field_values = array(), $entry = null, $hydrate_values = true ) {
		return $this->populate_form( $form, $ajax, $field_values, $entry, $hydrate_values );
	}

	/**
	 * @param array $form The current form.
	 * @param bool $ajax Whether the form is using AJAX.
	 * @param array $field_values The field values used during population.
	 * @param array $entry The current entry.
	 * @param bool $hydrate_values Whether to hydrate values.
	 *
	 * @return array
	 *
	 * @since 2.0
	 */
	public function populate_form( $form, $ajax = false, $field_values = array(), $entry = null, $hydrate_values = true ) {

		/**
		 * Filter to bypass Populate Anything population. Useful for preventing dynamic population while running
		 * intensive batch processes that add/edit entries.
		 *
		 * @param bool $bypass_population Whether to bypass population.
		 * @param array $form The current form.
		 * @param bool $ajax Whether the form is using AJAX.
		 * @param array $field_values The field values used during population.
		 * @param array $entry The current entry.
		 * @param bool $hydrate_values Whether to hydrate values.
		 *
		 * @since 2.0
		 */
		if ( gf_apply_filters( array( 'gppa_bypass_populate_form', $form['id'] ), false, $form, $ajax, $field_values, $entry, $hydrate_values ) ) {
			return $form;
		}

		if ( GFCommon::is_entry_detail_view() || GFCommon::is_form_editor() || ! is_array( $form ) ) {
			return $form;
		}

		if ( ! isset( $form['fields'] ) ) {
			return $form;
		}

		if ( ! isset( $GLOBALS['gppa-field-values'][ $form['id'] ] ) ) {
			$GLOBALS['gppa-field-values'][ $form['id'] ] = array();
		}

		if ( ! empty( $field_values ) && is_array( $field_values ) ) {
			$this->prepopulate_fields_values[ $form['id'] ] = $field_values;
			$GLOBALS['gppa-field-values'][ $form['id'] ]    = $field_values;
		}

		$field_values = $this->get_posted_field_values( $form );

		/**
		 * @deprecated 2.0 Use `gppa_populate_form_entry`
		 */
		$entry = gf_apply_filters(
			array(
				'gppa_hydrate_initial_load_entry',
				$form['id'],
			),
			$entry,
			$form,
			$ajax,
			$field_values
		);

		$entry = gf_apply_filters(
			array(
				'gppa_populate_form_entry',
				$form['id'],
			),
			$entry,
			$form,
			$ajax,
			$field_values
		);

		foreach ( $form['fields'] as &$field ) {
			// Ensure dateFormat is set if it's not specified (breaks LMT)
			if ( $field->type === 'date' && rgblank( $field->dateFormat ) ) {
				$field->dateFormat = 'mdy';
			}

			$force_use_field_value = $this->should_force_use_field_value( $field );
			$hydrated_field        = $this->populate_field( $field, $form, $field_values, $entry, $force_use_field_value );
			$hydrated_value        = $hydrated_field['field_value'];

			if ( $this->is_field_dynamically_populated( $field ) ) {
				$field = $hydrated_field['field'];

				if ( $hydrate_values ) {
					if ( is_array( $field->inputs ) ) {
						foreach ( $field->inputs as &$input ) {
							$value = rgar( $hydrated_value, $input['id'] );

							if ( $value ) {
								if ( $field->get_input_type() == 'checkbox' ) {
									$field = $this->select_choice( $field, $value );
								} else {
									$input['defaultValue'] = $value;
									// Update basePrice if we're populating a product field
									if ( $field->type === 'product' && $input['label'] === 'Price' ) {
										$field->basePrice = $value;
									}
								}
							}
						}
					} else {
						if ( rgar( $field, 'storageType' ) === 'json' ) {
							$field->allowsPrepopulate = true;
							$field->inputName         = $this->populated_value( $form['id'], $field->id, json_encode( $hydrated_value ) );
						} elseif ( ! is_array( $hydrated_value ) ) {
							/*
							 * Using is_null() check here to avoid a deprecation warning related to strpos not
							 * supporting null in GFCommon::replace_variables_prepopulate()
							 */
							$field->allowsPrepopulate = true;
							$field->inputName         = $this->populated_value( $form['id'], $field->id, is_null( $hydrated_value ) ? '' : $hydrated_value );
						}
					}
				}
			}

			/**
			 * Filter whether a field's value should be skipped during hydration. This can be useful in situations
			 * such as excluding hidden fields from being displayed in the @{order_summary} merge tag output when
			 * populated using a Live Merge Tag.
			 *
			 * @param bool $skip Whether to skip the field value during hydration.
			 * @param array $form The form currently being processed.
			 * @param GF_Field $field The field currently being processed.
			 * @param array $field_values The field values currently being processed.
			 * @param array $entry The entry being processed. Defaults to an empty array.
			 *
			 * @since 1.2.46
			 * @since 1.2.51 Added $entry parameter.
			 */
			if ( gf_apply_filters( array( 'gppa_skip_field_value_during_hydration', $form['id'], $field->id ), false, $form, $field, $field_values, array() ) ) {
				continue;
			}

			/**
			 * If hydrated value is an array of input, add individual fields to gppa-field-values instead
			 */
			if ( $this->is_field_value_array_of_input_value( $hydrated_value, $field ) ) {
				foreach ( $hydrated_value as $input_id => $input_value ) {
					$GLOBALS['gppa-field-values'][ $field->formId ][ $input_id ] = $input_value;
				}
			} else {
				$GLOBALS['gppa-field-values'][ $field->formId ][ $field->id ] = $hydrated_value;
			}

			// Fields with inputs/choices should be merged into the field values array adjacent to regular field values.
			// without this, PHP warnings can arise with checkbox fields.
			if ( $field->inputs && $field->choices && is_array( $hydrated_value ) ) {
				$field_values = $field_values + $hydrated_value;
			} else {
				$field_values[ $field->id ] = $hydrated_value;
			}
		}

		return $form;

	}

	/**
	* Passthrough value to form using gform_field_value filter.
	*
	* @since  2.0
	*
	* @param int    $form_id  ID of form being populated.
	* @param string $input_id ID of input being populated.
	* @param mixed  $value    Value to populate.
	*
	* @return string
	*/
	public function populated_value( $form_id, $input_id, $value ) {

		// Prepare filter name.
		$filter_name = sprintf(
			'gppa_value_%s_%s',
			$form_id,
			str_replace( '.', '_', $input_id )
		);

		// Add filter.
		add_filter( 'gform_field_value_' . $filter_name, function( $val ) use ( $value ) {
			return $value;
		} );

		return $filter_name;

	}

	/**
	 * Determine if the value is an array of input values for the given field.
	 *
	 * @param $value
	 * @param $field GF_Field
	 *
	 * @return boolean
	 */
	public function is_field_value_array_of_input_value( $value, $field ) {

		if ( ! is_array( $value ) ) {
			return false;
		}

		foreach ( $value as $input_id => $meta ) {
			if ( absint( $input_id ) !== absint( $field->id ) ) {
				return false;
			}
		}

		return true;

	}

	/**
	 * @param $form
	 * @param $ajax
	 * @param $field_values
	 *
	 * @deprecated 2.0 Use GP_Populate_Anything::populate_form()
	 *
	 * @return array
	 */
	public function modify_admin_field_values( $form, $ajax = false, $field_values = array() ) {
		return $this->populate_form( $form, $ajax, $field_values );
	}

	public function select_choice( $field, $value ) {
		foreach ( $field->choices as &$choice ) {
			if ( $choice['value'] == $value ) {
				$choice['isSelected'] = true;
			}
		}
		return $field;
	}

	/* Admin Methods */
	public function ajax_get_object_type_properties() {

		if ( ! GFCommon::current_user_can_any( array( 'gravityforms_edit_forms' ) ) ) {
			wp_die( -1 );
		}

		$object_type            = rgar( $this->_object_types, $_REQUEST['object-type'] );
		$primary_property_value = rgar( $_REQUEST, 'primary-property-value' );

		if ( ! $object_type ) {
			return array();
		}

		if ( $object_type->is_restricted() && ! is_super_admin() ) {
			wp_die( -1 );
		}

		$output = array();

		foreach ( $object_type->get_properties_filtered( $primary_property_value ) as $property_id => $property ) {
			if ( is_numeric( $property_id ) && is_string( $property ) ) {
				$output['ungrouped'] = array(
					'value' => $property,
					'label' => $property,
				);

				continue;
			}

			$output[ rgar( $property, 'group', 'ungrouped' ) ][] = array_merge(
				$property,
				array(
					'value' => $property_id,
				)
			);
		}

		foreach ( $output as $group_id => $group_items ) {
			usort(
				$output[ $group_id ],
				function ( $a, $b ) {
					if ( is_array( $a ) ) {
						$a = $a['label'];
					}

					if ( is_array( $b ) ) {
						$b = $b['label'];
					}

					return strnatcmp( $a, $b );
				}
			);
		}

		wp_send_json( $output );

	}

	public function ajax_get_property_values() {

		if ( ! GFCommon::current_user_can_any( array( 'gravityforms_edit_forms' ) ) ) {
			wp_die( -1 );
		}

		$object_type_id         = $_REQUEST['object-type'];
		$object_type            = rgar( $this->_object_types, $object_type_id );
		$primary_property_value = rgar( $_REQUEST, 'primary-property-value' );

		if ( ! $object_type ) {
			return array();
		}

		if ( $object_type->is_restricted() && ! is_super_admin() ) {
			wp_die( -1 );
		}

		$properties  = $object_type->get_properties_filtered( $primary_property_value );
		$property_id = $_REQUEST['property'];

		$property = rgar( $properties, $property_id );

		if ( $property_id === 'primary-property' ) {
			$property = $object_type->get_primary_property();
		}

		if ( ! $property ) {
			return array();
		}

		$property_args = rgar( $property, 'args', array() );

		$output = call_user_func_array( $property['callable'], $property_args );

		$label_filter = "gppa_property_label_{$object_type_id}_{$property_id}";

		if ( has_filter( $label_filter ) ) {
			$associative_output = array();

			foreach ( $output as $key => $value ) {
				$associative_output[ $value ] = apply_filters( $label_filter, $value );
			}

			$output = $associative_output;
		}

		/**
		 * Send back response to the editor that the property values should not be displayed in the dropdown for this
		 * particular property.
		 *
		 * Instead, a custom value or special value should by used by the user.
		 *
		 * This is done for usability purposes but also to help browsers from locking up if there are a huge number of
		 * results.
		 */
		if ( count( $output ) >= apply_filters( 'gppa_max_property_values_in_editor', 1000 ) ) {
			wp_send_json( 'gppa_over_max_values_in_editor' );
		}

		/**
		 * Transform array to flattened array for JavaScript ordering
		 */
		if ( gppa_is_assoc_array( $output ) ) {
			natcasesort( $output );

			$non_associative_output = array();

			foreach ( $output as $value => $label ) {
				$non_associative_output[] = array( $value, $label );
			}

			$output = $non_associative_output;
		} else {
			natcasesort( $output );
		}

		/* Remove duplicate property values */
		$output = array_unique( $output, SORT_REGULAR );

		wp_send_json( $output );

	}

	public function ajax_get_batch_field_html() {

		/**
		 * This option is used to simulate real-world server conditions while developing and with acceptance tests.
		 *
		 * NEVER set this option to true unless you are aware of the implications.
		 */
		if ( get_option( 'gppa_ajax_network_debug' ) ) {
			sleep( 1 );
		}

		$data = self::maybe_decode_json( WP_REST_Server::get_raw_data() );

		// Copy $data onto $_REQUEST and $_POST
		$_REQUEST = array_merge( $_REQUEST, $data );
		$_POST    = array_merge( $_POST, $data );

		check_ajax_referer( 'gppa', 'security' );

		$form_id = rgar( $data, 'form-id' );

		/**
		 * Filter a form, much like `gform_pre_render`, before it is used for AJAX refreshing of field markup and
		 * Live Merge Tags.
		 *
		 * @param array $form The current form.
		 *
		 * @since 2.0.7
		 */
		$form = gf_apply_filters( array( 'gppa_ajax_form_pre_render', $form_id ), GFAPI::get_form( $form_id ) );

		$fields       = rgar( $data, 'field-ids', array() );
		$field_values = $this->get_posted_field_values( $form );
		$entry_id     = rgar( $data, 'lead-id', 0 );
		$using_entry  = ! ! $entry_id;
		$entry        = $using_entry ? GFAPI::get_entry( $entry_id ) : null;
		$response     = array(
			'fields'           => array(),
			'merge_tag_values' => array(),
		);

		/**
		 * Remove field values for fields that are being populated as the choices may change.
		 */
		$field_values = array_filter( $field_values, function( $field_value, $field_id ) use ( $fields, $form ) {
			$field = null;

			/**
			 * Using GFAPI::get_field() has unforseen consequences here most likely due to hydration.
			 */
			foreach ( rgar( $form, 'fields' ) as $current_field ) {
				if ( isset( $current_field->id ) && $current_field->id === $field_id ) {
					$field = $current_field;
					break;
				}
			}

			/**
			 * Only remove field values for fields that have populated choices. Without this condition, Live Merge Tags
			 * may not be properly populated.
			 */
			if ( rgar( $field, 'gppa-choices-enabled' ) && in_array( $field_id, $fields ) ) {
				return false;
			}

			return true;
		}, ARRAY_FILTER_USE_BOTH );

		/**
		 * Map the field values to $_POST to ensure that $field->get_value_save_entry() works as expected.
		 */
		foreach ( $field_values as $input => $value ) {
			$_POST[ 'input_' . $input ] = $value;
		}

		$_POST = add_magic_quotes( $_POST );

		/**
		 * Populate form to get more accurate merge tag values.
		 */
		$form = $this->populate_form( $form, false, $field_values, $entry, false );

		/**
		 * Map field values again to $_POST after hydration. Note this is a duplication of a block above.
		 */
		foreach ( $GLOBALS['gppa-field-values'][ $form['id'] ] as $input => $value ) {
			$_POST[ 'input_' . $input ] = $value;
		}

		$_POST = add_magic_quotes( $_POST );

		$fake_lead = $GLOBALS['gppa-field-values'][ $form['id'] ];

		/**
		 * Allow throwing out field values during hydration using a filter.
		 *
		 * One use-case of this is excluding hidden fields from being displayed in the @{order_summary} merge tag.
		 */
		foreach ( $fake_lead as $input => $value ) {
			$field = GFFormsModel::get_field( $form, $input );

			if ( ! $field ) {
				continue;
			}

			/**
			 * Documented above.
			 */
			if ( gf_apply_filters( array( 'gppa_skip_field_value_during_hydration', $form['id'], $field->id ), false, $form, $field, $field_values, $fake_lead ) ) {
				unset( $fake_lead[ $input ] );

				if ( isset( $_POST[ 'input_' . $input ] ) ) {
					unset( $_POST[ 'input_' . $input ] );
				}

				if ( isset( $field_values[ $input ] ) ) {
					unset( $field_values[ $input ] );
				}

				if ( isset( $GLOBALS['gppa-field-values'][ $form['id'] ][ $input ] ) ) {
					unset( $GLOBALS['gppa-field-values'][ $form['id'] ][ $input ] );
				}
			}
		}

		/**
		 * Flush GF cache to prevent issues from the fake lead creation from before.
		 *
		 * For posterity, issues encountered in the past are issues with conditional logic.
		 */
		GFCache::flush();

		// Default to no tabindex but allow 3rd-parties to override.
		GFCommon::$tab_index = gf_apply_filters( array( 'gform_tabindex', $form['id'] ), 0, $form );

		/* Merge HTTP referer GET params into field values for parameter [pre]population */
		$referer_parsed = parse_url( rgar( $_SERVER, 'HTTP_REFERER' ) );
		parse_str( rgar( $referer_parsed, 'query' ), $referer_get_params );

		/* The union operator for arrays is kinda funky and the order is the opposite of what you'd expect. */
		$GLOBALS['gppa-field-values'][ $form['id'] ] = apply_filters( 'gppa_field_filter_values', $field_values + $referer_get_params, $field_values, $referer_get_params, $form, $fields, $entry_id );

		foreach ( $fields as $field_id ) {

			$field = GFFormsModel::get_field( $form, $field_id );

			/**
			 * Use force_use_field_values if a lead is loaded in to ensure that Live Merge Tags are populated
			 * correctly in GravityView.
			 */
			$hydrated_field = $this->populate_field( $field, $form, $GLOBALS['gppa-field-values'][ $form['id'] ], $entry, $using_entry, true, true );

			$response['fields'][ $field_id ] = apply_filters( 'gppa_get_batch_field_html', rgar( $hydrated_field, 'html' ), rgar( $hydrated_field, 'field' ), $form, $fields, $entry_id, $hydrated_field );

			/* Add hydrated field value to field values object */
			$form_field_values = &$GLOBALS['gppa-field-values'][ $form['id'] ];
			$field_value       = rgar( $hydrated_field, 'field_value' );

			if ( ! is_array( $field_value ) || $this->does_field_accept_json( $field ) ) {
				$form_field_values[ $field_id ] = $field_value;
				$fake_lead[ $field_id ]         = $field_value;
			} else {
				/* Unset values for current field in case there are lingering inputs (e.g. checkboxes). */
				foreach ( $form_field_values as $input_id => $input_value ) {
					if ( (int) $input_id === (int) $field_id ) {
						unset( $form_field_values[ $input_id ] );

						if ( isset( $fake_lead[ absint( $input_id ) ] ) ) {
							unset( $fake_lead[ absint( $input_id ) ] );
						}
					}
				}

				foreach ( $field_value as $input_id => $input_value ) {
					// Make sure the input ID is a float that way it doesn't overwrite values for other fields.
					// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					if ( (int) $input_id == $input_id ) {
						$input_id = $field_id . '.' . $input_id;
					}

					$form_field_values[ $input_id ] = $input_value;
					$fake_lead[ $input_id ]         = $input_value;
				}
			}

			// Remove decimal comma before replacing merge tag on number fields since GF will be interpret it
			// as a thousands separator in `GFCommon::format_number()` down the call stack.
			if ( $field->type === 'number' && $field->numberFormat === 'decimal_comma' ) {
				$fake_lead[ $field->id ] = GFCommon::clean_number( $fake_lead[ $field->id ], $field->numberFormat );
			}
		}

		$live_merge_tags = rgar( $data, 'merge-tags', array() );

		if ( ! empty( $live_merge_tags ) ) {
			$this->flush_dynamically_populated_fields_cache( $form );
		}

		foreach ( $live_merge_tags as $live_merge_tag ) {
			$live_merge_tag_value                            = $this->live_merge_tags->get_live_merge_tag_value( $live_merge_tag, $form, $fake_lead );
			$response['merge_tag_values'][ $live_merge_tag ] = gf_apply_filters(
				array(
					'gppa_ajax_merge_tag_value',
					$live_merge_tag,
				),
				$live_merge_tag_value,
				$live_merge_tag,
				$live_merge_tags
			);
		}

		wp_send_json( apply_filters( 'gppa_get_batch_field_html_response', $response ) );

	}

	public function does_field_accept_json( $field ) {
		return rgar( $field, 'storageType' ) === 'json';
	}

	/**
	 * From GFFormDisplay::get_form()
	 */
	public function get_save_and_continue_values( $token ) {
		static $save_and_continue_value_cache;

		if ( isset( $save_and_continue_value_cache[ $token ] ) ) {
			return $save_and_continue_value_cache[ $token ];
		}

		$incomplete_submission_info = GFFormsModel::get_draft_submission_values( $token );

		if ( $incomplete_submission_info ) {
			$submission_details_json = $incomplete_submission_info['submission'];
			$submission_details      = json_decode( $submission_details_json, true );

			$save_and_continue_value_cache[ $token ] = $submission_details['submitted_values'];

			return $submission_details['submitted_values'];
		}

		$save_and_continue_value_cache[ $token ] = array();

		return array();

	}

	/*
	 * Clear field from $GLOBALS['_fields'] cache to ensure that we have all of the hydrated choices so
	 * Gravity Forms can use choice labels if it desires.
	 *
	 * @param array $form The current form.
	 *
	 * @return array $form
	 */
	public function flush_dynamically_populated_fields_cache( $form ) {
		if ( empty( $form['fields'] ) ) {
			return $form;
		}

		foreach ( $form['fields'] as $field ) {
			if ( $this->is_field_dynamically_populated( $field ) && isset( $GLOBALS['_fields'][ $field->formId . '_' . $field->id ] ) ) {
				unset( $GLOBALS['_fields'][ $field->formId . '_' . $field->id ] );
				GFAPI::get_field( $form, $field->id ); // Re-fetch field using current form meta to re-prime cache.
			}
		}

		return $form;
	}

	public function check_gppa_settings_for_user( $form_meta, $form_id, $meta_name ) {

		if ( empty( $form_meta['fields'] ) ) {
			return $form_meta;
		}

		if ( is_super_admin() ) {
			return $form_meta;
		}

		foreach ( $form_meta['fields'] as &$field ) {
			$reset_gppa_settings = array();

			if ( $this->is_population_restricted( 'values', $field ) ) {
				$reset_gppa_settings[] = 'values';
			}

			if ( $this->is_population_restricted( 'choices', $field ) ) {
				$reset_gppa_settings[] = 'choices';
			}

			if ( ! count( $reset_gppa_settings ) ) {
				continue;
			}

			/**
			 * Reset GPPA settings back to original prior to saving if a restricted object type is in use.
			 */
			$field_original = GFAPI::get_field( $form_id, $field->id );

			// Un-reference the field object to prevent it from being modified.
			if ( is_object( $field_original ) ) {
				$field_original = clone $field_original;
			}

			foreach ( $reset_gppa_settings as $populate ) {
				foreach ( $field as $key => $value ) {
					if ( strpos( $key, 'gppa-' . $populate ) === 0 ) {
						unset( $field[ $key ] );
					}
				}

				if ( ! empty( $field_original ) ) {
					// @phpstan-ignore-next-line Fields implement ArrayAccess and are iterable.
					foreach ( $field_original as $orig_key => $orig_value ) {
						if ( strpos( $orig_key, 'gppa-' . $populate ) !== 0 ) {
							continue;
						}

						$field->{$orig_key} = $orig_value;
					}
				}
			}
		}

		return $form_meta;

	}

	/**
	 * Check if object type for population is restricted.
	 */
	public function is_population_restricted( $populate, $field ) {
		$object_type = $field[ 'gppa-' . $populate . '-object-type' ];

		if ( $object_type ) {
			$id_parts = explode( ':', $object_type );

			if ( $id_parts[0] === 'field_value_object' && $field ) {
				$field = GFFormsModel::get_field( $field['formId'], $id_parts[1] );

				$values_object_type_instance = $this->get_object_type( rgar( $field, 'gppa-choices-object-type' ), $field );
			} else {
				$values_object_type_instance = $this->get_object_type( $object_type );
			}

			if ( ! $values_object_type_instance ) {
				return false;
			}

			if ( $values_object_type_instance->is_restricted() ) {
				return true;
			}
		}

		return false;

	}

	public function add_helper_body_classes( $body_class ) {
		$gf_version_split    = explode( '.', GFForms::$version );
		$major_minor_version = $gf_version_split[0] . '.' . $gf_version_split[1];

		// @phpstan-ignore-next-line
		if ( is_callable( array( 'GFForms', 'get_page' ) ) && GFForms::get_page() ) {
			$body_class .= ' gf-version-' . str_replace( '.', '-', $major_minor_version );

			if ( version_compare( $major_minor_version, '2.5' ) <= 0 ) {
				$body_class .= ' gf-version-lte-2-5';
			}
		}

		return $body_class;
	}


	public function field_standard_settings() {
		/*
		 * The class of this root element needs to contain whatever field setting classes are used for the Add-On otherwise GF 2.6 will nuke them due to them
		 * not being present in the initial markup.
		 *
		 * Additionally, it needs to be an <li>, have the class to protect as the first class, and also have the field_setting class.
		 */
		?>
		<!-- Populated with Vue -->
		<li id="gppa" class="gppa field_setting" ></li>
		<?php
	}

	public function add_enabled_field_class( $css_class, $field, $form ) {
		if ( rgar( $field, 'gppa-choices-enabled' ) ) {
			$css_class .= ' gppa-choices-enabled';
		}

		if ( rgar( $field, 'gppa-values-enabled' ) ) {
			$css_class .= ' gppa-values-enabled';
		}

		return $css_class;
	}

	public function get_field_values_from_request() {
		return rgar( $_REQUEST, 'field-values', array() );
	}

	/**
	 * Gravity Forms attempts to prevent tampering of field values by checking a state. This is ignored for dynamically
	 * populated fields. Let's follow suit by indicating to GF that GPPA-enabled fields are dynamically populated.
	 *
	 * @param array $form
	 *
	 * @return array $form
	 */
	public function override_state_validation_for_populated_fields( $form ) {

		foreach ( $form['fields'] as &$field ) {
			/**
			 * Bypass state validation for fields with dynamically populated choices that have filters relying on
			 * other field values.
			 *
			 * This state validation was added in GF 2.5.10.1.
			 */
			if ( ! empty( $this->get_dependent_fields_by_filter_group( $field, 'choices' ) ) ) {
				$field->validateState = false;
			}

			// Check for LMTs in choice based fields and skip validation as well.
			// State validation has been expanded to more choice based fields in GF 2.5.10.1.
			// Setting allowsRepopulate to `true` should still work, but GF recommends `validateState`.
			// See: PR#248
			if ( rgar( $field, 'choices' ) ) {
				foreach ( $field->choices as $choice ) {
					if ( preg_match( '/@{[^}]+}/', $choice['value'] ) ) {
						$field->validateState = false;
						break;
					}
				}
			}

			if ( $this->is_field_dynamically_populated( $field ) && GFCommon::is_product_field( $field->type ) ) {
				$field->allowsPrepopulate = true;
			} elseif ( $field->type === 'consent' && $this->live_merge_tags->has_live_merge_tag( $field->checkboxLabel . $field->description ) ) {
				$field->allowsPrepopulate = true;
			}
		}

		return $form;
	}

	/**
	 * Convert the values input for dynamically-populated choice fields to use a text field instead. This is due to the
	 * fact that we don't have the dynamically populated values in the context of conditional logic. Additionally,
	 * if Form Field Values are used as filter values, you would not be able to get the results in the context of
	 * conditional logic.
	 *
	 * @see GP_Populate_Anything::conditional_logic_field_filters()
	 */
	public function conditional_logic_use_text_field() {
		// @phpstan-ignore-next-line
		if ( ! is_callable( array( 'GFForms', 'get_page' ) ) || ! GFForms::get_page() ) {
			return;
		}
		?>
		<script type="text/javascript">
			// GP Populate Anything - Replace dropdown for values with text input
			gform.addFilter( 'gform_conditional_logic_values_input', function( markup, objectType, ruleIndex, selectedFieldId, selectedValue ) {
				var field = GetFieldById( selectedFieldId );

				if ( field && field['gppa-choices-enabled'] ) {
					var inputId = objectType + '_rule_value_' + ruleIndex;
					selectedValue = selectedValue ? selectedValue.replace( /'/g, '&#039;' ) : '';
					markup = '<input ' +
						'type="text" ' +
						'placeholder="' + gf_vars.enterValue + '" ' +
						'class="gfield_rule_select gfield_rule_input" ' +
						'style="display: block" ' +
						'id="'+ inputId + '" ' +
						'name="'+ inputId + '" ' +
						'value="' + selectedValue.replace( /'/g, '&#039;' ) + '" ' +
						'onchange="SetRuleProperty( \'' + objectType + '\', ' + ruleIndex + ', \'value\', this.value );" ' +
						'onkeyup="SetRuleProperty( \'' + objectType + '\', ' + ruleIndex + ', \'value\', this.value );">';
				}

				return markup;
			} );
		</script>
		<?php
	}

	/**
	 * Converts dropdown for fields with dynamically populated choices to use a text input as the choices will not
	 * be correct.
	 *
	 * This filter differs from GP_Populate_Anything::conditional_logic_use_text_field() as it affects field filters
	 * used in locations such as GravityFlow's Conditional Routing option.
	 *
	 * @see GP_Populate_Anything::conditional_logic_use_text_field()
	 *
	 * @param $field_filters array The form field, entry properties, and entry meta filter settings.
	 * @param $form array The form object.
	 *
	 * @see [gform_field_filters](https://docs.gravityforms.com/gform_field_filters/)
	 *
	 * @return mixed
	 */
	public function conditional_logic_field_filters( $field_filters, $form ) {
		foreach ( $field_filters as &$field_filter ) {
			$field = GFAPI::get_field( $form, $field_filter['key'] );
			if ( $field && $field->{'gppa-choices-enabled'} ) {
				unset( $field_filter['values'] );
			}
		}
		return $field_filters;
	}

	/**
	 * Exclude the "Fill Out Other Fields" from field maps such as the one used in GP Easy Passthrough.
	 *
	 * @param array $choices
	 * @param int $form_id
	 * @param string $field_type
	 * @param array $exclude_field_types
	 *
	 * @return array
	 */
	public function exclude_error_choices_from_field_maps( $choices, $form_id, $field_type, $exclude_field_types ) {
		if ( empty( $choices['fields'] ) || empty( $choices['fields']['choices'] ) ) {
			return $choices;
		}

		foreach ( $choices['fields']['choices'] as $choice_index => $choice ) {
			$field_id            = absint( $choice['value'] );
			$missing_filter_text = apply_filters( 'gppa_missing_filter_text', '&ndash; ' . esc_html__( 'Fill Out Other Fields', 'gp-populate-anything' ) . ' &ndash;', $field_id );

			if ( $choice['label'] === $missing_filter_text ) {
				unset( $choices['fields']['choices'][ $choice_index ] );

				// Without this, when JSON-encoded, it will be turned into associative.
				$choices['fields']['choices'] = array_values( $choices['fields']['choices'] );
			}
		}

		return $choices;
	}

	/**
	 * Override the choices of Post Category fields as they run GFCommon::add_categories_as_choices() at the
	 * last moment which will override the choices set in gform_pre_render.
	 *
	 * @param array $choices
	 * @param GF_Field $field
	 * @param number $form_id
	 */
	public function post_category_hydrate_choices( $choices, $field, $form_id ) {
		if ( ! rgar( $field, 'gppa-choices-enabled' ) ) {
			return $choices;
		}

		$field_values = gp_populate_anything()->get_posted_field_values( GFAPI::get_form( $form_id ) );

		$field->choices = $this->get_input_choices( $field, $field_values );
		$field->choices = $this->maybe_select_choices( $field, $field_values );

		return $field->choices;
	}

}

function gp_populate_anything() {
	return GP_Populate_Anything::get_instance();
}

GFAddOn::register( 'GP_Populate_Anything' );
