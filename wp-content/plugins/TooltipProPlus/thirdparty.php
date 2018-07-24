<?php

class CMTT_Google_API {

	const TABLENAME		 = 'glossary_database_cache';
	const CACHE_GROUP		 = 'cmtt_google_cache';
	const COLUMN_TITLE	 = 'title';
	const COLUMN_CONTENT	 = 'content';

	private static $cmtt_glossary_google_languages = array(
		0	 =>
		array(
			'language'	 => 'af',
			'name'		 => 'Afrikaans',
		),
		1	 =>
		array(
			'language'	 => 'sq',
			'name'		 => 'Albanian',
		),
		2	 =>
		array(
			'language'	 => 'ar',
			'name'		 => 'Arabic',
		),
		3	 =>
		array(
			'language'	 => 'be',
			'name'		 => 'Belarusian',
		),
		4	 =>
		array(
			'language'	 => 'bg',
			'name'		 => 'Bulgarian',
		),
		5	 =>
		array(
			'language'	 => 'ca',
			'name'		 => 'Catalan',
		),
		6	 =>
		array(
			'language'	 => 'zh',
			'name'		 => 'Chinese (Simplified)',
		),
		7	 =>
		array(
			'language'	 => 'zh-TW',
			'name'		 => 'Chinese (Traditional)',
		),
		8	 =>
		array(
			'language'	 => 'hr',
			'name'		 => 'Croatian',
		),
		9	 =>
		array(
			'language'	 => 'cs',
			'name'		 => 'Czech',
		),
		10	 =>
		array(
			'language'	 => 'da',
			'name'		 => 'Danish',
		),
		11	 =>
		array(
			'language'	 => 'nl',
			'name'		 => 'Dutch',
		),
		12	 =>
		array(
			'language'	 => 'en',
			'name'		 => 'English',
		),
		13	 =>
		array(
			'language'	 => 'eo',
			'name'		 => 'Esperanto',
		),
		14	 =>
		array(
			'language'	 => 'et',
			'name'		 => 'Estonian',
		),
		15	 =>
		array(
			'language'	 => 'tl',
			'name'		 => 'Filipino',
		),
		16	 =>
		array(
			'language'	 => 'fi',
			'name'		 => 'Finnish',
		),
		17	 =>
		array(
			'language'	 => 'fr',
			'name'		 => 'French',
		),
		18	 =>
		array(
			'language'	 => 'gl',
			'name'		 => 'Galician',
		),
		19	 =>
		array(
			'language'	 => 'de',
			'name'		 => 'German',
		),
		20	 =>
		array(
			'language'	 => 'el',
			'name'		 => 'Greek',
		),
		21	 =>
		array(
			'language'	 => 'ht',
			'name'		 => 'Haitian Creole',
		),
		22	 =>
		array(
			'language'	 => 'iw',
			'name'		 => 'Hebrew',
		),
		23	 =>
		array(
			'language'	 => 'hi',
			'name'		 => 'Hindi',
		),
		24	 =>
		array(
			'language'	 => 'hu',
			'name'		 => 'Hungarian',
		),
		25	 =>
		array(
			'language'	 => 'is',
			'name'		 => 'Icelandic',
		),
		26	 =>
		array(
			'language'	 => 'id',
			'name'		 => 'Indonesian',
		),
		27	 =>
		array(
			'language'	 => 'ga',
			'name'		 => 'Irish',
		),
		28	 =>
		array(
			'language'	 => 'it',
			'name'		 => 'Italian',
		),
		29	 =>
		array(
			'language'	 => 'ja',
			'name'		 => 'Japanese',
		),
		30	 =>
		array(
			'language'	 => 'ko',
			'name'		 => 'Korean',
		),
		31	 =>
		array(
			'language'	 => 'lv',
			'name'		 => 'Latvian',
		),
		32	 =>
		array(
			'language'	 => 'lt',
			'name'		 => 'Lithuanian',
		),
        'lb' =>
        array(
            'language' => 'lb',
            'name'     => 'Luxembourgish',
        ),
		33	 =>
		array(
			'language'	 => 'mk',
			'name'		 => 'Macedonian',
		),
		34	 =>
		array(
			'language'	 => 'ms',
			'name'		 => 'Malay',
		),
		35	 =>
		array(
			'language'	 => 'mt',
			'name'		 => 'Maltese',
		),
		36	 =>
		array(
			'language'	 => 'no',
			'name'		 => 'Norwegian',
		),
		37	 =>
		array(
			'language'	 => 'fa',
			'name'		 => 'Persian',
		),
		38	 =>
		array(
			'language'	 => 'pl',
			'name'		 => 'Polish',
		),
		39	 =>
		array(
			'language'	 => 'pt',
			'name'		 => 'Portuguese',
		),
		40	 =>
		array(
			'language'	 => 'ro',
			'name'		 => 'Romanian',
		),
		41	 =>
		array(
			'language'	 => 'ru',
			'name'		 => 'Russian',
		),
		42	 =>
		array(
			'language'	 => 'sr',
			'name'		 => 'Serbian',
		),
		43	 =>
		array(
			'language'	 => 'sk',
			'name'		 => 'Slovak',
		),
		44	 =>
		array(
			'language'	 => 'sl',
			'name'		 => 'Slovenian',
		),
		45	 =>
		array(
			'language'	 => 'es',
			'name'		 => 'Spanish',
		),
		46	 =>
		array(
			'language'	 => 'sw',
			'name'		 => 'Swahili',
		),
		47	 =>
		array(
			'language'	 => 'sv',
			'name'		 => 'Swedish',
		),
		48	 =>
		array(
			'language'	 => 'th',
			'name'		 => 'Thai',
		),
		49	 =>
		array(
			'language'	 => 'tr',
			'name'		 => 'Turkish',
		),
		50	 =>
		array(
			'language'	 => 'uk',
			'name'		 => 'Ukrainian',
		),
		51	 =>
		array(
			'language'	 => 'vi',
			'name'		 => 'Vietnamese',
		),
		52	 =>
		array(
			'language'	 => 'cy',
			'name'		 => 'Welsh',
		),
		53	 =>
		array(
			'language'	 => 'yi',
			'name'		 => 'Yiddish',
		),
	);

