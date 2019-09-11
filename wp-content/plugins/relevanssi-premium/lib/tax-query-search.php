<?php
/**
 * /lib/tax_query_search.php
 *
 * Responsible for converting tax_query parameters to MySQL query restrictions.
 *
 * @package Relevanssi
 * @author  Mikko Saari
 * @license https://wordpress.org/about/gpl/ GNU General Public License
 * @see     https://www.relevanssi.com/
 */

/**
 * Processes the tax query to formulate a query restriction to the MySQL query.
 *
 * Tested.
 *
 * @uses relevanssi_process_tax_query_row()
 *
 * @global object $wpdb The WP database interface.
 *
 * @param string $tax_query_relation The base tax query relation. Default 'and'.
 * @param array  $tax_query          The tax query array.
 *
 * @return string The query restrictions for the MySQL query.
 */
function relevanssi_process_tax_query( $tax_query_relation, $tax_query ) {
	global $wpdb;

	$query_restrictions = '';

	if ( ! isset( $tax_query_relation ) ) {
		$tax_query_relation = 'and';
	}
	$tax_query_relation = relevanssi_strtolower( $tax_query_relation );
	$term_tax_ids       = array();
	$not_term_tax_ids   = array();
	$and_term_tax_ids   = array();

	$is_sub_row = false;
	foreach ( $tax_query as $row ) {
		if ( isset( $row['terms'] ) || ( isset( $row['operator'] ) && ( 'not exists' === strtolower( $row['operator'] ) || 'exists' === strtolower( $row['operator'] ) ) ) ) {
			list( $query_restrictions, $term_tax_ids, $not_term_tax_ids, $and_term_tax_ids ) =
			relevanssi_process_tax_query_row( $row, $is_sub_row, $tax_query_relation, $query_restrictions, $tax_query_relation, $term_tax_ids, $not_term_tax_ids, $and_term_tax_ids );
		} else {
			if ( is_array( $row ) ) {
				$row_tax_query_relation = $tax_query_relation;
				if ( isset( $row['relation'] ) ) {
					$row_tax_query_relation = $row['relation'];
				}
				$query_restrictions .= relevanssi_process_tax_query( $row_tax_query_relation, $row );
			}
		}
	}

	if ( 'or' === $tax_query_relation ) {
		$term_tax_ids = array_unique( $term_tax_ids );
		if ( count( $term_tax_ids ) > 0 ) {
			$term_tax_ids        = implode( ',', $term_tax_ids );
			$query_restrictions .= " AND relevanssi.doc IN (SELECT DISTINCT(tr.object_id) FROM $wpdb->term_relationships AS tr WHERE tr.term_taxonomy_id IN ($term_tax_ids))";
			// Clean: all variables are Relevanssi-generated.
		}
		if ( count( $not_term_tax_ids ) > 0 ) {
			$not_term_tax_ids    = implode( ',', $not_term_tax_ids );
			$query_restrictions .= " AND relevanssi.doc NOT IN (SELECT DISTINCT(tr.object_id) FROM $wpdb->term_relationships AS tr WHERE tr.term_taxonomy_id IN ($not_term_tax_ids))";
			// Clean: all variables are Relevanssi-generated.
		}
		if ( count( $and_term_tax_ids ) > 0 ) {
			$and_term_tax_ids    = implode( ',', $and_term_tax_ids );
			$n                   = count( explode( ',', $and_term_tax_ids ) );
			$query_restrictions .= " AND relevanssi.doc IN (
				SELECT ID FROM $wpdb->posts WHERE 1=1
				AND (
					SELECT COUNT(1)
					FROM $wpdb->term_relationships AS tr
					WHERE tr.term_taxonomy_id IN ($and_term_tax_ids)
					AND tr.object_id = $wpdb->posts.ID ) = $n
				)";
			// Clean: all variables are Relevanssi-generated.
		}
	}

	return $query_restrictions;
}

/**
 * Processes one tax_query row.
 *
 * Tested.
 *
 * @global object $wpdb The WordPress database interface.
 *
 * @param array   $row                The tax_query row array.
 * @param boolean $is_sub_row         True if this is a subrow.
 * @param string  $global_relation    The global tax_query relation (AND or OR).
 * @param string  $query_restrictions The MySQL query restriction.
 * @param string  $tax_query_relation The tax_query relation.
 * @param array   $term_tax_ids       Array of term taxonomy IDs.
 * @param array   $not_term_tax_ids   Array of excluded term taxonomy IDs.
 * @param array   $and_term_tax_ids   Array of AND term taxonomy IDs.
 *
 * @return array Returns an array where the first item is the updated
 * $query_restrictions, then $term_tax_ids, $not_term_tax_ids, and $and_term_tax_ids.
 */
