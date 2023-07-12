<?php

class GPPA_Object_Type_GF_Entry extends GPPA_Object_Type {

	public $gform_gf_query_sql_func;

	public function __construct( $id ) {
		parent::__construct( $id );

		add_action( 'gppa_pre_object_type_query_gf_entry', array( $this, 'add_filter_hooks' ) );

		add_filter( 'gppa_process_template', array( $this, 'replace_gf_merge_tags_for_entry' ), 15, 6 );
		add_filter( 'gppa_process_template', array( $this, 'maybe_combine_multi_input_entry_template' ), 5, 7 );
	}

	public function add_filter_hooks() {
		add_filter( 'gppa_object_type_gf_entry_filter', array( $this, 'process_filter_default' ), 10, 2 );
	}

	/**
	 * Extract unique identifier for a given GF Entry.
	 *
	 * @param StdClass $object
	 * @param null|string $primary_property_value
	 */
	public function get_object_id( $object, $primary_property_value = null ) {
		return isset( $object->id ) ? $object->id : null;
	}

	public function get_label() {
		return esc_html__( 'Gravity Forms Entry', 'gp-populate-anything' );
	}

	public function get_groups() {
		return array(
			'fields' => array(
				'label'     => esc_html__( 'Fields', 'gp-populate-anything' ),
				'operators' => $this->supported_operators(),
			),
			'meta'   => array(
				'label'     => esc_html__( 'Entry Meta', 'gp-populate-anything' ),
				'operators' => $this->supported_operators(),
			),
		);
	}

	public function get_primary_property() {
		return array(
			'id'       => 'form',
			'label'    => esc_html__( 'Form', 'gp-populate-anything' ),
			'callable' => array( $this, 'get_forms' ),
		);
	}

	public function supported_operators() {
		return array_merge( gp_populate_anything()->get_default_operators(), array( 'is_in', 'is_not_in' ) );
	}

	public function get_properties( $form_id = null ) {

		if ( ! $form_id ) {
			return array();
		}

		$properties = array(
			'id'             => array(
				'label'     => esc_html__( 'Entry ID', 'gp-populate-anything' ),
				'value'     => 'id',
				'callable'  => array( $this, 'get_col_rows' ),
				'args'      => array( GFFormsModel::get_entry_table_name(), 'id' ),
				'orderby'   => true,
				'operators' => $this->supported_operators(),
			),
			'created_by'     => array(
				'label'     => esc_html__( 'Created by (User ID)', 'gp-populate-anything' ),
				'value'     => 'created_by',
				'callable'  => '__return_empty_array',
				'orderby'   => true,
				'operators' => $this->supported_operators(),
			),
			'date_created'   => array(
				'label'     => esc_html__( 'Date Created', 'gp-populate-anything' ),
				'value'     => 'date_created',
				'callable'  => '__return_empty_array',
				'orderby'   => true,
				'operators' => $this->supported_operators(),
			),
			'date_updated'   => array(
				'label'     => esc_html__( 'Date Updated', 'gp-populate-anything' ),
				'value'     => 'date_updated',
				'callable'  => '__return_empty_array',
				'orderby'   => true,
				'operators' => $this->supported_operators(),
			),
			'ip'             => array(
				'label'     => esc_html__( 'IP', 'gp-populate-anything' ),
				'value'     => 'ip',
				'callable'  => '__return_empty_array',
				'orderby'   => true,
				'operators' => $this->supported_operators(),
			),
			'payment_method' => array(
				'label'     => esc_html__( 'Payment Method', 'gp-populate-anything' ),
				'value'     => 'payment_method',
				'callable'  => array( $this, 'get_col_rows' ),
				'args'      => array( GFFormsModel::get_entry_table_name(), 'payment_method' ),
				'orderby'   => true,
				'operators' => $this->supported_operators(),
			),
			'payment_status' => array(
				'label'     => esc_html__( 'Payment Status', 'gp-populate-anything' ),
				'value'     => 'payment_status',
				'callable'  => array( $this, 'get_col_rows' ),
				'args'      => array( GFFormsModel::get_entry_table_name(), 'payment_status' ),
				'orderby'   => true,
				'operators' => $this->supported_operators(),
			),
			'status'         => array(
				'label'     => esc_html__( 'Status', 'gp-populate-anything' ),
				'value'     => 'status',
				'callable'  => array( $this, 'get_col_rows' ),
				'args'      => array( GFFormsModel::get_entry_table_name(), 'status' ),
				'orderby'   => true,
				'operators' => $this->supported_operators(),
			),
			'transaction_id' => array(
				'label'     => esc_html__( 'Transaction ID', 'gp-populate-anything' ),
				'value'     => 'transaction_id',
				'callable'  => '__return_empty_array',
				'orderby'   => true,
				'operators' => $this->supported_operators(),
			),
		);

		foreach ( $this->get_form_fields( $form_id ) as $form_field ) {
			$properties[ 'gf_field_' . $form_field['value'] ] = array(
				'value'    => $form_field['value'],
				'group'    => 'fields',
				'label'    => $form_field['label'],
				'callable' => array( $this, 'get_form_fields_values' ),
				'args'     => array( $form_id, $form_field['value'] ),
				'orderby'  => true,
			);
		}

		foreach ( GFFormsModel::get_entry_meta( $form_id ) as $meta_key => $meta ) {
			$properties[ 'gf_field_' . $meta_key ] = array(
				'value'    => $meta_key,
				'group'    => 'meta',
				'label'    => $meta['label'],
				'callable' => array( $this, 'get_form_fields_values' ),
				'args'     => array( $form_id, $meta_key ),
				'orderby'  => true,
			);
		}

		return $properties;

	}

