<?php
/**
 * Meta data parsing functions for Microsoft Office documents
 *
 * @package Media Library Assistant
 * @since 2.82
 * /

$reader = new XMLReader();

$reader->open('zip://' . dirname(__FILE__) . '/test.odt#meta.xml');
$odt_meta = array();
while ($reader->read()) {
    if ($reader->nodeType == XMLREADER::ELEMENT) {
        $elm = $reader->name;
    } else {
        if ($reader->nodeType == XMLREADER::END_ELEMENT && $reader->name == 'office:meta') {
            break;
        }
        if (!trim($reader->value)) {
            continue;
        }
        $odt_meta[$elm] = $reader->value;
    }
}
print_r($odt_meta);
// */

/**
 * Class MLA (Media Library Assistant) Office extracts meta data from Microsoft Office files
 *
 * @package Media Library Assistant
 * @since 2.82
 */
class MLAOffice {
	/**
	 * Extract Metadata from a Microsoft Office file
	 * 
	 * @since 2.82
	 *
	 * @param	string	full path to the desired file
	 *
	 * @return	array	( 'mso' => array( key => value ) ) for each metadata field, in string format
	 */
	public static function mla_extract_office_metadata( $file_name ) {
		$metadata = array();

		if ( ! file_exists( $file_name ) ) {
			return array( 'mso' => $metadata );
		}

		$zip = new ZipArchive();
		if ( TRUE !== $zip->open( $file_name ) ) {
			MLACore::mla_debug_add( __LINE__ . ' ' . __( 'ERROR', 'media-library-assistant' ) . ': ' . _x( 'mla_extract_office_metadata zip open failed.', 'error_log', 'media-library-assistant' ), MLACore::MLA_DEBUG_CATEGORY_ANY );
			return array( 'mso' => $metadata );
		}

		$index = $zip->locateName('docProps/app.xml');
		if ( $index ) {
			$xml = $zip->getFromIndex( $index );
			$metadata = MLAData::mla_parse_xml_string( $xml );
			
			if ( NULL === $metadata ) {
				MLACore::mla_debug_add( __LINE__ . ' ' . __( 'ERROR', 'media-library-assistant' ) . ': ' . _x( 'mla_extract_office_metadata mla_parse_xml_string( docProps/app.xml ) failed.', 'error_log', 'media-library-assistant' ), MLACore::MLA_DEBUG_CATEGORY_ANY );
				$metadata = array();
			}
		} else {
			MLACore::mla_debug_add( __LINE__ . ' ' . __( 'ERROR', 'media-library-assistant' ) . ': ' . _x( 'mla_extract_office_metadata locateName( docProps/app.xml ) failed.', 'error_log', 'media-library-assistant' ), MLACore::MLA_DEBUG_CATEGORY_ANY );
		}

		$app_namespace = '';
		if ( isset( $metadata['app'] ) && !empty( $metadata['app']['xmlns'] ) ) {
			$app_namespace = $metadata['app']['xmlns'];
			unset ( $metadata['app']['xmlns'] );
		}
		
		$index = $zip->locateName('docProps/core.xml');
		if ( $index ) {
			$xml = $zip->getFromIndex( $index );
			$core = MLAData::mla_parse_xml_string( $xml );
			
			if ( NULL === $core ) {
				MLACore::mla_debug_add( __LINE__ . ' ' . __( 'ERROR', 'media-library-assistant' ) . ': ' . _x( 'mla_extract_office_metadata mla_parse_xml_string( docProps/core.xml ) failed.', 'error_log', 'media-library-assistant' ), MLACore::MLA_DEBUG_CATEGORY_ANY );
			} else {
				$metadata = array_merge( $metadata, $core );
			}
		} else {
			MLACore::mla_debug_add( __LINE__ . ' ' . __( 'ERROR', 'media-library-assistant' ) . ': ' . _x( 'mla_extract_office_metadata locateName( docProps/core.xml ) failed.', 'error_log', 'media-library-assistant' ), MLACore::MLA_DEBUG_CATEGORY_ANY );
		}

		if ( !empty( $app_namespace ) ) {
			$metadata['xmlns']['app'] = $app_namespace;
		}
		
		return array( 'mso' => $metadata );
	}
} // class MLAOffice
?>