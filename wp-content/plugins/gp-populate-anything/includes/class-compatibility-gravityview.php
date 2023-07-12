<?php

class GPPA_Compatibility_GravityView {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function __construct() {

		add_filter( 'gppa_populate_form_entry', array( $this, 'populate_form_entry' ), 10, 4 );

		add_filter( 'gravityview_widget_search_filters', array( $this, 'hydrate_gravityview_search_filters' ), 10, 4 );
		add_filter( 'gravityview_widget_search_filters', array( $this, 'localize_for_search' ), 10, 4 );
		add_filter( 'gravityview_widget_search_filters', array( $this, 'add_gravityview_id_filter' ), 10, 4 );

		add_filter( 'gppa_field_filter_values', array( $this, 'field_filter_values_replace_filter_prefix' ), 10, 6 );
		add_filter( 'gppa_get_batch_field_html', array( $this, 'render_search_field' ), 10, 6 );

		add_filter( 'gravityview/fields/custom/form', array( $this, 'hydrate_submitted_entry_choices' ), 10, 2 );

		add_filter( 'gravityview/template/field/context', array( $this, 'replace_live_merge_tags_in_html_field_content' ) );

	}

	/**
	 * Hydrate form field choices so merge tag labels work properly and do not return only the value.
	 *
	 * @todo write a test for this
	 *
	 * @param $form array Current form
	 * @param $entry array Current entry being displayed in GravityView
	 *
	 * @return array Form with hydrated choices
	 */
	public function hydrate_submitted_entry_choices( $form, $entry ) {
		static $forms = array();

		$cache_key = $form['id'] . '-' . $entry['id'];

		if ( rgar( $forms, $cache_key ) ) {
			return $forms[ $cache_key ];
		}

		$forms[ $cache_key ] = gp_populate_anything()->populate_form( $form, false, array(), $entry );

		return $forms[ $cache_key ];
	}

	/**
	 * @param array $widget_args Args passed to this method.
	 * @param \GV\Template_Context $context
	 *
	 * @return array|null Form array
	 */
	public function get_widget_form( $widget_args, $context ) {
		$form_id = rgar( $widget_args, 'form_id' );

		/** @phpstan-ignore-next-line Without null-safe operators, it doesn't seem wise to remove these checks. */
		if ( ! $form_id && ! empty( $context->view ) && ! empty( $context->view->form ) && isset( $context->view->form->ID ) ) {
			$form_id = $context->view->form->ID;
		}

		return GFAPI::get_form( $form_id );
	}

	/**
	 * If editing a form with Gravity View's edit screen, then the form should be hydrated with fields from the current
	 * entry.
	 */
	public function populate_form_entry( $entry, $form, $ajax, $field_values ) {

		if ( ! class_exists( 'GravityView_frontend' ) ) {
			return $entry;
		}

		$gv_entry = GravityView_frontend::getInstance()->getEntry();
		if ( $gv_entry ) {
			return $gv_entry;
		}

		return $entry;

	}

	/**
	 * @deprecated 2.0 Use GPPA_Compatibility_GravityView::populate_form_entry()
	 */
	public function hydrate_initial_load_entry( $entry, $form, $ajax, $field_values ) {
		return $this->populate_form_entry( $entry, $form, $ajax, $field_values );
	}


	/**
	 * @param array $search_fields Array of search filters with `key`, `label`, `value`, `type`, `choices` keys
	 * @param GravityView_Widget_Search $widget Current widget object
	 * @param array $widget_args Args passed to this method.
	 * @param \GV\Template_Context $context
	 */
	public function hydrate_gravityview_search_filters( $search_fields, $widget, $widget_args, $context ) {
		$form = $this->get_widget_form( $widget_args, $context );

		foreach ( $search_fields as $search_field_index => $search_field ) {
			$field = GFFormsModel::get_field( $form, $search_field['key'] );

			$hydrated_field   = gp_populate_anything()->populate_field( $field, $form, $this->get_gravityview_filter_values() );
			$hydrated_choices = rgars( $hydrated_field, 'field/choices' );

			if ( $hydrated_choices === rgar( $search_field, 'choices' ) ) {
				continue;
			}

			if ( $hydrated_choices ) {
				$search_fields[ $search_field_index ]['choices'] = $hydrated_choices;
			}
		}

		return $search_fields;
	}

	public function get_gravityview_filter_values() {

		$values = array();

		foreach ( $_REQUEST as $key => $value ) {

			if ( strpos( $key, 'filter_' ) !== 0 ) {
				continue;
			}

			$key = str_replace( 'filter_', '', $key );

			$values[ $key ] = $value;

		}

		return $values;

	}

	public function gravityview_inline_edit_choices( $wrapper_attributes, $input_type, $gf_field_id, $entry, $form, $gf_field ) {
		if ( ! rgar( $gf_field, 'gppa-choices-enabled' ) || ! isset( $wrapper_attributes['data-source'] ) || $input_type !== 'select' ) {
			return $wrapper_attributes;
		}

		$choices = wp_list_pluck( gp_populate_anything()->get_input_choices( $gf_field, $entry ), 'text', 'value' );

		$wrapper_attributes['data-source'] = json_encode( $choices );

		return $wrapper_attributes;
	}

