<?php

abstract class GPPA_Object_Type {

	public $id;

	protected $_restricted = false;

	/**
	 * @var bool Whether the NULL special value should be shown for Filter Values.
	 */
	public $supports_null_filter_value = false;

	abstract public function query( $args );

	abstract public function get_label();

	/**
	 * Extract unique identifier for a given object. For example, if an object type has a uuid property, this method
	 * should extract that prop from the object.
	 *
	 * @param $object
	 * @param null|string $primary_property_value
	 *
	 * @return string|number|float|null
	 */
	abstract public function get_object_id( $object, $primary_property_value = null );

	abstract public function get_properties( $primary_property_value = null );

	/**
	 * Returns a string based off of the args passed into form a unique identifier for a given query.
	 *
	 * Return null if the object type doesn't support query caching.
	 *
	 * @param $args
	 *
	 * @return string|null
	 */
	public function query_cache_hash( $args ) {
		return null;
	}

	public function get_properties_filtered( $primary_property_value = null ) {
		/**
		 * Modify the properties that are available for filtering and ordering for the current object type.
		 *
		 * @since 1.0-beta-3.35
		 *
		 * @param array  $props       The properties available for filtering/ordering for the current object type.
		 * @param string $object_type The current object type.
		 */
		return gf_apply_filters( array( 'gppa_object_type_properties', $this->id ), $this->get_properties( $primary_property_value ), $this->id );
	}

	public function is_restricted() {
		return apply_filters( 'gppa_object_type_restricted_' . $this->id, $this->_restricted );
	}

	/**
	 * @deprecated 1.1.12
	 *
	 * @return boolean
	 */
	// phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
	public function isRestricted() {
		_deprecated_function( 'GPPA_Object_Type::isRestricted', '1.1.12', 'GPPA_Object_Type::is_restricted' );

		return apply_filters( 'gppa_object_type_restricted_' . $this->id, $this->_restricted );
	}

	public function __construct( $id ) {
		$this->id = $id;

		add_filter( 'gppa_replace_filter_value_variables_' . $this->id, array( $this, 'replace_gf_field_value' ), 10, 6 );
		add_filter( 'gppa_replace_filter_value_variables_' . $this->id, array( $this, 'parse_date_in_filter_value' ), 10, 7 );
		add_filter( 'gppa_replace_filter_value_variables_' . $this->id, array( $this, 'replace_special_values' ), 10 );
		add_filter( 'gppa_replace_filter_value_variables_' . $this->id, array( $this, 'clean_numbers' ), 10 );

		add_filter( 'gppa_object_type_query_' . $this->id, array( $this, 'add_limit_to_query' ), 10, 2 );
		add_filter( 'gppa_object_type_query_' . $this->id, array( $this, 'maybe_add_offset_to_query' ), 10, 2 );
	}

	public function parse_date_in_filter_value( $filter_value, $field_values, $primary_property_value, $filter, $ordering, $field, $property ) {
		$property_id = ! empty( $property['group'] ) ? $property['group'] . '_' . $property['value'] : $property['value'];

		/**
		 * @todo This should be documented and potentially be made into a generic gppa_parse_filter_value_as_date.
		 */
		if ( ! gf_apply_filters(
			array(
				'gppa_parse_' . $this->id . '_filter_value_as_date',
				$property_id,
			),
			false,
			$filter_value,
			$filter,
			$field,
			$property
		) ) {
			return $filter_value;
		}

		$date_time    = strtotime( $filter_value );
		$filter_value = gmdate( 'Y-m-d', $date_time );

		return $filter_value;
	}

	public function get_primary_property() {
		return null;
	}

	public function get_groups() {
		return array();
	}

	/**
	 * Get the group from just the property ID. This is used if loading properties is disabled during the queries.
	 *
	 * @param $property_id string
	 *
	 * @return string|null
	 */
	public function get_group_from_property_id( $property_id ) {
		return null;
	}

	public function get_property_value_from_property_id( $property_id ) {
		return str_replace( $this->get_group_from_property_id( $property_id ) . '_', '', $property_id );
	}

	public function get_default_templates() {
		return array();
	}

	public function default_query_args( $args ) {
		return array();
	}

