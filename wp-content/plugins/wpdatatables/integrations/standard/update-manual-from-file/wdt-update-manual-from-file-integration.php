<?php

namespace WDTIntegration;

use Connection;
use Exception;
use WDTConfigController;
use WDTException;
use wpDataTableConstructor;
use wpDataTableSourceFile;

defined('ABSPATH') or die('Access denied.');

// Full url to the Update manual tables from file root directory
define('WDT_UMFF_ROOT_URL', WDT_STANDARD_INTEGRATIONS_URL . 'update-manual-from-file/');
// Full path to the Update manual tables from file root directory
define('WDT_UMFF_ROOT_PATH', WDT_STANDARD_INTEGRATIONS_PATH . 'update-manual-from-file/');
// Update manual tables from file const
define('WDT_UMFF_INTEGRATION', true);


/**
 * Class UpdateManualFromFile
 *
 * @package WDTIntegration
 */
class UpdateManualFromFile
{
    public static function init()
    {
        // Add Update manual tables from file settings block in table settings
        add_action('wpdatatables_add_data_from_source_file_block', array('WDTIntegration\UpdateManualFromFile',
            'addSettingsBlock'));
    }

    /**
     * Adding data from source to the table columns
     *
     * @param $tableData
     *
     * @return mixed|string|void
     * @throws WDTException
     * @throws Exception
     */
    public static function addDataSourceToTable($tableData)
    {
        $columnTypes = array();
        $columnDateInputFormat = array();

        $uploadedFile = $tableData->file;
        $fileSourceAction = $tableData->fileSourceAction;

        if (!($file = wpDataTableConstructor::isUploadedFileEmpty($uploadedFile))) {
            return __('Empty file', 'wpdatatables');
        }

        for ($i = 0; $i < count($tableData->columns); $i++) {
            $columnTypes[$tableData->columns[$i]->orig_header] = sanitize_text_field($tableData->columns[$i]->type);
            $columnDateInputFormat[$tableData->columns[$i]->orig_header] = sanitize_text_field($tableData->columns[$i]->dateInputFormat);
        }

        $objSourceFile = new wpDataTableSourceFile(
            $file,
            $tableData,
            $columnTypes,
            $columnDateInputFormat,
            $fileSourceAction
        );

        $objSourceFile->getTableTypeFromFile();
        try {
            $objSourceFile->prepareHeadingsArray();
        } catch (Exception $e) {
            die($e);
        }

        $vendor = Connection::getVendor($objSourceFile->getTableData()->connection);

        $columnQuoteStart = Connection::getLeftColumnQuote($vendor);
        $columnQuoteEnd = Connection::getRightColumnQuote($vendor);

        $columnHeaders = array();
        foreach ($objSourceFile->getTableData()->columns as $column) {
            if (!in_array($column->type, array('formula', 'masterdetail', 'select', 'index'))) {
                $columnHeaders[] = preg_replace('/\s*/', '', strtolower($column->orig_header));
            }
        }
        //Removes the WPDT table id from the array
        $columnHeaders = array_values(array_filter($columnHeaders, function ($el) {
            $standardColumnHeaders = ["wdt_id",
                "wdt_created_by",
                "wdt_created_at",
                "wdt_last_edited_by",
                "wdt_last_edited_at"];
            return !in_array($el, $standardColumnHeaders);
        }));

        //Error handling
        try {
            $objSourceFile->checkIfFileDataIsCorrect($columnHeaders);
        } catch (WDTException $exception) {
            die($exception);
        }

        if ($objSourceFile->getFileSourceAction() == 'replaceTableData' || $objSourceFile->getFileSourceAction() == 'replaceTable') {
            //Creating delete statement
            $delete_table_data_statement = "DELETE FROM " . $columnQuoteStart
                . $objSourceFile->getTableData()->mysql_table_name . $columnQuoteEnd;

            $objSourceFile->executeQueryStatement($delete_table_data_statement, $objSourceFile->getTableData()->connection);
        }

        //Removing all existing columns from the table
        $objSourceFile->maybeReplaceData($columnTypes);

        //Creating insert statement
        $insert_statement_beginning = WDTConfigController::createInsertStatement(
            $objSourceFile->getTableData()->mysql_table_name,
            $objSourceFile->getColumnOrigHeaders(),
            $columnQuoteStart,
            $columnQuoteEnd
        );

        $objSourceFile->prepareInsertBlocks($insert_statement_beginning, $objSourceFile->getColumnOrigHeaders(), $objSourceFile->getTableData()->mysql_table_name, 'upload');

        return $objSourceFile->getTableData();
    }

    /**
     * Adds Update manual tables block in table settings
     */
    public static function addSettingsBlock()
    {
        ob_start();
        include 'templates/update_manual_from_file_settings_block.inc.php';
        $settingsBlock = ob_get_contents();
        ob_end_clean();
        echo $settingsBlock;
    }
}

UpdateManualFromFile::init();