function relevanssi_process_tax_query_row( $row, $is_sub_row, $global_relation, $query_restrictions, $tax_query_relation, $term_tax_ids, $not_term_tax_ids, $and_term_tax_ids ) {
	global $wpdb;

	$local_term_tax_ids     = array();
	$local_not_term_tax_ids = array();
	$local_and_term_tax_ids = array();
	$term_tax_id            = array();

	$exists_query = false;
	if ( isset( $row['operator'] ) && ( 'exists' === strtolower( $row['operator'] ) || 'not exists' === strtolower( $row['operator'] ) ) ) {
		$exists_query = true;
	}

	$using_term_tax_id = false;
	if ( $exists_query ) {
		$row['field'] = 'exists';
	}
	if ( ! isset( $row['field'] ) ) {
		$row['field'] = 'term_id'; // In case 'field' is not set, go with the WP default of 'term_id'.
	}
	$row['field'] = strtolower( $row['field'] ); // In some cases, you can get 'ID' instead of 'id'.
	if ( 'slug' === $row['field'] ) {
		$slug          = $row['terms'];
		$numeric_slugs = array();
		$slug_in       = null;
		if ( is_array( $slug ) ) {
			$slugs   = array();
			$term_id = array();
			foreach ( $slug as $t_slug ) {
				$term = get_term_by( 'slug', $t_slug, $row['taxonomy'] );
				if ( ! $term && is_numeric( $t_slug ) ) {
					$numeric_slugs[] = "'$t_slug'";
				} else {
					if ( isset( $term->term_id ) ) {
						$t_slug    = sanitize_title( $t_slug );
						$term_id[] = $term->term_id;
						$slugs[]   = "'$t_slug'";
					}
				}
			}
			if ( ! empty( $slugs ) ) {
				$slug_in = implode( ',', $slugs );
			}
		} else {
			$term = get_term_by( 'slug', $slug, $row['taxonomy'], OBJECT );
			if ( ! $term && is_numeric( $slug ) ) {
				$numeric_slugs[] = $slug;
			} else {
				if ( isset( $term->term_id ) ) {
					$slug    = sanitize_title( $slug );
					$term_id = $term->term_id;
					$slug_in = "'$slug'";
				}
			}
		}
		if ( ! empty( $slug_in ) ) {
			$row_taxonomy = sanitize_text_field( $row['taxonomy'] );

			$tt_q = "SELECT tt.term_taxonomy_id
				  	FROM $wpdb->term_taxonomy AS tt
				  	LEFT JOIN $wpdb->terms AS t ON (tt.term_id=t.term_id)
				  	WHERE tt.taxonomy = '$row_taxonomy' AND t.slug IN ($slug_in)";
			// Clean: $row_taxonomy is sanitized, each slug in $slug_in is sanitized.
			$term_tax_id = $wpdb->get_col( $tt_q ); // WPCS: unprepared SQL ok.
		}
		if ( ! empty( $numeric_slugs ) ) {
			$row['field'] = 'term_id';
		}
	}
	if ( 'name' === $row['field'] ) {
		$name          = $row['terms'];
		$numeric_names = array();
		$name_in       = null;
		if ( is_array( $name ) ) {
			$names   = array();
			$term_id = array();
			foreach ( $name as $t_name ) {
				$term = get_term_by( 'name', $t_name, $row['taxonomy'] );
				if ( ! $term && is_numeric( $t_name ) ) {
					$numeric_names[] = "'$t_name'";
				} else {
					if ( isset( $term->term_id ) ) {
						$t_name    = sanitize_title( $t_name );
						$term_id[] = $term->term_id;
						$names[]   = "'$t_name'";
					}
				}
			}
			if ( ! empty( $names ) ) {
				$name_in = implode( ',', $names );
			}
		} else {
			$term = get_term_by( 'name', $name, $row['taxonomy'] );
			if ( ! $term && is_numeric( $name ) ) {
				$numeric_slugs[] = $name;
			} else {
				if ( isset( $term->term_id ) ) {
					$name    = sanitize_title( $name );
					$term_id = $term->term_id;
					$name_in = "'$name'";
				}
			}
		}
		if ( ! empty( $name_in ) ) {
			$row_taxonomy = sanitize_text_field( $row['taxonomy'] );

			$tt_q = "SELECT tt.term_taxonomy_id
				  	FROM $wpdb->term_taxonomy AS tt
				  	LEFT JOIN $wpdb->terms AS t ON (tt.term_id=t.term_id)
				  	WHERE tt.taxonomy = '$row_taxonomy' AND t.name IN ($name_in)";
			// Clean: $row_taxonomy is sanitized, each name in $name_in is sanitized.
			$term_tax_id = $wpdb->get_col( $tt_q ); // WPCS: unprepared SQL ok.
		}
		if ( ! empty( $numeric_names ) ) {
			$row['field'] = 'term_id';
		}
	}
	if ( 'id' === $row['field'] || 'term_id' === $row['field'] ) {
		$id      = $row['terms'];
		$term_id = $id;
		if ( is_array( $id ) ) {
			$numeric_values = array();
			foreach ( $id as $t_id ) {
				if ( is_numeric( $t_id ) ) {
					$numeric_values[] = $t_id;
				}
			}
			$id = implode( ',', $numeric_values );
		}
		$row_taxonomy = sanitize_text_field( $row['taxonomy'] );

		if ( ! empty( $id ) ) {
			$tt_q = "SELECT tt.term_taxonomy_id
			FROM $wpdb->term_taxonomy AS tt
			LEFT JOIN $wpdb->terms AS t ON (tt.term_id=t.term_id)
			WHERE tt.taxonomy = '$row_taxonomy' AND t.term_id IN ($id)";
			// Clean: $row_taxonomy is sanitized, $id is checked to be numeric.
			$id_term_tax_id = $wpdb->get_col( $tt_q ); // WPCS: unprepared SQL ok.
			if ( ! empty( $term_tax_id ) && is_array( $term_tax_id ) ) {
				$term_tax_id = array_unique( array_merge( $term_tax_id, $id_term_tax_id ) );
			} else {
				$term_tax_id = $id_term_tax_id;
			}
		}
	}
	if ( 'term_taxonomy_id' === $row['field'] ) {
		$using_term_tax_id = true;
		$id                = $row['terms'];
		$term_tax_id       = $id;
		if ( is_array( $id ) ) {
			$numeric_values = array();
			foreach ( $id as $t_id ) {
				if ( is_numeric( $t_id ) ) {
					$numeric_values[] = $t_id;
				}
			}
			$term_tax_id = implode( ',', $numeric_values );
		}
	}

	if ( ! $exists_query && ( ! isset( $row['include_children'] ) || true === $row['include_children'] ) ) {
		if ( ! $using_term_tax_id && isset( $term_id ) ) {
			if ( ! is_array( $term_id ) ) {
				$term_id = array( $term_id );
			}
		} else {
			if ( ! is_array( $term_tax_id ) ) {
				$term_tax_id = array( $term_tax_id );
				$term_id     = $term_tax_id;
			}
		}
		if ( empty( $term_tax_id ) ) {
			$term_tax_id = array();
		}
		if ( ! is_array( $term_tax_id ) ) {
			$term_tax_id = array( $term_tax_id );
		}
		if ( isset( $term_id ) && is_array( $term_id ) ) {
			foreach ( $term_id as $t_id ) {
				if ( $using_term_tax_id ) {
					$t_term = get_term_by( 'term_taxonomy_id', $t_id, $row['taxonomy'] );
					$t_id   = $t_term->ID;
				}
				$kids = get_term_children( $t_id, $row['taxonomy'] );
				foreach ( $kids as $kid ) {
					$kid_term_tax_id = relevanssi_get_term_tax_id( $kid, $row['taxonomy'] );
					if ( $kid_term_tax_id ) {
						// In some weird cases, this may be null. See: https://wordpress.org/support/topic/childrens-of-chosen-product_cat-not-showing-up/.
						$term_tax_id[] = $kid_term_tax_id;
					}
				}
			}
		}
	}

	$term_tax_id = array_unique( $term_tax_id );
	if ( ! empty( $term_tax_id ) ) {
		$n           = count( $term_tax_id );
		$term_tax_id = implode( ',', $term_tax_id );

		$tq_operator = 'IN'; // Assuming the default operator "IN", unless something else is provided.
		if ( isset( $row['operator'] ) ) {
			$tq_operator = strtoupper( $row['operator'] );
		}
		if ( ! in_array( $tq_operator, array( 'IN', 'NOT IN', 'AND' ), true ) ) {
			$tq_operator = 'IN';
		}
		if ( 'and' === $tax_query_relation ) {
			if ( 'AND' === $tq_operator ) {
				$query_restrictions .= " AND relevanssi.doc IN (
					SELECT ID FROM $wpdb->posts WHERE 1=1
					AND (
						SELECT COUNT(1)
						FROM $wpdb->term_relationships AS tr
						WHERE tr.term_taxonomy_id IN ($term_tax_id)
						AND tr.object_id = $wpdb->posts.ID ) = $n
					)";
				// Clean: $term_tax_id and $n are Relevanssi-generated.
			} else {
				$query_restrictions .= " AND relevanssi.doc $tq_operator (SELECT DISTINCT(tr.object_id) FROM $wpdb->term_relationships AS tr
				WHERE tr.term_taxonomy_id IN ($term_tax_id))";
				// Clean: all variables are Relevanssi-generated.
			}
		} else {
			if ( 'IN' === $tq_operator ) {
				$local_term_tax_ids[] = $term_tax_id;
			}
			if ( 'NOT IN' === $tq_operator ) {
				$local_not_term_tax_ids[] = $term_tax_id;
			}
			if ( 'AND' === $tq_operator ) {
				$local_and_term_tax_ids[] = $term_tax_id;
			}
		}
	} else {
		global $wp_query;
		$wp_query->is_category = false;
	}

	if ( $is_sub_row && 'and' === $global_relation && 'or' === $tax_query_relation ) {
		$local_term_tax_ids     = array_unique( $local_term_tax_ids );
		$local_not_term_tax_ids = array_unique( $local_not_term_tax_ids );
		$local_and_term_tax_ids = array_unique( $local_and_term_tax_ids );

		if ( count( $local_term_tax_ids ) > 0 ) {
			$local_term_tax_ids  = implode( ',', $local_term_tax_ids );
			$query_restrictions .= " AND relevanssi.doc IN (SELECT DISTINCT(tr.object_id) FROM $wpdb->term_relationships AS tr
		    	WHERE tr.term_taxonomy_id IN ($local_term_tax_ids))";
			// Clean: all variables are Relevanssi-generated.
		}
		if ( count( $local_not_term_tax_ids ) > 0 ) {
			$local_not_term_tax_ids = implode( ',', $local_not_term_tax_ids );
			$query_restrictions    .= " AND relevanssi.doc NOT IN (SELECT DISTINCT(tr.object_id) FROM $wpdb->term_relationships AS tr
		    	WHERE tr.term_taxonomy_id IN ($local_not_term_tax_ids))";
			// Clean: all variables are Relevanssi-generated.
		}
		if ( count( $local_and_term_tax_ids ) > 0 ) {
			$local_and_term_tax_ids = implode( ',', $local_and_term_tax_ids );
			$n                      = count( explode( ',', $local_and_term_tax_ids ) );
			$query_restrictions    .= " AND relevanssi.doc IN (
				SELECT ID FROM $wpdb->posts WHERE 1=1
				AND (
					SELECT COUNT(1)
					FROM $wpdb->term_relationships AS tr
					WHERE tr.term_taxonomy_id IN ($local_and_term_tax_ids)
					AND tr.object_id = $wpdb->posts.ID ) = $n
				)";
			// Clean: all variables are Relevanssi-generated.
		}
	}

	$copy_term_tax_ids = false;
	if ( ! $is_sub_row ) {
		$copy_term_tax_ids = true;
	}
	if ( $is_sub_row && 'or' === $global_relation ) {
		$copy_term_tax_ids = true;
	}

	if ( $copy_term_tax_ids ) {
		$term_tax_ids     = array_merge( $term_tax_ids, $local_term_tax_ids );
		$not_term_tax_ids = array_merge( $not_term_tax_ids, $local_not_term_tax_ids );
		$and_term_tax_ids = array_merge( $and_term_tax_ids, $local_and_term_tax_ids );
	}

	if ( $exists_query ) {
		$taxonomy = $row['taxonomy'];
		$operator = 'IN';
		if ( 'not exists' === strtolower( $row['operator'] ) ) {
			$operator = 'NOT IN';
		}
		$query_restrictions .= " AND relevanssi.doc $operator (SELECT DISTINCT(tr.object_id) FROM $wpdb->term_relationships AS tr,
			$wpdb->term_taxonomy AS tt WHERE tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = '$taxonomy')";
	}

	return array( $query_restrictions, $term_tax_ids, $not_term_tax_ids, $and_term_tax_ids );
}
