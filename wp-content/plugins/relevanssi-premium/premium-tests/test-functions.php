<?php
/**
 * Class PremiumFunctionsTest
 *
 * @package Relevanssi_Premium
 * @author  Mikko Saari
 */

/**
 * Test Relevanssi Premium Functions.
 *
 * @group premium_functions
 */
class PremiumFunctionsTest extends WP_UnitTestCase {
	/**
	 * Sets up the tests.
	 */
	public static function wpSetUpBeforeClass() {
		relevanssi_install();
	}

	/**
	 * Test if synonym indexing fails when there are no synonyms.
	 */
	public function test_blank_synonyms() {
		delete_option( 'relevanssi_synonyms' );
		update_option( 'relevanssi_index_synonyms', 'on' );
		try {
			$this->assertEquals(
				array( 'a' => 1 ),
				relevanssi_add_indexing_synonyms( array( 'a' => 1 ) )
			);
		} catch ( Exception $e ) {
			$this->fail( "There was an error: $e" );
		}
	}

	/**
	 * Test the English stemmer.
	 */
	public function test_simple_english_stemmer() {
		$this->assertEquals(
			relevanssi_simple_english_stemmer( 'merrily' ),
			relevanssi_simple_english_stemmer( 'merry' )
		);
		$this->assertEquals(
			relevanssi_simple_english_stemmer( 'united' ),
			relevanssi_simple_english_stemmer( 'units' )
		);
		$this->assertEquals(
			relevanssi_simple_english_stemmer( 'happiest' ),
			relevanssi_simple_english_stemmer( 'happy' )
		);
		$this->assertEquals(
			relevanssi_simple_english_stemmer( 'searching' ),
			relevanssi_simple_english_stemmer( 'searcher' )
		);
		$this->assertEquals(
			relevanssi_simple_english_stemmer( 'commodity' ),
			relevanssi_simple_english_stemmer( 'commodities' )
		);
	}

	/**
	 * Uninstalls Relevanssi.
	 */
	public static function wpTearDownAfterClass() {
		require_once dirname( dirname( __FILE__ ) ) . '/lib/uninstall.php';
		require_once dirname( dirname( __FILE__ ) ) . '/premium/uninstall.php';

		if ( function_exists( 'relevanssi_uninstall' ) ) {
			relevanssi_uninstall();
		}
		if ( function_exists( 'relevanssi_uninstall_free' ) ) {
			relevanssi_uninstall_free();
		}
	}

}