	public function get_property_value_from_property_id( $property_id ) {
		return preg_replace( '/^gf_field_/', '', $property_id );
	}

	public function get_object_prop_value( $object, $prop ) {

		$prop = preg_replace( '/^gf_field_/', '', $prop );

		if ( ! isset( $object->{$prop} ) ) {
			return null;
		}

		/**
		 * Filter if GPPA should re-format entries' date fields to the format specified in the form they belong to.
		 * Up until 1.0.14 GPPA returned date fields' values in MySQL format (YYYY-MM-DD).
		 *
		 * Starting from 1.1.3, date fields' values will be returned in the form's format (e.g. mm/dd/yyyy).
		 * This filter allows users to revert to the old behavior.
		 *
		 * @since 1.1.3
		 *
		 * @param bool    $reformat_date_fields  Whether or not date fields' values should be reformatted (default: true)
		 * @param object  $entry                 Current entry being accessed
		 */
		$reformat_date_fields = gf_apply_filters( array( 'gppa_gf_entry_reformat_date_fields', $object->form_id, $prop ), true, $object );

		if ( $reformat_date_fields ) {
			$field = GFAPI::get_field( $object->form_id, $prop );
			if ( $field && $field['type'] === 'date' && ! rgblank( $field['dateFormat'] ) ) {
				return GFCommon::date_display( $object->{$prop}, 'ymd', $field['dateFormat'] );
			}
		}

		return $object->{$prop};

	}

	public function date_to_time( $date, $format = 'mdy' ) {

		$delimiter = '/';

		if ( strpos( $date, '-' ) !== false ) {
			$delimiter = '-';
		} elseif ( strpos( $date, '.' ) !== false ) {
			$delimiter = '.';
		}

		/**
		 * Check for the delimiter prior to converting below as PHP notices will result if the delimiter is not present.
		 */
		if ( strpos( $date, $delimiter ) === false ) {
			return null;
		}

		/**
		 * If the date is in YYYY-MM-DD (likely coming straight from the DB), ignore the passed format
		 */
		if ( preg_match( '/\d{4}-\d{2}-\d{2}/', $date ) ) {
			list( $year, $month, $day ) = explode( '-', $date );
		} elseif ( strpos( $format, 'ymd' ) === 0 ) {
			list( $year, $month, $day ) = explode( $delimiter, $date );
		} elseif ( strpos( $format, 'dmy' ) === 0 ) {
			list( $day, $month, $year ) = explode( $delimiter, $date );
		} elseif ( strpos( $format, 'mdy' ) === 0 ) {
			list( $month, $day, $year ) = explode( $delimiter, $date );
		} else {
			return null;
		}

		// Convert m/d/y to integer values
		$month = intval( $month );
		$day   = intval( $day );
		$year  = intval( $year );

		// Ensure m/d/y are available
		if ( ! $month || ! $day || ! $year ) {
			return null;
		}

		return mktime( 0, 0, 0, $month, $day, $year );

	}