	public static function getLanguages() {
		return self::$cmtt_glossary_google_languages;
	}

	public static function getApiKey() {
		return get_option( 'cmtt_tooltip3RDGoogleApiKey', '' );
	}

	public static function install() {
		global $wpdb;
		$sql = "CREATE TABLE {$wpdb->prefix}" . self::TABLENAME . " (
                id INT(11) NOT NULL AUTO_INCREMENT,
                term VARCHAR(64) NOT NULL,
                thesaurus TEXT NULL,
                dictionary TEXT NULL,
                wikipedia TEXT NULL,
                translate_title TEXT NULL,
                translate_content TEXT NULL,
                PRIMARY KEY  (id),
                KEY term_id (id)
              )
          CHARACTER SET utf8 COLLATE utf8_general_ci;";
		include_once(ABSPATH . 'wp-admin/includes/upgrade.php');
//        if($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}" . self::TABLENAME . "'") == $wpdb->prefix . self::TABLENAME)
//        {
//            $wpdb->query("ALTER TABLE {$wpdb->prefix}" . self::TABLENAME . " DROP INDEX id");
//        }
		dbDelta( $sql );
		$sql = "DELETE FROM " . $wpdb->prefix . self::TABLENAME . "";
		$wpdb->query( $sql );
	}

	public static function flushDatabase() {
		global $wpdb;
		$sql = "TRUNCATE TABLE " . $wpdb->prefix . self::TABLENAME;
		$wpdb->query( $sql );
	}

	public static $sourceId		 = null;
	public static $targetId		 = null;
	public static $isShortcode	 = false;
	public static $tableExists	 = false;

	public static function translateShortcode( $atts ) {
		extract( shortcode_atts( array(
			'term'	 => '',
			'source' => get_option( 'cmtt_tooltip3RDGoogleSource', -1 ),
			'target' => get_option( 'cmtt_tooltip3RDGoogleTarget', -1 )
		), $atts ) );

		$term = str_replace( array( '"', '\'' ), array( '', '' ), html_entity_decode( $term, ENT_COMPAT, 'utf-8' ) );

		self::$sourceId		 = (is_numeric( $source )) ? $source : self::languageReverseLookup( $source );
		self::$targetId		 = (is_numeric( $target )) ? $target : self::languageReverseLookup( $target );
		self::$isShortcode	 = true;

		return self::translate( $term, $term );
	}

	protected static function languageReverseLookup( $name ) {
		$lowerName = mb_strtolower( $name );

		foreach ( self::$cmtt_glossary_google_languages as $langId => $langArr ) {
			if ( $lowerName == mb_strtolower( $langArr[ 'name' ] ) ) {
				return $langId;
			}
		}

		return -1;
	}

	public static function addShortcodes() {
		add_shortcode( 'glossary_translate', array( __CLASS__, 'translateShortcode' ) );
		add_action( 'wp_ajax_cmtt_test_google_api', array( __CLASS__, 'testGoogle' ) );
	}

	public static function testGoogle() {
		self::checkIfTableExists();

		$source_id	 = get_option( 'cmtt_tooltip3RDGoogleSource', -1 );
		$target_id	 = get_option( 'cmtt_tooltip3RDGoogleTarget', -1 );

		if ( self::getApiKey() != '' ) {
			if ( $source_id != -1 && $target_id != -1 ) {
				$langs = self::getLanguages();

				$target		 = isset( $langs[ $target_id ] ) ? $langs[ $target_id ] : null;
				$targetCode	 = isset( $target[ "language" ] ) ? $target[ "language" ] : 'en';

				if ( $targetCode !== 'en' ) {
					$sourceCode	 = 'en';
					$term		 = 'creative';
				} else {
					$sourceCode	 = 'es';
					$term		 = 'creativo';
				}

				$query	 = http_build_query( array( 'key' => self::getApiKey(), 'q' => $term, 'source' => $sourceCode, 'target' => $targetCode ) );
				$url	 = 'https://www.googleapis.com/language/translate/v2?' . $query;

				$handle		 = curl_init( $url );
				curl_setopt( $handle, CURLOPT_SSL_VERIFYHOST, 0 );
				curl_setopt( $handle, CURLOPT_SSL_VERIFYPEER, 0 );
				curl_setopt( $handle, CURLOPT_RETURNTRANSFER, true );
				$response	 = curl_exec( $handle );
				$error		 = curl_error( $handle );
				curl_close( $handle );

				if ( !empty( $error ) ) {
					echo $error;
				} else {
					echo $response;
				}
			} else {
				_e( 'You have to choose both source and target languages and Save Settings!', 'cm-tooltip-glossary' );
			}
		} else {
			_e( 'You have to enter the API Key and Save Settings!', 'cm-tooltip-glossary' );
		}
		die();
	}

	public static function getTranslateFromDb( $term, $column = 'title' ) {
		global $wpdb;

		$columnName	 = 'translate_' . $column;
		$sql		 = $wpdb->prepare( "SELECT " . $columnName . " FROM " . $wpdb->prefix . self::TABLENAME . " WHERE term LIKE '%s'", $term );
		$result		 = $wpdb->get_row( $sql );
		if ( $result === null || $result->{$columnName} === null )
			return false;
		return $result->{$columnName};
	}

	public static function setTranslateInDb( $term, $translate, $column = self::COLUMN_TITLE ) {
		global $wpdb;
		$sql		 = $wpdb->prepare( "SELECT term FROM " . $wpdb->prefix . self::TABLENAME . " WHERE term LIKE %s", $term );
		$result		 = $wpdb->get_row( $sql );
		$columnName	 = 'translate_' . $column;
		if ( empty( $result ) ) {
			$wpdb->insert( $wpdb->prefix . self::TABLENAME, array( 'term' => $term, $columnName => $translate ) );
		} else {
			$wpdb->update( $wpdb->prefix . self::TABLENAME, array( $columnName => $translate ), array( 'term' => $term ), array( '%s' ), array( '%s' ) );
		}
	}

	public static function checkIfTableExists() {
		global $wpdb;

		if ( !empty( self::$tableExists ) ) {
			return self::$tableExists;
		}

		if ( !$wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}" . self::TABLENAME . "'" ) == $wpdb->prefix . self::TABLENAME ) {
			self::install();
		} else {
			self::checkIfColumnExists();
		}
		self::$tableExists = true;
		return self::$tableExists;
	}

	public static function checkIfColumnExists() {
		global $wpdb;

		$wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . self::TABLENAME . " LIMIT 1" );
		$columns = $wpdb->get_col_info( 'name' );

		if ( in_array( 'translate_title', $columns ) && in_array( 'translate_content', $columns ) ) {
			return true;
		}

		self::install();
		return true;
	}

	public static function translate( $term, $termTitle, $column = self::COLUMN_TITLE, $onGlossaryIndex = FALSE ) {
		if ( self::$isShortcode ) {
			$source_id	 = self::$sourceId;
			$target_id	 = self::$targetId;
		} else {
			$source_id	 = get_option( 'cmtt_tooltip3RDGoogleSource', -1 );
			$target_id	 = get_option( 'cmtt_tooltip3RDGoogleTarget', -1 );
		}

		if ( (self::getApiKey() != '') && $source_id != -1 && $target_id != -1 && !empty( $term ) ) {
			self::checkIfTableExists();

			$langs	 = self::getLanguages();
			$source	 = $langs[ $source_id ];
			$target	 = $langs[ $target_id ];
			$name	 = sanitize_file_name( implode( '_', array( $term, $source[ 'language' ],
				$target[ 'language' ] ) ) );

			$termName = mb_strtolower( trim( $termTitle ) );

			$result = wp_cache_get( $name, self::CACHE_GROUP );

			if ( $result === false && !self::$isShortcode ) {
				$result = self::getTranslateFromDb( $termName, $column );
			}

			if ( $result === false && (!$onGlossaryIndex || get_option( 'cmtt_glossaryRunApiCalls' ) == 1) ) {
				$query	 = http_build_query( array( 'key' => self::getApiKey(), 'q' => $term, 'source' => $source[ "language" ], 'target' => $target[ "language" ] ) );
				$url	 = 'https://www.googleapis.com/language/translate/v2?' . $query;

				$handle		 = curl_init( $url );
				curl_setopt( $handle, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $handle, CURLOPT_SSL_VERIFYHOST, 0 );
				curl_setopt( $handle, CURLOPT_SSL_VERIFYPEER, 0 );
				$response	 = curl_exec( $handle );
				curl_close( $handle );

				if ( isset( $_GET[ 'cminds_debug' ] ) && $_GET[ 'cminds_debug' ] == '2' ) {
					echo '<pre>';
					var_dump( 'URL:' . $url );
					var_dump( 'Response:' . $response );
					echo '</pre>';
				}

				$responseDecoded = json_decode( $response, true );

				if ( isset( $responseDecoded[ 'data' ][ 'translations' ][ 0 ][ 'translatedText' ] ) ) {
					$result = $responseDecoded[ 'data' ][ 'translations' ][ 0 ][ 'translatedText' ];
				} else {
					$result = $term;
				}
				wp_cache_set( $name, $result, self::CACHE_GROUP );
				self::setTranslateInDb( $termName, $result, $column );
				return $result;
			} else {
				return $result;
			}
		} else {
			return $term;
		}
	}

	public static function enabled() {
		return $source_id = get_option( 'cmtt_tooltip3RDGoogleEnabled', 0 );
	}

	public static function term() {
		return $source_id = get_option( 'cmtt_tooltip3RDGoogleTerm', 0 );
	}

	public static function together() {
		return $source_id = get_option( 'cmtt_tooltip3RDGoogleTogether', 0 );
	}

}

