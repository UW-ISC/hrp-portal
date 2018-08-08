<?php
/**
 * /premium/search.php
 *
 * @package Relevanssi_Premium
 * @author  Mikko Saari
 * @license https://wordpress.org/about/gpl/ GNU General Public License
 * @see     https://www.relevanssi.com/
 */

/**
 * Recognizes negative search terms.
 *
 * Finds all the search terms that begin with a -.
 *
 * @param string $q Search query.
 *
 * @return array $negative_terms Array of negative search terms.
 */
function relevanssi_recognize_negatives( $q ) {
	$term           = strtok( $q, ' ' );
	$negative_terms = array();
	while ( false !== $term ) {
		if ( '-' === substr( $term, 0, 1 ) ) {
			array_push( $negative_terms, substr( $term, 1 ) );
		}
		$term = strtok( ' ' );
	}
	return $negative_terms;
}

/**
 * Recognizes positive search terms.
 *
 * Finds all the search terms that begin with a +.
 *
 * @param string $q Search query.
 *
 * @return array $positive_terms Array of positive search terms.
 */
function relevanssi_recognize_positives( $q ) {
	$term           = strtok( $q, ' ' );
	$positive_terms = array();
	while ( false !== $term ) {
		if ( '+' === substr( $term, 0, 1 ) ) {
			$term_part = substr( $term, 1 );
			if ( ! empty( $term_part ) ) { // To avoid problems with just plus signs.
				array_push( $positive_terms, $term_part );
			}
		}
		$term = strtok( ' ' );
	}
	return $positive_terms;
}

/**
 * Creates SQL code for positive and negative terms.
 *
 * Creates the necessary SQL code for positive (AND) and negative (NOT) search terms.
 *
 * @param array  $negative_terms   Negative terms.
 * @param array  $positive_terms   Positive terms.
 * @param string $relevanssi_table Relevanssi table name.
 *
 * @return string $query_restrictions MySQL code for the terms.
 */
function relevanssi_negatives_positives( $negative_terms, $positive_terms, $relevanssi_table ) {
	$query_restrictions = '';
	if ( $negative_terms ) {
		$size = count( $negative_terms );
		for ( $i = 0; $i < $size; $i++ ) {
			$negative_terms[ $i ] = "'" . esc_sql( $negative_terms[ $i ] ) . "'";
		}
		$negatives           = implode( ',', $negative_terms );
		$query_restrictions .= " AND doc NOT IN (SELECT DISTINCT(doc) FROM $relevanssi_table WHERE term IN ( $negatives))";
		// Clean: $negatives is escaped.
	}

	if ( $positive_terms ) {
		$size = count( $positive_terms );
		for ( $i = 0; $i < $size; $i++ ) {
			$positive_term       = esc_sql( $positive_terms[ $i ] );
			$query_restrictions .= " AND doc IN (SELECT DISTINCT(doc) FROM $relevanssi_table WHERE term = '$positive_term')";
			// Clean: $positive_term is escaped.
		}
	}
	return $query_restrictions;
}

/**
 * Gets the recency bonus option.
 *
 * Gets the recency bonus and converts the cutoff day count to time().
 *
 * @return array $recency_bonus Array( recency bonus, cutoff date ).
 */
function relevanssi_get_recency_bonus() {
	$recency_bonus_option = get_option( 'relevanssi_recency_bonus' );
	$recency_bonus        = false;
	$recency_cutoff_date  = false;

	if ( isset( $recency_bonus_option['bonus'] ) ) {
		$recency_bonus = floatval( $recency_bonus_option['bonus'] );
	}
	if ( $recency_bonus && isset( $recency_bonus_option['days'] ) ) {
		$recency_cutoff_date = time() - 60 * 60 * 24 * $recency_bonus_option['days'];
	}

	return array( $recency_bonus, $recency_cutoff_date );
}

/**
 * Introduces the query variables for Relevanssi Premium.
 *
 * @param array $qv The WordPress query variable array.
 */
function relevanssi_premium_query_vars( $qv ) {
	$qv[] = 'searchblogs';
	$qv[] = 'customfield_key';
	$qv[] = 'customfield_value';
	$qv[] = 'operator';
	$qv[] = 'include_attachments';
	return $qv;
}

/**
 * Sets the operator parameter.
 *
 * The operator parameter is taken from $query->query_vars['operator'],
 * or from the implicit operator setting.
 *
 * @param object $query The query object.
 */
function relevanssi_set_operator( $query ) {
	if ( isset( $query->query_vars['operator'] ) ) {
		$operator = $query->query_vars['operator'];
	} else {
		$operator = get_option( 'relevanssi_implicit_operator' );
	}
	return $operator;
}

/**
 * Forms a tax_query from the taxonomy parameters.
 *
 * Improved handling of taxonomy parameters to support multiple taxonomies and terms.
 *
 * @param string $taxonomy      Taxonomies, with multiple taxonomies separated by '|'.
 * @param string $taxonomy_term Terms, with multiple terms separated by '|'.
 * @param array  $tax_query     The tax_query array.
 */
function relevanssi_process_taxonomies( $taxonomy, $taxonomy_term, $tax_query ) {
	$taxonomies = explode( '|', $taxonomy );
	$terms      = explode( '|', $taxonomy_term );

	$i = 0;
	foreach ( $taxonomies as $taxonomy ) {
		$term_tax_id    = null;
		$taxonomy_terms = explode( ',', $terms[ $i ] );
		foreach ( $taxonomy_terms as $taxonomy_term ) {
			if ( ! empty( $taxonomy_term ) ) {
				$tax_query[] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => $taxonomy_term,
				);
			}
		}
		$i++;
	}
	return $tax_query;
}