	public function process_filter_default( $processed_args, $args ) {

		/** @var string|string[] */
		$filter_value = null;

		/** @var array */
		$filter = null;

		/** @var array */
		$filter_group = null;

		/** @var int */
		$filter_group_index = null;

		/** @var string */
		$primary_property_value = null;

		/** @var string */
		$property = null;

		/** @var string */
		$property_id = null;

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( $args );

		if ( ! isset( $processed_args['where'] ) ) {
			$processed_args['where'] = array();
		}

		if ( ! isset( $processed_args['where'][ $filter_group_index ] ) ) {
			$processed_args['where'][ $filter_group_index ] = array();
		}

		switch ( strtoupper( $filter['operator'] ) ) {
			case 'CONTAINS':
				$operator     = GF_Query_Condition::LIKE;
				$filter_value = $this->get_sql_value( $filter['operator'], $filter_value );
				break;
			case 'DOES_NOT_CONTAIN':
				$operator     = GF_Query_Condition::NLIKE;
				$filter_value = $this->get_sql_value( $filter['operator'], $filter_value );
				break;
			case 'STARTS_WITH':
				$operator     = GF_Query_Condition::LIKE;
				$filter_value = $this->get_sql_value( $filter['operator'], $filter_value );
				break;
			case 'ENDS_WITH':
				$operator     = GF_Query_Condition::LIKE;
				$filter_value = $this->get_sql_value( $filter['operator'], $filter_value );
				break;
			case 'IS NOT':
			case 'ISNOT':
			case '<>':
				$operator = GF_Query_Condition::NEQ;
				break;
			case 'LIKE':
				$operator = GF_Query_Condition::LIKE;
				break;
			case 'IS_NOT_IN':
				$operator = GF_Query_Condition::NIN;
				break;
			case 'IS_IN':
				$operator = GF_Query_Condition::IN;
				break;
			case '>=':
				$operator = GF_Query_Condition::GTE;
				break;
			case '<=':
				$operator = GF_Query_Condition::LTE;
				break;
			case '<':
				$operator = GF_Query_Condition::LT;
				break;
			case '>':
				$operator = GF_Query_Condition::GT;
				break;
			case 'IS':
			case '=':
				$operator = GF_Query_Condition::EQ;
				// Implemented to support Checkbox fields as a Form Field Value filters.
				if ( is_array( $filter_value ) ) {
					$operator = GF_Query_Condition::IN;
				}
				break;
			default:
				return $processed_args;
		}

		if (
			( is_array( $filter_value ) || GP_Populate_Anything::is_json( $filter_value ) )
			|| in_array( $operator, array( GF_Query_Condition::IN, GF_Query_Condition::NIN ), true )
		) {
			if ( GP_Populate_Anything::is_json( $filter_value ) ) {
				$filter_value = GP_Populate_Anything::maybe_decode_json( $filter_value );
			}

			if ( ! is_array( $filter_value ) ) {
				$filter_value = array_map( 'trim', explode( ',', $filter_value ) );
			}

			foreach ( $filter_value as &$_filter_value ) {
				$_filter_value = new GF_Query_Literal( $_filter_value );
			}
			unset( $_filter_value );
			$filter_value = new GF_Query_Series( $filter_value );
		} else {
			/**
			 * Get current source field to parse the query value appropriately
			 */
			$form_id      = $primary_property_value;
			$field_id     = str_replace( 'gf_field_', '', rgar( $args, 'property_id' ) );
			$source_field = GFAPI::get_field( $form_id, absint( $field_id ) );
			$is_field     = is_a( $source_field, 'GF_Field' );

			// Parse date_created and date_updated as a date value in filters
			$source_is_date = ! $is_field && in_array( $field_id, array( 'date_created', 'date_updated' ), true );

			/*
			 * Cast numeric values to float to allow for numeric comparisons. Exclude those that start with 0 as they
			 * could be other things like zip codes.
			 */
			if ( is_numeric( $filter_value ) && strpos( $filter_value, '0' ) !== 0 ) {
				$filter_value = floatval( $filter_value );
			}

			/**
			 * Force a value to be parsed as a date to enable date comparison using operators such as >, <, <=, etc.
			 *
			 * By default, values from date fields will be treated as dates. Using this filter, non-date fields can have
			 * their values parsed as dates.
			 *
			 * @since 1.0-beta-4.89
			 *
			 * @param boolean $value Whether or not to parse the value as a date.
			 * @param \GF_Field $field The field that is having its value parsed.
			 */
			$gppa_process_value_as_date = gf_apply_filters( array_filter( array( 'gppa_process_value_as_date', $form_id, $is_field ? $source_field->id : null ) ), $source_is_date || ( $is_field && $source_field->type === 'date' ), $source_field );
			/**
			 * Convert date string to ISO 8601 for MySQL date comparisons
			 *
			 * strtotime doesn't play nicely with formats like d/m/y out of the box so we need to parse the date
			 * ourselves into a time based on the format from the actual date field saved in the form that we're
			 * pulling entries from.
			 */
			if ( $gppa_process_value_as_date && strlen( $filter_value ) > 1 && ( $source_field || $source_is_date ) ) {
				$date_format = rgar( (array) $source_field, 'dateFormat' );
				$time        = false;
				if ( $date_format ) {
					$time = $this->date_to_time( $filter_value, $date_format );
				}

				if (
					! is_numeric( $filter_value )
					&& ( $time || strtotime( $filter_value ) )
				) {
					if ( ! $time ) {
						$time = strtotime( $filter_value );
					}

					$filter_value = gmdate( 'Y-m-d', $time );
				}

				// If we're querying `date_created` or `date_updated` we need a new WHERE clause that uses a date
				// range of `Y-m-d 00:00:00` and `Y-m-d 23:59:59` as an upper/lower bounds.
				$sql_date_range_query = $this->sql_date_range( $processed_args, $filter_value, $operator, $property, $primary_property_value );

				if ( $source_is_date && ! is_wp_error( $sql_date_range_query ) ) {
					$processed_args['where'][ $filter_group_index ][] = $sql_date_range_query;
					return $processed_args;
				}
			}

			/* Replace \r\n with just \n to provide consistent querying. */
			if ( is_string( $filter_value ) ) {
				$filter_value = str_replace( "\r\n", "\n", $filter_value );
			}

			$filter_value = new GF_Query_Literal( $filter_value );
		}

		require_once plugin_dir_path( __FILE__ ) . 'class-gppa-gf-query-condition.php';

		$processed_args['where'][ $filter_group_index ][] = new GPPA_GF_Query_Condition(
			new GF_Query_Column( rgar( $property, 'value' ), (int) $primary_property_value ),
			$operator,
			$filter_value
		);

		return $processed_args;

	}

