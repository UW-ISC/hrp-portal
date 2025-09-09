<?php

defined('ABSPATH') or die('Access denied.');

class WDTTools
{

    public static $jsVars = array();

    /**
     * Helper function that returns array of possible column types
     * @return array
     */
    public static function getPossibleColumnTypes(): array
    {
        $possibleColumnTypes = array(
            'input' => __('One line string', 'wpdatatables'),
            'memo' => __('Multi-line string', 'wpdatatables'),
            'select' => __('One-line selectbox', 'wpdatatables'),
            'multiselect' => __('Multi-line selectbox', 'wpdatatables'),
            'hidden' => __('Hidden (Dynamic)', 'wpdatatables'),
            'int' => __('Integer', 'wpdatatables'),
            'float' => __('Float', 'wpdatatables'),
            'date' => __('Date', 'wpdatatables'),
            'datetime' => __('Datetime', 'wpdatatables'),
            'time' => __('Time', 'wpdatatables'),
            'link' => __('URL Link', 'wpdatatables'),
            'email' => __('E-mail', 'wpdatatables'),
            'image' => __('Image', 'wpdatatables'),
            'file' => __('Attachment', 'wpdatatables')
        );

        return apply_filters('wpdatatables_filter_possible_column_types', $possibleColumnTypes);
    }

    /**
     * Helper function that sanitize column header
     *
     * @param $headersInFormula
     *
     * @return array
     */
    public static function sanitizeHeaders($headersInFormula): array
    {

        $headers = array();
        foreach ($headersInFormula as $key => $header) {
            $headers[$header] = str_replace(
                range('0', '9'),
                range('a', 'j'),
                "wpdatacolumn" . $key
            );
        }
        return $headers;
    }

    /**
     * Helper function for applying placeholders(variables)
     *
     * @param $string
     *
     * @return mixed
     */
    public static function applyPlaceholders($string)
    {
        if (defined('WDT_PH_INTEGRATION')) {
            return \WDTIntegration\Placeholders::maybeApply($string);
        }

        return $string;

    }

