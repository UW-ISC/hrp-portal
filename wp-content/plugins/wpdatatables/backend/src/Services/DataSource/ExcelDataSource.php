<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\DataSource;

use WPDT\PhpOffice\PhpSpreadsheet\Shared\Date;
use WDTException;
use WPDataTables\Services\Tools\ToolsService;
use WPDataTable;
use WPDataTableCache;
use WPDataTables\Infrastructure\Excel\SpreadsheetReaderAdapter;

/**
 * Excel / CSV / ODS data source adapter.
 *
 * Cache lookup, reading the spreadsheet through {@see SpreadsheetReaderAdapter},
 * building the named-data array (with date/datetime/time cells normalised to
 * timestamps), persisting the cache, the `wpdatatables_filter_excel_array`
 * hook, then handing off to the shared `arrayBasedConstruct()`. Reader creation
 * is isolated behind the Infrastructure\Excel seam.
 *
 * @package WPDataTables\Services\DataSource
 */
class ExcelDataSource implements DataSourceInterface
{
    /** @var SpreadsheetReaderAdapter */
    private SpreadsheetReaderAdapter $reader;

    public function __construct(SpreadsheetReaderAdapter $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritDoc}
     *
     * @throws WDTException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \Exception
     */
    public function read(WPDataTable $table, $source, array $wdtParameters = [])
    {
        $xls_url = $source;
        $cache = WPDataTableCache::maybeCache($table->getCacheSourceData(), (int)$table->getWpId());
        if (!$cache) {
            ini_set('memory_limit', '2048M');
            $fileLocation = $table->getFileLocation();
            if (!$xls_url) {
                throw new WDTException(esc_html__('Excel file not found!', 'wpdatatables'));
            }
            if ($fileLocation == 'wp_media_lib' && !file_exists($xls_url)) {
                throw new WDTException('Provided file ' . stripcslashes($xls_url) . ' does not exist!');
            }

            $format = substr(strrchr($xls_url, "."), 1);
            $objReader = $this->reader->createReader($xls_url);
            $xls_url = apply_filters('wpdatatables_filter_excel_based_data_url', $xls_url, $table->getWpId());
            if ($fileLocation == 'wp_any_url') {
                $xls_url_original = $xls_url;
                $data = ToolsService::curlGetData($xls_url);
                if ($data == null)
                    throw new WDTException(esc_html__("File from provided URL is empty."));
                $tempFileName = 'tempfile' . $table->getWpId() . '.' . $format;
                $fillFileWithData = file_put_contents($tempFileName, $data);
                if ($fillFileWithData === false)
                    throw new WDTException(esc_html__("File from provided URL is empty."));
                $xls_url = $tempFileName;
            }
            $objPHPExcel = $objReader->load($xls_url);
            if ($fileLocation == 'wp_any_url') {
                $xls_url = $xls_url_original;
                unlink($tempFileName);
            }
            $objWorksheet = $objPHPExcel->getActiveSheet();
            $objWorksheet = apply_filters('wpdatatables_before_get_excel_headers', $objWorksheet, $table->getWpId(), $xls_url);
            $highestRow = $objWorksheet->getHighestRow();
            $highestColumn = $objWorksheet->getHighestDataColumn();

            $headingsArray = $objWorksheet->rangeToArray('A1:' . $highestColumn . '1', null, true, true, true);
            while (!end($headingsArray[1])) {
                array_pop($headingsArray[1]);
            };
            foreach ($headingsArray[1] as $heading) {
                if ($heading === '' || $heading === null)
                    throw new WDTException(esc_html__('One or more columns doesn\'t have a header. Please enter headers for all columns in order to proceed.'));
            }
            $headingsArray = array_map('trim', $headingsArray[1]);

            $r = -1;
            $namedDataArray = array();

            $dataRows = $objWorksheet->rangeToArray('A2:' . $highestColumn . $highestRow, null, true, true, true);
            for ($row = 2; $row <= $highestRow; ++$row) {
                if (max($dataRows[$row]) !== null) {
                    ++$r;
                    foreach ($headingsArray as $dataColumnIndex => $dataColumnHeading) {
                        $dataColumnHeading = trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataColumnHeading)));
                        $namedDataArray[$r][$dataColumnHeading] = trim(isset($dataRows[$row][$dataColumnIndex]) ? $dataRows[$row][$dataColumnIndex] : '');
                        $namedDataArray[$r][$dataColumnHeading] = wp_kses_post($namedDataArray[$r][$dataColumnHeading]);
                        $currentDateFormat = isset($wdtParameters['dateInputFormat'][$dataColumnHeading]) ? $wdtParameters['dateInputFormat'][$dataColumnHeading] : null;
                        if (!empty($wdtParameters['data_types'][$dataColumnHeading]) && in_array($wdtParameters['data_types'][$dataColumnHeading], array('date',
                                'datetime',
                                'time'))) {
                            if ($format === 'xls' || $format === 'ods') {
                                $cell = $objPHPExcel->getActiveSheet()->getCell($dataColumnIndex . '' . $row);
                                if (Date::isDateTime($cell) && $cell->getValue() !== null) {
                                    $namedDataArray[$r][$dataColumnHeading] = Date::excelToTimestamp($cell->getValue());
                                } else {
                                    $namedDataArray[$r][$dataColumnHeading] = ToolsService::wdtConvertStringToUnixTimestamp($dataRows[$row][$dataColumnIndex], $currentDateFormat);
                                }
                            } elseif ($format === 'csv') {
                                $namedDataArray[$r][$dataColumnHeading] = ToolsService::wdtConvertStringToUnixTimestamp($dataRows[$row][$dataColumnIndex], $currentDateFormat);
                            }
                        }
                    }
                }
            }
            if (empty($namedDataArray)) {
                throw new WDTException(esc_html__('There is no data in your source file. Please check your source file and try again.', 'wpdatatables'));
            }

            WPDataTableCache::maybeSaveData(
                (int)$table->getWpId(),
                $format,
                $xls_url,
                $table->getAutoUpdateCache(),
                $namedDataArray,
                $table->getCacheSourceData()
            );

        } else {
            $namedDataArray = $cache;
        }

        // Let arrayBasedConstruct know that dates have been converted to timestamps
        $wdtParameters['dates_detected'] = true;

        $namedDataArray = apply_filters('wpdatatables_filter_excel_array', $namedDataArray, $table->getWpId(), $xls_url);

        return $table->arrayBasedConstruct($namedDataArray, $wdtParameters);
    }
}