	public function sql_date_range( $gf_query_where, $filter_value, $operator, $property, $primary_property_value ) {
		switch ( $operator ) {
			case GF_Query_Condition::EQ:
				return call_user_func_array( array( 'GF_Query_Condition', '_and' ), array(
					new GF_Query_Condition(
						new GF_Query_Column( rgar( $property, 'value' ), (int) $primary_property_value ),
						GF_Query_Condition::GTE,
						new GF_Query_Literal( $filter_value . ' 00:00:00' )
					),
					new GF_Query_Condition(
						new GF_Query_Column( rgar( $property, 'value' ), (int) $primary_property_value ),
						GF_Query_Condition::LTE,
						new GF_Query_Literal( $filter_value . ' 23:59:59' )
					),
				) );
			case GF_Query_Condition::NEQ:
				return call_user_func_array( array( 'GF_Query_Condition', '_or' ), array(
					new GF_Query_Condition(
						new GF_Query_Column( rgar( $property, 'value' ), (int) $primary_property_value ),
						GF_Query_Condition::LTE,
						new GF_Query_Literal( $filter_value . ' 00:00:00' )
					),
					new GF_Query_Condition(
						new GF_Query_Column( rgar( $property, 'value' ), (int) $primary_property_value ),
						GF_Query_Condition::GTE,
						new GF_Query_Literal( $filter_value . ' 23:59:59' )
					),
				) );
			case GF_Query_Condition::GTE:
			case GF_Query_Condition::GT:
				return new GF_Query_Condition(
					new GF_Query_Column( rgar( $property, 'value' ), (int) $primary_property_value ),
					$operator,
					new GF_Query_Literal( $filter_value . ' 00:00:00' )
				);
			case GF_Query_Condition::LTE:
			case GF_Query_Condition::LT:
				return new GF_Query_Condition(
					new GF_Query_Column( rgar( $property, 'value' ), (int) $primary_property_value ),
					$operator,
					new GF_Query_Literal( $filter_value . ' 23:59:59' )
				);
		}

		return new WP_Error( 'Unsupported operator for sql_date_range.' );
	}

	public function include_active_entries( $where_filter_groups ) {

		$where_active = new GF_Query_Condition(
			new GF_Query_Column( 'status' ),
			GF_Query_Condition::EQ,
			new GF_Query_Literal( 'active' )
		);

		return call_user_func_array( array( 'GF_Query_Condition', '_and' ), array( $where_filter_groups, $where_active ) );

	}