	public function to_simple_array() {

		$output = array(
			'id'                      => $this->id,
			'label'                   => $this->get_label(),
			'properties'              => array(),
			'groups'                  => $this->get_groups(),
			'templates'               => $this->get_default_templates(),
			'restricted'              => $this->is_restricted(),
			'supportsNullFilterValue' => $this->supports_null_filter_value,
		);

		if ( $this->get_primary_property() ) {
			$output['primary-property'] = $this->get_primary_property();
		}

		return $output;

	}

	public function replace_gf_field_value( $value, $field_values, $primary_property_value, $filter, $ordering, $field ) {

		if ( ! is_string( $value ) ) {
			return $value;
		}

		if ( preg_match_all( '/{\w+:gf_field_(\d+)}/', $value, $field_matches ) ) {
			if ( count( $field_matches ) && ! empty( $field_matches[0] ) ) {
				foreach ( $field_matches[0] as $index => $match ) {
					$field_id       = $field_matches[1][ $index ];
					$replaced_value = $this->replace_gf_field_value( "gf_field:{$field_id}", $field_values, $primary_property_value, $filter, $ordering, $field );
					$value          = str_replace( $match, $replaced_value, $value );
				}

				return $value;
			}
		}

		if ( strpos( $value, 'gf_field' ) !== 0 ) {
			return $value;
		}

		if ( ! $field_values ) {
			return null;
		}

		$value_exploded = explode( ':', $value );
		$input_id       = $value_exploded[1];
		$value          = gp_populate_anything()->get_field_value_from_field_values( $input_id, $field_values );

		/**
		 * Strip price from pricing fields if the current field is a product field.
		 */
		if (
			GFCommon::is_product_field( $field->type )
			&& is_string( $value )
			&& strpos( $value, '|' ) !== false
		) {
			$has_product_field_in_filter = false;

			/*
			 * If the filter property is also a product field, don't strip the price as product fields can have prices
			 * in their saved values.
			 */
			if ( strpos( rgar( $filter, 'property' ), 'gf_field_' ) === 0 ) {
				$filter_field = GFAPI::get_field( $primary_property_value, rgar( $filter, 'property' ) );

				if ( $filter_field && GFCommon::is_product_field( $filter_field->type ) ) {
					$has_product_field_in_filter = true;
				}
			}

			if ( ! $has_product_field_in_filter ) {
				$field = GFAPI::get_field( rgar( $field, 'formId' ), $input_id );
				$value = gp_populate_anything()->maybe_extract_value_from_product( $value, $field );
			}
		}

		return $value === '' ? null : $value;

	}

	public function replace_special_values( $value ) {

		if ( ! is_scalar( $value ) || strpos( $value, 'special_value:' ) !== 0 ) {
			return $value;
		}

		$special_value       = str_replace( 'special_value:', '', $value );
		$special_value_parts = explode( ':', $special_value );

		switch ( $special_value_parts[0] ) {
			case 'current_user':
				$user = wp_get_current_user();

				if ( $user->ID > 0 ) {
					return $user->{$special_value_parts[1]};
				}

				/* No current post or user, return impossible ID */
				return apply_filters( 'gppa_special_value_no_result', -1, $value, $special_value );
			case 'current_post':
				$post            = get_post();
				$referer         = rgar( $_SERVER, 'HTTP_REFERER' );
				$referer_post_id = url_to_postid( $referer );

				if ( ! $post && $referer && $referer_post_id ) {
					$post = get_post( $referer_post_id );
				}

				if ( $post ) {
					return $post->{$special_value_parts[1]};
				}

				/* No current post or user, return impossible ID */
				return apply_filters( 'gppa_special_value_no_result', -1, $value, $special_value );
			case 'null':
				return null;
		}

		/**
		 * @todo document
		 */
		return apply_filters( 'gppa_special_value', $value, $special_value, $special_value_parts );

	}

	public function clean_numbers( $value ) {

		// @todo Consider cleaning numbers inside the array?
		if ( is_array( $value ) ) {
			return $value;
		}

		if ( GFCommon::is_numeric( $value, 'decimal_dot' ) ) {
			return GFCommon::clean_number( $value, 'decimal_dot' );
		}

		if ( GFCommon::is_numeric( $value, 'decimal_comma' ) ) {
			return GFCommon::clean_number( $value, 'decimal_comma' );
		}

		return $value;

	}