	/**
	 * @param array $search_fields Array of search filters with `key`, `label`, `value`, `type`, `choices` keys
	 * @param GravityView_Widget_Search $widget Current widget object
	 * @param array $widget_args Args passed to this method.
	 * @param \GV\Template_Context $context
	 */
	public function localize_for_search( $search_fields, $widget, $widget_args, $context ) {
		$form = $this->get_widget_form( $widget_args, $context );

		gp_populate_anything()->field_value_js( $form );
		gp_populate_anything()->field_value_object_js( $form );

		// Ensure that the scripts are enqueued as there may not be a form present.
		// @phpstan-ignore-next-line
		if ( is_callable( array( 'GFCommon', 'output_hooks_javascript' ) ) ) {
			GFCommon::output_hooks_javascript();
		}

		gp_populate_anything()->enqueue_scripts( $form );

		return $search_fields;
	}

	/**
	 * @param array $search_fields Array of search filters with `key`, `label`, `value`, `type`, `choices` keys
	 * @param GravityView_Widget_Search $widget Current widget object
	 * @param array $widget_args Args passed to this method.
	 * @param \GV\Template_Context $context
	 */
	public function add_gravityview_id_filter( $search_fields, $widget, $widget_args, $context ) {
		$form    = $this->get_widget_form( $widget_args, $context );
		$form_id = rgar( $form, 'id' );

		$dynamic_search_fields = array();

		foreach ( $search_fields as $search_field ) {
			$field = GFFormsModel::get_field( $form, $search_field['key'] );

			if ( rgar( $field, 'gppa-choices-enabled' ) ) {
				$dynamic_search_fields[] = $search_field;
			}
		}

		if ( ! count( $dynamic_search_fields ) ) {
			return $search_fields;
		}

		wp_localize_script(
			'gp-populate-anything',
			'GPPA_GRAVITYVIEW_META_' . $form_id,
			array(
				'search_fields' => $dynamic_search_fields,
			)
		);

		return $search_fields;
	}

	public function field_filter_values_replace_filter_prefix( $field_values, $field_values_original, $referer_get_params, $form, $fields, $lead_id ) {

		if ( ! rgar( $_REQUEST, 'gravityview-meta' ) ) {
			return $field_values;
		}

		foreach ( $referer_get_params as $param_name => $param_value ) {
			if ( strpos( $param_name, 'filter_' ) !== 0 ) {
				continue;
			}

			$new_param_name = str_replace( 'filter_', '', $param_name );

			unset( $field_values[ $param_name ] );

			if ( ! empty( $field_values_original[ $new_param_name ] ) ) {
				continue;
			}

			$field_values[ $new_param_name ] = $param_value;
		}

		return $field_values;

	}

	public function render_search_field( $html, $field, $form, $fields, $lead_id, $hydrated_field ) {

		$view_id = rgar( $_REQUEST, 'gravityview-meta' );

		if ( ! $view_id ) {
			return $html;
		}

		$search_field = array(
			'key'     => $field['id'],
			'name'    => 'filter_' . $field['id'],
			'label'   => $field['label'],
			'input'   => 'select',
			'value'   => '',
			'type'    => 'select',
			'choices' => array(),
		);

		$choices = rgars( $hydrated_field, 'field/choices' );

		if ( $choices ) {
			$search_field['choices'] = $choices;
		}

		$value = rgar( $hydrated_field, 'field_value' );

		if ( $value ) {
			$search_field['value'] = $value;
		}

		// @phpstan-ignore-next-line
		\GravityView_View::getInstance()->search_field = $search_field;

		ob_start();
		\GravityView_View::getInstance()->render( 'search-field', $search_field['type'], false );
		$output = ob_get_clean();

		return $output;

	}

	public function replace_live_merge_tags_in_html_field_content( $context ) {
		if ( is_a( $context->field->field, 'GF_Field' ) && $context->field->field->get_input_type() === 'html' ) {
			$lmt   = gp_populate_anything()->live_merge_tags;
			$field =& $context->field->field;
			$form  = $context->view->form->form;

			$lmt->populate_lmt_whitelist( $form );

			/*
			 * Preserve the original content of the HTML field prior to merge tag replacement as the field is a reference
			 * and if we update it for one entry, we'll lose the merge tags for subsequent entries thus causing the
			 * same HTML content to be used for each HTML field.
			 */
			if ( ! isset( $field->originalContent ) ) {
				$field->originalContent = $field->content;
			}

			$field->content = $lmt->replace_live_merge_tags_static( $field->originalContent, $form, $context->entry->as_entry() );
		}

		return $context;
	}

}


function gppa_compatibility_gravityview() {
	return GPPA_Compatibility_GravityView::get_instance();
}