	/**
	 * Creates a GF_Query instance to be used for querying and generating a cache hash.
	 */
	public function create_gf_query( $args ) {
		/** @var string */
		$populate = null;

		/** @var array */
		$filter_groups = null;

		/** @var array */
		$ordering = null;

		/** @var array */
		$templates = null;

		/** @var string */
		$primary_property_value = null;

		/** @var array */
		$field_values = null;

		/** @var GF_Field */
		$field = null;

		/** @var boolean */
		$unique = null;

		/** @var int|null */
		$page = null;

		/** @var int */
		$limit = null;

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( $args );

		if ( ! $primary_property_value ) {
			return array();
		}

		$processed_query = $this->process_query_args( $args, array() );

		$order_key = str_replace( 'gf_field_', '', rgar( $ordering, 'orderby' ) );
		$gf_query  = new GF_Query(
			$primary_property_value,
			null,
			array(
				'direction' => rgar( $ordering, 'order', 'ASC' ),
				'key'       => $order_key,
			),
			array(
				'page_size' => $processed_query['limit'],
				'offset'    => rgar( $processed_query, 'offset' ),
			)
		);

		$gf_query_where_groups = rgar( $processed_query, 'where', array() );

		$has_status_filter = false;
		foreach ( $gf_query_where_groups as $gf_query_where_index => $gf_query_where_group ) {
			if (
				! empty( $gf_query_where_group[0]->get_columns() )
				&& $gf_query_where_group[0]->get_columns()[0]->field_id === 'status'
			) {
				$has_status_filter = true;
			}

			$gf_query_where_groups[ $gf_query_where_index ] = call_user_func_array( array( 'GF_Query_Condition', '_and' ), $gf_query_where_group );
		}

		$where_filter_groups = call_user_func_array( array( 'GF_Query_Condition', '_or' ), $gf_query_where_groups );
		// Exclude all non-active entries unless "Status" is one of the conditionals
		$where = ( ! $has_status_filter ) ? $this->include_active_entries( $where_filter_groups ) : $where_filter_groups;

		$gf_query->where( $where );

		// If we're ordering form a form field, check the field type and adjust the SQL accordingly.
		if ( strlen( $field['gppa-choices-ordering-property'] ) > 0 && is_numeric( $order_key ) ) {
			$source_field = GFAPI::get_field( $field['gppa-choices-primary-property'], $order_key );
			// 12-hour Time fields need to be parsed since "01:00 pm" < "11:00 am" to MySQL's ORDER BY
			if ( $source_field && $source_field['type'] === 'time' && $source_field['timeFormat'] === '12' ) {
				$mask = '%h:%i %p'; // MySQL's STR_TO_DATE mask

				// @param array $sql An array with all the SQL fragments: select, from, join, where, order, paginate.
				$this->gform_gf_query_sql_func = function ( $sql ) use ( $mask ) {
					// Regex: meta_value with a look behind to capture (`...`.`meta_value`)
					$sql['order'] = preg_replace( '((<?`[^`]*`.`)meta_value`)', sprintf( "STR_TO_DATE($0, '%s')", $mask ), $sql['order'] );
					return $sql;
				};

				add_filter( 'gform_gf_query_sql', $this->gform_gf_query_sql_func );
			}
		}

		return $gf_query;
	}

	public function query( $args ) {
		$gf_query = $this->create_gf_query( $args );
		$entries  = $gf_query->get();

		if ( isset( $this->gform_gf_query_sql_func ) ) {
			remove_filter( 'gform_gf_query_sql', $this->gform_gf_query_sql_func );
		}

		foreach ( $entries as $entry_index => $entry ) {
			$entry_object = new StdClass();

			foreach ( $entry as $key => $value ) {
				$entry_object->{$key} = $value;
			}

			$entries[ $entry_index ] = $entry_object;
		}

		return $entries;

	}

	/**
	 * Hashes GF Entry Query Arguments
	 *
	 * @param $args array  Query arguments to hash
	 *
	 * @return string   SHA1 representation of the requested query
	 */
	public function query_cache_hash( $args ) {
		$gf_query = $this->create_gf_query( $args );

		return sha1( serialize( $gf_query ) );
	}

	/**
	 * Filter null values from the field values for query cache hash. This is needed as once fields are hydrated by
	 * Populate Anything, field_values will have a null value for any field without a value.
	 *
	 * As such, this makes the field_values unique for every single field during hydration which was not the original
	 * intent.
	 *
	 * @param $value
	 *
	 * @return bool
	 */
	public function filter_null_field_values( $value ) {
		return $value !== null;
	}