	/**
	 * Adds an offset to the query if page is provided in the initial query. Used primarily for GP Advanced Select.
	 *
	 * @param array $processed_filter_groups
	 * @param array $args
	 *
	 * @return array
	 */
	public function maybe_add_offset_to_query( $processed_filter_groups, $args ) {
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

		if ( $page ) {
			/*
			 * Reduce the limit by 1 as it is set to be one more than the actual displayed limit, so we can detect if
			 * there are more results to paginate through.
			 *
			 * Used by GP Advanced Select
			 */
			$processed_filter_groups['offset'] = max( ( $page - 1 ) * ( $limit - 1 ), 0 );
		}

		return $processed_filter_groups;
	}

	/**
	 * Add the limit to the query from the query args.
	 *
	 * @param array $processed_filter_groups
	 * @param array $args
	 *
	 * @return array
	 */
	public function add_limit_to_query( $processed_filter_groups, $args ) {
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

		$processed_filter_groups['limit'] = $limit;

		return $processed_filter_groups;
	}

	public function get_object_prop_value( $object, $prop ) {

		if ( ! isset( $object->{$prop} ) ) {
			return null;
		}

		return $object->{$prop};

	}

	public function get_col_rows( $table, $col, $where = '' ) {

		static $_cache;

		global $wpdb;

		/**
		 * Filter the query used to pull property values into the dropdowns displayed under the GP Populate Anything
		 * field settings in the Form Editor.
		 *
		 * @since 1.0-beta-1.9
		 *
		 * @param string $sql SQL query that will be ran to fetch the property values.
		 * @param string $col Column that property values are being fetched from.
		 * @param array $table Table that property values are being fetched from.
		 * @param \GPPA_Object_Type $object_type The current object type.
		 *
		 * @example https://github.com/gravitywiz/snippet-library/blob/master/gp-populate-anything/gppa-postmeta-property-value-limit.php
		 */
		$query = apply_filters( 'gppa_object_type_col_rows_query', "SELECT DISTINCT $col FROM $table {$where} LIMIT 1000", $col, $table, $this );
		if ( isset( $_cache[ $query ] ) ) {
			return $_cache[ $query ];
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->get_col( $query );

		$_cache[ $query ] = is_array( $result ) ? $this->filter_values( $result ) : array();

		return $_cache[ $query ];
	}

	public function get_meta_values( $meta_key, $table ) {

		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$result = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT meta_value FROM $table WHERE meta_key = %s", $meta_key ) );

		return is_array( $result ) ? $this->filter_values( $result ) : array();

	}

	/**
	 * Determine if an object type supports skipping the loading of properties during query. Before this optimization,
	 * we would query for all properties (usually fairly inexpensive, but can be slow in some cases) so we knew
	 * everything about a property such as its group, etc.
	 *
	 * If an object types has both a get_group_from_property_id() and get_property_value_from_property_id() method
	 * to extract out the group and property from a property ID, we can skip loading all properties and save on the
	 * queries on the frontend.
	 *
	 * @return bool
	 */
	public function can_skip_loading_properties_during_query() {
		return method_exists( $this, 'get_property_value_from_property_id' );
	}

	public function process_query_args( $args, $processed_filter_groups = array() ) {

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

		if ( ! $this->can_skip_loading_properties_during_query() ) {
			$properties = $this->get_properties_filtered( $primary_property_value );
		}

		gf_do_action( array( 'gppa_pre_object_type_query', $this->id ), $processed_filter_groups, $args );

		if ( ! is_array( $filter_groups ) ) {
			return $processed_filter_groups;
		}

		foreach ( $filter_groups as $filter_group_index => $filter_group ) {
			foreach ( $filter_group as $filter ) {
				$filter_value = gp_populate_anything()->extract_custom_value( $filter['value'] );

				if ( is_scalar( $filter_value ) ) {
					$filter_value = GFCommon::replace_variables_prepopulate( $filter_value, false, false, true );
				}

				if ( ! $filter['value'] || ! $filter['property'] ) {
					continue;
				}

				if ( ! $this->can_skip_loading_properties_during_query() ) {
					$property = rgar( $properties, $filter['property'] );

					if ( ! $property ) {
						continue;
					}
				} else {
					$property = array(
						'value' => $this->get_property_value_from_property_id( $filter['property'] ),
						'group' => method_exists( $this, 'get_property_value_from_property_id' ) ? $this->get_group_from_property_id( $filter['property'] ) : null,
					);
				}

				$filter_value   = apply_filters( 'gppa_replace_filter_value_variables_' . $this->id, $filter_value, $field_values, $primary_property_value, $filter, $ordering, $field, $property );
				$wp_filter_name = 'gppa_object_type_' . $this->id . '_filter_' . $filter['property'];

				$group = rgar( $property, 'group' );

				if ( ! has_filter( $wp_filter_name ) && $group ) {
					$wp_filter_name = 'gppa_object_type_' . $this->id . '_filter_group_' . $group;
				}

				if ( ! has_filter( $wp_filter_name ) ) {
					$wp_filter_name = 'gppa_object_type_' . $this->id . '_filter';
				}

				$processed_filter_groups = apply_filters(
					$wp_filter_name,
					$processed_filter_groups,
					array(
						'filter_value'           => $filter_value,
						'filter'                 => $filter,
						'field'                  => $field,
						'filter_group'           => $filter_group,
						'filter_group_index'     => $filter_group_index,
						'primary_property_value' => $primary_property_value,
						'property'               => $property,
						'property_id'            => $filter['property'],
					)
				);

				/**
				 * Filter a field's filter groups immediately after a filter has been processed.
				 *
				 * This is advantageous over `gppa_object_type_query` as this filter is ran while in the loop which gives
				 * you the ability to easily modify the last change to the processed filter groups depending on a
				 * specific filter or property.
				 *
				 * @param array $processed_filter_groups The processed filter groups for the current query.
				 * @param array $args {
				 *     @var mixed     $filter_value             Filter's value.
				 *     @var array     $filter                   Filter being processed.
				 *     @var \GF_Field $field                    Field being dynamically populated.
				 *     @var array     $filter_group             Filter group being processed.
				 *     @var int       $filter_group_index       Filter group's index.
				 *     @var mixed     $primary_property_value   Value of the primary property for the object type if needed. (e.g. form ID if GF Entries Object Type)
				 *     @var array     $property                 Property being filtered by.
				 *     @var string    $property_id              ID of property that is being filtered by.
				 * }
				 *
				 * @since 1.2.20
				 */
				$processed_filter_groups = gf_apply_filters(
					array( 'gppa_object_type_filter_after_processing', $this->id ),
					$processed_filter_groups,
					array(
						'filter_value'           => $filter_value,
						'filter'                 => $filter,
						'field'                  => $field,
						'filter_group'           => $filter_group,
						'filter_group_index'     => $filter_group_index,
						'primary_property_value' => $primary_property_value,
						'property'               => $property,
						'property_id'            => $filter['property'],
					)
				);
			}
		}

		$processed_filter_groups = apply_filters( 'gppa_object_type_query', $processed_filter_groups, $args );
		$processed_filter_groups = apply_filters( 'gppa_object_type_query_' . $this->id, $processed_filter_groups, $args );

		return $processed_filter_groups;

	}

	/**
	 * @deprecated 2.0 Use GPPA_Object_Type::process_query_args()
	 *
	 * @return array
	 */
	public function process_filter_groups( $args, $processed_filter_groups = array() ) {
		return $this->process_query_args( $args, $processed_filter_groups );
	}


	/**
	 * Generate MySQL query using the provided select, from, joins, wheres, group_by, order, and order_by.
	 *
	 * @param $query_args array Typically generated by GPPA_Object_Type::process_filter_groups()
	 * @param $field GF_Field
	 *
	 * @return string
	 */
	public function build_mysql_query( $query_args, $field ) {

		global $wpdb;

		$query = array();

		$select = ! is_array( $query_args['select'] ) ? array( $query_args['select'] ) : $query_args['select'];
		$select = array_map( array( __CLASS__, 'esc_property_to_ident' ), $select );
		$select = implode( ', ', $select );

		$from = self::esc_property_to_ident( $query_args['from'] );

		$query[] = "SELECT {$select} FROM {$from}";

		if ( ! empty( $query_args['joins'] ) ) {
			foreach ( $query_args['joins'] as $join_name => $join ) {
				$query[] = $join;
			}
		}

		if ( ! empty( $query_args['where'] ) ) {
			$where_clauses = array();

			foreach ( $query_args['where'] as $where_or_grouping => $where_or_grouping_clauses ) {
				$where_clauses[] = '(' . implode( ' AND ', $where_or_grouping_clauses ) . ')';
			}

			$query[] = "WHERE \n" . implode( "\n OR ", $where_clauses );
		}

		if ( ! empty( $query_args['group_by'] ) ) {
			$group_by = self::esc_property_to_ident( $query_args['group_by'] );

			$query[] = "GROUP BY {$group_by}";
		}

		if ( ! empty( $query_args['order_by'] ) && ! empty( $query_args['order'] ) ) {
			$order_by = self::esc_property_to_ident( $query_args['order_by'], 'order_by' );
			$order    = $query_args['order'];

			if ( ! in_array( strtoupper( $order ), array( 'ASC', 'DESC', 'RAND' ), true ) ) {
				$order = 'DESC';
			} elseif ( strtoupper( $order ) === 'RAND' ) {
				// Use MySQL's rand() function if random ordering is requested.
				$order_by = 'rand()';
				$order    = '';
			}

			$query[] = "ORDER BY {$order_by} {$order}";
		}

		$offset = isset( $query_args['offset'] ) ? $query_args['offset'] : null;

		if ( $offset !== null ) {
			$query[] = $wpdb->prepare( 'LIMIT %d, %d', $offset, $query_args['limit'] );
		} else {
			$query[] = $wpdb->prepare( 'LIMIT %d', $query_args['limit'] );
		}

		return implode( "\n", $query );
	}

	public function get_value_specification( $value, $operator, $sql_operator ) {

		$specification = '%s';

		// Cast numeric strings to the appropriate type for operators such as > and <.
		// Regex check ensures that strings mimicing scientific notation like "1e4465" are not cast
		// to infinity which breaks SQL.
		if ( is_numeric( $value ) && ! preg_match( '/[a-z]/i', $value ) ) {
			// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			$value = ( $value == (int) $value ) ? (int) $value : (float) $value;
		}

		if ( $sql_operator !== 'LIKE' ) {
			if ( is_int( $value ) ) {
				$specification = '%d';
			} elseif ( is_float( $value ) ) {
				$specification = '%f';
			}
		}

		if ( in_array( $sql_operator, array( 'IN', 'NOT IN' ), true ) ) {
			$specification_array = array_map( function ( $v ) {
				return '%s';
			}, $value );

			$specification = '(' . join( ',', $specification_array ) . ')';
		}

		return $specification;

	}

	public function get_sql_value( $operator, $value ) {

		global $wpdb;

		switch ( $operator ) {
			case 'starts_with':
				return $wpdb->esc_like( $value ) . '%';

			case 'ends_with':
				return '%' . $wpdb->esc_like( $value );

			case 'contains':
			case 'does_not_contain':
				return '%' . $wpdb->esc_like( $value ) . '%';

			case 'is_in':
			case 'is_not_in':
				if ( GP_Populate_Anything::is_json( $value ) ) {
					$value = json_decode( $value, true );
				}

				$value = is_array( $value ) ? $value : array_map( 'trim', explode( ',', $value ) );
				return array_map( 'esc_sql', $value );

			default:
				return $value;
		}

	}

	public function get_sql_operator( $operator ) {

		switch ( $operator ) {
			case 'starts_with':
				return 'LIKE';

			case 'ends_with':
				return 'LIKE';

			case 'contains':
				return 'LIKE';

			case 'does_not_contain':
				return 'NOT LIKE';

			case 'is':
				return '=';

			case 'isnot':
				return '!=';

			case 'is_in':
				return 'IN';

			case 'is_not_in':
				return 'NOT IN';

			default:
				return $operator;
		}

	}

	public function build_where_clause( $table, $column, $operator, $raw_value ) {

		global $wpdb;

		$sql_operator = $this->get_sql_operator( $operator );
		$value        = $this->get_sql_value( $operator, $raw_value );

		/**
		 * Filter the specification used by `$wpdb->prepare()` when inserting a value into a query.
		 *
		 * @param string           $value_specification The value specification to be used. Typically one of `%s`, `%d`, `%f`.
		 * @param string           $table               Table being queried.
		 * @param string           $column              Column being queried.
		 * @param string           $operator            Operator that was selected. Examples: `is`, `isnot`, `starts_with`, `contains`, etc.
		 * @param string           $value               Value being searched for.
		 * @param GPPA_Object_Type $object_type         Current object type.
		 *
		 * @since 1.2.14
		 */
		$specification = apply_filters( 'gppa_value_specification', $this->get_value_specification( $value, $operator, $sql_operator ), $table, $column, $operator, $value, $this );

		$ident = self::esc_property_to_ident( "{$table}.{$column}" );

		if ( $value === null && in_array( $operator, array( 'is', 'isnot' ), true ) ) {
			$null_operator = $operator === 'isnot' ? 'IS NOT' : 'IS';
			$where_clause  = "{$ident} {$null_operator} NULL";
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
			$where_clause = $wpdb->prepare( "{$ident} {$sql_operator} {$specification}", $value );
		}

		/**
		 * Modify the where clause generated by Populate Anything's field settings.
		 *
		 * @param string           $where_clause  The custom where clause. Defaults to an empty string.
		 * @param GPPA_Object_Type $object_type   The current variation of the GPPA_Object_Type for which the where clause is being generated.
		 * @param string           $table         The table being queried.
		 * @param string           $column        The column in which the value is being queried.
		 * @param string           $operator      The operator used to compare the column and value.
		 * @param mixed            $raw_value     The raw value being filtered by.
		 * @param string           $value         The sql-prepared value being filtered by.
		 * @param string           $sql_operator  The operator that will be used in the query based on Populate Anything's filter operator.
		 * @param string           $specification The value specification to be used. Typically one of `%s`, `%d`, `%f`.
		 * @param string           $ident         The disambiguated table column in which the value is being queried.
		 *
		 * @since 1.2.34
		 */
		$where_clause = apply_filters( 'gppa_where_clause', $where_clause, $this, $table, $column, $operator, $raw_value, $value, $sql_operator, $specification, $ident );

		return $where_clause;
	}

	/*
	 * array_filter - Remove serialized values
	 * array_filter - Remove falsey values
	 * array_unique - Ran to make sequential for json_encode
	 */
	public function filter_values( $values ) {

		$values = array_values(
			array_unique(
				array_filter(
					array_filter(
						$values,
						array(
							__class__,
							'is_not_serialized',
						)
					)
				)
			)
		);

		natcasesort( $values );

		/* Run array values again so it's an ordered indexed array again */
		return array_values( $values );

	}

	public static function is_not_serialized( $value ) {
		return ! is_serialized( $value );
	}

	/**
	 * Escapes property for an SQL query
	 *
	 * Prepares $property for use in an SQL statement. 'table.name' would be escaped as '`table`.`name`'.
	 * If 'order_by' is passed in $clause and $property contains SELECT, this function will return $property
	 * without any modifications to maintain the proper syntax for the subquery.
	 *
	 * @param string $property String to escape
	 * @param string $clause Current clause being processed (accepts 'order_by')
	 *
	 * @return string
	 */
	public static function esc_property_to_ident( $property, $clause = '' ) {
		if ( strpos( $property, 'SELECT ' ) !== false && $clause === 'order_by' ) {
			return $property;
		}
		return implode( '.', self::esc_sql_ident( explode( '.', $property ) ) );
	}

	public static function esc_sql_ident( $ident ) {
		if ( is_string( $ident ) ) {
			return self::esc_sql_ident_cb( $ident );
		}

		return array_map( array( __CLASS__, 'esc_sql_ident_cb' ), $ident );
	}

	public static function esc_sql_ident_cb( $ident ) {
		if ( $ident === '*' ) {
			return $ident;
		}

		return '`' . str_replace( '`', '``', $ident ) . '`';
	}

}
