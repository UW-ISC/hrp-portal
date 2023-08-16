<?php

class GPPA_Object_Type_Database extends GPPA_Object_Type {

	protected $_restricted = true;

	private static $blacklisted_columns = array( 'password', 'user_pass', 'user_activation_key' );

	public $supports_null_filter_value = true;

	public function __construct( $id ) {
		parent::__construct( $id );

		add_action( sprintf( 'gppa_pre_object_type_query_%s', $id ), array( $this, 'add_filter_hooks' ) );
	}

	public function add_filter_hooks() {
		add_filter( sprintf( 'gppa_object_type_%s_filter', $this->id ), array( $this, 'process_filter_default' ), 10, 2 );
	}

	/**
	 * Due to complexities with passing through field value objects and getting the primary key using SHOW COLUMNS,
	 * the easiest thing to do is simply to select the first column since 99 times out of 100, it'll be a unique ID
	 * column.
	 *
	 * @param array $object
	 * @param null|string $primary_property_value
	 */
	public function get_object_id( $object, $primary_property_value = null ) {
		if ( ! $object || ! $primary_property_value ) {
			return null;
		}

		$props = array_keys( $object );
		$key   = $props[0];

		return $object[ $key ];
	}

	public function get_label() {
		return esc_html__( 'Database: ', 'gp-populate-anything' ) . DB_NAME;
	}

	public function get_groups() {
		return array(
			'columns' => array(
				'label' => esc_html__( 'Columns', 'gp-populate-anything' ),
			),
		);
	}

	public function get_primary_property() {
		return array(
			'id'       => 'table',
			'label'    => esc_html__( 'Table', 'gp-populate-anything' ),
			'callable' => array( $this, 'get_tables' ),
		);
	}

	public function get_properties( $table = null ) {

		if ( ! $table ) {
			return array();
		}

		$properties = array();

		foreach ( $this->get_columns( $table ) as $column ) {
			$properties[ $column['value'] ] = array(
				'group'     => 'columns',
				'label'     => $column['label'],
				'value'     => $column['value'],
				'orderby'   => true,
				'callable'  => array( $this, 'get_column_values' ),
				'args'      => array( $table, $column['value'] ),
				'operators' => array(
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
					'is_in',
					'is_not_in',
				),
			);
		}

		return $properties;

	}

	public function get_db() {
		global $wpdb;

		return $wpdb;
	}

	public function get_tables() {
		$result = $this->get_db()->get_results( 'SHOW FULL TABLES', ARRAY_N );

		return wp_list_pluck( $result, 0 );
	}

	public function get_columns( $table ) {
		$table   = self::esc_sql_ident( $table );
		$columns = array();

		$results = $this->get_db()->get_results( "SHOW COLUMNS FROM $table", ARRAY_N );

		foreach ( $results as $column ) {
			$columns[] = array(
				'value' => $column[0],
				'label' => $column[0],
			);
		}

		return $columns;
	}

	public function get_column_values( $table, $col ) {
		global $wpdb;

		$table = self::esc_sql_ident( $table );
		$col   = self::esc_sql_ident( $col );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$query  = $wpdb->prepare( "SELECT DISTINCT $col FROM $table LIMIT %d", gp_populate_anything()->get_query_limit( $this ) );
		$query  = apply_filters( 'gppa_object_type_database_column_value_query', $query, $table, $col, $this );
		$result = $this->get_db()->get_results( $query, ARRAY_N );

		return $this->filter_values( wp_list_pluck( $result, 0 ) );
	}

	public function process_filter_default( $query_builder_args, $args ) {

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

		$query_builder_args['where'][ $filter_group_index ][] = $this->build_where_clause( $primary_property_value, $property_id, $filter['operator'], $filter_value );

		return $query_builder_args;

	}

	public function default_query_args( $args ) {

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

		$orderby = rgar( $ordering, 'orderby' );
		$order   = rgar( $ordering, 'order', 'ASC' );

		return array(
			'select'   => '*',
			'from'     => $primary_property_value,
			'where'    => array(),
			'order_by' => $orderby,
			'order'    => $order,
		);

	}

	public function query_cache_hash( $args ) {
		$query_args = $this->process_query_args( $args, $this->default_query_args( $args ) );

		return $this->build_mysql_query( apply_filters( 'gppa_object_type_database_pre_query_parts', $query_args, $this ), rgar( $args, 'field' ) );
	}

	public function query( $args ) {

		$query_args = $this->process_query_args( $args, $this->default_query_args( $args ) );

		$query = $this->build_mysql_query( apply_filters( 'gppa_object_type_database_pre_query_parts', $query_args, $this ), rgar( $args, 'field' ) );

		return $this->get_db()->get_results( apply_filters( 'gppa_object_type_database_query', $query, $args, $this ), ARRAY_A );

	}

	public function get_object_prop_value( $object, $prop ) {

		if ( in_array( $prop, self::$blacklisted_columns, true ) ) {
			return null;
		}

		if ( ! isset( $object[ $prop ] ) ) {
			return null;
		}

		return $object[ $prop ];

	}

	public function build_where_clause( $table, $column, $operator, $value ) {
		$value = $this->maybe_convert_to_date( $table, $column, $value, $operator );
		return parent::build_where_clause( $table, $column, $operator, $value );
	}

	private $tables_cache = array(); // MySQL tables format cache

	/**
	 * Converts $value to MySQL friendly date if the table column is of type date.
	 *
	 * @param $table  string  Table name to look up
	 * @param $column string  Column name
	 * @param $value  string  Value to convert
	 * @param $operator  string The operator being used.
	 *
	 * @return string  Converted date value if applicable.
	 */
	private function maybe_convert_to_date( $table, $column, $value, $operator ) {
		$is_date = false;

		if ( isset( $this->tables_cache[ $table ] ) ) {
			$is_date = in_array( $this->tables_cache[ $table ][ $column ], array( 'date', 'datetime' ), true );
		} else {
			$structure = $this->get_db()->get_results( sprintf( 'DESCRIBE `%s`', esc_sql( $table ) ), ARRAY_N );

			foreach ( $structure as $row ) {
				$this->tables_cache[ $table ][ $row[0] ] = $row[1];
				if ( $row[0] === $column && in_array( $row[1], array( 'date', 'datetime' ), true ) ) {
					$is_date = true;
				}
			}
		}

		if ( $is_date ) {
			$value = gmdate( 'Y-m-d', strtotime( $value ) );

			switch ( $operator ) {
				case '>=':
				case '>':
					$value .= ' 00:00:00';
					break;

				case '<=':
				case '<':
					$value .= ' 23:59:59';
					break;
			}
		}

		return $value;
	}

}