	public function get_forms() {

		$forms = GFFormsModel::get_forms();

		return wp_list_pluck( $forms, 'title', 'id' );

	}

	public function get_form_fields( $form_id ) {

		$form = GFAPI::get_form( $form_id );

		if ( ! $form || ! $form_id ) {
			return array();
		}

		$output = array();

		foreach ( $form['fields'] as $field ) {
			if ( $field['type'] === 'page' ) {
				continue;
			}
			/**
			 * Use admin label when listing out fields
			 */
			$use_admin_label_prev = $field->get_context_property( 'use_admin_label' );
			$field->set_context_property( 'use_admin_label', true );

			if ( empty( $field['inputs'] ) || in_array( $field['type'], GP_Populate_Anything::get_interpreted_multi_input_field_types(), true ) ) {
				$output[] = array(
					'value' => $field['id'],
					'label' => GFCommon::get_label( $field ),
				);
			} elseif ( is_array( $field['inputs'] ) ) {
				$output[] = array(
					'value' => $field['id'],
					'label' => GFCommon::get_label( $field ),
				);

				foreach ( $field['inputs'] as $input ) {
					$output[] = array(
						'value' => $input['id'],
						'label' => GFCommon::get_label( $field, $input['id'] ),
					);
				}
			}

			$field->set_context_property( 'use_admin_label', $use_admin_label_prev );
		}

		return $output;

	}

	public function get_form_fields_values( $form_id, $input_id ) {

		global $wpdb;

		$entry_meta_table = GFFormsModel::get_entry_meta_table_name();

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_col( $wpdb->prepare( "SELECT meta_value from $entry_meta_table WHERE form_id = %d AND meta_key = %s", $form_id, $input_id ) );

	}

	/**
	 * Combine multi-input fields such as checkboxes into a single array. Useful for populating multi-selectable choice
	 * inputs with the value of a multi-selectable choice entry field.
	 *
	 * @param $template_value
	 * @param $field
	 * @param $template
	 * @param $populate
	 * @param $object
	 * @param $object_type
	 * @param $objects
	 *
	 * @return mixed
	 */
	public function maybe_combine_multi_input_entry_template( $template_value, $field, $template, $populate, $object, $object_type, $objects ) {

		if ( $object_type->id !== $this->id ) {
			return $template_value;
		}

		if ( ! $object || is_scalar( $object ) ) {
			return $template_value;
		}

		$templates = rgar( $field, 'gppa-' . $populate . '-templates', array() );
		$template  = (string) rgar( $templates, $template );

		if ( strpos( $template, 'gf_field_' ) !== 0 ) {
			return $template_value;
		}

		$field_id = str_replace( 'gf_field_', '', $template );

		/**
		 * We do not want to loop the object below unless the field ID is an integer and nothing is found in the entry
		 * with the supplied field ID.
		 *
		 * Coerce field ID string to an integer using "+ 0". This trick works well with is_float as well.
		 */
		if ( ! is_numeric( $field_id )
			|| ( isset( $object->{$field_id} ) && is_scalar( $object->{$field_id} ) )
			|| ! is_int( $field_id + 0 )
		) {
			return $template_value;
		}

		$output = array();

		foreach ( $object as $key => $value ) {

			if ( absint( $key ) === absint( $field_id ) ) {
				$output[ $key ] = $value;
			}
		}

		$output = array_filter( $output );

		return json_encode( $output );

	}

	public function replace_gf_merge_tags_for_entry( $template_value, $field, $template, $populate, $object, $object_type ) {

		if ( $object_type->id !== $this->id ) {
			return $template_value;
		}

		/* Replacing merge tags on a PHP serialized array will corrupt it. */
		if ( is_serialized( $template_value ) ) {
			return $template_value;
		}

		/**
		 * Check for existence of merge tags prior to trying to parse as looking up the form and replace_variables()
		 * itself can be expensive when there are a lot of entries.
		 */
		if ( ! is_string( $template_value ) || ! preg_match( gp_populate_anything()->live_merge_tags->merge_tag_regex, $template_value ) ) {
			return $template_value;
		}

		$form = GFAPI::get_form( $object->form_id );

		if ( empty( $object->form_id ) || ! $form ) {
			return $template_value;
		}

		$entry = (array) $object;

		return GFCommon::replace_variables( $template_value, $form, $entry, false, false, false, 'text' );

	}

}