    /**
     * Helper function that returns curl data
     *
     * @param $url
     *
     * @return mixed|null
     * @throws Exception
     */
    public static function curlGetData($url)
    {
        $ch = curl_init();
        $timeout = 100;
        $agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36';

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_REFERER, site_url());
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $data = apply_filters('wpdatatables_curl_get_data', null, $ch, $url);
        if (null === $data) {
            $data = curl_exec($ch);
            if (curl_error($ch)) {
                $error = curl_error($ch);
                curl_close($ch);

                throw new Exception($error);
            }
            if (strpos($data, '<TITLE>Moved Temporarily</TITLE>') ||
                strpos($data, 'Error 400 (Bad Request)')) {
                throw new Exception(__('wpDataTables was unable to read your Google Spreadsheet, as it\'s not been published correctly. <br/> You can publish it by going to <b>File ->Share -> Publish to the web</b> ', 'wpdatatables'));
            }
            $info = curl_getinfo($ch);
            curl_close($ch);

            if ($info['http_code'] === 404) {
                return NULL;
            }
            if ($info['http_code'] === 401) {
                throw new Exception(__('wpDataTables was unable to access data. Unauthorized access. Please make file accessible.', 'wpdatatables'));
            }

            $data = apply_filters('wpdatatables_curl_get_data_complete', $data, $url);
        }
        return $data;
    }

    /**
     * Helper function to find CSV delimiter
     *
     * @param $csv_url
     *
     * @return string
     */
    public static function detectCSVDelimiter($csv_url)
    {

        if (!file_exists($csv_url) || !is_readable($csv_url)) {
            throw new WDTException('Could not open ' . $csv_url . ' for reading! File does not exist.');
        }
        $fileResurce = fopen($csv_url, 'r');

        $delimiterList = [',', ':', ';', "\t", '|'];
        $counts = [];
        foreach ($delimiterList as $delimiter) {
            $counts[$delimiter] = [];
        }

        $lineNumber = 0;
        while (($line = fgets($fileResurce)) !== false && (++$lineNumber < 1000)) {
            $lineCount = [];
            for ($i = strlen($line) - 1; $i >= 0; --$i) {
                $character = $line[$i];
                if (isset($counts[$character])) {
                    if (!isset($lineCount[$character])) {
                        $lineCount[$character] = 0;
                    }
                    ++$lineCount[$character];
                }
            }
            foreach ($delimiterList as $delimiter) {
                $counts[$delimiter][] = isset($lineCount[$delimiter])
                    ? $lineCount[$delimiter]
                    : 0;
            }
        }

        $RMSD = [];
        $middleIdx = floor(($lineNumber - 1) / 2);

        foreach ($delimiterList as $delimiter) {
            $series = $counts[$delimiter];
            sort($series);

            $median = ($lineNumber % 2)
                ? $series[$middleIdx]
                : ($series[$middleIdx] + $series[$middleIdx + 1]) / 2;

            if ($median === 0) {
                continue;
            }

            $RMSD[$delimiter] = array_reduce(
                    $series,
                    function ($sum, $value) use ($median) {
                        return $sum + pow($value - $median, 2);
                    }
                ) / count($series);
        }

        $min = INF;
        $finalDelimiter = '';
        foreach ($delimiterList as $delimiter) {
            if (!isset($RMSD[$delimiter])) {
                continue;
            }

            if ($RMSD[$delimiter] < $min) {
                $min = $RMSD[$delimiter];
                $finalDelimiter = $delimiter;
            }
        }

        if ($delimiter === null) {
            $finalDelimiter = reset($delimiterList);
        }

        return $finalDelimiter;
    }

    /**
     * Helper function that convert CSV file to Array
     *
     * @param $csv
     *
     * @return array
     */
    public static function csvToArray($csv)
    {
        $arr = array();
        $lines = explode("\r\n", $csv);
        foreach ($lines as $row) {
            $arr[] = str_getcsv($row, ",");
        }
        return self::gsArrayToWDTArray($arr);
    }

    /**
     * Helper function that convert Google Sheet array to adopt in wpdt Array
     *
     * @param $arr
     *
     * @return array
     */
    public static function gsArrayToWDTArray($arr)
    {
        $count = count($arr) - 1;
        $labels = array_shift($arr);
        $countLabels = count($labels);
        $keys = array();
        foreach ($labels as $label) {
            $keys[] = trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $label)));
        }
        $keys = array_map('trim', $keys);
        $returnArray = array();
        for ($j = 0; $j < $count; $j++) {
            if (count($arr[$j]) < $countLabels) {
                for ($k = 0; $k < $countLabels; $k++) {
                    if (!isset($arr[$j][$k])) {
                        $arr[$j][$k] = '';
                    }
                }
            }
            if (count($keys) == count($arr[$j])) {
                $d = array_combine($keys, $arr[$j]);
                $returnArray[$j] = $d;
            }
        }
        return $returnArray;
    }

    /**
     * Helper function that extract Google Spreadsheet URL and get ID
     *
     * @param $url
     *
     * @return string
     */
    public static function getGoogleSpreadsheetID($url)
    {
        $url_arr = explode('/', $url);
        return $url_arr[count($url_arr) - 2];
    }

    /**
     * Helper function that extract Google Spreadsheet URL and get Worksheets ID
     *
     * @param $url
     *
     * @return string
     */
    public static function getGoogleWorksheetsID($url)
    {
        if (strpos($url, '#') !== false) {
            $url_query = parse_url($url, PHP_URL_FRAGMENT);
        } else {
            $url_query = parse_url($url, PHP_URL_QUERY);
        }

        if (!empty($url_query)) {
            parse_str($url_query, $url_query_params);
            if (!empty($url_query_params['gid'])) {
                return $url_query_params['gid'];
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    /**
     * Helper function that extract Google Spreadsheet
     *
     * @param $url
     *
     * @return array|string
     * @throws Exception
     */
    public static function extractGoogleSpreadsheetArray($url)
    {
        if (empty($url)) {
            return '';
        }
        $url_arr = explode('/', $url);
        $spreadsheet_key = $url_arr[count($url_arr) - 2];

        if (strpos($url, '2PACX') !== false) {
            $csv_url = "https://docs.google.com/spreadsheets/d/e/{$spreadsheet_key}/pub?output=csv";
        } else {
            $csv_url = "https://docs.google.com/spreadsheets/d/{$spreadsheet_key}/pub?hl=en_US&hl=en_US&single=true&output=csv";
        }

        if (strpos($url, '#') !== false) {
            $url_query = parse_url($url, PHP_URL_FRAGMENT);
        } else {
            $url_query = parse_url($url, PHP_URL_QUERY);
        }

        if (!empty($url_query)) {
            parse_str($url_query, $url_query_params);
            if (!empty($url_query_params['gid'])) {
                $csv_url .= '&gid=' . $url_query_params['gid'];
            } else {
                $csv_url .= '&gid=0';
            }
        }
        $csv_data = WDTTools::curlGetData($csv_url);
        if (!is_null($csv_data)) {
            return WDTTools::csvToArray($csv_data);
        }

        return array();
    }

    public static function getTranslationStringsBrowse()
    {
        return array(
            'deleteSelectedBrowser' => __('Delete selected', 'wpdatatables'),
            'deleteBrowser' => __('Delete', 'wpdatatables'),
            'copyBrowser' => __('Copy', 'wpdatatables'),
        );
    }

    public static function getTranslationStringsCommon()
    {
        return array(
            'success_common' => __('Success!', 'wpdatatables'),
            'error_common' => __('Error!', 'wpdatatables'),
            'settings_saved_error_common' => __('Unable to save settings of plugin. Please try again or contact us over Support page.', 'wpdatatables'),
            'close_common' => __('Close', 'wpdatatables'),
            'tableNameEmpty_common' => __('Table name can not be empty! Please provide a name for your table.', 'wpdatatables'),
            'masterdetail_error_common' => __('For the selected master-detail option, the following fields cannot be empty: Parent Table Column Name and Child Table Column Name. Additionally, the tables must be connected through a common unique ID column.', 'wpdatatables'),
            'masterdetailParentId_error_common' => __('For the selected master-detail option, the following field cannot be empty: Parent Table Column Name.', 'wpdatatables'),
            'tableSaved_common' => __('Table saved successfully!', 'wpdatatables'),
            'selectExcelCsv_common' => __('Select an Excel or CSV file', 'wpdatatables'),
            'choose_file_common' => __('Use selected file', 'wpdatatables'),
            'chooseFile_common' => __('Choose file', 'wpdatatables'),
            'shortcodeSaved_common' => __('Shortcode has been copied to the clipboard.', 'wpdatatables'),
            'dataSaved_common' => __('Data has been saved!', 'wpdatatables'),
            'databaseInsertError_common' => __('There was an error trying to insert a new row!', 'wpdatatables'),
            'databaseDeleteError_common' => __('There was an error trying to delete a row!', 'wpdatatables'),
            'rowDeleted_common' => __('Row has been deleted!', 'wpdatatables'),
            'systemInfoSaved_common' => __('System info data has been copied to the clipboard. You can now paste it in file or in support ticket.', 'wpdatatables'),
            'selected_replace_data_option_common' => __("<small>You've selected the <strong>'Replace rows with source data'</strong> option. This means that you're about to <strong>delete all the data</strong> you currently have in your table and replace it with data from your source file.<br><br> If you have any <strong>date type columns</strong> in your file, please make sure you set the <strong>date input format in Main settings of plugin</strong> to the one you're using in your source file first.<br><br>Please consider <strong>duplicating your table first</strong>, before updating.<br><br><strong>There is no undo.</strong></small> ", "wpdatatables"),
            'selected_add_data_option_common' => __("<small>You've selected the <strong>'Add data to current table data'</strong> option. This means that you're about to <strong>add data</strong> from the file source to your table.<br><br> If you have any <strong>date type columns</strong> in your file, please make sure you set the <strong>date input format in Main settings of plugin</strong> to the one you're using in your source file first.<br><br>Please consider <strong>duplicating your table first</strong>, before updating.<br><br><strong>There is no undo.</strong></small>", "wpdatatables"),
            'selected_replace_table_option_common' => __("<small>You've selected the <strong>'Replace entire table data'</strong> option. This means that you're about to <strong>delete your entire table data and current column settings</strong> and replace it with data from your source file with default settings for columns.<br><br> If you have any <strong>date type columns</strong> in your file, please make sure you set the <strong>date input format in Main settings of plugin</strong> to the one you're using in your source file first. <br><br>Please consider <strong>duplicating your table first</strong>, before updating.<br><br><strong>There is no undo.</strong></small> ", "wpdatatables"),
            'clear_table_data_common' => __('Clear table data', 'wpdatatables'),
            'delete_common' => __('Delete', 'wpdatatables'),
            'deleteSelected_common' => __('Delete selected', 'wpdatatables'),
            'getJsonRoots_common' => __('JSON roots are found!', 'wpdatatables'),
            'errorText_common' => __('Unable to retrieve results', 'wpdatatables'),
        );
    }

    public static function getTranslationStringsConstructor()
    {
        global $wpdb;
        return array(
            'success_constructor' => __('Success!', 'wpdatatables'),
            'error_constructor' => __('Error!', 'wpdatatables'),
            'fileUploadEmptyFile_constructor' => __('Please upload or choose a file from Media Library!', 'wpdatatables'),
            'columnsEmpty_constructor' => __('Please select columns that you want to use in table', 'wpdatatables'),
            'tableNameEmpty_constructor' => __('Table name can not be empty! Please provide a name for your table.', 'wpdatatables'),
            'numberOfColumnsError_constructor' => __('Number of columns can not be empty or 0', 'wpdatatables'),
            'numberOfRowsError_constructor' => __('Number of rows can not be empty or 0', 'wpdatatables'),
            'newColumnName_constructor' => __('New column', 'wpdatatables'),
            'selectAll_constructor' => __('Select all', 'wpdatatables'),
            'deselectAll_constructor' => __('Deselect all', 'wpdatatables'),
            'customDatabaseNameError_constructor' => __('The database name must be less than 64 characters and can only contain letters, numbers, and underscores. It cannot start with a number unless the prefix is included.', 'wpdatatables'),
            'customDatabaseNameLengthError_constructor' => __('The database name must be less than 64 characters.', 'wpdatatables'),
            'customDatabaseNameTypeError_constructor' => __('The database name can only contain letters, numbers, and underscores. It cannot start with a number unless the prefix is included.', 'wpdatatables'),
            'wpPrefixForDatabase_constructor' => $wpdb->prefix,
            'emtyfields_woo' => __('All of the following fields must be filled out: Taxonomy, Tax Field and Tax Terms.', 'wpdatatables'),
        );
    }

    public static function getTranslationStringsSimpleTable()
    {
        return array(
            'success_simple_table' => __('Success!', 'wpdatatables'),
            'error_simple_table' => __('Error!', 'wpdatatables'),
            'tableSaved_simple_table' => __('Table saved successfully!', 'wpdatatables'),
            'clear_table_data_simple_table' => __('Clear table data', 'wpdatatables'),
            'star_rating_simple_table' => __('Star rating', 'wpdatatables'),
            'shortcode_simple_table' => __('Shortcode', 'wpdatatables'),
            'html_code_simple_table' => __('HTML code', 'wpdatatables'),
            'media_simple_table' => __('Media', 'wpdatatables'),
            'link_simple_table' => __('Link', 'wpdatatables'),
            'clip_simple_table' => __('Clip', 'wpdatatables'),
            'overflow_simple_table' => __('Overflow', 'wpdatatables'),
            'wrap_simple_table' => __('Wrap', 'wpdatatables'),
            'left_simple_table' => __('Left', 'wpdatatables'),
            'center_simple_table' => __('Center', 'wpdatatables'),
            'right_simple_table' => __('Right', 'wpdatatables'),
            'justify_simple_table' => __('Justify', 'wpdatatables'),
            'top_simple_table' => __('Top', 'wpdatatables'),
            'middle_simple_table' => __('Middle', 'wpdatatables'),
            'bottom_simple_table' => __('Bottom', 'wpdatatables'),
            'insert_row_above_simple_table' => __('Insert row above', 'wpdatatables'),
            'insert_row_below_simple_table' => __('Insert row below', 'wpdatatables'),
            'remove_row_simple_table' => __('Remove row', 'wpdatatables'),
            'insert_col_left_simple_table' => __('Insert column left', 'wpdatatables'),
            'insert_col_right_simple_table' => __('Insert column right', 'wpdatatables'),
            'remove_column_simple_table' => __('Remove column', 'wpdatatables'),
            'alignment_simple_table' => __('Alignment', 'wpdatatables'),
            'cut_simple_table' => __('Cut', 'wpdatatables'),
            'insert_custom_simple_table' => __('Insert custom', 'wpdatatables'),
            'undo_simple_table' => __('Undo', 'wpdatatables'),
            'redo_simple_table' => __('Redo', 'wpdatatables'),
            'text_wrapping_simple_table' => __('Text wrapping', 'wpdatatables'),
            'merge_cells_simple_table' => __('Merge cells', 'wpdatatables'),
            'copy_simple_table' => __('Copy', 'wpdatatables'),
        );
    }

    public static function getTranslationStringsTableSettingsMain()
    {
        return array(
            'success_main' => __('Success!', 'wpdatatables'),
            'error_main' => __('Error!', 'wpdatatables'),
            'selected_replace_data_option_main' => __("<small>You've selected the <strong>'Replace rows with source data'</strong> option. This means that you're about to <strong>delete all the data</strong> you currently have in your table and replace it with data from your source file.<br><br> If you have any <strong>date type columns</strong> in your file, please make sure you set the <strong>date input format in Main settings of plugin</strong> to the one you're using in your source file first.<br><br>Please consider <strong>duplicating your table first</strong>, before updating.<br><br><strong>There is no undo.</strong></small> ", "wpdatatables"),
            'selected_add_data_option_main' => __("<small>You've selected the <strong>'Add data to current table data'</strong> option. This means that you're about to <strong>add data</strong> from the file source to your table.<br><br> If you have any <strong>date type columns</strong> in your file, please make sure you set the <strong>date input format in Main settings of plugin</strong> to the one you're using in your source file first.<br><br>Please consider <strong>duplicating your table first</strong>, before updating.<br><br><strong>There is no undo.</strong></small>", "wpdatatables"),
            'selected_replace_table_option_main' => __("<small>You've selected the <strong>'Replace entire table data'</strong> option. This means that you're about to <strong>delete your entire table data and current column settings</strong> and replace it with data from your source file with default settings for columns.<br><br> If you have any <strong>date type columns</strong> in your file, please make sure you set the <strong>date input format in Main settings of plugin</strong> to the one you're using in your source file first. <br><br>Please consider <strong>duplicating your table first</strong>, before updating.<br><br><strong>There is no undo.</strong></small> ", "wpdatatables"),
            'tableNameEmpty_main' => __('Table name can not be empty! Please provide a name for your table.', 'wpdatatables'),
            'tableSaved_main' => __('Table saved successfully!', 'wpdatatables'),
            'selectExcelCsv_main' => __('Select an Excel or CSV file', 'wpdatatables'),
            'chooseFile_main' => __('Choose file', 'wpdatatables'),
            'selectAll_main' => __('Select all', 'wpdatatables'),
            'deselectAll_main' => __('Deselect all', 'wpdatatables'),
            'getJsonRoots_main' => __('JSON roots are found!', 'wpdatatables'),
            'errorText_main' => __('Unable to retrieve results', 'wpdatatables'),
            'nothingSelected_main' => __('Nothing selected', 'wpdatatables'),
            'sLoadingRecords_main' => __('Loading...', 'wpdatatables'),
            'currentlySelected_main' => __('Currently selected', 'wpdatatables'),
            'search_main' => __('Search...', 'wpdatatables'),
            'statusInitialized_main' => __('Start typing a search query', 'wpdatatables'),
            'statusNoResults_main' => __('No Results', 'wpdatatables'),
            'statusTooShort_main' => __('Please enter more characters', 'wpdatatables'),
            'api_google_maps_ok_main' => __('Google Maps API key is valid!', 'wpdatatables'),
            'api_google_maps_not_ok_main' => __('There was an error while trying to save Google Maps API key!', 'wpdatatables'),
            'api_google_maps_removed_main' => __('Google Maps API key is removed!', 'wpdatatables'),
            'api_google_key_contains_main' => __('API key is valid', 'wpdatatables'),
            'validate_api_main' => __('Validate & Save', 'wpdatatables'),
            'remove_api_main' => __('Remove', 'wpdatatables'),
            'empty_api_google_key_main' => __('API key is not valid!', 'wpdatatables'),
            'settings_saved_successful_main' => __('Plugin settings saved successfully', 'wpdatatables'),
            'settings_saved_error_main' => __('Unable to save settings of plugin. Please try again or contact us over Support page.', 'wpdatatables'),
            'purchaseCodeInvalid_main' => __('The purchase code is invalid or it has expired', 'wpdatatables'),
            'activation_domains_limit_main' => __('You have reached maximum number of registered domains', 'wpdatatables'),
            'activation_envato_failed_main' => __('It seems you don\'t have a valid purchase of wpDataTables', 'wpdatatables'),
            'envato_failed_powerful_main' => __('It seems you don\'t have a valid purchase of Powerful Filters for wpDataTables', 'wpdatatables'),
            //*
            'envato_failed_report_main' => __('It seems you don\'t have a valid purchase of Report Builder for wpDataTables', 'wpdatatables'),
            //*
            'envato_failed_gravity_main' => __('It seems you don\'t have a valid purchase of Gravity Forms integration for wpDataTables', 'wpdatatables'),
            //*
            'envato_failed_formidable_main' => __('It seems you don\'t have a valid purchase of Formidable Forms integration for wpDataTables', 'wpdatatables'),
            //*
            'pluginActivated_main' => __('Plugin has been activated', 'wpdatatables'),
            'pluginDeactivated_main' => __('Plugin has been deactivated', 'wpdatatables'),
            //*
            'envato_api_activated_main' => __('Activated with Envato', 'wpdatatables'),
            'activateWithEnvato_main' => __('Activate with Envato', 'wpdatatables'),
            'unable_to_deactivate_plugin_main' => __('Unable to deactivate plugin. Please try again later.', 'wpdatatables'),
        );
    }

    public static function getTranslationStringsWpDataTables()
    {
        return array(
            'success_wpdatatables' => __('Success!', 'wpdatatables'),
            'error_wpdatatables' => __('Error!', 'wpdatatables'),
            'dataSaved_wpdatatables' => __('Data has been saved!', 'wpdatatables'),
            'databaseInsertError_wpdatatables' => __('There was an error trying to insert a new row!', 'wpdatatables'),
            'databaseDeleteError_wpdatatables' => __('There was an error trying to delete a row!', 'wpdatatables'),
            'rowDeleted_wpdatatables' => __('Row has been deleted!', 'wpdatatables'),
            'errorText_wpdatatables' => __('Unable to retrieve results', 'wpdatatables'),
            'nothingSelected_wpdatatables' => __('Nothing selected', 'wpdatatables'),
            'sLoadingRecords_wpdatatables' => __('Loading...', 'wpdatatables'),
            'currentlySelected_wpdatatables' => __('Currently selected', 'wpdatatables'),
            'search_wpdatatables' => __('Search...', 'wpdatatables'),
            'statusInitialized_wpdatatables' => __('Start typing a search query', 'wpdatatables'),
            'statusNoResults_wpdatatables' => __('No Results', 'wpdatatables'),
            'statusTooShort_wpdatatables' => __('Please enter more characters', 'wpdatatables'),
            'select_upload_file_wpdatatables' => __('Select a file to use in table', 'wpdatatables'),
            'choose_file_wpdatatables' => __('Use selected file', 'wpdatatables'),
            'chooseFile_wpdatatables' => __('Choose file', 'wpdatatables'),
            'add_new_entry_wpdatatables' => __('Add new entry', 'wpdatatables'),
            'duplicate_entry_wpdatatables' => __('Duplicate entry', 'wpdatatables'),
            'edit_entry_wpdatatables' => __('Edit entry', 'wpdatatables'),
            'invalid_email_wpdatatables' => __('Please provide a valid e-mail address for field', 'wpdatatables'),
            'invalid_link_wpdatatables' => __('Please provide a valid URL link for field', 'wpdatatables'),
            'cannot_be_empty_wpdatatables' => __(' field cannot be empty!', 'wpdatatables'),
            'sInfo_wpdatatables' => __('Showing _START_ to _END_ of _TOTAL_ entries', 'wpdatatables'),
            'sInfoEmpty_wpdatatables' => __('Showing 0 to 0 of 0 entries', 'wpdatatables'),
            'sInfoFiltered_wpdatatables' => __('(filtered from _MAX_ total entries)', 'wpdatatables'),
            'sInfoPostFix_wpdatatables' => '',
            'sInfoThousands_wpdatatables' => __(',', 'wpdatatables'),
            'sLengthMenu_wpdatatables' => __('Show _MENU_ entries', 'wpdatatables'),
            'sLoadingRecords_wpdatatables' => __('Loading...', 'wpdatatables'),
            'sProcessing_wpdatatables' => __('Processing...', 'wpdatatables'),
            'sSearch_wpdatatables' => __('Search: ', 'wpdatatables'),
            'sLengthMenu_wpdatatables' => __('Show _MENU_ entries', 'wpdatatables'),
            'lengthMenu_wpdatatables' => __('Show _MENU_ entries', 'wpdatatables'),
            'sEmptyTable_wpdatatables' => __('No data available in table', 'wpdatatables'),
            'sZeroRecords_wpdatatables' => __('No matching records found', 'wpdatatables'),
            'oAria_wpdatatables' => array(
                'sSortAscending_wpdatatables' => __(': activate to sort column ascending', 'wpdatatables'),
                'sSortDescending_wpdatatables' => __(': activate to sort column descending', 'wpdatatables')
            ),
            'oPaginate_wpdatatables' => array(
                'sFirst_wpdatatables' => __('First', 'wpdatatables'),
                'sLast_wpdatatables' => __('Last', 'wpdatatables'),
                'sNext_wpdatatables' => __('Next', 'wpdatatables'),
                'sPrevious_wpdatatables' => __('Previous', 'wpdatatables')
            ),
            'from_wpdatatables' => __('From', 'wpdatatables'),
            'to_wpdatatables' => __('To', 'wpdatatables'),
            'sortingError_wpdatatables' => __('At least one show/hide sorting icon must be enabled!', 'wpdatatables'),
            'firstPageWCAG_wpdatatables' => __('Navigate to First page', 'wpdatatables'),
            'lastPageWCAG_wpdatatables' => __('Navigate to Last page', 'wpdatatables'),
            'nextPageWCAG_wpdatatables' => __('Navigate to Next page', 'wpdatatables'),
            'previousPageWCAG_wpdatatables' => __('Navigate to Previous page', 'wpdatatables'),
            'pageWCAG_wpdatatables' => __('Navigate to wpDataTable Page ', 'wpdatatables'),
            'spacerWCAG_wpdatatables' => __('Spacer', 'wpdatatables'),
            'printTableWCAG_wpdatatables' => __('Print table', 'wpdatatables'),
            'exportTableWCAG_wpdatatables' => __('Export table', 'wpdatatables'),
            'newEntryWCAG_wpdatatables' => __('New entry', 'wpdatatables'),
            'deleteRowWCAG_wpdatatables' => __('Delete row', 'wpdatatables'),
            'editRowWCAG_wpdatatables' => __('Edit row', 'wpdatatables'),
            'duplicateRowWCAG_wpdatatables' => __('Duplicate row', 'wpdatatables'),
            'clearFiltersWCAG_wpdatatables' => __('Clear filters', 'wpdatatables'),
            'columnVisibilityWCAG_wpdatatables' => __('Column visibility', 'wpdatatables'),
            'sInfoEmptyWCAG_wpdatatables' => __('Showing 0 to 0 of 0 entries _COLUMN_ _DATA_', 'wpdatatables'),
            'sInfoWCAG_wpdatatables' => __('Showing _START_ to _END_ of _TOTAL_ entries _COLUMN_ _DATA_', 'wpdatatables'),
            'masterDetailWCAG_wpdatatables' => __('Master Detail', 'wpdatatables'),
            'globalSearchWCAG_wpdatatables' => __('Global Search Table Input Field', 'wpdatatables'),
            'chooseExportWCAG_wpdatatables' => __('Choose how to export table', 'wpdatatables'),
            'optionHideWCAG_wpdatatables' => __('Option to either display or hide columns', 'wpdatatables'),
            'rowsPerPageWCAG_wpdatatables' => __('Open dropdown menu for show rows per page', 'wpdatatables'),
            'forWCAG_wpdatatables' => __('for ', 'wpdatatables'),
            'columnSearchWCAG_wpdatatables' => __(' column searching for ', 'wpdatatables'),
            'valueFromWCAG_wpdatatables' => __('value from ', 'wpdatatables'),
            'valueToWCAG_wpdatatables' => __(' value to ', 'wpdatatables'),
            'andforWCAG_wpdatatables' => __(' and for ', 'wpdatatables'),
            'andforGloablWCAG_wpdatatables' => __(' and for Global search of value ', 'wpdatatables'),
            'forGloablWCAG_wpdatatables' => __('for Global search of value ', 'wpdatatables'),
            'lenghtMenuWCAG_wpdatatables' => __('Length menu:', 'wpdatatables'),
            'searchTableWCAG_wpdatatables' => __('Search table:', 'wpdatatables'),
            'all_wpdatatables' => __('All', 'wpdatatables'),
            'customDisplayError_wpdatatables' => __('Invalid format of custom rows per page. Please enter a valid format like "1,2,3,4". If you use the number 0, it must be in the format 0 without any preceding zeros.', 'wpdatatables'),
            'close_common_wpdatatables' => __('Close', 'wpdatatables'),
            'error_adding_to_cart_wpdatatables' => __('Error adding products to cart.', 'wpdatatables'),
            'select_products_for_cart_wpdatatables' => __('Please select products to add to the cart.', 'wpdatatables'),
            'error_fetching_cart_info_wpdatatables' => __('Error fetching cart info.', 'wpdatatables'),
            'could_not_add_to_cart_wpdatatables' => __('Could not add this product to cart - the stock of this product could be limited.', 'wpdatatables'),
            'emtyfields_woo_front' => __('All of the following fields must be filled out: Taxonomy, Tax Field and Tax Terms.', 'wpdatatables'),
        );
    }

    public static function getTranslationStringsColumnFilter()
    {
        return array(
            'errorText_columnfilter' => __('Unable to retrieve results', 'wpdatatables'),
            'nothingSelected_columnfilter' => __('Nothing selected', 'wpdatatables'),
            'sLoadingRecords_columnfilter' => __('Loading...', 'wpdatatables'),
            'currentlySelected_columnfilter' => __('Currently selected', 'wpdatatables'),
            'search_columnfilter' => __('Search...', 'wpdatatables'),
            'statusInitialized_columnfilter' => __('Start typing a search query', 'wpdatatables'),
            'statusNoResults_columnfilter' => __('No Results', 'wpdatatables'),
            'statusTooShort_columnfilter' => __('Please enter more characters', 'wpdatatables'),
            'from_columnfilter' => __('From', 'wpdatatables'),
            'to_columnfilter' => __('To', 'wpdatatables'),
            'fromDate_columnfilter' => __('Date from', 'wpdatatables'),
            'toDate_columnfilter' => __('Date to', 'wpdatatables'),
            'fromDateTime_columnfilter' => __('DateTime from', 'wpdatatables'),
            'toDateTime_columnfilter' => __('DateTime to', 'wpdatatables'),
            'fromTime_columnfilter' => __('Time from', 'wpdatatables'),
            'toTime_columnfilter' => __('Time to', 'wpdatatables'),
            'filterInputString_columnfilter' => __('Filter input for ', 'wpdatatables'),
            'filterInputNumber_columnfilter' => __('Filter input for number range filter ', 'wpdatatables'),
            'filterInputDate_columnfilter' => __('Filter input for date picker ', 'wpdatatables'),
            'filterInputDateTime_columnfilter' => __('Filter input for datetime picker ', 'wpdatatables'),
            'filterInputTime_columnfilter' => __('Filter input for time picker ', 'wpdatatables'),
            'filterCheckbox_columnfilter' => __('Filter checkbox for ', 'wpdatatables'),
            'minValue_columnfilter' => __('Minimum Value: ', 'wpdatatables'),
            'maxValue_columnfilter' => __('Maximum Value: ', 'wpdatatables'),
            'multiSelectBoxOption_columnfilter' => __('MultiSelectBox option', 'wpdatatables'),
            'selectBoxOption_columnfilter' => __('SelectBox option', 'wpdatatables'),
            'dividerSearchBox_columnfilter' => __('This is divider between searchbox input and options to select', 'wpdatatables'),
        );
    }

    public static function getTranslationStringsExcel()
    {
        return array(
            'select_upload_file_excel' => __('Select a file to use in table', 'wpdatatables'),
            'choose_file_excel' => __('Use selected file', 'wpdatatables'),
            'chooseFile_excel' => __('Choose file', 'wpdatatables'),
            'browse_file_excel' => __('Browse', 'wpdatatables'),
            'detach_file_excel' => __('detach', 'wpdatatables'),
        );
    }

    public static function getTranslationStringsExcelPlugin()
    {
        return array(
            'invalid_value_excel' => __('You have entered invalid value. Press ESC to cancel.', 'wpdatatables'),
        );
    }

    public static function getTranslationStringsPlugin()
    {
        return array(
            'success' => __('Success!', 'wpdatatables'),
            'error' => __('Error!', 'wpdatatables'),
            'modalTitle' => __('Row details', 'wpdatatables'),
            'previousFilter' => __('Choose an option in previous filters', 'wpdatatables'),
        );
    }

    public static function getTranslationStringsFunctions()
    {
        return array(
            'sInfo_functions' => __('Showing _START_ to _END_ of _TOTAL_ entries', 'wpdatatables'),
            'sInfoEmpty_functions' => __('Showing 0 to 0 of 0 entries', 'wpdatatables'),
            'sInfoFiltered_functions' => __('(filtered from _MAX_ total entries)', 'wpdatatables'),
            'sInfoPostFix_functions' => '',
            'sInfoThousands_functions' => __(',', 'wpdatatables'),
            'sLengthMenu_functions' => __('Show _MENU_ entries', 'wpdatatables'),
            'sLoadingRecords_functions' => __('Loading...', 'wpdatatables'),
            'sProcessing_functions' => __('Processing...', 'wpdatatables'),
            'sSearch_functions' => __('Search: ', 'wpdatatables'),
            'sLengthMenu_functions' => __('Show _MENU_ entries', 'wpdatatables'),
            'lengthMenu_functions' => __('Show _MENU_ entries', 'wpdatatables'),
            'sEmptyTable_functions' => __('No data available in table', 'wpdatatables'),
            'sZeroRecords_functions' => __('No matching records found', 'wpdatatables'),
            'oAria_functions' => array(
                'sSortAscending_functions' => __(': activate to sort column ascending', 'wpdatatables'),
                'sSortDescending_functions' => __(': activate to sort column descending', 'wpdatatables')
            ),
            'oPaginate_functions' => array(
                'sFirst_functions' => __('First', 'wpdatatables'),
                'sLast_functions' => __('Last', 'wpdatatables'),
                'sNext_functions' => __('Next', 'wpdatatables'),
                'sPrevious_functions' => __('Previous', 'wpdatatables')
            ),
            'nothingSelected_functions' => __('Nothing selected', 'wpdatatables'),
        );
    }

    public static function getTranslationStringsInlineEditing()
    {
        return array(
            'invalid_email_inline' => __('Please provide a valid e-mail address for field', 'wpdatatables'),
            'invalid_link_inline' => __('Please provide a valid URL link for field', 'wpdatatables'),
            'cannot_be_empty_inline' => __(' field cannot be empty!', 'wpdatatables'),
            'cannot_be_edit_inline' => __('You can\'t edit this field', 'wpdatatables'),
            'errorText_inline' => __('Unable to retrieve results', 'wpdatatables'),
            'nothingSelected_inline' => __('Nothing selected', 'wpdatatables'),
            'sLoadingRecords_inline' => __('Loading...', 'wpdatatables'),
            'currentlySelected_inline' => __('Currently selected', 'wpdatatables'),
            'search_inline' => __('Search...', 'wpdatatables'),
            'statusInitialized_inline' => __('Start typing a search query', 'wpdatatables'),
            'statusNoResults_inline' => __('No Results', 'wpdatatables'),
            'statusTooShort_inline' => __('Please enter more characters', 'wpdatatables'),
            'selectFileAttachment_inline' => __('Select file', 'wpdatatables'),
            'changeFileAttachment_inline' => __('Change', 'wpdatatables'),
            'saveFileAttachment_inline' => __('Save', 'wpdatatables'),
            'removeFileAttachment_inline' => __('Remove', 'wpdatatables'),
            'select_upload_file_inline' => __('Select a file to use in table', 'wpdatatables'),
            'choose_file_inline' => __('Use selected file', 'wpdatatables'),
            'chooseFile_inline' => __('Choose file', 'wpdatatables'),
            'inlineEditing_inline' => __('Inline editing of the cell ', 'wpdatatables'),
        );
    }

    public static function getTranslationStringsAddRemoveColumn()
    {
        return array(
            'successAddRemoveColumn' => __('Success!', 'wpdatatables'),
            'errorAddRemoveColumn' => __('Error!', 'wpdatatables'),
            'columnAddedAddRemoveColumn' => __('Column has been added!', 'wpdatatables'),
            'columnHeaderEmptyAddRemoveColumn' => __('Column header cannot be empty!', 'wpdatatables'),
            'outOfRangeTypeValueAddRemoveColumn' => __('Type value is out-of-range!', 'wpdatatables'),
            'columnRemoveConfirmAddRemoveColumn' => __('Please confirm column deletion!', 'wpdatatables'),
            'columnRemovedAddRemoveColumn' => __('Column has been removed!', 'wpdatatables'),
            'columnsEmptyAddRemoveColumn' => __('Please select columns that you want to use in table', 'wpdatatables'),
            'userIdAddRemoveColumn' => __('Current User ID', 'wpdatatables'),
            'userNameAddRemoveColumn' => __('Current User Name', 'wpdatatables'),
            'userFirstNameAddRemoveColumn' => __('Current User First Name', 'wpdatatables'),
            'userLastNameAddRemoveColumn' => __('Current User First Name', 'wpdatatables'),
            'userEmailAddRemoveColumn' => __('Current User Email', 'wpdatatables'),
            'userLoginAddRemoveColumn' => __('Current User Login', 'wpdatatables'),
            'userIPAddressAddRemoveColumn' => __('Current User IP Address', 'wpdatatables'),
            'dateAddRemoveColumn' => __('Current Date', 'wpdatatables'),
            'datetimeAddRemoveColumn' => __('Current Datetime', 'wpdatatables'),
            'timeAddRemoveColumn' => __('Current Time', 'wpdatatables'),
            'pvar1AddRemoveColumn' => __('Placeholder %VAR1%', 'wpdatatables'),
            'pvar2AddRemoveColumn' => __('Placeholder %VAR2%', 'wpdatatables'),
            'pvar3AddRemoveColumn' => __('Placeholder %VAR3%', 'wpdatatables'),
            'pvar4AddRemoveColumn' => __('Placeholder %VAR4%', 'wpdatatables'),
            'pvar5AddRemoveColumn' => __('Placeholder %VAR5%', 'wpdatatables'),
            'pvar6AddRemoveColumn' => __('Placeholder %VAR6%', 'wpdatatables'),
            'pvar7AddRemoveColumn' => __('Placeholder %VAR7%', 'wpdatatables'),
            'pvar8AddRemoveColumn' => __('Placeholder %VAR8%', 'wpdatatables'),
            'pvar9AddRemoveColumn' => __('Placeholder %VAR9%', 'wpdatatables'),
            'postIdAddRemoveColumn' => __('Post/Page ID', 'wpdatatables'),
            'postTitleAddRemoveColumn' => __('Post/Page Title', 'wpdatatables'),
            'postCategoryAddRemoveColumn' => __('Post/Page Category', 'wpdatatables'),
            'postMetaAddRemoveColumn' => __('Post/Page Meta Value', 'wpdatatables'),
            'postMetaStringAddRemoveColumn' => __('Post/Page Meta Value as string', 'wpdatatables'),
            'postTagsAddRemoveColumn' => __('Post/Page Tags', 'wpdatatables'),
            'postTermsAddRemoveColumn' => __('Post/Page Terms', 'wpdatatables'),
            'loginUrlAddRemoveColumn' => __('Login URL', 'wpdatatables'),
            'currentUrlAddRemoveColumn' => __('Current URL', 'wpdatatables'),
            'userAgentAddRemoveColumn' => __('HTTP User Agent', 'wpdatatables'),
            'referUrlAddRemoveColumn' => __('HTTP Refer URL', 'wpdatatables'),
            'queryParamAddRemoveColumn' => __('Query Parameter (GET)', 'wpdatatables'),
        );
    }

    public static function getTranslationStringsChartWizard()
    {
        return array(
            'selectAllChart' => __('Select all', 'wpdatatables'),
            'deselectAllChart' => __('Deselect all', 'wpdatatables'),
            'saveChart' => __('Save chart', 'wpdatatables'),
        );
    }

    /**
     * Helper function that returns all update info
     * TODO (Update before new versions)
     * @return array
     */
    public static function getUpdateInfo()
    {
        return array(
            'version'  => get_option('wdtVersion'),
            'release_date' => '20.08.2025',
            'features' => [
            ],
            'improvements' => [
            ],
            'bugfixes' => [
                0 => [
                    'text' => 'Fixed issue with Transform Value not working when Row Grouping and filters are enabled.',
                    'link' => ''
                ],
                1 => [
                    'text' => 'Fixed issue with deleting Custom Fields during WooCommerce table creation wizard.',
                    'link' => ''
                ],
                2 => [
                    'text' => 'Fixed issue with Gutenberg not loading the tables properly.',
                    'link' => ''
                ],
                3 => [
                    'text' => 'Fixed issue with Fixed header displaying column sorting arrows.',
                    'link' => ''
                ],
                4 => [
                    'text' => 'Fixed issue with the Global settings responsiveness.',
                    'link' => ''
                ],
            ],
        );
    }

    /**
     * Helper function that returns all update info
     * TODO (Update before new versions)
     * @return array
     */
    public static function getDeactivationInfo()
    {
        return array(
            'version' => get_option('wdtVersion'),
            'wdt_nonce' => wp_nonce_field('wdtDeactivationNonce', 'wdtNonce'),
            'titleDeactivation' => __('QUICK FEEDBACK', 'wpdatatables'),
            'captionDeactivation' => __('If you have a moment, please let us know why you are deactivating the wpDataTables plugin:', 'wpdatatables'),
            'captionDeactivationError' => __('Please select one option from the following list: ', 'wpdatatables'),
            'deactivate_reasons' => [
                0 => [
                    'id' => 'feature_needed',
                    'title' => esc_html__('The plugin doesn’t have a feature that I need'),
                    'input_placeholder' => esc_html__('Please explain your use case and the feature you need: '),
                    'alert' => '',
                ],
                1 => [
                    'id' => 'premium_version',
                    'title' => esc_html__('I bought the premium version'),
                    'input_placeholder' => '',
                    'alert' => '',
                ],
                2 => [
                    'id' => 'stopped_working',
                    'title' => esc_html__('The plugin suddenly stopped working'),
                    'input_placeholder' => esc_html__('Tell us more… '),
                    'alert' => esc_html__('Have you reached out to our support team?'),
                ],
                3 => [
                    'id' => 'broke_my_site',
                    'title' => esc_html__('The plugin broke my site'),
                    'input_placeholder' => esc_html__('Tell us more… '),
                    'alert' => esc_html__('Have you reached out to our support team?'),
                ],
                4 => [
                    'id' => 'better_plugin',
                    'title' => esc_html__('I found a better plugin'),
                    'input_placeholder' => esc_html__('Please share which plugin: '),
                    'alert' => '',
                ],
                5 => [
                    'id' => 'temporary_deactivation',
                    'title' => esc_html__('It is a temporary deactivation - I’m troubleshooting an issue'),
                    'input_placeholder' => '',
                    'alert' => '',
                ],
                6 => [
                    'id' => 'able_to_work',
                    'title' => esc_html__('I haven’t been able to get the plugin to work'),
                    'input_placeholder' => esc_html__('Tell us more… '),
                    'alert' => esc_html__('Have you reached out to our support team?'),
                ],
                7 => [
                    'id' => 'no_longer_needed',
                    'title' => esc_html__('I no longer need the plugin'),
                    'input_placeholder' => esc_html__('Please share more about your use case: '),
                    'alert' => '',
                ],
                8 => [
                    'id' => 'conflict',
                    'title' => esc_html__('The plugin has a conflict with the theme or other plugin'),
                    'input_placeholder' => esc_html__('Please share which plugin/theme: '),
                    'alert' => esc_html__('Have you reached out to our support team?'),
                ],
                9 => [
                    'id' => 'other',
                    'title' => esc_html__('Other'),
                    'input_placeholder' => esc_html__('How could we improve? '),
                    'alert' => '',
                ],
            ]
        );
    }

    /**
     * Helper function that returns an array with date and time settings from wp_options
     * @return array
     */
    public static function getDateTimeSettings()
    {
        return array(
            'wdtDateFormat' => get_option('wdtDateFormat'),
            'wdtTimeFormat' => get_option('wdtTimeFormat'),
            'wdtNumberFormat' => get_option('wdtNumberFormat'),
            'wdtGlobalTableLoader' => get_option('wdtGlobalTableLoader'),
        );
    }

    public static function getGoogleApiMapsKey()
    {
        return array(
            'wdtGoogleApiMaps' => get_option('wdtGoogleApiMaps'),
            'wdtGoogleApiMapsValidated' => get_option('wdtGoogleApiMapsValidated'),
            'wdtGlobalChartLoader' => get_option('wdtGlobalChartLoader'),
        );
    }

    /**
     * Helper function that returns an array with wpDataTables admin pages
     * @return array
     */
    public static function getWpDataTablesAdminPages()
    {
        return array(
            'dashboardUrl' => menu_page_url('wpdatatables-dashboard', false),
            'browseTablesUrl' => menu_page_url('wpdatatables-administration', false),
            'browseChartsUrl' => menu_page_url('wpdatatables-charts', false)
        );
    }

    /**
     * Helper function that returns an array of strings for tutorials
     * @return array
     */
    public static function getTutorialsTranslationStrings()
    {
        $guideTeacherIMG = '<img class="wdt-emoji-title" src="' . WDT_ROOT_URL . 'assets/img/male-teacher.png">';
        $waveIMG = '<img class="wdt-emoji-body" src="' . WDT_ROOT_URL . 'assets/img/wave.png">';
        $partyTitleIMG = '<img class="wdt-emoji-title" src="' . WDT_ROOT_URL . 'assets/img/party-popper.png">';
        $hourglassIMG = '<img class="wdt-emoji-title" src="' . WDT_ROOT_URL . 'assets/img/hourglass-not-done.png">';
        $raisedHandsIMG = '<img class="wdt-emoji-title m-l-5" src="' . WDT_ROOT_URL . 'assets/img/raising-hands.png">';
        $chartIMG = '<img class="wdt-emoji-title" src="' . WDT_ROOT_URL . 'assets/img/chart-increasing.png">';
        $username = wp_get_current_user()->user_login;

        return array(
            'cannot_be_empty_field' => __('The field cannot be empty!', 'wpdatatables'),
            'cannot_be_empty_chart_type' => __('Please choose the chart type.', 'wpdatatables'),
            'cannot_be_empty_chart_table' => __('Please select wpDataTable from the dropdown.', 'wpdatatables'),
            'cannot_be_empty_chart_table_columns' => __('Columns field cannot be empty.', 'wpdatatables'),
            'cancel_button' => __('Cancel', 'wpdatatables'),
            'cancel_tour' => __('The tutorial is not canceled, closed, or end properly. Please cancel it by clicking on the Cancel button.', 'wpdatatables'),
            'error_data_source' => __('Please check the data source that you use for this table.', 'wpdatatables'),
            'finish_button' => __('Finish Tutorial', 'wpdatatables'),
            'next_button' => __('Continue', 'wpdatatables'),
            'start_button' => __('Start', 'wpdatatables'),
            'skip_button' => __('Skip Tutorial', 'wpdatatables'),
            'tour0' => array(
                'step0' => array(
                    'title' => $guideTeacherIMG . __('Welcome to the tutorial!', 'wpdatatables'),
                    'content' => __('Hello ', 'wpdatatables') . $username . $waveIMG . __(', in this tutorial, we will show you how to create a simple table from scratch by choosing a custom number of columns and rows. How to customize each cell, merge cells and a lot more.', 'wpdatatables'),
                ),
                'step1' => array(
                    'title' => __(' Let\'s create a new wpDataTable from scratch!', 'wpdatatables'),
                    'content' => __('Click on \'Create a Table\' to access the wpDataTables Table Wizard.', 'wpdatatables'),
                ),
                'step2' => array(
                    'title' => __('Choose this option', 'wpdatatables'),
                    'content' => __('Please select \'Create a simple table from scratch\'.', 'wpdatatables'),
                ),
                'step3' => array(
                    'title' => __('Click Next', 'wpdatatables'),
                    'content' => __('Please click the \'Next\' button to continue.', 'wpdatatables'),
                ),
                'step4' => array(
                    'title' => __('Welcome to the Simple table wizard!', 'wpdatatables'),
                    'content' => __('Please click \'Continue\' button to move on.', 'wpdatatables'),
                ),
                'step5' => array(
                    'title' => __('Choose a name for your table', 'wpdatatables'),
                    'content' => __('After inserting table name, click \'Continue\' to move on.', 'wpdatatables'),
                ),
                'step6' => array(
                    'title' => __('Choose the number of columns for your table', 'wpdatatables'),
                    'content' => __('Please choose how many columns it will have. Remember that you can always add or reduce the number of columns later. Click \'Continue\' when you finish.', 'wpdatatables'),
                ),
                'step7' => array(
                    'title' => __('Choose the number of rows for your table.', 'wpdatatables'),
                    'content' => __('Please choose how many rows it will have. Remember that you can always add or reduce the number of rows later. Click \'Continue\' when you finish.', 'wpdatatables'),
                ),
                'step8' => array(
                    'title' => __('Click on the \'Generate Table\' button', 'wpdatatables'),
                    'content' => __('When you click on the button, the empty table will be ready for you. ', 'wpdatatables'),
                ),
                'step9' => array(
                    'title' => $hourglassIMG . __('We are generating the table...', 'wpdatatables'),
                    'content' => __('Please, when you see the table, click \'Continue\' to move on.', 'wpdatatables'),
                ),
                'step10' => array(
                    'title' => __('Nice job! You just configured your table and it is ready to fill it with data.', 'wpdatatables') . $raisedHandsIMG,
                    'content' => __('Now we will guide you on how to insert data and check table layout throw Simple table editor, table toolbar and table preview. Please click \'Continue\' to move on.', 'wpdatatables'),
                ),
                'step11' => array(
                    'title' => __('This is Simple table editor', 'wpdatatables'),
                    'content' => __('Here you can populate your table with data. <br><br>You can move around the cells using keyboard arrows and the Tab button. <br><br>Rearrange columns or rows by drag and drop column or row headers. Easily resize column width and row height by dragging the right corner of the column header, or the bottom line of the row header. Click \'Continue\' to move on.', 'wpdatatables'),
                ),
                'step12' => array(
                    'title' => __('Check out the Simple table toolbar', 'wpdatatables'),
                    'content' => __('Here you can style and insert custom data for each cell or range of cells. You can add or delete columns and rows, merge cells, customize sections by colors, background, alignment, insert custom links, media, shortcodes, star ratings or custom HTML code.', 'wpdatatables'),
                ),
                'step13' => array(
                    'title' => __('Responsive table views', 'wpdatatables'),
                    'content' => __('You can switch between Desktop, Tablet or Mobile devices by clicking on the tab that you need, so you can make sure your table looks excellent across all devices. ', 'wpdatatables'),
                ),
                'step14' => array(
                    'title' => __('Real-time preview', 'wpdatatables'),
                    'content' => __('Here you will see how your table will look like on the page. Please click \'Continue\' to move on.', 'wpdatatables'),
                ),
                'step15' => array(
                    'title' => $partyTitleIMG . __('Congrats! Your table is ready.', 'wpdatatables'),
                    'content' => __('Now you can copy the shortcode for this table, and check out how it looks on your website when you paste it to a post or page. You can always come back and edit the table as you like.', 'wpdatatables'),
                )
            ),
            'tour1' => array(
                'step0' => array(
                    'title' => $guideTeacherIMG . __('Welcome to the tutorial!', 'wpdatatables'),
                    'content' => __('Hello ', 'wpdatatables') . $username . $waveIMG . __(', in this tutorial we will show you how to create a wpDataTable linked to an existing data source. "Linked" in this context means that if you create a table, for example, based on an Excel file, it will read the data from this file every time it loads, making sure all table values changes are instantly reflected in the table.', 'wpdatatables'),
                ),
                'step1' => array(
                    'title' => __('Let\'s create a new wpDataTable!', 'wpdatatables'),
                    'content' => __('Click on \'Create a Table\' to access the wpDataTables Table Wizard.', 'wpdatatables'),
                ),
                'step2' => array(
                    'title' => __('Choose this option.', 'wpdatatables'),
                    'content' => __('Please select \'Create a table linked to an existing data source\'.', 'wpdatatables'),
                ),
                'step3' => array(
                    'title' => __('Click Next', 'wpdatatables'),
                    'content' => __('Please click the \'Next\' button to continue.', 'wpdatatables'),
                ),
                'step4' => array(
                    'title' => __('Input data source type', 'wpdatatables'),
                    'content' => __('Please select a data source type that you need.', 'wpdatatables'),
                ),
                'step5' => array(
                    'title' => __('Select Data source type', 'wpdatatables'),
                    'content' => __('Please choose the data source that you need (SQL, Excel, CSV, JSON, Google Spreadsheet, or PHP array) and then click \'Continue\' button.', 'wpdatatables'),
                ),
                'step6' => array(
                    'title' => __('Input file path or URL', 'wpdatatables'),
                    'content' => __('Upload your file or provide the full URL here. When you finish click \'Continue\' button.', 'wpdatatables'),
                ),
                'step7' => array(
                    'title' => __('Write SQL query', 'wpdatatables'),
                    'content' => __('Please write your custom SQL query and then click \'Continue\' button.', 'wpdatatables'),
                ),
                'step8' => array(
                    'title' => __('Click Save Changes', 'wpdatatables'),
                    'content' => __('Please click on the \'Save Changes\' button to create a table.<br><br> If you get an error message after button click and you are not able to solve it, please contact us on our support platform and provide us this data source that you use for creating this table and copy error message as well and click Skip tutorial.', 'wpdatatables'),
                ),
                'step9' => array(
                    'title' => $hourglassIMG . __('The table is creating...', 'wpdatatables'),
                    'content' => __('Now the table is creating. Wait until you see it in the background and then click \'Continue\'.', 'wpdatatables'),
                ),
                'step10' => array(
                    'title' => $partyTitleIMG . __('Nice job! You just created your first wpDataTable!', 'wpdatatables') . $raisedHandsIMG,
                    'content' => __('Now you can copy the shortcode for this table, and check out how it looks on your website when you paste it to a post or page.', 'wpdatatables'),
                )
            ),
            'tour2' => array(
                'step0' => array(
                    'title' => $guideTeacherIMG . __('Welcome to the tutorial!', 'wpdatatables'),
                    'content' => __('Hello ', 'wpdatatables') . $username . $waveIMG . __(', in this tutorial we will show you how to create a table manually, fully in WordPress dashboard.', 'wpdatatables'),
                ),
                'step1' => array(
                    'title' => __('Let\'s create a new wpDataTable!', 'wpdatatables'),
                    'content' => __('Click on \'Create a Table\' to access wpDataTables Table Wizard.', 'wpdatatables'),
                ),
                'step2' => array(
                    'title' => __('Choose this option', 'wpdatatables'),
                    'content' => __('Please select \'Create a table manually\' to proceed.', 'wpdatatables'),
                ),
                'step3' => array(
                    'title' => __('Click NEXT', 'wpdatatables'),
                    'content' => __('Please click \'Next\' button to continue.', 'wpdatatables'),
                ),
                'step4' => array(
                    'title' => __('Welcome to manual table constructor', 'wpdatatables'),
                    'content' => __('This table constructor will help you to create a table from scratch. <br><br>  Click \'Continue\' to set section by section.', 'wpdatatables'),
                ),
                'step5' => array(
                    'title' => __('Choose names and number of columns.', 'wpdatatables'),
                    'content' => __('Give your table a name and choose how many columns it will have.<br><br>  Click the \'Continue\' button when you’re ready to move forward.', 'wpdatatables'),
                ),
                'step6' => array(
                    'title' => __('Column creating wizard', 'wpdatatables'),
                    'content' => __('Here you can set the name, choose the type for each column, drag and drop to reorder them or remove a column.<br><br>  When you set everything, click the \'Continue\' button.', 'wpdatatables'),
                ),
                'step7' => array(
                    'title' => $partyTitleIMG . __('Congrats! You just configured your table.', 'wpdatatables') . $raisedHandsIMG,
                    'content' => __('To start working with it, finish the tutorial, click on the \'Create the table\' button and choose in which editor you would like to open it. ', 'wpdatatables'),
                )
            ),
            'tour3' => array(
                'step0' => array(
                    'title' => $guideTeacherIMG . __('Welcome to the tutorial!', 'wpdatatables'),
                    'content' => __('Hello ', 'wpdatatables') . $username . $waveIMG . __(',  this tutorial will show you how to create a table by importing data from the existing data source.', 'wpdatatables'),
                ),
                'step1' => array(
                    'title' => __('Let\'s create a new wpDataTable!', 'wpdatatables'),
                    'content' => __('Click on \'Create a Table\' to access wpDataTables Table Wizard.', 'wpdatatables'),
                ),
                'step2' => array(
                    'title' => __('Choose this option', 'wpdatatables'),
                    'content' => __('Please select \'Create a table by importing data from a data source\' to proceed.', 'wpdatatables'),
                ),
                'step3' => array(
                    'title' => __('Click Next', 'wpdatatables'),
                    'content' => __('Please click \'Next\' button to continue.', 'wpdatatables'),
                ),
                'step4' => array(
                    'title' => __('Choose your data source', 'wpdatatables'),
                    'content' => __('Upload your file or provide the full URL here. When you finish click \'Continue\' button.', 'wpdatatables'),
                ),
                'step5' => array(
                    'title' => __('Click \'Next\'', 'wpdatatables'),
                    'content' => __('Please click \'Next\' button to continue.', 'wpdatatables'),
                ),
                'step6' => array(
                    'title' => __('Now you can edit your table in the manual constructor', 'wpdatatables'),
                    'content' => __('This constructor will show you columns that will be saved in the database based on your file.<br><br>  Click \'Continue\' button to check section by section.', 'wpdatatables'),
                ),
                'step7' => array(
                    'title' => __('Choose a name for your table.', 'wpdatatables'),
                    'content' => __('Click \'Continue\' button when you’re ready to move forward.', 'wpdatatables'),
                ),
                'step8' => array(
                    'title' => __('Welcome to Column Creating Wizard.', 'wpdatatables'),
                    'content' => __('Your data preview is here. You can change the name and type for each column, drag and drop to reorder them, remove or add a column.<br><br>  When you set everything, click \'Continue\' button.', 'wpdatatables'),
                ),
                'step9' => array(
                    'title' => $partyTitleIMG . __('Congrats! You are ready to create the table!', 'wpdatatables') . $raisedHandsIMG,
                    'content' => __('To start working with it, finish the tutorial, click on the \'Create the table\' button and choose in which editor you would like to open it. ', 'wpdatatables'),
                )
            ),
            'tour4' => array(
                'step0' => array(
                    'title' => $guideTeacherIMG . __('Welcome to the tutorial!', 'wpdatatables'),
                    'content' => __('Hello ', 'wpdatatables') . $username . $waveIMG . __(', in this tutorial, we will show you how to create a table by generating a query from WordPress posts.', 'wpdatatables'),
                ),
                'step1' => array(
                    'title' => __('Let\'s create a new wpDataTable!', 'wpdatatables'),
                    'content' => __('Click on \'Create a Table\' to access the wpDataTables Table Wizard.', 'wpdatatables'),
                ),
                'step2' => array(
                    'title' => __('Choose this option', 'wpdatatables'),
                    'content' => __('Please select \'Generate a query to the WordPress database\' to proceed.', 'wpdatatables'),
                ),
                'step3' => array(
                    'title' => __('Click Next', 'wpdatatables'),
                    'content' => __('Please click \'Next\' button to continue.', 'wpdatatables'),
                ),
                'step4' => array(
                    'title' => __('Choose a name for your table.', 'wpdatatables'),
                    'content' => __('Click \'Continue\' button when you’re ready to move forward.', 'wpdatatables'),
                ),
                'step5' => array(
                    'title' => __('Welcome to MySQL query generator.', 'wpdatatables'),
                    'content' => __('Choose one or more post types from the lefthand side for your table, either by dragging and dropping the post type names or by selecting post types and clicking on the right arrow. Once the post types are marked as selected, \'All post properties\' section will be populated.<br><br>From this section, you need to choose the post properties, you can as well select the ones you want to show in the table either by dragging and dropping or by selecting and clicking on the right arrow. When you finish, click \'Continue\' to move forward.', 'wpdatatables'),
                ),
                'step6' => array(
                    'title' => __('Choose relations', 'wpdatatables'),
                    'content' => __('Define relations (joining rules) between post types.<br><br>  Click \'Continue\' button when you’re ready to move forward.', 'wpdatatables'),
                ),
                'step7' => array(
                    'title' => __('Define post types relations', 'wpdatatables'),
                    'content' => __('When you define it toggle checkbox to have an inner join, uncheck to have left join. <br><br> Click \'Continue\' button when you’re ready to move forward.', 'wpdatatables'),
                ),
                'step8' => array(
                    'title' => __('Set the conditions between the columns', 'wpdatatables'),
                    'content' => __('Add and define conditions between columns in your table by clicking the \'+ Add Condition\' button.<br><br>  When you are ready, click \'Continue\' button to move forward.', 'wpdatatables'),
                ),
                'step9' => array(
                    'title' => __('Set the grouping rules', 'wpdatatables'),
                    'content' => __('By the \'+ Add Grouping\' button, you can define the column grouping rules for the table.<br><br>  Once you\'re done, click "Continue" button to move on.', 'wpdatatables'),
                ),
                'step10' => array(
                    'title' => __('Click \'Next\'', 'wpdatatables'),
                    'content' => __('Please click \'Next\' button to continue.', 'wpdatatables'),
                ),
                'step11' => array(
                    'title' => $hourglassIMG . __('We are creating your query...', 'wpdatatables'),
                    'content' => __('Please wait until wpDataTables query constructor builds the query for you. Once done, it will show five lines of data as a preview. <br><br>Click \'Continue\' button when you see it in the background.', 'wpdatatables'),
                ),
                'step12' => array(
                    'title' => __('Your query preview is ready!', 'wpdatatables') . $raisedHandsIMG,
                    'content' => __('Here you see the query that wpDataTables generated for you. If you are not completely satisfied with it, you can edit it straight from this window.<br><br> Click "Continue" button to move on.', 'wpdatatables'),
                ),
                'step13' => array(
                    'title' => __('Preview of the first 5 rows.', 'wpdatatables'),
                    'content' => __('Here you can see the first few rows the MySQL server returns using the query provided. If you see “No data”, it means that the MySQL server could not execute it, either because of an error in the query or because it is simply returning an empty data-set, even though the syntax is correct. <br>If this is the case, please click \'Skip Tutorial\' and try to create a new one. <br><br>If everything is fine, please click \'NEXT\' to continue.', 'wpdatatables'),
                ),
                'step14' => array(
                    'title' => $partyTitleIMG . __('Congrats! You are ready to create the table!', 'wpdatatables') . $raisedHandsIMG,
                    'content' => __('To start working with it, finish the tutorial, click on the \'Create the table\' button and choose in which editor you would like to open it. ', 'wpdatatables'),
                )
            ),
            'tour5' => array(
                'step0' => array(
                    'title' => $guideTeacherIMG . __('Welcome to the tutorial!', 'wpdatatables'),
                    'content' => __('Hello ', 'wpdatatables') . $username . $waveIMG . __(', this tutorial will show you how to create a table by generating a query from the MySQL database.', 'wpdatatables'),
                ),
                'step1' => array(
                    'title' => __('Let\'s create a new wpDataTable!', 'wpdatatables'),
                    'content' => __('Click on \'Create a Table\' to access wpDataTables Table Wizard.', 'wpdatatables'),
                ),
                'step2' => array(
                    'title' => __('Choose this option', 'wpdatatables'),
                    'content' => __('Please select \'Generate a query to the MySQL database\' to proceed.', 'wpdatatables'),
                ),
                'step3' => array(
                    'title' => __('Click Next', 'wpdatatables'),
                    'content' => __('Please click \'Next\' button to continue.', 'wpdatatables'),
                ),
                'step4' => array(
                    'title' => __('Choose a name for your table', 'wpdatatables'),
                    'content' => __('Click \'Continue\' button when you’re ready to move forward.', 'wpdatatables'),
                ),
                'step5' => array(
                    'title' => __('Welcome to MySQL query generator', 'wpdatatables'),
                    'content' => __('Here you can choose one or more MySQL tables as data sources for your new wpDataTable, either by dragging and dropping the table names or by selecting the MySQL table and clicking the right arrow. \'All SQL columns\' section will be populated. Next, please choose which columns you would like to show in the wpDataTable - as well, either with drag and drop or by selecting columns and clicking on the right arrow to move them to the \'Selected SQL columns\' section.<br><br> When you finish, click \'Continue\' button to move forward.', 'wpdatatables'),
                ),
                'step6' => array(
                    'title' => __('Define SQL tables relations', 'wpdatatables'),
                    'content' => __('Once you configure the relations, mark the checkbox for using an inner join rule, or uncheck to use left join.<br><br> When you finish, click \'Continue\' button to move forward.', 'wpdatatables'),
                ),
                'step7' => array(
                    'title' => __('Set conditions between the columns.', 'wpdatatables'),
                    'content' => __('Add and define conditions between columns in your table by clicking on the button \'+ Add Condition\'.<br><br> When you finish, click on \'Continue\' button to move forward.', 'wpdatatables'),
                ),
                'step8' => array(
                    'title' => __('Set grouping rules', 'wpdatatables'),
                    'content' => __('By the \'+ Add Grouping\' button, you can define the column grouping rules for the table.<br><br> Once you\'re done, click "Continue" button to move on.', 'wpdatatables'),
                ),
                'step9' => array(
                    'title' => __('Click \'Next\'', 'wpdatatables'),
                    'content' => __('Please click \'Next\' button to continue.', 'wpdatatables'),
                ),
                'step10' => array(
                    'title' => $hourglassIMG . __('We are creating your query...', 'wpdatatables'),
                    'content' => __('Please wait until wpDataTables query constructor builds the query for you. Once done, it will show five lines of data as a preview.<br><br> Click \'Continue\' button when you see it in the background.', 'wpdatatables'),
                ),
                'step11' => array(
                    'title' => __('Your query preview is ready!', 'wpdatatables') . $raisedHandsIMG,
                    'content' => __('Here you see the query that wpDataTables generated for you. If you are not completely satisfied with it, you can edit it straight from this window.<br><br> Click \'Continue\' button to move forward.', 'wpdatatables'),
                ),
                'step12' => array(
                    'title' => __('Preview of the first 5 rows', 'wpdatatables'),
                    'content' => __('Here you can see the first few rows the MySQL server returns using the query provided. If you see “No data”, it means that the MySQL server could not execute it, either because of an error in the query or because it is simply returning an empty data-set, even though the syntax is correct. <br>If this is the case, please click \'Skip Tutorial\' and try to create a new one. <br>If everything is fine, please click \'Continue\' to move forward.', 'wpdatatables'),
                ),
                'step13' => array(
                    'title' => $partyTitleIMG . __('Congrats! You are ready to create the table!', 'wpdatatables') . $raisedHandsIMG,
                    'content' => __('To start working with it, finish the tutorial, click on the \'Create the table\' button and choose in which editor you would like to open it. ', 'wpdatatables'),
                )
            ),
            'tour6' => array(
                'step0' => array(
                    'title' => $guideTeacherIMG . __('Welcome to the tutorial!', 'wpdatatables'),
                    'content' => __('Hello ', 'wpdatatables') . $username . $waveIMG . __(', in this tutorial we will show you how to create a chart in wpDataTables plugin.', 'wpdatatables'),
                ),
                'step1' => array(
                    'title' => __('Let\'s create a new wpDataTables Chart!', 'wpdatatables'),
                    'content' => __('Click on \'Create a Chart\' to access the wpDataTables Chart Wizard.', 'wpdatatables'),
                ),
                'step2' => array(
                    'title' => $chartIMG . __('Welcome to the Chart Wizard!', 'wpdatatables'),
                    'content' => __('You are at the first step now; we will introduce you the wpDataTables Chart Wizard section by section.<br><br> Click \'Continue\' button to move forward.', 'wpdatatables'),
                ),
                'step3' => array(
                    'title' => __('Follow the steps in the Chart Wizard', 'wpdatatables'),
                    'content' => __('By following these steps, you will finish building your chart in the Chart Wizard. The current step will always be highlighted in blue.<br><br> Click \'Continue\' button to move forward.', 'wpdatatables'),
                ),
                'step4' => array(
                    'title' => __('Choose a name for your Chart', 'wpdatatables'),
                    'content' => __('Click \'Continue\' button when you’re ready to move forward.', 'wpdatatables'),
                ),
                'step5' => array(
                    'title' => __('In wpDataTables you can find several charts render engines.', 'wpdatatables'),
                    'content' => __('Click on the dropdown, and you will see several options that you can choose from. <br><br>To continue, click on the dropdown.', 'wpdatatables'),
                ),
                'step6' => array(
                    'title' => __('Choose your desired chart engine.', 'wpdatatables'),
                    'content' => __('By clicking on one of the three options, you will choose the engine that will render your chart.<br><br> When you finish, please click \'Continue\' button to move forward.', 'wpdatatables'),
                ),
                'step7' => array(
                    'title' => __('Different charts types based on the engine you choose. ', 'wpdatatables'),
                    'content' => __('Here you can choose a chart type. Please, click on the chart type that you prefer.<br><br> When you finish, please click \'Continue\' button to move forward.', 'wpdatatables'),
                ),
                'step10' => array(
                    'title' => __('The first step is finished!', 'wpdatatables'),
                    'content' => __('Let\'s move on. Please, click \'Next\' to continue.', 'wpdatatables'),
                ),
                'step11' => array(
                    'title' => __('Now you need to choose a wpDataTable based on which we will build a chart for you', 'wpdatatables'),
                    'content' => __('Click on the dropdown, and all your tables will be listed. The columns of the table that you choose will be used for creating the chart.<br><br>If you didn\'t create a wpDataTable yet, then please click on the \'Skip Tutorial\' button and create wpDataTable that would contain the data to visualize first.', 'wpdatatables'),
                ),
                'step12' => array(
                    'title' => __('Pick your wpDataTable', 'wpdatatables'),
                    'content' => __('Pick a wpDataTable from which you want to render a chart and when you finish, please click \'Continue\' to move on.', 'wpdatatables'),
                ),
                'step13' => array(
                    'title' => __('The second step is finished!', 'wpdatatables') . $raisedHandsIMG,
                    'content' => __('Let\'s see what is coming up next. <br><br> Please, click \'Next\' to continue.', 'wpdatatables'),
                ),
                'step14' => array(
                    'title' => __('Just a heads up!', 'wpdatatables'),
                    'content' => __('Here you will choose from which columns you will create a chart.<br><br> Please click \'Continue\' button to move forward.', 'wpdatatables'),
                ),
                'step15' => array(
                    'title' => __('Meet the wpDataTable Column Blocks', 'wpdatatables'),
                    'content' => __('Here you will choose columns you want to use in the chart. Drag and drop it, or click on the arrow to move the desired column to the \'Columns used in the chart\' section.<br><br> When you finish please, click \'Continue.\'', 'wpdatatables'),
                ),
                'step16' => array(
                    'title' => __('Well done!', 'wpdatatables') . $raisedHandsIMG,
                    'content' => __('Just two more steps to go. Please click \'Next\' to continue.', 'wpdatatables'),
                ),
                'step17' => array(
                    'title' => __('Chart settings and chart preview.', 'wpdatatables'),
                    'content' => __('Here you can adjust chart settings, different parameters are grouped in section; adjusting the parameters will be reflected in the preview of your chart in real-time on the right-hand side.<br><br> Please click \'Continue\' button to move forward.', 'wpdatatables'),
                ),
                'step18' => array(
                    'title' => __('In this sidebar, you can find the chart settings section.', 'wpdatatables'),
                    'content' => __('By clicking on each section, you can set your desired parameters per section.<br><br> Please click \'Continue\' button to move on.', 'wpdatatables'),
                ),
                'step19' => array(
                    'title' => __('Here are the available chart options', 'wpdatatables'),
                    'content' => __('Set different chart options for the chosen section to get your desired chart look.<br><br> Please click \'Continue\' button to move on.', 'wpdatatables'),
                ),
                'step27' => array(
                    'title' => __('How your chart will look like on the page of your website', 'wpdatatables'),
                    'content' => __('Here you can see a preview of your chart based on the settings you have chosen.<br><br> Please click \'Continue\' button to move on.', 'wpdatatables'),
                ),
                'step28' => array(
                    'title' => __('You can save your chart now', 'wpdatatables'),
                    'content' => __('If you are satisfied with your chart appearance, click on the \'Save chart\' button and all your settings for this chart will be saved in the database.', 'wpdatatables'),
                ),
                'step29' => array(
                    'title' => $partyTitleIMG . __('Congrats! Your first chart is ready!', 'wpdatatables') . $raisedHandsIMG,
                    'content' => __('Now you can copy the shortcode for this chart and paste it in any WP post or page. <br><br>You may now finish this tutorial. ', 'wpdatatables'),
                )
            )
        );
    }

    /**
     * Helper function that define default value
     *
     * @param $possible
     * @param $index
     * @param string $default
     *
     * @return string
     */
    public static function defineDefaultValue($possible, $index, $default = '')
    {
        return isset($possible[$index]) ? $possible[$index] : $default;
    }

    /**
     * Helper function that extract column headers in array
     *
     * @param $rawDataArr
     *
     * @return array
     * @throws WDTException
     */
    public static function extractHeaders($rawDataArr)
    {
        reset($rawDataArr);
        if (!is_array($rawDataArr[key($rawDataArr)])) {
            throw new WDTException('Please provide a valid 2-dimensional array.');
        }
        return array_keys($rawDataArr[key($rawDataArr)]);
    }

    /**
     * Helper function that detect columns data type
     *
     * @param $rawDataArr
     * @param $headerArr
     *
     * @return array
     * @throws WDTException
     */
    public static function detectColumnDataTypes($rawDataArr, $headerArr)
    {
        $autodetectData = array();
        $autodetectRowsCount = (10 > count($rawDataArr)) ? count($rawDataArr) - 1 : 9;
        $wdtColumnTypes = array();
        for ($i = 0; $i <= $autodetectRowsCount; $i++) {
            foreach ($headerArr as $key) {
                $cur_val = current($rawDataArr);
                if (!is_array($cur_val[$key])) {
                    $autodetectData[$key][] = $cur_val[$key];
                } else {
                    if (array_key_exists('value', $cur_val[$key])) {
                        $autodetectData[$key][] = $cur_val[$key]['value'];
                    } else {
                        throw new WDTException('Please provide a correct format for the cell.');
                    }
                }
            }
            next($rawDataArr);
        }
        foreach ($headerArr as $key) {
            $wdtColumnTypes[$key] = self::wdtDetectColumnType($autodetectData[$key]);
        }
        return $wdtColumnTypes;
    }

    /**
     * Helper function that convert XML to Array
     *
     * @param $xml SimpleXMLElement
     * @param bool $root
     *
     * @return array|string
     */
    public static function convertXMLtoArr($xml, $root = true)
    {
        if (!$xml->children()) {
            return (string)$xml;
        }

        $array = array();
        foreach ($xml->children() as $element => $node) {
            $totalElement = count($xml->{$element});

            // Has attributes
            if ($attributes = $node->attributes()) {
                $data = array(
                    'attributes' => array(),
                    'value' => (count($node) > 0) ? self::convertXMLtoArr($node, false) : (string)$node
                );

                foreach ($attributes as $attr => $value) {
                    $data['attributes'][$attr] = (string)$value;
                }

                $array[] = $data['attributes'];
            } else {
                if ($totalElement > 1) {
                    $array[][] = self::convertXMLtoArr($node, false);
                } else {
                    $array[$element] = self::convertXMLtoArr($node, false);
                }
            }
        }

        return $array;
    }

    /**
     * Helper function that check if the array is associative
     *
     * @param $arr
     *
     * @return bool
     */
    public static function isArrayAssoc($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Helper function that detect single column type
     *
     * @param $values
     *
     * @return string
     */
    private static function wdtDetectColumnType($values)
    {
        $array = array_filter($values);
        if (empty($array)) {
            return 'string';
        } else {

            if (self::_detect($values, 'WDTTools::wdtIsIP')) {
                return 'string';
            }
            if (self::_detect($values, 'WDTTools::wdtIsInteger')) {
                return 'int';
            }
            if (self::_detect($values, 'preg_match', WDT_TIME_12H_REGEX)
                || self::_detect($values, 'preg_match', WDT_TIME_24H_REGEX)
                || self::_detect($values, 'preg_match', WDT_TIME_WITH_SECONDS_REGEX)) {
                return 'time';
            }
            if (self::_detect($values, 'WDTTools::wdtIsDateTime')) {
                return 'datetime';
            }
            if (self::_detect($values, 'WDTTools::wdtIsDate')) {
                return 'date';
            }
            if (self::_detect($values, 'preg_match', WDT_CURRENCY_REGEX) || self::wdtIsFloat($values)) {
                return 'float';
            }
            if (self::_detect($values, 'preg_match', WDT_EMAIL_REGEX)) {
                return 'email';
            }
            if (self::_detect($values, 'preg_match', WDT_URL_REGEX)) {
                return 'link';
            }
            if (self::_detect($values, 'WDTTools::wdtIsSelect'))
                return 'select';
            if (self::_detect($values, 'WDTTools::wdtIsAddToCart'))
                return 'cart';
            return 'string';
        }
    }


    /** @noinspection PhpUnusedPrivateMethodInspection
     * Function that checks if the passed value is integer
     * wdtIsInteger(23); //bool(true)
     * wdtIsInteger("23"); //bool(true)
     *
     * @param $input
     *
     * @return bool
     */
    private static function wdtIsInteger($input)
    {
        return ctype_digit((string)$input);
    }

    private static function wdtIsIP($input)
    {
        return (bool)filter_var($input, FILTER_VALIDATE_IP);
    }

    /**
     * Function that checks if the passed values are float
     *
     * @param $values
     *
     * @return bool
     */
    private static function wdtIsFloat($values)
    {
        $count = 0;
        for ($i = 0; $i < count($values); $i++) {
            if (is_null($values[$i])) continue;
            if (is_numeric(str_replace(array('.', ','), '', $values[$i]))) {
                $count++;
            }
        }

        return $count == count($values);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection
     * Function that checks if the passed value is date
     *
     * @param $input
     *
     * @return bool
     */
    private static function wdtIsDate($input)
    {
        return strlen($input) > 5 &&
            (
                strtotime($input) ||
                strtotime(str_replace('/', '-', $input)) ||
                strtotime(str_replace(array('.', '-'), '/', $input))
            );
    }

    /** @noinspection PhpUnusedPrivateMethodInspection
     * Function that checks if the passed values is datetime
     *
     * @param $input
     *
     * @return bool
     */
    private static function wdtIsDateTime($input)
    {
        return (
                strtotime($input) ||
                strtotime(str_replace('/', '-', $input)) ||
                strtotime(str_replace(array('.', '-'), '/', $input))
            ) &&
            (
                call_user_func('preg_match', WDT_TIME_12H_REGEX, substr($input, strpos($input, ':') - 2, 5)) ||
                call_user_func('preg_match', WDT_TIME_24H_REGEX, substr($input, strpos($input, ':') - 2, 5)) ||
                call_user_func('preg_match', WDT_AM_PM_TIME_REGEX, substr($input, strpos($input, ':') - 2))
            );
    }

    /**
     * Function that checks if the passed values match a Woo Table Select Column
     *
     * @param $input
     *
     * @return bool
     */
    private static function wdtIsSelect($input): bool
    {
        return $input == '<input type="checkbox" class="select-checkbox">';
    }

    /**
     * Function that checks if the passed values match a Woo Table Add To Cart Column
     *
     * @param $input
     *
     * @return bool
     */
    private static function wdtIsAddToCart($input): bool
    {
        return strpos($input, 'class="single_add_to_cart_button button alt ajax_add_to_cart"') !== false;
    }

    /**
     * @param $valuesArray
     * @param $checkFunction
     * @param string $regularExpression
     *
     * @return bool
     * @throws WDTException
     */
    private static function _detect($valuesArray, $checkFunction, $regularExpression = '')
    {
        if (!is_callable($checkFunction)) {
            throw new WDTException('Please provide a valid type detection function for wpDataTables');
        }
        $count = 0;
        for ($i = 0; $i < count($valuesArray); $i++) {
            if ($regularExpression != '') {
                if ($valuesArray[$i] == null || call_user_func($checkFunction, $regularExpression, $valuesArray[$i])) {
                    $count++;
                } else {
                    return false;
                }
            } else {
                if ($valuesArray[$i] == null || call_user_func($checkFunction, $valuesArray[$i])) {
                    $count++;
                } else {
                    return false;
                }
            }
        }
        if ($count == count($valuesArray)) {
            return true;
        }
        return false;
    }

    /**
     * Get information about the remote version.
     *
     * @param string $slug
     * @param string $purchaseCode
     * @param string $envatoTokenEmail
     *
     * @return bool|object
     */
    public static function getRemoteInformation($slug, $purchaseCode, $envatoTokenEmail)
    {
        $serverName = (defined('WP_CLI') && WP_CLI) ? php_uname('n') : $_SERVER['SERVER_NAME'];
        $request = wp_remote_post(
            WDT_STORE_API_URL . 'autoupdate/info',
            [
                'body' => [
                    'slug' => $slug,
                    'purchaseCode' => $purchaseCode,
                    'envatoTokenEmail' => $envatoTokenEmail,
                    'domain' => self::getDomain(
                        $serverName
                    ),
                    'subdomain' => self::getSubDomain(
                        $serverName
                    )
                ]
            ]
        );

        if ((!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) && isset($request['body'])) {
            $body = json_decode($request['body']);

            return $body && isset($body->info) ? unserialize($body->info, ["allowed_classes" => [stdClass::class]]) : false;
        }

        return false;
    }

    /**
     * Helper function that converts PHP to Moment Date Format
     *
     * @param $dateFormat
     *
     * @return string
     */
    public static function convertPhpToMomentDateFormat($dateFormat)
    {
        $replacements = array(
            'd' => 'DD',
            'D' => 'ddd',
            'j' => 'D',
            'l' => 'dddd',
            'N' => 'E',
            'S' => 'o',
            'w' => 'e',
            'z' => 'DDD',
            'W' => 'W',
            'F' => 'MMMM',
            'm' => 'MM',
            'M' => 'MMM',
            'n' => 'M',
            't' => '', // no equivalent
            'L' => '', // no equivalent
            'o' => 'YYYY',
            'Y' => 'YYYY',
            'y' => 'YY',
            'a' => 'a',
            'A' => 'A',
            'B' => '', // no equivalent
            'g' => 'h',
            'G' => 'H',
            'h' => 'hh',
            'H' => 'HH',
            'i' => 'mm',
            's' => 'ss',
            'u' => 'SSS',
            'e' => 'zz', // deprecated since version 1.6.0 of moment.js
            'I' => '', // no equivalent
            'O' => '', // no equivalent
            'P' => '', // no equivalent
            'T' => '', // no equivalent
            'Z' => '', // no equivalent
            'c' => '', // no equivalent
            'r' => '', // no equivalent
            'U' => 'X',
        );

        return strtr($dateFormat, $replacements);
    }

    /**
     * Helper method to wrap values in quotes for DB
     */
    public static function wrapQuotes($value, $connection)
    {
        $valueQuote = $connection ? "'" : '';
        return $valueQuote . $value . $valueQuote;
    }

    /**
     * Helper method to detect the headers that are present in formula
     *
     * @param $formula
     * @param $headers
     *
     * @return array
     */
    public static function getColHeadersInFormula($formula, $headers)
    {
        $headersInFormula = array();
        foreach ($headers as $header) {
            if (strpos($formula, (string)$header) !== false) {
                $headersInFormula[] = $header;
            }
        }
        return $headersInFormula;
    }

    /**
     * Helper function which converts WP upload URL to Path
     *
     * @param $uploadUrl
     *
     * @return mixed
     */
    public static function urlToPath($uploadUrl)
    {
        $uploadsDir = wp_upload_dir();
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $uploadPath = str_replace($uploadsDir['baseurl'], str_replace('\\', '/', $uploadsDir['basedir']), $uploadUrl);
        } else {
            $uploadPath = str_replace($uploadsDir['baseurl'], $uploadsDir['basedir'], $uploadUrl);
        }
        return $uploadPath;
    }

    /**
     * Helper function which converts upload path to URL
     *
     * @param $uploadPath
     *
     * @return mixed
     */
    public static function pathToUrl($uploadPath)
    {
        $uploadsDir = wp_upload_dir();
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $uploadUrl = str_replace(str_replace('\\', '/', $uploadsDir['basedir']), $uploadsDir['baseurl'], $uploadPath);
        } else {
            $uploadUrl = str_replace($uploadsDir['basedir'], $uploadsDir['baseurl'], $uploadPath);
        }
        return $uploadUrl;
    }


    /**
     * Helper function that convert hex color to rgba
     *
     * @param $color
     * @param bool $opacity
     *
     * @return string
     */
    public static function hex2rgba($color, $opacity = false)
    {

        $default = 'rgb(0,0,0)';

        //Return default if no color provided
        if (empty($color))
            return $default;

        //Sanitize $color if "#" is provided
        if ($color[0] == '#') {
            $color = substr($color, 1);
        }

        //Check if color has 6 or 3 characters and get values
        if (strlen($color) == 6) {
            $hex = array($color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]);
        } elseif (strlen($color) == 3) {
            $hex = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
        } else {
            return $default;
        }

        //Convert hexadec to rgb
        $rgb = array_map('hexdec', $hex);

        //Check if opacity is set(rgba or rgb)
        if ($opacity) {
            if (abs($opacity) > 1)
                $opacity = 1.0;
            $output = 'rgba(' . implode(",", $rgb) . ',' . $opacity . ')';
        } else {
            $output = 'rgb(' . implode(",", $rgb) . ')';
        }

        //Return rgb(a) color string
        return $output;
    }


    /**
     * Helper function that checks if given string is a valid color (hex, rgba, rgb, hsla)
     *
     * @param $color
     *
     * @return bool
     */
    public static function isStringAColor($color)
    {

        $regex = '/^(\#[\da-f]{3}|\#[\da-f]{6}|rgba\(((\d{1,2}|1\d\d|2([0-4]\d|5[0-5]))\s*,\s*){2}((\d{1,2}|1\d\d|2([0-4]\d|5[0-5]))\s*)(,\s*(0\.\d+|1))\)|hsla\(\s*((\d{1,2}|[1-2]\d{2}|3([0-5]\d|60)))\s*,\s*((\d{1,2}|100)\s*%)\s*,\s*((\d{1,2}|100)\s*%)(,\s*(0\.\d+|1))\)|rgb\((?:\s*\d+\s*,){2}\s*[\d]+\)|hsl\(\s*((\d{1,2}|[1-2]\d{2}|3([0-5]\d|60)))\s*,\s*((\d{1,2}|100)\s*%)\s*,\s*((\d{1,2}|100)\s*%)\))$/i';

        return preg_match($regex, $color);
    }

    /**
     * Sanitizes the cell string and wraps it with quotes
     *
     * @param $string
     * @param $connection
     *
     * @return string
     */
    public static function prepareStringCell($string, $connection)
    {
        global $wpdb;
        if (self::isHtml($string)) {
            $string = self::stripJsAttributes($string);
        }

        if ($connection) {
            $string = stripslashes($string);
            $vendor = Connection::getVendor($connection);
            $isMySql = $vendor === Connection::$MYSQL;
            $isMSSql = $vendor === Connection::$MSSQL;
            $isPostgreSql = $vendor === Connection::$POSTGRESQL;

            if ($isPostgreSql) {
                if (version_compare(WDT_PHP_SERVER_VERSION, '8.1', '>')) {
                    $connectionPostgreSql = PgSqlConnection::getInstance($connection);
                    $string = pg_escape_string($connectionPostgreSql, $string);
                    $string = stripslashes($string);
                } else {
                    $string = pg_escape_string($string);
                    $string = stripslashes($string);
                }
            }
            if ($isMSSql) {
                $string = str_replace("'", "''", $string);
            }
            if ($isMySql) {
                $string = $wpdb->_real_escape($string);
                $string = $wpdb->remove_placeholder_escape($string);
            }
        }
        $string = self::wrapQuotes($string, $connection);
        return $string;
    }

    /**
     * Check if passed string is HTML element
     *
     * @param $string
     *
     * @return bool
     */
    public static function isHtml($string)
    {
        return preg_match("/<[^<]+>/", $string, $m) != 0;
    }

    /**
     * Function that strip JS attributes to prevent XSS attacks
     *
     * @param $htmlString
     *
     * @return bool|string
     */
    public static function stripJsAttributes($htmlString)
    {
        $htmlString = stripcslashes($htmlString);
        $htmlString = '<div>' . $htmlString . '</div>';
        if (function_exists('mb_convert_encoding')) {
            $domd = new DOMDocument();
            $domd_status = @$domd->loadHTML(mb_convert_encoding($htmlString, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);
            if ($domd_status) {
                foreach ($domd->getElementsByTagName('*') as $node) {
                    $remove = array();
                    foreach ($node->attributes as $attributeName => $attribute) {
                        if (substr($attributeName, 0, 2) == 'on') {
                            $remove[] = $attributeName;
                        }
                    }
                    foreach ($remove as $i) {
                        $node->removeAttribute($i);
                    }
                }
                return substr($domd->saveHTML($domd->documentElement), 5, -6);
            }
        }
        return $htmlString;
    }

    /**
     * Enqueue JS and CSS UI Kit files
     */
    public static function wdtUIKitEnqueue()
    {
        if (get_option('wdtIncludeGoogleFonts'))
            wp_enqueue_style('wdt-include-inter-google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-bootstrap', WDT_CSS_PATH . 'bootstrap/wpdatatables-bootstrap.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-bootstrap-select', WDT_CSS_PATH . 'bootstrap/bootstrap-select/bootstrap-select.min.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-bootstrap-tagsinput', WDT_CSS_PATH . 'bootstrap/bootstrap-tagsinput/bootstrap-tagsinput.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-bootstrap-datetimepicker', WDT_CSS_PATH . 'bootstrap/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-bootstrap-nouislider', WDT_CSS_PATH . 'bootstrap/bootstrap-nouislider/bootstrap-nouislider.min.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-wp-bootstrap-datetimepicker', WDT_CSS_PATH . 'bootstrap/bootstrap-datetimepicker/wdt-bootstrap-datetimepicker.min.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-bootstrap-colorpicker', WDT_CSS_PATH . 'bootstrap/bootstrap-colorpicker/bootstrap-colorpicker.min.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-wpdt-icons', WDT_ROOT_URL . 'assets/css/style.min.css', array(), WDT_CURRENT_VERSION);
        if (is_admin() && (get_option('wdtGettingStartedPageStatus') != 1)) {
            wp_enqueue_style('wdt-bootstrap-tour-css', WDT_CSS_PATH . 'bootstrap/bootstrap-tour/bootstrap-tour.css', array(), WDT_CURRENT_VERSION);
            wp_enqueue_style('wdt-bootstrap-tour-guide-css', WDT_CSS_PATH . 'bootstrap/bootstrap-tour/bootstrap-tour-guide.css', array(), WDT_CURRENT_VERSION);
        }

        wp_enqueue_style('wdt-animate', WDT_CSS_PATH . 'animate/animate.min.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-uikit', WDT_CSS_PATH . 'uikit/uikit.css', array(), WDT_CURRENT_VERSION);

        if (!is_admin() && get_option('wdtIncludeBootstrap') == 1) {
            wp_enqueue_script('wdt-bootstrap', WDT_JS_PATH . 'bootstrap/bootstrap.min.js', array('jquery'), WDT_CURRENT_VERSION, true);
        } else if (is_admin() && get_option('wdtIncludeBootstrapBackEnd') == 1) {
            wp_enqueue_script('wdt-bootstrap', WDT_JS_PATH . 'bootstrap/bootstrap.min.js', array('jquery'), WDT_CURRENT_VERSION, true);
        } else {
            wp_enqueue_script('wdt-bootstrap', WDT_JS_PATH . 'bootstrap/noconf.bootstrap.min.js', array('jquery'), WDT_CURRENT_VERSION, true);
        }

        if (is_admin() && (get_option('wdtGettingStartedPageStatus') != 1)) {
            wp_enqueue_script('wdt-bootstrap-tour', WDT_JS_PATH . 'bootstrap/bootstrap-tour/bootstrap-tour.js', array('jquery'), WDT_CURRENT_VERSION, true);
            wp_enqueue_script('wdt-bootstrap-tour-guide', WDT_JS_PATH . 'bootstrap/bootstrap-tour/bootstrap-tour-guide.js', array('jquery'), WDT_CURRENT_VERSION, true);
            wp_localize_script('wdt-bootstrap-tour-guide', 'wpdtTutorialStrings', WDTTools::getTutorialsTranslationStrings());
        }
        wp_enqueue_script('wdt-bootstrap-tagsinput', WDT_JS_PATH . 'bootstrap/bootstrap-tagsinput/bootstrap-tagsinput.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-moment', WDT_JS_PATH . 'moment/moment.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-bootstrap-datetimepicker', WDT_JS_PATH . 'bootstrap/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-bootstrap-nouislider', WDT_JS_PATH . 'bootstrap/bootstrap-nouislider/bootstrap-nouislider.min.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-wNumb', WDT_JS_PATH . 'bootstrap/bootstrap-nouislider/wNumb.min.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-bootstrap-colorpicker', WDT_JS_PATH . 'bootstrap/bootstrap-colorpicker/bootstrap-colorpicker.min.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-bootstrap-growl', WDT_JS_PATH . 'bootstrap/bootstrap-growl/bootstrap-growl.min.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-bootstrap-select', WDT_JS_PATH . 'bootstrap/bootstrap-select/bootstrap-select.min.js', array('jquery', 'wdt-bootstrap'), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-bootstrap-ajax-select', WDT_JS_PATH . 'bootstrap/bootstrap-select/ajax-bootstrap-select.min.js', array(), WDT_CURRENT_VERSION, true);
    }

    public static function wdtUIKitEnqueueNotEdit()
    {
        if (get_option('wdtIncludeGoogleFonts'))
            wp_enqueue_style('wdt-include-inter-google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-bootstrap', WDT_CSS_PATH . 'bootstrap/wpdatatables-bootstrap.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-bootstrap-select', WDT_CSS_PATH . 'bootstrap/bootstrap-select/bootstrap-select.min.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-animate', WDT_CSS_PATH . 'animate/animate.min.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wdt-uikit', WDT_CSS_PATH . 'uikit/uikit.css', array(), WDT_CURRENT_VERSION);

        if (!is_admin() && get_option('wdtIncludeBootstrap') == 1) {
            wp_enqueue_script('wdt-bootstrap', WDT_JS_PATH . 'bootstrap/bootstrap.min.js', array('jquery',
                'wdt-bootstrap-select'), WDT_CURRENT_VERSION, true);
        } else if (is_admin() && get_option('wdtIncludeBootstrapBackEnd') == 1) {
            wp_enqueue_script('wdt-bootstrap', WDT_JS_PATH . 'bootstrap/bootstrap.min.js', array('jquery',
                'wdt-bootstrap-select'), WDT_CURRENT_VERSION, true);
        } else {
            wp_enqueue_script('wdt-bootstrap', WDT_JS_PATH . 'bootstrap/noconf.bootstrap.min.js', array('jquery',
                'wdt-bootstrap-select'), WDT_CURRENT_VERSION, true);
        }

        wp_enqueue_script('wdt-bootstrap-select', WDT_JS_PATH . 'bootstrap/bootstrap-select/bootstrap-select.min.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-bootstrap-ajax-select', WDT_JS_PATH . 'bootstrap/bootstrap-select/ajax-bootstrap-select.min.js', array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_script('wdt-moment', WDT_JS_PATH . 'moment/moment.js', array(), WDT_CURRENT_VERSION, true);
    }

    /**
     * Helper method to add PHP vars to JS vars
     *
     * @param $varName
     * @param $phpVar
     */
    public static function exportJSVar($varName, $phpVar)
    {
        self::$jsVars[$varName] = $phpVar;
    }

    /**
     * Helper method to print PHP vars to JS vars
     */
    public static function printJSVars()
    {
        if (!empty(self::$jsVars)) {
            $jsBlock = '<script type="text/javascript">';
            foreach (self::$jsVars as $varName => $jsVar) {
                $jsBlock .= "var {$varName} = " . json_encode($jsVar) . ";";
            }
            $jsBlock .= '</script>';
            echo $jsBlock;
        }
    }

    /**
     * Helper method that converts provided String to Unix Timestamp
     * based on provided date format
     *
     * @param $dateString
     * @param $dateFormat
     *
     * @return false|int
     */
    public static function wdtConvertStringToUnixTimestamp($dateString, $dateFormat)
    {
        if ($dateString == '') return null;
        if (!$dateFormat) $dateFormat = get_option('wdtDateFormat');

        if (null !== $dateFormat && substr($dateFormat, 0, 5) === 'd/m/Y') {
            $returnDate = strtotime(str_replace('/', '-', $dateString));
        } else if (null !== $dateFormat && in_array($dateFormat, ['m.d.Y',
                'm-d-Y',
                'm-d-y',
                'd.m.y',
                'Y.m.d',
                'd-m-Y'])) {
            $returnDate = strtotime(str_replace(['.', '-'], '/', $dateString));
        } else if (null !== $dateFormat && $dateFormat == 'm/Y') {
            $dateObject = DateTime::createFromFormat($dateFormat, $dateString);
            if (!$dateObject) return strtotime($dateString);
            $returnDate = $dateObject->getTimestamp();
        } else {
            $returnDate = strtotime($dateString);
        }

        return $returnDate ?: '';
    }

    /**
     * Helper method that converts provided Unix Timestamp to string
     * based on provided date format
     *
     * @param $columnType
     * @param $displayColumnNameData
     */
    public static function wdtConvertUnixTimestampToString($columnType, $displayColumnNameData)
    {
        if ($columnType == 'date') {
            $displayColumnNameData = date(get_option('wdtDateFormat'), $displayColumnNameData);
        } else if ($columnType == 'datetime') {
            $displayColumnNameData = date(get_option('wdtDateFormat') . ' ' . get_option('wdtTimeFormat'), $displayColumnNameData);
        } else if ($columnType == 'time') {
            $displayColumnNameData = date(get_option('wdtTimeFormat'), $displayColumnNameData);
        }

        return $displayColumnNameData;
    }

    /**
     * Show error message
     *
     * @param $errorMessage
     *
     * @return string
     */
    public static function wdtShowError($errorMessage)
    {
        self::wdtUIKitEnqueue();
        ob_start();
        include WDT_ROOT_PATH . 'templates/common/error.inc.php';
        $errorBlock = ob_get_contents();
        ob_end_clean();
        return $errorBlock;
    }

    /**
     * Helper function to generate unique MySQL column headers
     *
     * @param $header
     * @param $existing_headers
     *
     * @return mixed|string
     */
    public static function generateMySQLColumnName($header, $existing_headers)
    {
        // Prepare the column MySQL title
        $column_header = self::slugify($header);

        // Add index until column header becomes unique
        if (in_array($column_header, $existing_headers)) {
            $index = 0;
            do {
                $index++;
                $try_column_header = $column_header . $index;
            } while (in_array($try_column_header, $existing_headers));
            $column_header = $try_column_header;
        }

        return $column_header;
    }

    /**
     * @param $slug
     */
    public static function deactivatePlugin($slug)
    {
        if ($slug === 'wpdatatables') {
            update_option('wdtPurchaseCodeStore', '');
            update_option('wdtEnvatoTokenEmail', '');
            update_option('wdtActivated', 0);
        } else if ($slug === 'wdt-powerful-filters') {
            update_option('wdtPurchaseCodeStorePowerful', '');
            update_option('wdtEnvatoTokenEmailPowerful', '');
            update_option('wdtActivatedPowerful', 0);
        } else if ($slug === 'reportbuilder') {
            update_option('wdtPurchaseCodeStoreReport', '');
            update_option('wdtEnvatoTokenEmailReport', '');
            update_option('wdtActivatedReport', 0);
        } else if ($slug === 'wdt-gravity-integration') {
            update_option('wdtPurchaseCodeStoreGravity', '');
            update_option('wdtEnvatoTokenEmailGravity', '');
            update_option('wdtActivatedGravity', 0);
        } else if ($slug === 'wdt-formidable-integration') {
            update_option('wdtPurchaseCodeStoreFormidable', '');
            update_option('wdtEnvatoTokenEmailFormidable', '');
            update_option('wdtActivatedFormidable', 0);
        } else if ($slug === 'wdt-master-detail') {
            update_option('wdtPurchaseCodeStoreMasterDetail', '');
            update_option('wdtActivatedMasterDetail', 0);
        }
    }

    /**
     * Helper function to translate special UTF-8 to latin for MySQL
     *
     * @param $text
     *
     * @return mixed|string
     */
    public static function slugify($text)
    {
        // replace non letter or digits by _
        $text = preg_replace('#[^\\pL\d]+#u', '_', $text);

        // trim
        $text = trim($text, '_');

        // transliterate
        if (function_exists('iconv')) {
            $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        }

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('#[^-\w]+#', '', $text);

        // WP sanitize
        $text = str_replace(array('-', '_'), '', sanitize_title($text));

        if (empty($text) || is_numeric($text)) {
            return 'wdtcolumn';
        }

        return $text;
    }

    /**
     * Extract Domain from user serve name
     *
     * @param $domain
     *
     * @return mixed
     */
    public static function extractDomain($domain)
    {
        if (!file_exists(WDT_ROOT_PATH . 'templates/admin/settings/top_level_domains_base.inc.php'))
            return '';
        $topLevelDomainsJSON = require(WDT_ROOT_PATH . 'templates/admin/settings/top_level_domains_base.inc.php');
        $topLevelDomains = json_decode($topLevelDomainsJSON, true);
        $tempDomain = '';

        $extractDomainArray = explode('.', $domain);
        for ($i = 0; $i <= count($extractDomainArray); $i++) {
            $slicedDomainArray = array_slice($extractDomainArray, $i);
            $slicedDomainString = implode('.', $slicedDomainArray);

            if (in_array($slicedDomainString, $topLevelDomains)) {
                $tempDomain = array_slice($extractDomainArray, $i - 1);
                break;
            }
        }
        if ($tempDomain == '') {
            $tempDomain = $extractDomainArray;
        }

        return implode('.', $tempDomain);
    }

    /**
     * Extract subomain from user serve name
     *
     * @param $domain
     *
     * @return string
     */
    public static function extractSubdomain($domain)
    {
        $host = explode('.', $domain);
        $domain = self::extractDomain($domain);
        $domain = explode('.', $domain);
        return implode('.', array_diff($host, $domain));
    }

    /**
     * Check if serve name is IPv4 or Ipv6
     *
     * @param $domain
     *
     * @return boolean
     */
    public static function isIP($domain)
    {
        if (preg_match("/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/", $domain) ||
            preg_match("/^((?:[0-9A-Fa-f]{1,4}))((?::[0-9A-Fa-f]{1,4}))*::((?:[0-9A-Fa-f]{1,4}))((?::[0-9A-Fa-f]{1,4}))*|((?:[0-9A-Fa-f]{1,4}))((?::[0-9A-Fa-f]{1,4})){7}$/", $domain)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Remove all variants www from server name
     *
     * @param $url
     *
     * @return string
     */
    public static function removeWWW($url)
    {
        if (in_array(substr($url, 0, 5), ['www1.', 'www2.', 'www3.', 'www4.'])) {
            return substr_replace($url, "", 0, 5);
        } else if (substr($url, 0, 4) === 'www.') {
            return substr_replace($url, "", 0, 4);
        }
        return $url;
    }

    /**
     * Get filtered domain
     *
     * @param $domain
     *
     * @return string
     */
    public static function getDomain($domain)
    {
        $domain = self::isIP($domain) ? $domain : self::extractDomain(self::removeWWW($domain));
        return $domain;
    }

    /**
     * Get filtered subdomain
     *
     * @param $subdomain
     *
     * @return string
     */
    public static function getSubDomain($subdomain)
    {
        $subdomain = self::isIP($subdomain) ? '' : self::extractSubdomain(self::removeWWW($subdomain));
        return $subdomain;
    }

    /**
     * Get table count from database
     *
     * @param $filter
     *
     * @return null|string
     */
    public static function getTablesCount($filter)
    {
        global $wpdb;
        $filter === 'table' ? $tableFromDB = 'wpdatatables' : $tableFromDB = 'wpdatacharts';
        $query = "SELECT COUNT(*) FROM {$wpdb->prefix}$tableFromDB";
        return (int)$wpdb->get_var($query);
    }

    /**
     * Get data for last insert table from database
     *
     * @param $filter
     *
     * @return stdClass
     */
    public static function getLastTableData($filter)
    {
        global $wpdb;
        $filter === 'table' ? $tableFromDB = 'wpdatatables' : $tableFromDB = 'wpdatacharts';
        $query = "SELECT MAX(id) FROM {$wpdb->prefix}$tableFromDB";
        $lastID = $wpdb->get_var($query);
        $chartQuery = $wpdb->prepare(
            "SELECT * 
                        FROM " . $wpdb->prefix . "wpdatacharts 
                        WHERE id = %d",
            $lastID
        );

        if ($filter === 'table') {
            return WDTConfigController::loadTableFromDB($lastID);
        } else if ($filter === 'chart') {
            return $wpdb->get_row($chartQuery);
        }

    }

    /**
     * Convert Table type for readable content
     *
     * @param $tableType
     *
     * @return string
     */
    public static function getConvertedTableType($tableType)
    {
        switch ($tableType) {
            case 'mysql':
            case 'mssql':
            case 'postgresql':
                return 'SQL';
            case 'manual':
                return 'Manual';
            case 'xls':
                return 'Excel';
            case 'csv':
                return 'CSV';
            case 'xml':
                return 'XML';
            case 'wp_posts_query':
                return 'WP Posts';
            case 'woo_commerce':
                return 'WooCommerce';
            case 'json':
                return 'JSON';
            case 'nested_json':
                return 'Nested JSON';
            case 'serialized':
                return 'Serialized PHP array';
            case 'google_spreadsheet':
                return 'Google sheet';
            default:
                if (in_array($tableType, WPDataTable::$allowedTableTypes)) {
                    return ucfirst($tableType);
                }
                return 'Unknown';
        }

    }

    /**
     * Check if current user can update and delete own rows not others
     *
     * @param $tableData
     * @param $mySqlTableName
     * @param $columnsData
     * @param $id
     * @param $action
     *
     */
    public static function checkCurrentUsersActionsPermissions($tableData, $mySqlTableName, $columnsData, $id, $action)
    {
        global $wpdb;
        $idValCheck = 0;
        $idColumnName = '';
        $userIDColumnName = '';
        foreach ($columnsData as $column) {
            if ($column->id_column) {
                $idColumnName = $column->orig_header;
                $idValCheck = $action == 'delete' ? $id : (int)$id[$idColumnName];
            } else {
                // Defining the values for User ID columns and for "none" input types
                if ($column->id == $tableData->userid_column_id) {
                    $userIDColumnName = $column->orig_header;
                }
            }
        }

        if (!(Connection::isSeparate($tableData->connection))) {
            if ($idValCheck != '0') {
                $res = $wpdb->query($wpdb->prepare("SELECT `{$idColumnName}` FROM {$mySqlTableName} WHERE `{$idColumnName}` = %d AND `{$userIDColumnName}` = %d", $idValCheck, get_current_user_id()));
                if (!$res) {
                    if ($action == 'delete') {
                        $returnResult['error'] = __('User do not have permissions to delete this row! ', 'wpdatatables');
                    } else {
                        $returnResult['error'] = __('User do not have permission to update data!', 'wpdatatables');
                    }
                    echo json_encode($returnResult);
                    exit();
                }
            }
        } else {
            // If plugin is using a separate DB

            $vendor = Connection::getVendor($tableData->connection);
            $isMySql = $vendor === Connection::$MYSQL;
            $isMSSql = $vendor === Connection::$MSSQL;
            $isPostgreSql = $vendor === Connection::$POSTGRESQL;

            $leftSysIdentifier = Connection::getLeftColumnQuote($vendor);
            $rightSysIdentifier = Connection::getRightColumnQuote($vendor);

            $sql = Connection::getInstance($tableData->connection);
            if ($idValCheck != '0') {
                $query = "SELECT {$leftSysIdentifier}{$idColumnName}{$rightSysIdentifier} FROM {$mySqlTableName} WHERE {$leftSysIdentifier}{$idColumnName}{$rightSysIdentifier} = {$idValCheck} AND {$leftSysIdentifier}{$userIDColumnName}{$rightSysIdentifier} =" . get_current_user_id();
                if (!$sql->getField($query)) {
                    if ($action == 'delete') {
                        $returnResult['error'] = __('User does not have permissions to delete this row! ', 'wpdatatables');
                    } else {
                        $returnResult['error'] = __('User does not have permission to update data!', 'wpdatatables');
                    }
                    echo json_encode($returnResult);
                    exit();
                }
            }
        }
    }
}

add_action('admin_footer', array('WDTTools', 'printJSVars'), 100);
add_action('wp_footer', array('WDTTools', 'printJSVars'), 100);
