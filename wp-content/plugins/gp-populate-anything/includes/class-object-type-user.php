<?php

class GPPA_Object_Type_User extends GPPA_Object_Type {

	private static $blacklisted_props = array( 'user_pass', 'user_activation_key' );

	private $meta_query_counter = 0;

	public function __construct( $id ) {
		parent::__construct( $id );

		add_action( 'gppa_pre_object_type_query_user', array( $this, 'add_filter_hooks' ) );
	}

	public function supported_operators() {
		return array_merge( gp_populate_anything()->get_default_operators(), array( 'is_in', 'is_not_in' ) );
	}

	/**
	 * Extract unique identifier for a given user.
	 *
	 * @param WP_User|null $object
	 * @param null|string $primary_property_value
	 */
	public function get_object_id( $object, $primary_property_value = null ) {
		if ( empty( $object ) ) {
			return -1;
		}

		return $object->ID;
	}

	public function get_label() {
		return esc_html__( 'User', 'gp-populate-anything' );
	}

	public function get_groups() {
		return array(
			'meta'        => array(
				'label'  => esc_html__( 'User Meta', 'gp-populate-anything' ),
				'prefix' => 'meta_',
			),
			'bp_xprofile' => array(
				'label'  => esc_html__( 'BuddyPress Extended Profile', 'gp-populate-anything' ),
				'prefix' => 'bp_xprofile_',
			),
		);
	}

	/**
	 * @param $property_id string
	 *
	 * @return string|null
	 */
	public function get_group_from_property_id( $property_id ) {
		if ( strpos( $property_id, 'meta_' ) === 0 ) {
			return 'meta';
		} elseif ( strpos( $property_id, 'bp_xprofile_' ) === 0 ) {
			return 'bp_xprofile';
		}

		return null;
	}

	public function get_property_value_from_property_id( $property_id ) {
		$property_id = preg_replace( '/^meta_/', '', $property_id );
		$property_id = preg_replace( '/^bp_xprofile_/', '', $property_id );

		return $property_id;
	}

	public function get_default_templates() {
		return array(
			'value' => 'ID',
			'label' => 'display_name',
		);
	}

	public function add_filter_hooks() {
		add_filter( 'gppa_object_type_user_filter', array( $this, 'process_filter_default' ), 10, 2 );
		add_filter( 'gppa_object_type_user_filter_roles', array( $this, 'process_filter_roles' ), 10, 2 );
		add_filter( 'gppa_object_type_user_filter_group_meta', array( $this, 'process_filter_meta' ), 10, 2 );
		add_filter( 'gppa_object_type_user_filter_group_bp_xprofile', array( $this, 'process_filter_bp_xprofile' ), 10, 2 );

		add_filter( 'gppa_object_type_query_user', array( $this, 'maybe_add_primary_blog_where' ), 10, 2 );
	}

	public function process_filter_default( $query_builder_args, $args ) {

		global $wpdb;

		/** @var string|string[] */
		$filter_value = null;

		/** @var array */
		$filter = null;

		/** @var array */
		$filter_group = null;

		/** @var int */
		$filter_group_index = null;

		/** @var string */
		$property = null;

		/** @var string */
		$property_id = null;

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( $args );

		$query_builder_args['where'][ $filter_group_index ][] = $this->build_where_clause( $wpdb->users, rgar( $property, 'value' ), $filter['operator'], $filter_value );

		return $query_builder_args;

	}

	public function process_filter_roles( $query_builder_args, $args ) {

		global $wpdb;

		/** @var string|string[] */
		$filter_value = null;

		/** @var array */
		$filter = null;

		/** @var array */
		$filter_group = null;

		/** @var int */
		$filter_group_index = null;

		/** @var string */
		$property = null;

		/** @var string */
		$property_id = null;

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( $args );

		$meta_value = $this->get_sql_value( 'contains', '"' . $filter_value . '"' );

		$blog_id  = get_current_blog_id();
		$operator = rgar( $filter, 'operator' ) === 'isnot' ? 'NOT LIKE' : 'LIKE';

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$where = $wpdb->prepare( "( {$wpdb->usermeta}.meta_key = %s AND {$wpdb->usermeta}.meta_value {$operator} %s )", $wpdb->get_blog_prefix( $blog_id ) . 'capabilities', $meta_value );

		$query_builder_args['where'][ $filter_group_index ][] = $where;

		// Experimental! Set this join as the first join to improve performance on for large user meta tables.
		// See: https://secure.helpscout.net/conversation/1080344220/15842?folderId=14965
		$query_builder_args['joins'] = array_merge(
			array(
				'usermeta' => "LEFT JOIN {$wpdb->usermeta} ON ( {$wpdb->users}.ID = {$wpdb->usermeta}.user_id )",
			),
			$query_builder_args['joins']
		);

		return $query_builder_args;

	}

