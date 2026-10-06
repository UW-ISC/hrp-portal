<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Localization;

/**
 * Admin/frontend JS translation string bundles.
 *
 * @package WPDataTables\Services\Localization
 */
class TranslationStringsProvider
{
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
                'failedToLoadFormFields_common' => __('Failed to load form fields', 'wpdatatables'),
                'invalidResponseServer_common' => __('Invalid response from server', 'wpdatatables'),
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
            $locale = get_user_locale();
            $localeShort = substr($locale, 0, 2);
            $localeFile = $localeShort . '.js';
            $localePath = WDT_ROOT_PATH . 'assets/js/moment/locale/' . $localeFile;

            if (! file_exists($localePath)) {
                $localeShort = strtolower($locale);
            }

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
                'wdt_locale_language' => $localeShort,
            );
        }

    public static function getTranslationStringsFolders()
        {
            return array(
                'successFolders' => __('Success!', 'wpdatatables'),
                'errorFolders' => __('Error!', 'wpdatatables'),
                'collapseFolders' => __('Collapse folders option is saved!', 'wpdatatables'),
                'showFolders' => __('Show folders option is saved!', 'wpdatatables'),
                'unableSortFolders' => __('Unable to sort a folder. Please try again.', 'wpdatatables'),
                'unableFIndFolders' => __('Unable to find a folder. Please try again.', 'wpdatatables'),
                'showAllFolders' => __('Show all folders option is saved!.', 'wpdatatables'),
                'closeAllFolders' => __('Close all folders option is saved!', 'wpdatatables'),
                'chosenSortFolders' => __('Chosen sort order is saved.', 'wpdatatables'),
                'columnVisibilityFolders' => __('Column visibility is changed!', 'wpdatatables'),
                'unableColumnHideFolders' => __('Unable to hide a column. Please try again.', 'wpdatatables'),
                'assignedToFolders' => __('s are assigned to the folder.', 'wpdatatables'),
                'idsEmptyFolders' => __(' ids are empty.', 'wpdatatables'),
                'unableAssignFolders' => __('Unable to assign a folder. Please try again.', 'wpdatatables'),
                'idsItemEmptyFolders' => __('Item ids are empty.', 'wpdatatables'),
                'unableAssignItemFolders' => __('Unable to assign the item to a folder. Please try again.', 'wpdatatables'),
                'isAssignedToFolders' => __(' is assigned to the folder.', 'wpdatatables'),
                'foldercreatedFolders' => __('Folder is created.', 'wpdatatables'),
                'emptyDataFolders' => __('Data is empty. Please try again.', 'wpdatatables'),
                'unableCreateFolders' => __('Unable to create folder. Please try again.', 'wpdatatables'),
                'foldereditedFolders' => __('Folder is edited.', 'wpdatatables'),
                'unableEditFolders' => __('Unable to edit a folder. Please try again.', 'wpdatatables'),
                'folderdeletedFolders' => __('Folder is deleted.', 'wpdatatables'),
                'unableDeleteFolders' => __('Unable to delete a folder. Please try again.', 'wpdatatables'),
                'removedFromFolders' => __(' is removed from folder.', 'wpdatatables'),
                'unableRemoveFromFolders' => __('Unable to remove the item from a folder. Please try again.', 'wpdatatables'),
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
}
