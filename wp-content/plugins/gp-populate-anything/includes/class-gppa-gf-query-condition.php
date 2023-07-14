<?php
// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared

/**
 * Overrides GF_Query_Condition with various niceties for Populate Anything such as concatenating values from multiple
 * inputs into a single string for easier searching.
 */
class GPPA_GF_Query_Condition extends GF_Query_Condition {
	public function sql( $query ) {
		global $wpdb;

		if ( ! is_numeric( $this->left->field_id ) || intval( $this->left->field_id ) != $this->left->field_id ) {
			return parent::sql( $query );
		}

		$field       = GFFormsModel::get_field( GFAPI::get_form( $this->left->source ), $this->left->field_id );
		$operator    = $this->operator;
		$is_negative = in_array( $operator, array( self::NLIKE, self::NBETWEEN, self::NEQ ) );

		/**
		 * Handle multi-input (but not choice-based) fields such as Name and concatenate the values so the entire
		 * value can be searched.
		 */
		if ( $field->get_entry_inputs() && empty( $field->choices ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$subquery = $wpdb->prepare(
				sprintf( "SELECT
    					GROUP_CONCAT(meta_value SEPARATOR ' ') as concat_value
						FROM `%s`
						WHERE `meta_key` LIKE %%s
						  AND `entry_id` = `%s`.`id`
						HAVING concat_value %s %s",
					GFFormsModel::get_entry_meta_table_name(),
					$query->_alias( null, $this->left->source ),
					$operator,
					str_replace( '%', '%%', $this->right->sql( $query ) )
				),
				sprintf( '%d.%%', $this->left->field_id )
			);

			$compare_condition = new self( new GF_Query_Call( sprintf( '%sEXISTS', $is_negative ? 'NOT ' : '' ), array( $subquery ) ) );

			return $compare_condition->sql( $query );
		}

		return parent::sql( $query );
	}


}