	public function process_filter_meta( $query_builder_args, $args ) {

		global $wpdb;

		/** @var string|string[] */
		$filter_value = null;

		/** @var array */
		$filter = null;

		/** @var array */
		$filter_group = null;

		/** @var int */
		$filter_group_index = null;

		/** @var string */
		$property = null;

		/** @var string */
		$property_id = null;

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( $args );

		$meta_operator      = $this->get_sql_operator( $filter['operator'] );
		$meta_specification = $this->get_value_specification( $filter_value, $filter['operator'], $meta_operator );
		$meta_value         = $this->get_sql_value( $filter['operator'], $filter_value );

		$this->meta_query_counter++;
		$as_table = 'mq' . $this->meta_query_counter;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$query_builder_args['where'][ $filter_group_index ][] = $wpdb->prepare( "( {$as_table}.meta_key = %s AND {$as_table}.meta_value {$meta_operator} {$meta_specification} )", rgar( $property, 'value' ), $meta_value );
		$query_builder_args['joins'][ $as_table ]             = "LEFT JOIN {$wpdb->usermeta} AS {$as_table} ON ( {$wpdb->users}.ID = {$as_table}.user_id )";

		return $query_builder_args;

	}

	public function process_filter_bp_xprofile( $query_builder_args, $args ) {

		global $wpdb;

		/** @var string|string[] */
		$filter_value = null;

		/** @var array */
		$filter = null;

		/** @var array */
		$filter_group = null;

		/** @var int */
		$filter_group_index = null;

		/** @var string */
		$property = null;

		/** @var string */
		$property_id = null;

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( $args );

		$data_operator      = $this->get_sql_operator( $filter['operator'] );
		$data_specification = $this->get_value_specification( $filter_value, $filter['operator'], $data_operator );
		$data_value         = $this->get_sql_value( $filter['operator'], $filter_value );

		// @phpstan-ignore-next-line
		$bp_prefix     = bp_core_get_table_prefix();
		$bp_data_table = $bp_prefix . 'bp_xprofile_data';

		$as_table = 'bp_data';

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$query_builder_args['where'][ $filter_group_index ][] = $wpdb->prepare( "( {$as_table}.field_id = %s AND {$as_table}.value {$data_operator} {$data_specification} )", rgar( $property, 'value' ), $data_value );
		$query_builder_args['joins'][ $as_table ]             = "LEFT JOIN {$bp_data_table} AS {$as_table} ON ( {$wpdb->users}.ID = {$as_table}.user_id )";

		return $query_builder_args;

	}

	public function get_properties( $primary_property = null ) {

		global $wpdb;

		return array_merge(
			array(
				'display_name' => array(
					'label'     => esc_html__( 'Display Name', 'gp-populate-anything' ),
					'value'     => 'display_name',
					'callable'  => array( $this, 'get_user_col_rows' ),
					'args'      => array( $wpdb->users, 'display_name' ),
					'orderby'   => true,
					'operators' => $this->supported_operators(),
				),
				'ID'           => array(
					'label'     => esc_html__( 'User ID', 'gp-populate-anything' ),
					'value'     => 'ID',
					'callable'  => array( $this, 'get_user_col_rows' ),
					'args'      => array( $wpdb->users, 'ID' ),
					'orderby'   => true,
					'operators' => $this->supported_operators(),
				),
				'user_login'   => array(
					'label'     => esc_html__( 'Username', 'gp-populate-anything' ),
					'value'     => 'user_login',
					'callable'  => array( $this, 'get_user_col_rows' ),
					'args'      => array( $wpdb->users, 'user_login' ),
					'orderby'   => true,
					'operators' => $this->supported_operators(),
				),
				'user_email'   => array(
					'label'     => esc_html__( 'User Email', 'gp-populate-anything' ),
					'value'     => 'user_email',
					'callable'  => array( $this, 'get_user_col_rows' ),
					'args'      => array( $wpdb->users, 'user_email' ),
					'orderby'   => true,
					'operators' => $this->supported_operators(),
				),
				'user_url'     => array(
					'label'     => esc_html__( 'User URL', 'gp-populate-anything' ),
					'value'     => 'user_url',
					'callable'  => array( $this, 'get_user_col_rows' ),
					'args'      => array( $wpdb->users, 'user_url' ),
					'orderby'   => true,
					'operators' => $this->supported_operators(),
				),
				'roles'        => array(
					'label'     => esc_html__( 'Role', 'gp-populate-anything' ),
					'value'     => 'roles',
					'callable'  => array( $this, 'get_user_roles' ),
					'operators' => array(
						'is',
						'isnot',
					),
				),
			),
			$this->get_buddypress_xprofile_properties(),
			$this->get_user_meta_properties()
		);

	}