class CMTT_Mw_API {

	const TABLENAME				 = 'glossary_database_cache';
	const DICT_CACHE_GROUP		 = 'cmtt_mw_cache';
	const THESAURUS_CACHE_GROUP	 = 'cmtt_thesaurus_cache';

	public static $tableExists = false;

	public static function init() {
		add_action( 'cmtt_do_activate', array( __CLASS__, 'install' ) );
	}

	public static function install() {
		global $wpdb;
		$sql = "CREATE TABLE {$wpdb->prefix}" . self::TABLENAME . " (
                id INT(11) NOT NULL AUTO_INCREMENT,
                term VARCHAR(64) NOT NULL,
                thesaurus TEXT NULL,
                dictionary TEXT NULL,
                wikipedia TEXT NULL,
                translate_title TEXT NULL,
                translate_content TEXT NULL,
                PRIMARY KEY  (id),
                KEY term_id (id)
              )
          CHARACTER SET utf8 COLLATE utf8_general_ci;";
		include_once(ABSPATH . 'wp-admin/includes/upgrade.php');
//        if($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}" . self::TABLENAME . "'") == $wpdb->prefix . self::TABLENAME)
//        {
//            $wpdb->query("ALTER TABLE {$wpdb->prefix}" . self::TABLENAME . " DROP INDEX id");
//        }
		dbDelta( $sql );
		$sql = "DELETE FROM " . $wpdb->prefix . self::TABLENAME . "";
		$wpdb->query( $sql );
	}

	public static function dictionaryShortcode( $atts ) {
		extract( shortcode_atts( array(
			'term' => '' ), $atts ) );

		$term = str_replace( array( '"', '\'' ), array( '', '' ), html_entity_decode( $term, ENT_COMPAT, 'utf-8' ) );

		$dictionary = self::get_dictionary( $term );

		return $dictionary;
	}

	public static function thesaurusShortcode( $atts ) {
		extract( shortcode_atts( array(
			'term' => '' ), $atts ) );

		$term = str_replace( array( '"', '\'' ), array( '', '' ), html_entity_decode( $term, ENT_COMPAT, 'utf-8' ) );

		$thesaurus = self::get_thesaurus( $term );
		return $thesaurus;
	}

	public static function addShortcodes() {
		add_shortcode( 'glossary_dictionary', array( __CLASS__, 'dictionaryShortcode' ) );
		add_shortcode( 'glossary_thesaurus', array( __CLASS__, 'thesaurusShortcode' ) );

		add_action( 'wp_ajax_cmtt_test_dictionary_api', array( __CLASS__, 'testDictionary' ) );
		add_action( 'wp_ajax_cmtt_test_thesaurus_api', array( __CLASS__, 'testThesaurus' ) );
	}

	public function testDictionary() {
		self::checkIfTableExists();

		$key = self::getApiKey_Dictionary();

		if ( ($key != '' ) ) {
			$term			 = 'creative';
			$termCacheName	 = htmlspecialchars( $term );

			$uri	 = "http://www.dictionaryapi.com/api/v1/references/collegiate/xml/" . urlencode( $termCacheName ) . "?key=" . urlencode( $key );
			if ( function_exists( 'curl_version' ) ) {
				$curl	 = curl_init();
				curl_setopt( $curl, CURLOPT_URL, $uri );
				curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
				$xmlstr	 = curl_exec( $curl );
				curl_close( $curl );
			} else if ( file_get_contents( __FILE__ ) && ini_get( 'allow_url_fopen' ) ) {
			$xmlstr	 = file_get_contents( $uri );
			} else {
				echo 'You have neither cUrl installed nor allow_url_fopen activated. Please setup one of those!';
			}
			echo $xmlstr;
		} else {
			_e( 'You have to enter the Dictionary API and Save Settings!', 'cm-tooltip-glossary' );
		}
		die();
	}

	public function testThesaurus() {
		self::checkIfTableExists();

		$key = self::getApiKey_Thesaurus();

		if ( ($key != '' ) ) {
			$term			 = 'creative';
			$termCacheName	 = htmlspecialchars( $term );

			$uri	 = "http://www.dictionaryapi.com/api/v1/references/thesaurus/xml/" . urlencode( $termCacheName ) . "?key=" . urlencode( $key );
			if ( function_exists( 'curl_version' ) ) {
				$curl	 = curl_init();
				curl_setopt( $curl, CURLOPT_URL, $uri );
				curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
				$xmlstr	 = curl_exec( $curl );
				curl_close( $curl );
			} else if ( file_get_contents( __FILE__ ) && ini_get( 'allow_url_fopen' ) ) {
			$xmlstr	 = file_get_contents( $uri );
			} else {
				echo 'You have neither cUrl installed nor allow_url_fopen activated. Please setup one of those!';
			}
			echo $xmlstr;
		} else {
			_e( 'You have to enter the Thesaurus API and Save Settings!', 'cm-tooltip-glossary' );
		}
		die();
	}

	public static function flushDatabase() {
		global $wpdb;
		$sql = "TRUNCATE TABLE " . $wpdb->prefix . self::TABLENAME;
		$wpdb->query( $sql );
	}

	public static function flushTermCache( $term ) {
		global $wpdb;
		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM " . $wpdb->prefix . self::TABLENAME . " WHERE term LIKE '%s'", $term );

		$results = $wpdb->get_var( $sql );

		if ( $results === '1' ) {
			$sql = $wpdb->prepare( "DELETE FROM " . $wpdb->prefix . self::TABLENAME . " WHERE term LIKE '%s'", $term );
			$wpdb->query( $sql );
		}
	}

	public static function getApiKey_Dictionary() {
		return get_option( 'cmtt_tooltip3RD_MWDictionaryApiKey', '' );
	}

	public static function getApiKey_Thesaurus() {
		return get_option( 'cmtt_tooltip3RD_MWThesaurusApiKey', '' );
	}

	public static function checkIfTableExists() {
		global $wpdb;

		if ( !empty( self::$tableExists ) ) {
			return self::$tableExists;
		}

		if ( !$wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}" . self::TABLENAME . "'" ) == $wpdb->prefix . self::TABLENAME ) {
			self::install();
		}
		self::$tableExists = true;
		return self::$tableExists;
	}

	public static function getDictionaryFromDb( $term ) {
		global $wpdb;
		$sql	 = $wpdb->prepare( "SELECT dictionary FROM " . $wpdb->prefix . self::TABLENAME . " WHERE term LIKE '%s'", $term );
		$result	 = $wpdb->get_row( $sql );
		if ( !empty( $wpdb->last_error ) ) {
			self::install();
		}
		if ( $result === null || $result->dictionary === null )
			return false;
		return $result->dictionary;
	}

	public static function setDictionaryInDb( $term, $dictionary ) {
		global $wpdb;
		$sql	 = $wpdb->prepare( "SELECT term FROM " . $wpdb->prefix . self::TABLENAME . " WHERE term LIKE %s", $term );
		$result	 = $wpdb->get_row( $sql );
		if ( !empty( $wpdb->last_error ) ) {
			self::install();
		}
		if ( empty( $result ) ) {
			$wpdb->insert( $wpdb->prefix . self::TABLENAME, array( 'term' => $term, 'dictionary' => $dictionary ) );
		} else {
			$wpdb->update( $wpdb->prefix . self::TABLENAME, array( 'dictionary' => $dictionary ), array( 'term' => $term ), array( '%s' ), array( '%s' ) );
		}
	}

	public static function getThesaurusFromDb( $term ) {
		global $wpdb;
		$sql	 = $wpdb->prepare( "SELECT thesaurus FROM " . $wpdb->prefix . self::TABLENAME . " WHERE term LIKE '%s'", $term );
		$result	 = $wpdb->get_row( $sql );
		if ( $result === null || $result->thesaurus === null ) {
			return false;
		}
		return $result->thesaurus;
	}

	public static function setThesaurusInDb( $term, $thesaurus ) {
		global $wpdb;
		$sql	 = $wpdb->prepare( "SELECT term FROM " . $wpdb->prefix . self::TABLENAME . " WHERE term LIKE %s", $term );
		$result	 = $wpdb->get_row( $sql );
		if ( empty( $result ) ) {
			$wpdb->insert( $wpdb->prefix . self::TABLENAME, array( 'term' => $term, 'thesaurus' => $thesaurus ) );
		} else {
			$wpdb->update( $wpdb->prefix . self::TABLENAME, array( 'thesaurus' => $thesaurus ), array( 'term' => $term ), array( '%s' ), array( '%s' ) );
		}
	}

	public static function iterateOverXmlElement( $iterator ) {
		static $array = array();

		if ( is_a( $iterator, 'SimpleXMLIterator' ) ) {
			if ( $iterator->hasChildren() ) {
				foreach ( $iterator->getChildren() as $key => $value ) {
					if ( is_a( $value, 'SimpleXMLIterator' ) || is_a( $value, 'SimpleXMLElement' ) ) {
						self::iterateOverXmlElement( $value );
					}
				}
			}
		}
	}

	public static function get_dictionary( $term, $onGlossaryIndex = false ) {
		self::checkIfTableExists();

		if ( (self::getApiKey_Dictionary() != '') && !empty( $term ) ) {
			$term			 = mb_strtolower( trim( $term ) );
			$termCacheName	 = htmlspecialchars( $term );
			$result			 = wp_cache_get( $termCacheName, self::DICT_CACHE_GROUP );
			$returnMessage	 = '';

			if ( $result === false ) {
				$result = self::getDictionaryFromDb( $term );
			}

			if ( $result === false && (!$onGlossaryIndex || get_option( 'cmtt_glossaryRunApiCalls' ) == 1) ) {
				$key	 = self::getApiKey_Dictionary();
				$uri	 = "http://www.dictionaryapi.com/api/v1/references/collegiate/xml/" . urlencode( $termCacheName ) . "?key=" . urlencode( $key );
				$xmlstr	 = @file_get_contents( $uri );

				if ( !empty( $xmlstr ) ) {
					try {
						$data = @new SimpleXMLElement( $xmlstr );
					} catch ( Exception $e ) {
//                        $returnMessage = __('Connection error. Merriam-Webster did not respond.', 'cm-tooltip-glossary');
						$returnMessage = '';
						wp_cache_set( $termCacheName, $returnMessage, self::DICT_CACHE_GROUP, 10 );
						self::setDictionaryInDb( $term, '' );
						return $returnMessage;
					}

					$dictionaryArr = array();

					if ( isset( $data->entry ) ) {
						foreach ( $data->entry as $entry ) {
							if ( isset( $entry->ew ) && mb_strtolower( $entry->ew ) == mb_strtolower( $term ) ) {
								unset( $entry->def->date );
								$dictionaryArr[] = array( 'name'	 => (string) $entry->ew[ 0 ], 'type'	 => (string) $entry->fl[ 0 ],
									'def'	 => $entry->def->asXML() );
							}
						}
					}
					unset( $data );
				}

				// Format the display string
				if ( !empty( $dictionaryArr ) ) {
					$returnHtml = '<div class=mw-dictionary-container> <!-- mw container start this is a test-->';
					$returnHtml .= '<div class=glossary_mw_dictionary>Merriam-Webster Online Dictionary</div>';

					foreach ( $dictionaryArr as $entry ) {
						$returnHtml .= '<strong>' . $entry[ 'name' ] . '</strong> (<em>' . $entry[ 'type' ] . '</em>)';
						$returnHtml .= '<div class=\'scnt mw-group\'><!-- mw-group start -->';

						try {
							$definitionSets = new SimpleXMLIterator( $entry[ 'def' ] );
						} catch ( Exception $e ) {
							$returnMessage = '';
							wp_cache_set( $termCacheName, $returnMessage, self::DICT_CACHE_GROUP );
							return $returnMessage;
						}

						$tempArr = array();

						for ( $definitionSets->rewind(); $definitionSets->valid(); $definitionSets->next() ) {
							switch ( $definitionSets->key() ) {
								case 'sn': {
										$input = explode( " ", $definitionSets->current() );

										if ( !empty( $tempArr ) ) {
											$returnHtml .= '<div class=\'dd\'>' . implode( ' ', $tempArr ) . '</div>';
											$tempArr = array();
										}

										if ( is_array( $input ) ) {
											if ( count( $input ) == 1 || empty( $input[ 1 ] ) ) {
												if ( is_numeric( $input[ 0 ] ) ) {
													$startNewTerm	 = true;
													$content		 = '<div class=term-number>' . $input[ 0 ] . '.</div>';
												} else {
													if ( !empty( $input[ 0 ] ) ) {
														$tempArr[] = '<span class=prdiv>' . $input[ 0 ] . ') </span>';
													}
													$content = '';
												}
											} else {
												$startNewTerm	 = true;
												$content		 = '<div class=term-number>' . $input[ 0 ] . '.</div>';
												$tempArr[]		 = '<span class=prdiv>' . $input[ 1 ] . ') </span>';
											}
										}

										if ( $definitionSets->hasChildren() ) {
											foreach ( $definitionSets->getChildren() as $itemName => $data ) {
												switch ( $itemName ) {
													case 'snp': {
															$tempArr[] = '<em class=\'sn\'>' . $data . '</em>';
															break;
														}
												}
											}
										}
										$returnHtml .= $content;
										break;
									}

								case 'dt': {
										$content = '';
										if ( $definitionSets->hasChildren() ) {
											$content .= str_replace( ":", "", $definitionSets->current() );
											$children = array();

											foreach ( $definitionSets->getChildren() as $itemName => $data ) {
												switch ( $itemName ) {
													case 'sx': $children[]	 = '<strong>' . $data . '</strong>';
														break;
													case 'vi': $children[]	 = '<span class=example>' . strip_tags( $data->asXML() ) . '</span>';
														break;
													case 'dx': $children[]	 = strip_tags( $data->asXML() );
														break;
													default: {
															$children[] = strip_tags( $data->asXML() );
															break;
														}
												}
											}
											if ( $children )
												$content .= ' - ' . implode( ' ', $children );
										}
										else {
											$content .= str_replace( ":", " ", $definitionSets->current() );
										}

										$content	 = '<span class=definition>' . $content . '</span>';
										$tempArr[]	 = $content;

//                                        $returnHtml .= '<div class=\'dd\'>'.$content . '</div>';
										break;
									}
								case 'sd': {
										$content	 = ', <em class=\'mw-group type-sd\'>' . $definitionSets->current() . '</em> ';
										$tempArr[]	 = $content;
//                                        $returnHtml .= $content;
										break;
									}
								case 'vt': {
										$content = '<div><em class=\'type-vt\'>' . $definitionSets->current() . '</em></div>';
										$returnHtml .= $content;
										break;
									}

								default: {
										$content	 = '<span class=definition>' . str_replace( ":", "- ", $definitionSets->current() ) . '</span>';
										$tempArr[]	 = $content;
//                                        $returnHtml .= $content;
									}
							}
						}

						if ( !empty( $tempArr ) ) {
							$returnHtml .= '<div class=\'dd\'>' . implode( ' ', $tempArr ) . '</div>';
							$tempArr = array();
						}

						$returnHtml .= '</div><!-- mw-group end -->';
					}

					$returnHtml .= '<div class=break></div>';
					$returnHtml .= '</div><!-- mw container end -->';

					wp_cache_set( $termCacheName, $returnHtml, self::DICT_CACHE_GROUP );
					self::setDictionaryInDb( $term, $returnHtml );
					return($returnHtml);
				}
//                $returnMessage = __('Merriam-Webster has no Dictionary definition.', 'cm-tooltip-glossary');
				$returnMessage = '';
				wp_cache_set( $termCacheName, $returnMessage, self::DICT_CACHE_GROUP, 30 );
				self::setDictionaryInDb( $term, '' );
				return $returnMessage;
			} else {
				return $result;
			}
		} else {
			return $term;
		}
	}

	public static function dictionary_enabled() {
		return $source_id = get_option( 'cmtt_tooltip3RD_MWDictionaryEnabled', 0 );
	}

	public static function dictionary_only_on_empty_content() {
		return $source_id = get_option( 'cmtt_tooltip3RD_MWDictionaryAutoContent', 0 );
	}

	public static function dictionary_show_in_tooltip() {
		return $source_id = get_option( 'cmtt_tooltip3RD_MWDictionaryTooltip', 0 );
	}

	public static function dictionary_show_in_term() {
		return $source_id = get_option( 'cmtt_tooltip3RD_MWDictionaryTerm', 0 );
	}

	public static function get_thesaurus( $term, $onGlossaryIndex = false ) {
		self::checkIfTableExists();

		if ( (self::getApiKey_Thesaurus() != '') && !empty( $term ) ) {
			$term			 = mb_strtolower( trim( $term ) );
			$termCacheName	 = htmlspecialchars( $term );
			$result			 = wp_cache_get( $termCacheName, self::THESAURUS_CACHE_GROUP );
			$returnMessage	 = '';

			if ( $result === false ) {
				$result = self::getThesaurusFromDb( $term );
			}

			if ( $result === false && (!$onGlossaryIndex || get_option( 'cmtt_glossaryRunApiCalls' ) == 1) ) {
				$thesaurusArr	 = array();
				$key			 = self::getApiKey_Thesaurus();
				$uri			 = "http://www.dictionaryapi.com/api/v1/references/thesaurus/xml/" . urlencode( $termCacheName ) . "?key=" . urlencode( $key );
				$xmlstr			 = @file_get_contents( $uri );

				if ( !empty( $xmlstr ) ) {
					try {
						$thesaurusArr = @json_decode( json_encode( (array) simplexml_load_string( $xmlstr ) ), 1 );
					} catch ( Exception $e ) {
//                        $returnMessage = __('Connection error. Merriam-Webster did not respond.', 'cm-tooltip-glossary');
						$returnMessage = '';
						wp_cache_set( $termCacheName, $returnMessage, self::THESAURUS_CACHE_GROUP, 10 );
						return $returnMessage;
					}
				}

				// Format the display string
				if ( !empty( $thesaurusArr[ 'entry' ] ) ) {
					$thesaurusEntries = (isset( $thesaurusArr[ 'entry' ][ 0 ] )) ? $thesaurusArr[ 'entry' ] : array( $thesaurusArr[ 'entry' ] );

					$returnHtml = '<div class=mw-thesaurus-container> <!-- mw container start -->';
					$returnHtml .= '<div class=glossary_mw_dictionary>Merriam-Webster Online Thesaurus</div>';

					foreach ( $thesaurusEntries as $entry ) {
						$definitionSets = (isset( $entry[ 'sens' ][ 0 ] )) ? $entry[ 'sens' ] : array( $entry[ 'sens' ] );

						if ( $entry[ 'term' ][ 'hw' ] !== $term )
							continue;

						foreach ( $definitionSets as $definitionSet ) {
							$returnHtml .= '<strong>' . $entry[ 'term' ][ 'hw' ] . '</strong> (<em>' . $entry[ 'fl' ] . '</em>)';
							$returnHtml .= '<div class=\'scnt mw-group\'><!-- mw-group start -->';

							foreach ( $definitionSet as $definitionKey => $definitionValue ) {
								switch ( $definitionKey ) {
									case 'sn': {
											$content = explode( " ", $definitionValue );

											if ( is_array( $content ) ) {
												if ( count( $content ) == 1 || empty( $content[ 1 ] ) ) {
													if ( is_numeric( $content[ 0 ] ) ) {
														$startNewTerm	 = true;
														$content		 = '<div class=term-number>' . $content[ 0 ] . '.</div><dd>';
													} else {
														if ( !empty( $content[ 0 ] ) )
															$content[ 0 ] = '<span class=prdiv>' . $content[ 0 ] . ') ';

														$content = '<dd>' . $content[ 0 ] . '</span>';
													}
												}
												else {
													$startNewTerm	 = true;
													$content		 = '<div class=term-number>' . $content[ 0 ] . '.</div><dd><span class=prdiv>' . $content[ 1 ] . ') </span>';
												}
											}

											$returnHtml .= $content;
											break;
										}

									case 'mc':
										$returnHtml .= '<div class=\'mw-group mw-definitnion break\'>' . $definitionValue . '</div>';
										break;

									case 'vi':
										break;

									case 'syn': {
											$content = str_replace( ' []', '', $definitionValue );
											$returnHtml .= '<div class=\'mw-group break\'><div class=\'group-title\'>SYNONYMS:</div>' . $content . '</div>';
											break;
										}
									case 'rel': {
											$returnHtml .= '<div class=\'mw-group break\'><div class=\'group-title\'>RELATED WORDS:</div>' . $definitionValue . '</div>';
											break;
										}
									case 'near': {
											$returnHtml .= '<div class=\'mw-group break\'><div class=\'group-title\'>NEAR ANTONYMS:</div>' . $definitionValue . '</div>';
											break;
										}
									default:
										$returnHtml .= '<span>' . $definitionValue . '</span>';
								}
							}

							$returnHtml .= '</div><!-- mw-group end -->';
						}
					}

					$returnHtml .= '<div class=break></div>';
					$returnHtml .= '</div><!-- mw container end -->';

					wp_cache_set( $termCacheName, $returnHtml, self::THESAURUS_CACHE_GROUP );
					self::setThesaurusInDb( $term, $returnHtml );
					return($returnHtml);
				}

//                $returnMessage = __('Merriam-Webster has no Thesaurus definition.', 'cm-tooltip-glossary');
				$returnMessage = '';
				wp_cache_set( $termCacheName, $returnMessage, self::THESAURUS_CACHE_GROUP, 30 );
				self::setThesaurusInDb( $term, '' );
				return $returnMessage;
			} else {
				return $result;
			}
		} else {
			return $term;
		}
	}

	public static function thesaurus_enabled() {
		return $source_id = get_option( 'cmtt_tooltip3RD_MWThesaurusEnabled', 0 );
	}

	public static function thesaurus_only_on_empty_content() {
		return $source_id = get_option( 'cmtt_tooltip3RD_MWThesaurusAutoContent', 0 );
	}

	public static function thesaurus_show_in_tooltip() {
		return $source_id = get_option( 'cmtt_tooltip3RD_MWThesaurusTooltip', 0 );
	}

	public static function thesaurus_show_in_term() {
		return $source_id = get_option( 'cmtt_tooltip3RD_MWThesaurusTerm', 0 );
	}

	public static function together() {
		return true; // For testing
		//return $source_id = get_option('cmtt_tooltip3RDGoogleTogether', 0);
	}

}