	public function get_object_prop_value( $object, $prop ) {

		if ( in_array( $prop, self::$blacklisted_props, true ) ) {
			return null;
		}

		if ( empty( $object ) ) {
			return null;
		}

		/* BuddyPress Extended Profile */
		if ( strpos( $prop, 'bp_xprofile_' ) === 0 ) {

			$xprofile_field = preg_replace( '/^bp_xprofile_/', '', $prop );
			$args           = array(
				'user_id' => $object->ID,
				'field'   => $xprofile_field,
			);

			if ( function_exists( 'bp_get_profile_field_data' ) ) {
				return bp_get_profile_field_data( $args );
			}
		}

		$prop  = preg_replace( '/^meta_/', '', $prop );
		$value = $object->{$prop};

		switch ( $prop ) {
			case 'roles':
				$value = implode( ', ', $value );
				break;
		}

		return $value;

	}

	public function get_current_blog_user_ids() {
		global $wpdb;

		return $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT {$wpdb->users}.ID
						FROM {$wpdb->users}
						LEFT JOIN {$wpdb->usermeta} AS um ON ( {$wpdb->users}.ID = um.user_id )
						WHERE um.meta_key = 'primary_blog' AND um.meta_value = %d",
				get_current_blog_id()
			)
		);
	}

	public function get_current_blog_user_ids_where_clause( $table = null, $column = 'ID' ) {
		global $wpdb;

		if ( ! is_multisite() || ! apply_filters( 'gppa_object_type_user_limit_to_current_site', true ) ) {
			return null;
		}

		if ( ! $table ) {
			$table = $wpdb->users;
		}

		$current_site_user_ids_in_clause = implode( ',', $this->get_current_blog_user_ids() );

		return " WHERE {$table}.{$column} IN({$current_site_user_ids_in_clause})";
	}

	public function get_user_col_rows( $table, $property ) {

		return $this->get_col_rows( $table, $property, $this->get_current_blog_user_ids_where_clause() );

	}

	public function get_user_meta_properties() {

		global $wpdb;

		$user_meta_properties = array();
		$meta_rows            = $this->get_col_rows( $wpdb->usermeta, 'meta_key', $this->get_current_blog_user_ids_where_clause( $wpdb->usermeta, 'user_id' ) );

		foreach ( $meta_rows as $user_meta_key ) {
			$user_meta_properties[ 'meta_' . $user_meta_key ] = array(
				'label'     => $user_meta_key,
				'value'     => $user_meta_key,
				'meta'      => true,
				'callable'  => array( $this, 'get_meta_values' ),
				'args'      => array( $user_meta_key, $wpdb->usermeta ),
				'group'     => 'meta',
				'orderby'   => true,
				'operators' => $this->supported_operators(),
			);
		}

		return $user_meta_properties;

	}

	public function get_buddypress_xprofile_properties() {

		if ( ! function_exists( 'bp_core_get_table_prefix' ) ) {
			return array();
		}

		global $wpdb;

		$xprofile_properties = array();
		$bp_prefix           = bp_core_get_table_prefix();

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$query_results = $wpdb->get_results( "SELECT id, name FROM {$bp_prefix}bp_xprofile_fields" );

		foreach ( $query_results as $field ) {
			$xprofile_properties[ 'bp_xprofile_' . $field->id ] = array(
				'label'     => $field->name,
				'value'     => $field->id,
				'meta'      => true,
				'callable'  => array( $this, 'get_buddypress_xprofile_values' ),
				'args'      => array( $field->id ),
				'group'     => 'bp_xprofile',
				'operators' => $this->supported_operators(),
			);
		}

		return $xprofile_properties;

	}

	public function get_buddypress_xprofile_values( $field_id ) {

		if ( ! function_exists( 'bp_core_get_table_prefix' ) ) {
			return array();
		}

		global $wpdb;

		$bp_prefix = bp_core_get_table_prefix();

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$result = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT value FROM {$bp_prefix}bp_xprofile_data WHERE field_id = %d", $field_id ) );

		return is_array( $result ) ? $this->filter_values( $result ) : array();

	}

	public function get_user_roles() {

		$output = array();

		foreach ( get_editable_roles() as $role_name => $role_info ) {
			$output[ $role_name ] = $role_info['name'];
		}

		return $output;

	}

	public function default_query_args( $args ) {

		global $wpdb;

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

		$query_args = array(
			'select'   => "{$wpdb->users}.*",
			'from'     => $wpdb->users,
			'where'    => array(),
			'joins'    => array(),
			'group_by' => "{$wpdb->users}.ID",
			'order_by' => $orderby ? "{$wpdb->users}.{$orderby}" : '',
			'order'    => $order,
		);

		/**
		 * Support ordering by user meta.
		 *
		 * Logic was borrowed from Post Object type.
		 */
		if ( strpos( $orderby, 'meta_' ) === 0 ) {
			$meta_key = str_replace( 'meta_', '', $orderby );

			/**
			 * This order_by setup is required to get both numeric meta and alphanumeric meta to sort is a some-what
			 * natural order.
			 */
			$query_args['order_by'] = "
					(
						SELECT ({$wpdb->usermeta}.meta_value + 0)
							FROM {$wpdb->usermeta}
							WHERE {$wpdb->users}.ID = {$wpdb->usermeta}.user_id
							AND {$wpdb->usermeta}.meta_key = '{$meta_key}'
							LIMIT 1
					) {$order},
					(
						SELECT ({$wpdb->usermeta}.meta_value)
							FROM {$wpdb->usermeta}
							WHERE {$wpdb->users}.ID = {$wpdb->usermeta}.user_id
							AND {$wpdb->usermeta}.meta_key = '{$meta_key}'
							LIMIT 1
					)";
		}

		return $query_args;

	}

	public function add_primary_blog_where( $query_builder_args, $index = null ) {

		global $wpdb;

		$where = $wpdb->prepare(
			'( um_primary_blog.meta_key = %s AND um_primary_blog.meta_value = %d )',
			'primary_blog',
			get_current_blog_id()
		);

		$query_builder_args['where'][ $index ]['primary_blog'] = $where;
		$query_builder_args['joins']['primary_blog']           = "LEFT JOIN {$wpdb->usermeta} um_primary_blog ON ( {$wpdb->users}.ID = um_primary_blog.user_id )";

		return $query_builder_args;

	}

	public function maybe_add_primary_blog_where( $query_builder_args, $args ) {

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

		if ( ! is_multisite() || ! apply_filters( 'gppa_object_type_user_limit_to_current_site', true ) ) {
			return $query_builder_args;
		}

		if ( ! is_array( $query_builder_args['where'] ) || ! count( $query_builder_args['where'] ) ) {
			return $this->add_primary_blog_where( $query_builder_args );

		}

		foreach ( $query_builder_args['where'] as $filter_group_index => $filter_group_wheres ) {
			$query_builder_args = $this->add_primary_blog_where( $query_builder_args, $filter_group_index );
		}

		return $query_builder_args;

	}

	/**
	 * @param $args array  Query arguments to hash
	 *
	 * @return string   SHA1 representation of the requested query
	 */
	public function query_cache_hash( $args ) {
		return sha1( serialize( $this->process_query_args( $args, $this->default_query_args( $args ) ) ) );
	}

	public function query( $args ) {

		static $_cache;

		global $wpdb;

		$query_args = $this->process_query_args( $args, $this->default_query_args( $args ) );

		$query = $this->build_mysql_query( $query_args, rgar( $args, 'field' ) );

		/* Reset meta query counter */
		$this->meta_query_counter = 0;

		if ( isset( $_cache[ $query ] ) ) {
			return $_cache[ $query ];
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$users = $wpdb->get_results( $query );

		foreach ( $users as $key => $user ) {
			$users[ $key ] = new WP_User( $user );

			/* Remove certain keys for security */
			if ( isset( $users[ $key ]->user_pass ) ) {
				unset( $users[ $key ]->user_pass );
			}
		}

		$_cache[ $query ] = $users;

		return $users;

	}

}
