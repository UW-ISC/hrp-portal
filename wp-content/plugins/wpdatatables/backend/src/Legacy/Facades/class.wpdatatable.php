<?php

use WPDT\jlawrence\eos\Parser;
use WPDataTables\Plugin\Plugin;
use WPDataTables\Services\DataSource\DataSourceFactory;
use WPDataTables\Services\Table\FilterService;
use WPDataTables\Services\Table\SummaryService;
use WPDataTables\Services\Table\TableService;
use WPDataTables\Services\Table\TableConfigService;
use WPDataTables\Services\Table\TableHydrationService;
use WPDataTables\Entity\Table\RuntimeTable;
use WPDataTables\Infrastructure\Excel\SpreadsheetReaderAdapter;
use WPDataTables\Rendering\AssetManager;
use WPDataTables\Rendering\TableRenderer;
use WPDataTables\Rendering\FilterRenderer;
use WPDataTables\Rendering\EditDialogRenderer;
use WPDataTables\Rendering\ColumnCssBuilder;
use WPDataTables\Rendering\ColumnDefinitionBuilder;
use WPDataTables\Rendering\ExcelTableRenderer;

defined('ABSPATH') or die('Access denied.');


/**
 * Main engine of wpDataTables plugin
 */
class WPDataTable
{
    // Properties for MD
    public $masterDetail;
    public $masterDetailLogic;
    public $masterDetailRender;
    public $masterDetailSender;
    public $masterDetailSendTableType;
    public $masterDetailSendChildTableID;
    public $masterDetailSendParentTableColumnIDName;
    public $masterDetailSendChildTableColumnIDName;
    public $masterDetailRenderPage;
    public $masterDetailRenderPost;
    public $masterDetailPopupTitle;
    public $masterDetailLinkTargetAttribute;

    // Properties for PF
    public $cascadeFiltering;
    public $cascadeFilteringLogic;
    public $hideFiltersOnDependent;
    public $hideTableBeforeFiltering;
    public $showSearchFiltersButton;
    public $disableSearchFiltersButton;

    protected static $_columnClass = 'WDTColumn';
    protected $_wdtIndexedColumns = array();
    private $_wdtNamedColumns = array();
    public static $wdt_internal_idcount = 0;
    public static $modalRendered = false;
    private $_firstOnPage = false;
    private $_dataRows = array();
    public $_cacheHash = '';
    private $_cssClassArray = array();
    private $_id;
    private $_db;
    private $_columnsCSS = '';
    private $_aggregateFuncsRes = array();
    private $_previewMode = false;
    public $column_id;

    public $connection;
    public static $allowedTableTypes = array(
        'xls',
        'csv',
        'manual',
        'mysql',
        'json',
        'nested_json',
        'google_spreadsheet',
        'xml',
        'serialized',
        'simple',
        'wp_posts_query',
        'woo_commerce',
    );
    /** @var RuntimeTable|null Lazily-built runtime engine entity. */
    private $_runtimeTable = null;

    /**
     * @return bool
     */
    public function isClearFilters()
    {
        return $this->getRuntimeTable()->isClearFilters();
    }

    /**
     * Render-time only flag, so it stays on the facade instead of the
     * config-hydrated RuntimeTable entity.
     *
     * @return bool
     */
    public function isPreviewMode()
    {
        return $this->_previewMode;
    }

    /**
     * @param bool $previewMode
     */
    public function setPreviewMode($previewMode)
    {
        $this->_previewMode = (bool)$previewMode;
    }

    /**
     * @return array
     */
    public function getWdtColumnTypes()
    {
        return $this->getRuntimeTable()->getWdtColumnTypes();
    }


    /**
     * @param bool $clearFilters
     */
    public function setClearFilters($clearFilters)
    {
        $this->getRuntimeTable()->setClearFilters($clearFilters);
    }

    /**
     * @return bool
     */
    public function isFixedLayout()
    {
        return $this->getRuntimeTable()->isFixedLayout();
    }

    /**
     * @param bool $fixedLayout
     */
    public function setFixedLayout($fixedLayout)
    {
        $this->getRuntimeTable()->setFixedLayout($fixedLayout);
    }

    /**
     * @return bool
     */
    public function isWordWrap()
    {
        return $this->getRuntimeTable()->isWordWrap();
    }

    /**
     * @param bool $wordWrap
     */
    public function setWordWrap($wordWrap)
    {
        $this->getRuntimeTable()->setWordWrap($wordWrap);
    }

    public function isFixedColumns()
    {
        return $this->getRuntimeTable()->isFixedColumns();
    }

    public function setFixedColumns($fixedcolumns)
    {
        $this->getRuntimeTable()->setFixedColumns($fixedcolumns);
    }

    public function getCusgtomDisplayLength()
    {
        return $this->getRuntimeTable()->getCusgtomDisplayLength();
    }

    public function setCustomDisplayLength($customRowDisplay)
    {
        $this->getRuntimeTable()->setCustomDisplayLength($customRowDisplay);
    }

    public function getCustomStringEmptyFiltering()
    {
        return $this->getRuntimeTable()->getCustomStringEmptyFiltering();
    }

    public function setCustomStringEmptyFiltering($customStringEmptyFiltering)
    {
        $this->getRuntimeTable()->setCustomStringEmptyFiltering($customStringEmptyFiltering);
    }

    public function getLeftFixedColumnsNumber()
    {
        return $this->getRuntimeTable()->getLeftFixedColumnsNumber();
    }

    public function setLeftFixedColumnsNumber($fixedleftcolumns)
    {
        $this->getRuntimeTable()->setLeftFixedColumnsNumber($fixedleftcolumns);
    }

    public function getRightFixedColumnsNumber()
    {
        return $this->getRuntimeTable()->getRightFixedColumnsNumber();
    }

    public function setRightFixedColumnsNumber($fixedrightcolumns)
    {
        $this->getRuntimeTable()->setRightFixedColumnsNumber($fixedrightcolumns);
    }


    /**
     * @return bool
     */
    public function isAjaxReturn()
    {
        return $this->getRuntimeTable()->isAjaxReturn();
    }

    public function isFixedHeaders()
    {
        return $this->getRuntimeTable()->isFixedHeaders();
    }

    public function setFixedHeaders($fixedheader)
    {
        $this->getRuntimeTable()->setFixedHeaders($fixedheader);
    }
    public function getIndexColumn()
    {
        return $this->getRuntimeTable()->getIndexColumn();
    }

    public function setIndexColumn($indexcolumn)
    {
        $this->getRuntimeTable()->setIndexColumn($indexcolumn);
    }

    public function getFixedHeadersOffset()
    {
        return $this->getRuntimeTable()->getFixedHeadersOffset();
    }

    public function setFixedHeadersOffset($fixedheaderoffset)
    {
        $this->getRuntimeTable()->setFixedHeadersOffset($fixedheaderoffset);
    }

    /**
     * @param bool $ajaxReturn
     */
    public function setAjaxReturn($ajaxReturn)
    {
        $this->getRuntimeTable()->setAjaxReturn($ajaxReturn);
    }

    public function setNoData($no_data)
    {
        $this->getRuntimeTable()->setNoData($no_data);
    }

    public function getNoData()
    {
        return $this->getRuntimeTable()->getNoData();
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     * @return string
     */
    public function getTableContent()
    {
        return $this->getRuntimeTable()->getTableContent();
    }

    /**
     * @param string $tableContent
     */
    public function setTableContent($tableContent)
    {
        $this->getRuntimeTable()->setTableContent($tableContent);
    }

    /**
     * @return string
     */
    public function getFileLocation()
    {
        return $this->getRuntimeTable()->getFileLocation();
    }

    /**
     * @param string $fileLocation
     */
    public function setFileLocation($fileLocation)
    {
        $this->getRuntimeTable()->setFileLocation($fileLocation);
    }

    /**
     * @return string
     */
    public function getTableType()
    {
        return $this->getRuntimeTable()->getTableType();
    }

    /**
     * @param string $tableType
     */
    public function setTableType($tableType)
    {
        $this->getRuntimeTable()->setTableType($tableType);
    }

    public function setDefaultSearchValue($value)
    {
        if (!empty($value)) {
            $this->getRuntimeTable()->setDefaultSearchValue(urlencode($value));
        }
    }

    public function getDefaultSearchValue()
    {
        return urldecode($this->getRuntimeTable()->getDefaultSearchValue());
    }

    public function sortEnabled()
    {
        return $this->getRuntimeTable()->isTableSort();
    }

    public function sortEnable()
    {
        $this->getRuntimeTable()->setTableSort(true);
    }

    public function sortDisable()
    {
        $this->getRuntimeTable()->setTableSort(false);
    }

    public function addSumColumn($columnKey)
    {
        $this->getRuntimeTable()->addSumColumn($columnKey);
    }

    public function setSumColumns($sumColumns)
    {
        $this->getRuntimeTable()->setSumColumns($sumColumns);
    }

    public function getSumColumns()
    {
        return $this->getRuntimeTable()->getSumColumns();
    }

    public function addAvgColumn($columnKey)
    {
        $this->getRuntimeTable()->addAvgColumn($columnKey);
    }

    public function setAvgColumns($avgColumns)
    {
        $this->getRuntimeTable()->setAvgColumns($avgColumns);
    }

    public function getAvgColumns()
    {
        return $this->getRuntimeTable()->getAvgColumns();
    }

    public function addMinColumn($columnKey)
    {
        $this->getRuntimeTable()->addMinColumn($columnKey);
    }

    public function setMinColumns($minColumns)
    {
        $this->getRuntimeTable()->setMinColumns($minColumns);
    }

    public function getMinColumns()
    {
        return $this->getRuntimeTable()->getMinColumns();
    }

    public function addMaxColumn($columnKey)
    {
        $this->getRuntimeTable()->addMaxColumn($columnKey);
    }

    public function setMaxColumns($maxColumns)
    {
        $this->getRuntimeTable()->setMaxColumns($maxColumns);
    }

    public function getMaxColumns()
    {
        return $this->getRuntimeTable()->getMaxColumns();
    }

    public function addSumFooterColumn($columnKey)
    {
        $this->getRuntimeTable()->addSumFooterColumn($columnKey);
    }

    public function setSumFooterColumns($sumColumns)
    {
        $this->getRuntimeTable()->setSumFooterColumns($sumColumns);
    }

    public function getSumFooterColumns()
    {
        return $this->getRuntimeTable()->getSumFooterColumns();
    }

    public function addAvgFooterColumn($columnKey)
    {
        $this->getRuntimeTable()->addAvgFooterColumn($columnKey);
    }

    public function setAvgFooterColumns($avgColumns)
    {
        $this->getRuntimeTable()->setAvgFooterColumns($avgColumns);
    }

    public function getAvgFooterColumns()
    {
        return $this->getRuntimeTable()->getAvgFooterColumns();
    }

    public function addMinFooterColumn($columnKey)
    {
        $this->getRuntimeTable()->addMinFooterColumn($columnKey);
    }

    public function setMinFooterColumns($minColumns)
    {
        $this->getRuntimeTable()->setMinFooterColumns($minColumns);
    }

    public function getMinFooterColumns()
    {
        return $this->getRuntimeTable()->getMinFooterColumns();
    }

    public function addMaxFooterColumn($columnKey)
    {
        $this->getRuntimeTable()->addMaxFooterColumn($columnKey);
    }

    public function setMaxFooterColumns($maxColumns)
    {
        $this->getRuntimeTable()->setMaxFooterColumns($maxColumns);
    }

    public function getMaxFooterColumns()
    {
        return $this->getRuntimeTable()->getMaxFooterColumns();
    }

    public function addColumnsDecimalPlaces($columnKey, $decimalPlaces)
    {
        $this->getRuntimeTable()->addColumnsDecimalPlaces($columnKey, $decimalPlaces);
    }

    public function addColumnsThousandsSeparator($columnKey, $thousandsSeparator)
    {
        $this->getRuntimeTable()->addColumnsThousandsSeparator($columnKey, $thousandsSeparator);
    }

    public function getColumnsCSS()
    {
        return $this->_columnsCSS;
    }

    public function setColumnsCss($css)
    {
        $this->_columnsCSS = $css;
    }

    public function reorderColumns($posArray)
    {
        if (!is_array($posArray)) {
            throw new WDTException('Invalid position data provided!');
        }
        $resultArray = array();
        $resultByKeys = array();

        foreach ($posArray as $pos => $dataColumnIndex) {
            $resultArray[$pos] = $this->_wdtNamedColumns[$dataColumnIndex];
            $resultByKeys[$dataColumnIndex] = $this->_wdtNamedColumns[$dataColumnIndex];
        }
        $this->_wdtIndexedColumns = $resultArray;
        $this->_wdtNamedColumns = $resultByKeys;
    }

    public function getWpId()
    {
        return $this->getRuntimeTable()->getWpId();
    }

    public function setWpId($wpId)
    {
        $this->getRuntimeTable()->setWpId($wpId);
    }

    public function getCssClassesArr()
    {
        $classesStr = $this->_cssClassArray;
        $classesStr = apply_filters('wpdatatables_filter_table_cssClassArray', $classesStr, $this->getWpId());
        return implode(' ', $classesStr);
    }

    public function getCSSClasses()
    {
        return implode(' ', $this->_cssClassArray);
    }

    public function addCSSClass($cssClass)
    {
        $this->_cssClassArray[] = $cssClass;
    }

    public function getCSSStyle()
    {
        return $this->getRuntimeTable()->getCSSStyle();
    }

    public function setCSSStyle($style)
    {
        $this->getRuntimeTable()->setCSSStyle($style);
    }

    public function setTitle($title)
    {
        $this->getRuntimeTable()->setTitle($title);
    }

    public function getName()
    {
        return $this->getRuntimeTable()->getName();
    }

    public function setDescription($description)
    {
        $this->getRuntimeTable()->setDescription($description);
    }

    public function getDescription()
    {
        return $this->getRuntimeTable()->getDescription();
    }

    public function setShowDescription($show_description)
    {
        if ($show_description) {
            $this->getRuntimeTable()->setShowTableDescription(true);
        } else {
            $this->getRuntimeTable()->setShowTableDescription(false);
        }
    }

    public function getShowDescription()
    {
        return $this->getRuntimeTable()->isShowTableDescription();
    }

    public function setScrollable($scrollable)
    {
        if ($scrollable) {
            $this->getRuntimeTable()->setScrollable(true);
        } else {
            $this->getRuntimeTable()->setScrollable(false);
        }
    }

    public function isScrollable()
    {
        return $this->getRuntimeTable()->isScrollable();
    }

    public function setVerticalScroll($verticalScroll)
    {
        if ($verticalScroll) {
            $this->getRuntimeTable()->setVerticalScroll(true);
        } else {
            $this->getRuntimeTable()->setVerticalScroll(false);
        }
    }

    public function isVerticalScroll()
    {
        return $this->getRuntimeTable()->isVerticalScroll();
    }

    /**
     * @throws WDTException
     */
    public function setInterfaceLanguage($lang)
    {

        $lang = apply_filters('wpdatatables_filter_interface_lang', $lang, WDTSettingsController::getArrInterfaceLanguages(), $this->getWpId());

        if (empty($lang)) {
            throw new WDTException('Incorrect language parameter!');
        }
        // Security Fix: Prevent Path Traversal / LFI attacks (CVE-2026-28039)
        // Remove any path traversal attempts and allow only valid filenames
        $lang = basename($lang);

        // Additional security: Only allow .inc.php extension
        if (substr($lang, -8) !== '.inc.php') {
            throw new WDTException('Invalid language file format!');
        }

        // Build the safe path
        $safePath = WDT_LEGACY_LANG_PATH . $lang;

        // Verify the resolved path is still within the lang directory
        $realPath = realpath($safePath);
        $realLangDir = realpath(WDT_LEGACY_LANG_PATH);

        if ($realPath === false || strpos($realPath, $realLangDir) !== 0) {
            throw new WDTException('Language file not found or path traversal detected!');
        }

        if (!file_exists($safePath)) {
            throw new WDTException('Language file not found');
        }

        $this->getRuntimeTable()->setInterfaceLanguage($safePath);
    }

    public function getInterfaceLanguage()
    {
        return $this->getRuntimeTable()->getInterfaceLanguage();
    }

    public function setAutoRefresh($refresh_interval)
    {
        $this->getRuntimeTable()->setAutoRefresh($refresh_interval);
    }

    public function getRefreshInterval()
    {
        return $this->getRuntimeTable()->getRefreshInterval();
    }

    public function paginationEnabled()
    {
        return $this->getRuntimeTable()->isPagination();
    }

    public function enablePagination()
    {
        $this->getRuntimeTable()->setPagination(true);
    }

    public function disablePagination()
    {
        $this->getRuntimeTable()->setPagination(false);
    }

    public function enableTT()
    {
        $this->getRuntimeTable()->setShowTT(true);
    }

    public function disableTT()
    {
        $this->getRuntimeTable()->setShowTT(false);
    }

    public function TTEnabled()
    {
        return $this->getRuntimeTable()->isShowTT();
    }

    public function getTableToolsIncludeHTML()
    {
        return $this->getRuntimeTable()->getTableToolsIncludeHTML();
    }

    public function setTableToolsIncludeHTML($showTableToolsIncludeHTML)
    {
        $this->getRuntimeTable()->setTableToolsIncludeHTML($showTableToolsIncludeHTML);
    }

    public function getTableToolsIncludeTitle()
    {
        return $this->getRuntimeTable()->getTableToolsIncludeTitle();
    }

    public function setTableToolsIncludeTitle($showTableToolsIncludeTitle)
    {
        $this->getRuntimeTable()->setTableToolsIncludeTitle($showTableToolsIncludeTitle);
    }

    /**
     * The raw TableTools (export buttons) configuration array.
     *
     * Accessor so the Rendering layer's
     * {@see \WPDataTables\Rendering\AssetManager} can read the per-format
     * export flags without reaching into the private property.
     *
     * @return array
     */
    public function getTableToolsConfig()
    {
        return $this->getRuntimeTable()->getTableToolsConfig();
    }

    /**
     * Per-column decimal-places map. Getter exposing the private
     * `_columnsDecimalPlaces` for the Rendering layer.
     *
     * @return array
     */
    public function getColumnsDecimalPlaces()
    {
        return $this->getRuntimeTable()->getColumnsDecimalPlaces();
    }

    /**
     * Per-column thousands-separator map. Getter exposing the private
     * `_columnsThousandsSeparator` for the Rendering layer.
     *
     * @return array
     */
    public function getColumnsThousandsSeparator()
    {
        return $this->getRuntimeTable()->getColumnsThousandsSeparator();
    }

    public function hideToolbar()
    {
        $this->_toolbar = false;
    }

    public function setDefaultSortColumn($key)
    {
        if (!isset($this->_wdtIndexedColumns[$key])
            && !isset($this->_wdtNamedColumns[$key])
        ) {
            throw new WDTException('Incorrect column index');
        }

        $key = array_search($key, array_keys($this->_wdtNamedColumns));

        $this->getRuntimeTable()->setDefaultSortColumn($key);
    }

    public function getDefaultSortColumn()
    {
        return $this->getRuntimeTable()->getDefaultSortColumn();
    }

    public function setDefaultSortDirection($direction)
    {
        if (
            !in_array(
                $direction,
                array(
                    'ASC',
                    'DESC'
                )
            )
        ) {
            return false;
        }
        $this->getRuntimeTable()->setDefaultSortDirection($direction);
    }

    public function getDefaultSortDirection()
    {
        return $this->getRuntimeTable()->getDefaultSortDirection();
    }

    public function hideBeforeLoad()
    {
        $this->setCSSStyle('display: none; ');
        $this->getRuntimeTable()->setHideBeforeLoad(true);
    }

    public function showBeforeLoad()
    {
        $this->getRuntimeTable()->setHideBeforeLoad(false);
    }

    public function doHideBeforeLoad()
    {
        return $this->getRuntimeTable()->isHideBeforeLoad();
    }

    public function getDisplayLength()
    {
        return $this->getRuntimeTable()->getDisplayLength();
    }

    public function setDisplayLength($length)
    {
        $this->getRuntimeTable()->setDisplayLength($length);
    }

    public function setIdColumnKey($key)
    {
        $this->getRuntimeTable()->setIdColumnKey($key);
    }

    public function getIdColumnKey()
    {
        return $this->getRuntimeTable()->getIdColumnKey();
    }

    /**
     * @return boolean
     */
    public function isInfoBlock()
    {
        return $this->getRuntimeTable()->isInfoBlock();
    }

    public function setUserColumnKey($key)
    {
        $this->getRuntimeTable()->setUserColumnKey($key);
    }

    public function getUserColumnKey()
    {
        return $this->getRuntimeTable()->getUserColumnKey();
    }

    public function setUserEditColumnKey($key)
    {
        $this->getRuntimeTable()->setUserEditColumnKey($key);
    }

    public function getUserEditColumnKey()
    {
        return $this->getRuntimeTable()->getUserEditColumnKey();
    }

    public function setDatecreatedColumnKey($key)
    {
        $this->getRuntimeTable()->setDatecreatedColumnKey($key);
    }

    public function getDatecreatedColumnKey()
    {
        return $this->getRuntimeTable()->getDatecreatedColumnKey();
    }

    public function setDatecreatedEditColumnKey($key)
    {
        $this->getRuntimeTable()->setDatecreatedEditColumnKey($key);
    }

    public function getDatecreatedEditColumnKey()
    {
        return $this->getRuntimeTable()->getDatecreatedEditColumnKey();
    }

    /**
     * @param boolean $infoBlock
     */
    public function setInfoBlock($infoBlock)
    {
        $this->getRuntimeTable()->setInfoBlock($infoBlock);
    }

    /**
     * @param boolean $paginationOnTop
     */
    public function setPaginationOnTop($paginationOnTop)
    {
        $this->getRuntimeTable()->setPaginationOnTop($paginationOnTop);
    }

    public function getPaginationOnTop()
    {
        return $this->getRuntimeTable()->getPaginationOnTop();
    }

    /**
     * @return bool
     */
    public function isPagination()
    {
        return $this->getRuntimeTable()->isPagination();
    }

    /**
     * @param bool $pagination
     */
    public function setPagination($pagination)
    {
        $this->getRuntimeTable()->setPagination($pagination);
    }

    /**
     * @return string
     */
    public function getPaginationAlign()
    {
        return $this->getRuntimeTable()->getPaginationAlign();
    }

    /**
     * @param string $paginationAlign
     */
    public function setPaginationAlign($paginationAlign)
    {
        $this->getRuntimeTable()->setPaginationAlign($paginationAlign);
        if (wp_is_mobile()) {
            $this->getRuntimeTable()->setPaginationAlign('center');
        }
    }

    /**
     * @return string
     */
    public function getPaginationLayout()
    {
        return $this->getRuntimeTable()->getPaginationLayout();
    }

    /**
     * @param string $paginationLayout
     */
    public function setPaginationLayout($paginationLayout)
    {
        $this->getRuntimeTable()->setPaginationLayout($paginationLayout);
    }

    /**
     * @return string
     */
    public function getPaginationLayoutMobile()
    {
        return $this->getRuntimeTable()->getPaginationLayoutMobile();
    }

    /**
     * @param string $paginationLayout
     */
    public function setPaginationLayoutMobile($paginationLayout)
    {
        $this->getRuntimeTable()->setPaginationLayoutMobile($paginationLayout);
    }

    /**
     * @return boolean
     */
    public function isSimpleResponsive()
    {
        return $this->getRuntimeTable()->isSimpleResponsive();
    }

    /**
     * @param boolean $simpleResponsive
     */
    public function setSimpleResponsive($simpleResponsive)
    {
        $this->getRuntimeTable()->setSimpleResponsive($simpleResponsive);
    }

    /**
     * @return boolean
     */
    public function isSimpleHeader()
    {
        return $this->getRuntimeTable()->isSimpleHeader();
    }

    /**
     * @param boolean $simpleHeader
     */
    public function setSimpleHeader($simpleHeader)
    {
        $this->getRuntimeTable()->setSimpleHeader($simpleHeader);
    }

    /**
     * @return boolean
     */
    public function isStripeTable()
    {
        return $this->getRuntimeTable()->isStripeTable();
    }

    /**
     * @param boolean $stripeTable
     */
    public function setStripeTable($stripeTable)
    {
        $this->getRuntimeTable()->setStripeTable($stripeTable);
    }

    /**
     * @return boolean
     */
    public function getCellPadding()
    {
        return $this->getRuntimeTable()->getCellPadding();
    }

    /**
     * @param boolean $cellPadding
     */
    public function setCellPadding($cellPadding)
    {
        $this->getRuntimeTable()->setCellPadding($cellPadding);
    }

    /**
     * @return boolean
     */
    public function isRemoveBorders()
    {
        return $this->getRuntimeTable()->isRemoveBorders();
    }

    /**
     * @param boolean $removeBorders
     */
    public function setRemoveBorders($removeBorders)
    {
        $this->getRuntimeTable()->setRemoveBorders($removeBorders);
    }

    /**
     * @return string
     */
    public function getBorderCollapse()
    {
        return $this->getRuntimeTable()->getBorderCollapse();
    }

    /**
     * @param string $borderCollapse
     */
    public function setBorderCollapse($borderCollapse)
    {
        $this->getRuntimeTable()->setBorderCollapse($borderCollapse);
    }

    /**
     * @return int
     */
    public function getBorderSpacing()
    {
        return $this->getRuntimeTable()->getBorderSpacing();
    }

    /**
     * @param int $borderSpacing
     */
    public function setBorderSpacing($borderSpacing)
    {
        $this->getRuntimeTable()->setBorderSpacing($borderSpacing);
    }

    /**
     * @return boolean
     */
    public function getVerticalScrollHeight()
    {
        return $this->getRuntimeTable()->getVerticalScrollHeight();
    }

    /**
     * @param boolean $verticalScrollHeight
     */
    public function setVerticalScrollHeight($verticalScrollHeight)
    {
        $this->getRuntimeTable()->setVerticalScrollHeight($verticalScrollHeight);
    }

    /**
     * @return boolean
     */
    public function isGlobalSearch()
    {
        return $this->getRuntimeTable()->isGlobalSearch();
    }

    /**
     * @param boolean $globalSearch
     */
    public function setGlobalSearch($globalSearch)
    {
        $this->getRuntimeTable()->setGlobalSearch((bool)$globalSearch);
    }

    /**
     * @return boolean
     */
    public function isShowRowsPerPage()
    {
        return $this->getRuntimeTable()->isShowRowsPerPage();
    }

    /**
     * @param boolean $showRowsPerPage
     */
    public function setShowRowsPerPage($showRowsPerPage)
    {
        $this->getRuntimeTable()->setShowRowsPerPage((bool)$showRowsPerPage);
    }

    /**
     * @return array
     */
    public function getEditButtonsDisplayed()
    {
        return $this->getRuntimeTable()->getEditButtonsDisplayed();
    }

    /**
     * @param array $editButtonsDisplayed
     */
    public function setEditButtonsDisplayed(array $editButtonsDisplayed)
    {
        $this->getRuntimeTable()->setEditButtonsDisplayed($editButtonsDisplayed);
    }

    public function isEnableDuplicateButton()
    {
        return $this->getRuntimeTable()->isEnableDuplicateButton();
    }

    public function setEnableDuplicateButton($enableDuplicateButton)
    {
        $this->getRuntimeTable()->setEnableDuplicateButton($enableDuplicateButton);
    }

    /**
     * @return string
     */
    public function getTableSkin()
    {
        return $this->getRuntimeTable()->getTableSkin();
    }

    /**
     * @param string $tableSkin
     */
    public function setTableSkin($tableSkin)
    {
        $this->getRuntimeTable()->setTableSkin($tableSkin);
    }

    /**
     * @return mixed
     */
    public function getTableFontColorSettings()
    {
        return $this->getRuntimeTable()->getTableFontColorSettings();
    }

    /**
     * @param mixed $tableFontColorSettings
     */
    public function setTableFontColorSettings($tableFontColorSettings)
    {
        $this->getRuntimeTable()->setTableFontColorSettings($tableFontColorSettings);
    }

    /**
     * @return int
     */
    public function getTableBorderRemoval()
    {
        return $this->getRuntimeTable()->getTableBorderRemoval();
    }

    /**
     * @param int $tableBorderRemoval
     */
    public function setTableBorderRemoval($tableBorderRemoval)
    {
        $this->getRuntimeTable()->setTableBorderRemoval($tableBorderRemoval);
    }

    /**
     * @return int
     */
    public function getTableBorderRemovalHeader()
    {
        return $this->getRuntimeTable()->getTableBorderRemovalHeader();
    }

    /**
     * @param int $tableBorderRemovalHeader
     */
    public function setTableBorderRemovalHeader($tableBorderRemovalHeader)
    {
        $this->getRuntimeTable()->setTableBorderRemovalHeader($tableBorderRemovalHeader);
    }


    /**
     * @return string
     */
    public function getTableCustomCss()
    {
        return $this->getRuntimeTable()->getTableCustomCss();
    }


    /**
     * @return string
     */
    public function getPdfPaperSize()
    {
        return $this->getRuntimeTable()->getPdfPaperSize();
    }

    /**
     * @param string $pdfPaperSize
     */
    public function setPdfPaperSize($pdfPaperSize)
    {
        $this->getRuntimeTable()->setPdfPaperSize($pdfPaperSize);
    }

    /**
     * @return string
     */
    public function getPdfPageOrientation()
    {
        return $this->getRuntimeTable()->getPdfPageOrientation();
    }

    /**
     * @param string $pdfPageOrientation
     */
    public function setPdfPageOrientation($pdfPageOrientation)
    {
        $this->getRuntimeTable()->setPdfPageOrientation($pdfPageOrientation);
    }


    /**
     * @param string $tableCustomCss
     */
    public function setTableCustomCss($tableCustomCss)
    {
        $this->getRuntimeTable()->setTableCustomCss($tableCustomCss);
    }

    public function getDBConnection()
    {
        return $this->_db;
    }

    public function isTableWCAG()
    {
        return $this->getRuntimeTable()->isTableWCAG();
    }

    public function setTableWCAG($tableWCAG)
    {
        $this->getRuntimeTable()->setTableWCAG($tableWCAG);
    }

    public function isAdvancedFilterOption()
    {
        return $this->getRuntimeTable()->isAdvancedFilterOption();
    }

    public function setAdvancedFilterOption($advancedFilterOption)
    {
        $this->getRuntimeTable()->setAdvancedFilterOption($advancedFilterOption);
    }

    public function isLoaderVisible()
    {
        return $this->getRuntimeTable()->isLoaderVisible();
    }

    public function setLoader($loader)
    {
        $this->getRuntimeTable()->setLoader($loader);
    }

    public function getSimpleTemplateId()
    {
        return $this->getRuntimeTable()->getSimpleTemplateId();
    }

    public function setSimpleTemplateId($simple_template_id)
    {
        $this->getRuntimeTable()->setSimpleTemplateId($simple_template_id);
    }

    public function getShowCartInformation(): int
    {
        return $this->getRuntimeTable()->getShowCartInformation();
    }

    public function setShowCartInformation(int $showCartInformation): void
    {
        $this->getRuntimeTable()->setShowCartInformation($showCartInformation);
    }

    public function __construct($connection = null)
    {
        //[<-- Full version -->]//
        // connect to MySQL if enabled
        if (WDT_ENABLE_MYSQL && (Connection::isSeparate($connection))) {
            $this->_db = Connection::getInstance($connection);
            $this->connection = $connection;
        }
        //[<--/ Full version -->]//
        if (self::$wdt_internal_idcount == 0) {
            $this->_firstOnPage = true;
        }
        self::$wdt_internal_idcount++;
        $this->_id = 'table_' . self::$wdt_internal_idcount;
    }

    public function wdtDefineColumnsWidth($widthsArray)
    {
        if (empty($this->_wdtIndexedColumns)) {
            throw new WDTException('wpDataTable reports no columns are defined!');
        }
        if (!is_array($widthsArray)) {
            throw new WDTException('Incorrect parameter passed!');
        }
        if (WDTTools::isArrayAssoc($widthsArray)) {
            foreach ($widthsArray as $name => $value) {
                if (!isset($this->_wdtNamedColumns[$name])) {
                    continue;
                }
                $this->_wdtNamedColumns[$name]->setWidth($this->isFixedLayout() ? $value : '');
            }
        } else {
            // if width is provided in indexed array
            foreach ($widthsArray as $name => $value) {
                $this->_wdtIndexedColumns[$name]->setWidth($this->isFixedLayout() ? $value : '');
            }
        }
    }

    public function setColumnsPossibleValues($valuesArray)
    {
        if (empty($this->_wdtIndexedColumns)) {
            throw new WDTException('No columns in the table!');
        }
        if (!is_array($valuesArray)) {
            throw new WDTException('Valid array of width values is required!');
        }
        if (WDTTools::isArrayAssoc($valuesArray)) {
            foreach ($valuesArray as $key => $value) {
                if (!isset($this->_wdtNamedColumns[$key])) {
                    continue;
                }
                $possibleValues = $this->_wdtNamedColumns[$key]->getPossibleValuesList();
                if (empty($possibleValues)) {
                    $this->_wdtNamedColumns[$key]->setPossibleValues($value);
                }
            }
        } else {
            foreach ($valuesArray as $key => $value) {
                $this->_wdtIndexedColumns[$key]->setPossibleValues($value);
            }
        }
    }

    public function getHiddenColumnCount()
    {
        $count = 0;
        foreach ($this->_wdtIndexedColumns as $dataColumn) {
            if (!$dataColumn->isVisible()) {
                $count++;
            }
        }
        return $count;
    }

    //[<-- Full version -->]//
    public function enableServerProcessing()
    {
        $this->getRuntimeTable()->setServerProcessing(true);
    }

    public function disableServerProcessing()
    {
        $this->getRuntimeTable()->setServerProcessing(false);
    }

    public function serverSide()
    {
        return $this->getRuntimeTable()->isServerProcessing();
    }

    public function setResponsive($responsive)
    {
        if ($responsive) {
            $this->getRuntimeTable()->setResponsive(true);
        } else {
            $this->getRuntimeTable()->setResponsive(false);
        }
    }

    public function isResponsive()
    {
        return $this->getRuntimeTable()->isResponsive();
    }

    /**
     * @return string
     */
    public function getResponsiveAction()
    {
        return $this->getRuntimeTable()->getResponsiveAction();
    }

    /**
     * @param string $responsiveAction
     */
    public function setResponsiveAction($responsiveAction)
    {
        $this->getRuntimeTable()->setResponsiveAction($responsiveAction);
    }

    public function enableEditing()
    {
        $this->getRuntimeTable()->setEditable(true);
    }

    public function disableEditing()
    {
        $this->getRuntimeTable()->setEditable(false);
    }

    public function isEditable()
    {
        return $this->getRuntimeTable()->isEditable();
    }

    public function enablePopoverTools()
    {
        $this->getRuntimeTable()->setPopoverTools(true);
    }

    public function disablePopoverTools()
    {
        $this->getRuntimeTable()->setPopoverTools(false);
    }

    public function popoverToolsEnabled()
    {
        return $this->getRuntimeTable()->isPopoverTools();
    }

    public function enableInlineEditing()
    {
        $this->getRuntimeTable()->setInlineEditing(true);
    }

    public function disableInlineEditing()
    {
        $this->getRuntimeTable()->setInlineEditing(false);
    }

    public function inlineEditingEnabled()
    {
        return $this->getRuntimeTable()->isInlineEditing();
    }

    public function filterEnabled()
    {
        return $this->getRuntimeTable()->isShowFilter();
    }

    public function enableFilter()
    {
        $this->getRuntimeTable()->setShowFilter(true);
    }

    public function disableFilter()
    {
        $this->getRuntimeTable()->setShowFilter(false);
    }

    public function setFilteringForm($filteringForm)
    {
        $this->getRuntimeTable()->setFilteringForm((bool)$filteringForm);
    }

    public function getFilteringForm()
    {
        return $this->getRuntimeTable()->getFilteringForm();
    }

    public function setCacheSourceData($cacheSourceData)
    {
        $this->getRuntimeTable()->setCacheSourceData((bool)$cacheSourceData);
    }

    public function getCacheSourceData()
    {
        return $this->getRuntimeTable()->getCacheSourceData();
    }

    public function setAutoUpdateCache($autoUpdateCache)
    {
        $this->getRuntimeTable()->setAutoUpdateCache((bool)$autoUpdateCache);
    }

    public function getAutoUpdateCache()
    {
        return $this->getRuntimeTable()->getAutoUpdateCache();
    }

    public function advancedFilterEnabled()
    {
        return $this->getRuntimeTable()->isShowAdvancedFilter();
    }

    public function enableAdvancedFilter()
    {
        $this->getRuntimeTable()->setShowAdvancedFilter(true);
    }

    public function disableAdvancedFilter()
    {
        $this->getRuntimeTable()->setShowAdvancedFilter(false);
    }

    //[<--/ Full version -->]//

    public function enableGrouping()
    {
        $this->getRuntimeTable()->setGroupingEnabled(true);
    }

    public function disableGrouping()
    {
        $this->getRuntimeTable()->setGroupingEnabled(false);
    }

    public function groupingEnabled()
    {
        return $this->getRuntimeTable()->isGroupingEnabled();
    }

    public function groupByColumn($key)
    {
        if (!isset($this->_wdtIndexedColumns[$key])
            && !isset($this->_wdtNamedColumns[$key])
        ) {
            throw new WDTException('Column not found!');
        }

        if (!is_numeric($key)) {
            $key = array_search(
                $key,
                array_keys($this->_wdtNamedColumns)
            );
        }

        $this->enableGrouping();
        $this->getRuntimeTable()->setColumnGroupIndex($key);
    }

    /**
     * Returns the index of grouping column
     */
    public function groupingColumnIndex()
    {
        return $this->getRuntimeTable()->getColumnGroupIndex();
    }

    /**
     * Returns the grouping column index
     */
    public function groupingColumn()
    {
        return $this->getRuntimeTable()->getColumnGroupIndex();
    }

    public function countColumns()
    {
        return count($this->_wdtIndexedColumns);
    }

    public function getNamedColumns()
    {
        return $this->_wdtNamedColumns;
    }

    public function getColumnKeys()
    {
        return array_keys($this->_wdtNamedColumns);
    }

    public function setOnlyOwnRows($ownRows)
    {
        $this->getRuntimeTable()->setOnlyOwnRows((bool)$ownRows);
    }

    public function getOnlyOwnRows()
    {
        return $this->getRuntimeTable()->getOnlyOwnRows();
    }

    public function setUserIdColumn($column)
    {
        $this->getRuntimeTable()->setUserIdColumn($column);
    }

    public function getUserIdColumn()
    {
        return $this->getRuntimeTable()->getUserIdColumn();
    }

    /**
     * @return bool
     */
    public function isShowAllRows()
    {
        return $this->getRuntimeTable()->isShowAllRows();
    }

    /**
     * @param bool $showAllRows
     */
    public function setShowAllRows($showAllRows)
    {
        $this->getRuntimeTable()->setShowAllRows($showAllRows);
    }

    public function getColumns()
    {
        return $this->_wdtIndexedColumns;
    }

    public function getColumnsByHeaders()
    {
        return $this->_wdtNamedColumns;
    }

    public function addConditionalFormattingColumn($column)
    {
        $this->getRuntimeTable()->addConditionalFormattingColumn($column);
    }

    public function addTransformValueColumn($column)
    {
        $this->getRuntimeTable()->addTransformValueColumn($column);
    }

    public function getTransformValueColumn()
    {
        return $this->getRuntimeTable()->getTransformValueColumn();
    }

    public function getConditionalFormattingColumns()
    {
        return $this->getRuntimeTable()->getConditionalFormattingColumns();
    }

    public function createColumnsFromArr($headerArr, $wdtParameters, $wdtColumnTypes)
    {
        foreach ($headerArr as $key) {
            $dataColumnProperties = array();
            $dataColumnProperties['title'] = isset($wdtParameters['columnTitles'][$key]) ? $wdtParameters['columnTitles'][$key] : $key;
            $dataColumnProperties['width'] = !empty($wdtParameters['columnWidths'][$key]) ? $wdtParameters['columnWidths'][$key] : '';
            $dataColumnProperties['sorting'] = isset($wdtParameters['sorting'][$key]) ? $wdtParameters['sorting'][$key] : true;
            $dataColumnProperties['decimalPlaces'] = isset($wdtParameters['decimalPlaces'][$key]) ? $wdtParameters['decimalPlaces'][$key] : get_option('wdtDecimalPlaces');
            $dataColumnProperties['orig_header'] = $key;
            $dataColumnProperties['exactFiltering'] = !empty($wdtParameters['exactFiltering'][$key]) ? $wdtParameters['exactFiltering'][$key] : false;
            $dataColumnProperties['filterLabel'] = isset($wdtParameters['filterLabel'][$key]) ? $wdtParameters['filterLabel'][$key] : null;
            $dataColumnProperties['searchInSelectBox'] = !empty($wdtParameters['searchInSelectBox'][$key]) ? $wdtParameters['searchInSelectBox'][$key] : false;
            $dataColumnProperties['searchInSelectBoxEditing'] = !empty($wdtParameters['searchInSelectBoxEditing'][$key]) ? $wdtParameters['searchInSelectBoxEditing'][$key] : false;
            $dataColumnProperties['checkboxesInModal'] = isset($wdtParameters['checkboxesInModal'][$key]) ? $wdtParameters['checkboxesInModal'][$key] : null;
            $dataColumnProperties['andLogic'] = isset($wdtParameters['andLogic'][$key]) ? $wdtParameters['andLogic'][$key] : null;
            $dataColumnProperties['filterDefaultValue'] = isset($wdtParameters['filterDefaultValue'][$key]) ? $wdtParameters['filterDefaultValue'][$key] : null;
            $dataColumnProperties['possibleValuesType'] = !empty($wdtParameters['possibleValuesType'][$key]) ? $wdtParameters['possibleValuesType'][$key] : 'read';
            $dataColumnProperties['possibleValuesAddEmpty'] = !empty($wdtParameters['possibleValuesAddEmpty'][$key]) ? $wdtParameters['possibleValuesAddEmpty'][$key] : false;
            $dataColumnProperties['possibleValuesAjax'] = !empty($wdtParameters['possibleValuesAjax'][$key]) ? $wdtParameters['possibleValuesAjax'][$key] : 10;
            $dataColumnProperties['foreignKeyRule'] = isset($wdtParameters['foreignKeyRule'][$key]) ? $wdtParameters['foreignKeyRule'][$key] : '';
            $dataColumnProperties['editingDefaultValue'] = isset($wdtParameters['editingDefaultValue'][$key]) ? $wdtParameters['editingDefaultValue'][$key] : '';
            $dataColumnProperties['linkTargetAttribute'] = isset($wdtParameters['linkTargetAttribute'][$key]) ? $wdtParameters['linkTargetAttribute'][$key] : '';
            $dataColumnProperties['linkNoFollowAttribute'] = isset($wdtParameters['linkNoFollowAttribute'][$key]) ? $wdtParameters['linkNoFollowAttribute'][$key] : false;
            $dataColumnProperties['linkNoreferrerAttribute'] = isset($wdtParameters['linkNoreferrerAttribute'][$key]) ? $wdtParameters['linkNoreferrerAttribute'][$key] : false;
            $dataColumnProperties['linkSponsoredAttribute'] = isset($wdtParameters['linkSponsoredAttribute'][$key]) ? $wdtParameters['linkSponsoredAttribute'][$key] : false;
            $dataColumnProperties['linkButtonAttribute'] = isset($wdtParameters['linkButtonAttribute'][$key]) ? $wdtParameters['linkButtonAttribute'][$key] : false;
            $dataColumnProperties['linkButtonLabel'] = isset($wdtParameters['linkButtonLabel'][$key]) ? $wdtParameters['linkButtonLabel'][$key] : '';
            $dataColumnProperties['linkButtonClass'] = isset($wdtParameters['linkButtonClass'][$key]) ? $wdtParameters['linkButtonClass'][$key] : '';
            $dataColumnProperties['rangeSlider'] = !empty($wdtParameters['rangeSlider'][$key]) ? $wdtParameters['rangeSlider'][$key] : false;
            $dataColumnProperties['rangeMaxValueDisplay'] = isset($wdtParameters['rangeMaxValueDisplay'][$key]) ? $wdtParameters['rangeMaxValueDisplay'][$key] : 'default';
            $dataColumnProperties['customMaxRangeValue'] = isset($wdtParameters['customMaxRangeValue'][$key]) ? $wdtParameters['customMaxRangeValue'][$key] : null;
            $dataColumnProperties['parentTable'] = $this;
            $dataColumnProperties['globalSearchColumn'] = isset($wdtParameters['globalSearchColumn'][$key]) ? $wdtParameters['globalSearchColumn'][$key] : false;
            $dataColumnProperties = apply_filters_deprecated(
                'wpdt_filter_data_column_properties',
                array($dataColumnProperties, $wdtParameters, $key),
                WDT_INITIAL_STARTER_VERSION,
                'wpdatatables_filter_data_column_properties'
            );
            $dataColumnProperties = apply_filters('wpdatatables_filter_data_column_properties', $dataColumnProperties, $wdtParameters, $key);
            /** @var WDTColumn $tableColumnClass */
            $tableColumnClass = static::$_columnClass;
            $dataColumn = null;

            if (isset($wdtColumnTypes[$key])) {
                /** @var WDTColumn $dataColumn */
                $dataColumn = $tableColumnClass::generateColumn($wdtColumnTypes[$key], $dataColumnProperties);
                $dataColumn = apply_filters('wpdatatables_extend_datacolumn_object', $dataColumn, $dataColumnProperties);
                if ($wdtColumnTypes[$key] === 'formula') {
                    /** @var FormulaWDTColumn $dataColumn */
                    if (!empty($wdtParameters['columnFormulas'][$key])) {
                        $dataColumn->setFormula($wdtParameters['columnFormulas'][$key]);
                        if ($this->serverSide()) {
                            $dataColumn->setSorting(false);
                            $dataColumn->setSearchable(false);
                        }
                    } else {
                        $dataColumn->setFormula('');
                    }
                } elseif ($wdtColumnTypes[$key] === 'select' || $wdtColumnTypes[$key] === 'cart') {
                    $dataColumn->setSorting(false);
                    $dataColumn->setSearchable(false);
                }
                if ($wdtColumnTypes[$key] === 'index') {
                    $dataColumn->setSorting(false);
                    $dataColumn->setSearchable(false);
                }

                do_action('wpdatatables_columns_from_arr', $this, $dataColumn, $wdtColumnTypes, $key);

            }

            if ($dataColumn != null && $dataColumn->getPossibleValuesType() == 'foreignkey' && $dataColumn->getForeignKeyRule() != null) {
                $foreignKeyData = $this->joinWithForeignWpDataTable($dataColumn->getOriginalHeader(), $dataColumn->getForeignKeyRule(), $this->getDataRows());
                $this->_dataRows = $foreignKeyData['dataRows'];
                $dataColumn->setPossibleValues($foreignKeyData['distinctValues']);
            }

            $this->_wdtIndexedColumns[] = $dataColumn;
            $this->_wdtNamedColumns[$key] = &$this->_wdtIndexedColumns[count($this->_wdtIndexedColumns) - 1];
        }

    }

    public function getColumnHeaderOffset($key)
    {
        $keys = $this->getColumnKeys();
        if (!empty($key) && in_array($key, $keys)) {
            return array_search($key, $keys);
        } else {
            return -1;
        }
    }

    public function getColumnDefinitions()
    {
        return self::columnDefinitionBuilder()->buildColumnDefinitions($this);
    }

    /**
     * Get column filter definitions
     *
     * @return string
     */
    public function getColumnFilterDefinitions()
    {
        return self::columnDefinitionBuilder()->buildColumnFilterDefinitions($this);
    }

    /**
     * Get column editing definitions
     *
     * @return string
     */
    public function getColumnEditingDefinitions()
    {
        return self::columnDefinitionBuilder()->buildColumnEditingDefinitions($this);
    }

    /**
     * Get WDTColumn by column original header
     *
     * @param $originalHeader
     *
     * @return bool|mixed
     */
    public function getColumn($originalHeader)
    {
        if (!isset($originalHeader)
            || (!isset($this->_wdtNamedColumns[$originalHeader])
                && !isset($this->_wdtIndexedColumns[$originalHeader]))
        ) {
            return false;
        }
        if (!is_int($originalHeader)) {
            return $this->_wdtNamedColumns[$originalHeader];
        }

        return $this->_wdtIndexedColumns[$originalHeader];
    }

    /**
     * Indexed column instances in display order (used by the Rendering layer).
     *
     * @return array<int, WDTColumn>
     */
    public function getIndexedColumns()
    {
        return $this->_wdtIndexedColumns;
    }

    /**
     * Generates the structure in memory needed to render the tables
     *
     * @param array $rawDataArr Array of data for the table content
     * @param array $wdtParameters Array of rendering parameters
     *
     * @return bool Result of generation
     */
    public function arrayBasedConstruct($rawDataArr, $wdtParameters)
    {
        return self::tableService()->buildFromArray($this, $rawDataArr, $wdtParameters);
    }

    //[<-- Full version -->]//

    /**
     * Helper function that helps to calculate the formula-based cells
     */
    public function calculateFormulaCells()
    {

        foreach (array_keys($this->getRuntimeTable()->getWdtColumnTypes(), 'formula') as $column_key) {
            $headers = array();

            $formula = $this->getColumn($column_key)->getFormula();
            $headersInFormula = $this->detectHeadersInFormula($formula);
            $headers = WDTTools::sanitizeHeaders($headersInFormula);
            foreach ($this->_dataRows as &$row) {
                try {
                    $row[$column_key] =
                        self::solveFormula(
                            $formula,
                            $headers,
                            $row
                        );
                } catch (Exception $e) {
                    $row[$column_key] = 0;
                }
            }
        }
    }

    //[<--/ Full version -->]//

    public function hideColumn($dataColumnIndex)
    {
        if (!isset($dataColumnIndex)
            || !isset($this->_wdtNamedColumns[$dataColumnIndex])
        ) {
            throw new WDTException('A column with provided header does not exist.');
        }
        $this->_wdtNamedColumns[$dataColumnIndex]->setIsVisible(false);
    }

    public function showColumn($dataColumnIndex)
    {
        if (!isset($dataColumnIndex)
            || !isset($this->_wdtNamedColumns[$dataColumnIndex])
        ) {
            throw new WDTException('A column with provided header does not exist.');
        }
        $this->_wdtNamedColumns[$dataColumnIndex]->setIsVisible(true);
    }


    public function getCell($dataColumnIndex, $rowKey)
    {
        if (!isset($dataColumnIndex)
            || !isset($rowKey)
        ) {
            throw new WDTException('Please provide the column key and the row key');
        }
        if (!isset($this->_dataRows[$rowKey])) {
            throw new WDTException('Row does not exist.');
        }
        if (!isset($this->_wdtNamedColumns[$dataColumnIndex])
            && !isset($this->_wdtIndexedColumns[$dataColumnIndex])
        ) {
            throw new WDTException('Column does not exist.');
        }
        return $this->_dataRows[$rowKey][$dataColumnIndex];
    }

    public function returnCellValue($cellContent, $wdtColumnIndex)
    {
        if (!isset($wdtColumnIndex)) {
            throw new WDTException('Column index not provided!');
        }
        if (!isset($this->_wdtNamedColumns[$wdtColumnIndex])) {
            throw new WDTException('Column index out of bounds!');
        }
        return $this->_wdtNamedColumns[$wdtColumnIndex]->returnCellValue($cellContent);
    }

    public function getDataRows()
    {
        return $this->_dataRows;
    }

    public function setDataRows($dataRows)
    {
        return $this->_dataRows = $dataRows;
    }

    public function getDataRowsFormatted()
    {
        $dataRowsFormatted = array();
        foreach ($this->_dataRows as $dataRow) {
            $formattedRow = array();
            foreach ($dataRow as $colHeader => $cellValue) {
                $formattedRow[$colHeader] = $this->returnCellValue($cellValue, $colHeader);
            }
            $dataRowsFormatted[] = $formattedRow;
        }
        return $dataRowsFormatted;
    }

    /**
     * Helper method to calculate value for the specified column and function
     *
     * @param $columnKey
     * @param $function
     *
     * @return float|int
     */
    public function calcColumnFunction($columnKey, $function)
    {
        $result = null;
        if ($function == 'sum' || $function == 'avg') {
            foreach ($this->getDataRows() as $wdtRowDataArr) {
                if ($wdtRowDataArr[$columnKey] != null && is_numeric($wdtRowDataArr[$columnKey])) {
                    $result += $wdtRowDataArr[$columnKey];
                }
            }

            if ($function == 'avg') {
                $result = $result / count($this->getDataRows());

                $floatCol = WDTColumn::generateColumn('float', array('parentTable' => $this));

                return $floatCol->prepareCellOutput($result);
            }

        } else if ($function == 'min') {
            foreach ($this->getDataRows() as $wdtRowDataArr) {
                if (!isset($result) || $wdtRowDataArr[$columnKey] < $result && is_numeric($wdtRowDataArr[$columnKey])) {
                    $result = $wdtRowDataArr[$columnKey];
                }
            }
        } else if ($function == 'max') {
            foreach ($this->getDataRows() as $wdtRowDataArr) {
                if (!isset($result) || $wdtRowDataArr[$columnKey] > $result && is_numeric($wdtRowDataArr[$columnKey])) {
                    $result = $wdtRowDataArr[$columnKey];
                }
            }
        }

        return $this->returnCellValue($result, $columnKey);

    }

    /**
     * Helper method to generate values for SUM, MIN, MAX, AVG
     */
    private function calcColumnsAggregateFuncs()
    {
        // The client-side aggregate computation lives in SummaryService; this
        // passes in and stores back its results cache.
        $this->_aggregateFuncsRes = self::summaryService()
            ->calcColumnsAggregateFuncs($this, $this->_aggregateFuncsRes);
    }

    /**
     * Return LIKE expression for given vendor
     *
     * @param string $vendor
     * @param string $filterType
     * @param string $value
     *
     * @return string
     */
    public function getDateTimeExpression($vendor, $filterType, $value)
    {
        // Vendor date/time expression is built by FilterService.
        return self::filterService()->getDateTimeExpression($vendor, $filterType, $value, $this->connection);
    }

    /**
     * Return aggregate function results
     *
     * @param $columnKey
     * @param $function
     *
     * @return mixed
     */
    public function getColumnsAggregateFuncsResult($columnKey, $function)
    {
        if (!isset($this->_aggregateFuncsRes[$function][$columnKey])) {
            $this->calcColumnsAggregateFuncs();
        }
        return $this->_aggregateFuncsRes[$function][$columnKey];
    }

    //[<-- Full version -->]//
    public function queryBasedConstruct($query, $queryParams = array(), $wdtParameters = array(), $init_read = false)
    {
        // Routed through the TableService orchestrator, which delegates to the
        // ServerSideProcessor → MySqlQueryDataSource.
        return self::tableService()
            ->constructFromQuery($this, $query, $queryParams, $wdtParameters, $init_read);
    }

    /**
     * Create the supplementary array of column objects
     * which we will use for formatting
     *
     * @param $wdtParameters
     *
     * @return array
     */
    public function prepareColumns($wdtParameters)
    {
        return self::tableService()->prepareColumns($this, $wdtParameters);
    }

    /**
     * Reformat output array and reorder as user wanted
     *
     * @param $main_res_dataRows
     * @param $wdtParameters
     * @param $colObjs
     *
     * @return mixed
     * @throws WDTException
     */
    public function prepareOutputData($main_res_dataRows, $wdtParameters, $colObjs)
    {
        return self::tableService()->prepareOutputData($this, $main_res_dataRows, $wdtParameters, $colObjs);
    }


    /**
     * Formatting row data structure for ajax display table
     *
     * @param $row - key => value pairs as column name and cell value of a row
     *
     * @return array
     */
    public function formatAjaxQueryResultRow($row)
    {
        return array_values($row);
    }

    public function customBasedConstruct($tableData, $wdtParameters = array())
    {
        if (has_action('wpdatatables_generate_' . $tableData->table_type)) {
            //Check if Server-side processing is integrated in Add-on
            if (isset($tableData->advanced_settings) && isset(json_decode($tableData->advanced_settings, true)[$tableData->table_type]['hasServerSideIntegration'])) {
                if (!empty($tableData->server_side)) {
                    $this->enableServerProcessing();
                    if (!empty($tableData->auto_refresh)) {
                        $this->setAutoRefresh((int)$tableData->auto_refresh);
                    }
                }
                if (!empty($tableData->editable)) {
                    $editor_roles = isset($tableData->editor_roles) ? $tableData->editor_roles : '';
                    if (wdtCurrentUserCanEdit($editor_roles, $this->getWpId())) {
                        $this->enableEditing();
                        if (!empty($tableData->popover_tools)) {
                            $this->enablePopoverTools();
                        }
                    }
                }
            }
            do_action(
                'wpdatatables_generate_' . $tableData->table_type,
                $this,
                $tableData->content,
                $wdtParameters
            );
        } else {
            throw new WDTException(__('You are trying to load a table of an unknown type. Probably you did not activate the addon which is required to use this table type.', 'wpdatatables'));
        }
    }

    /**
     * Resolve the data-source adapter factory from the DI container. The
     * remote/file `*BasedConstruct` data-acquisition bodies live in
     * Services\DataSource adapters; the construct methods below are thin
     * delegators. `arrayBasedConstruct` is the shared column builder.
     *
     * @return DataSourceFactory
     */
    private static function dataSourceFactory()
    {
        return Plugin::container()->get(DataSourceFactory::class);
    }

    /**
     * Resolve the filtering engine from the DI container. Owns the
     * vendor-specific LIKE / date-time expressions; the global
     * `getDateTimeExpression()` delegates here.
     *
     * @return FilterService
     */
    private static function filterService()
    {
        return Plugin::container()->get(FilterService::class);
    }

    /**
     * Resolve the footer-aggregate engine from the DI container. Owns the
     * client-side aggregate computation; the private
     * `calcColumnsAggregateFuncs()` delegates here.
     *
     * @return SummaryService
     */
    private static function summaryService()
    {
        return Plugin::container()->get(SummaryService::class);
    }

    /**
     * Resolve the table engine orchestrator from the DI container. The
     * `queryBasedConstruct()` facade delegates here.
     *
     * @return TableService
     */
    private static function tableService()
    {
        return Plugin::container()->get(TableService::class);
    }

    /**
     * Resolve the frontend asset manager from the DI container. Owns the
     * `enqueueJSAndStyles()` body; the protected `enqueueJSAndStyles()` facade
     * delegates here.
     *
     * @return AssetManager
     */
    private static function assetManager()
    {
        return Plugin::container()->get(AssetManager::class);
    }

    /**
     * Resolve the table HTML renderer from the DI container. Owns the
     * generateTable / renderWithJSAndStyles / renderModal bodies; those facades
     * delegate here.
     *
     * @return TableRenderer
     */
    private static function tableRenderer()
    {
        return Plugin::container()->get(TableRenderer::class);
    }

    /**
     * Resolve the filter-form renderer from the DI container. Owns the
     * `filter_form.inc.php` rendering; the `renderFilterForm()` facade
     * delegates here.
     *
     * @return FilterRenderer
     */
    private static function filterRenderer()
    {
        return Plugin::container()->get(FilterRenderer::class);
    }

    /**
     * Resolve the edit-dialog renderer from the DI container. Owns the
     * `edit_dialog.inc.php` rendering; the `renderEditDialog()` facade
     * delegates here.
     *
     * @return EditDialogRenderer
     */
    private static function editDialogRenderer()
    {
        return Plugin::container()->get(EditDialogRenderer::class);
    }

    /**
     * Resolve the column CSS builder from the DI container.
     *
     * @return ColumnCssBuilder
     */
    private static function columnCssBuilder()
    {
        return Plugin::container()->get(ColumnCssBuilder::class);
    }

    /**
     * Resolve the column-definition JSON builder from the DI container.
     *
     * @return ColumnDefinitionBuilder
     */
    private static function columnDefinitionBuilder()
    {
        return Plugin::container()->get(ColumnDefinitionBuilder::class);
    }

    /**
     * Render the filter-in-form markup for this table. Thin facade over
     * {@see FilterRenderer}; invoked from `table_main.inc.php` in place of the
     * former inline `include` of `filter_form.inc.php`.
     *
     * @return string
     */
    public function renderFilterForm()
    {
        return self::filterRenderer()->render($this);
    }

    /**
     * Render the edit-dialog / inline-editing markup for this table. Thin facade
     * over {@see EditDialogRenderer}; invoked from `table_main.inc.php` in place
     * of the former inline `include` of `edit_dialog.inc.php`.
     *
     * @return string
     */
    public function renderEditDialog()
    {
        return self::editDialogRenderer()->render($this);
    }

    /**
     * The runtime engine entity for this table.
     *
     * The global `WPDataTable` is a delegating facade that internally holds a
     * runtime {@see RuntimeTable} entity. The wrapper is built lazily and shares
     * this instance's live state (no duplication).
     *
     * @return RuntimeTable
     */
    public function getRuntimeTable()
    {
        if ($this->_runtimeTable === null) {
            $this->_runtimeTable = self::tableService()->wrap($this);
        }

        return $this->_runtimeTable;
    }

    /**
     * @throws Exception
     */
    public function jsonBasedConstruct($json, $wdtParameters = array())
    {
        return self::dataSourceFactory()->make(DataSourceFactory::JSON)->read($this, $json, $wdtParameters);
    }

    /**
     * @throws Exception
     */
    public function serializedPHPBasedConstruct($url, $wdtParameters = array())
    {
        return self::dataSourceFactory()->make(DataSourceFactory::SERIALIZED)->read($this, $url, $wdtParameters);
    }

    /**
     * @throws WDTException
     */
    public function googleSheetBasedConstruct($sheetURL, $wdtParameters = array())
    {
        return self::dataSourceFactory()->make(DataSourceFactory::GOOGLE_SPREADSHEET)->read($this, $sheetURL, $wdtParameters);
    }

    /**
     * @throws Exception
     */
    public function nestedJsonBasedConstruct($jsonParams, $wdtParameters = array())
    {
        return self::dataSourceFactory()->make(DataSourceFactory::NESTED_JSON)->read($this, $jsonParams, $wdtParameters);
    }

    /**
     * @throws WDTException
     */
    public function XMLBasedConstruct($xml, $wdtParameters = array())
    {
        return self::dataSourceFactory()->make(DataSourceFactory::XML)->read($this, $xml, $wdtParameters);
    }

    /**
     * @throws WDTException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws Exception
     */
    public function excelBasedConstruct($xls_url, $wdtParameters = array())
    {
        return self::dataSourceFactory()->make(DataSourceFactory::EXCEL)->read($this, $xls_url, $wdtParameters);
    }

    /**
     * Helper method to get data from source URL
     *
     * @param $sourceObj
     * @param $sourceType
     * @param $source
     *
     * @return array|mixed|string|void|null
     * @throws WDTException
     * @throws Exception
     */
    public static function sourceRenderData($sourceObj, $sourceType, $source)
    {
        $wpId = $sourceObj->getWpId();
        $sourceArray = array();
        if ($sourceType == 'json') {
            $sourceArray = self::jsonRenderData($source, $wpId);
        }

        if ($sourceType == 'nested_json') {
            $sourceArray = self::nestedJsonRenderData($source, $wpId);
        }

        if ($sourceType == 'google_spreadsheet') {
            $sourceArray = self::googleRenderData($source);
        }

        if ($sourceType == 'serialized') {
            $sourceArray = self::serializedPhpRenderData($source, $wpId);
        }

        if ($sourceType == 'xml') {
            $sourceArray = self::xmlRenderData($source, $wpId);
        }

        WPDataTableCache::maybeSaveData(
            (int)$wpId,
            $sourceType,
            $source,
            $sourceObj->getAutoUpdateCache(),
            $sourceArray,
            $sourceObj->getCacheSourceData()
        );

        return $sourceArray;
    }

    /**
     * Helper method to get data from source URL
     *
     * @param $json
     * @param $id
     *
     * @return mixed|null
     * @throws Exception
     */
    public static function jsonRenderData($json, $id)
    {
        $json = WDTTools::applyPlaceholders($json);
        $json = WDTTools::curlGetData($json);
        $json = apply_filters('wpdatatables_filter_json', $json, $id);
        return json_decode($json, true);
    }

    /**
     * Helper method to get data from source URL
     *
     * @param $jsonParams
     * @param $id
     *
     * @return mixed|void
     * @throws Exception
     */
    public static function nestedJsonRenderData($jsonParams, $id)
    {
        if (!is_object($jsonParams))
            $jsonParams = json_decode($jsonParams);
        $nestedJSON = new WDTNestedJson($jsonParams);
        return $nestedJSON->getData($id);
    }

    /**
     * Helper method to get data from source URL
     *
     * @param $sheetURL
     *
     * @throws WDTException
     * @throws Exception
     */
    public static function googleRenderData($sheetURL)
    {
        $credentials = get_option('wdtGoogleSettings');
        $token = get_option('wdtGoogleToken');
        if ($credentials) {
            $googleSheet = new WPDataTable_Google_Sheet();
            return $googleSheet->getData($sheetURL, $credentials, $token);
        }
        return WDTTools::extractGoogleSpreadsheetArray($sheetURL);
    }


    /**
     * Helper method to get data from source URL
     *
     * @param $url
     * @param $id
     *
     * @return mixed
     */
    public static function serializedPhpRenderData($url, $id)
    {
        $url = apply_filters('wpdatatables_filter_url_php_array', WDTTools::applyPlaceholders($url), $id);
        $serialized_content = apply_filters('wpdatatables_filter_serialized', WDTTools::curlGetData($url), $id);
        return unserialize($serialized_content, ["allowed_classes" => false]);
    }

    /**
     * Helper method to get data from source URL
     *
     * @param $xml
     *
     * @return array|string
     */
    public static function xmlRenderData($xml, $id)
    {
        $xml = WDTTools::applyPlaceholders($xml);
        $XMLObject = simplexml_load_file($xml);
        $XMLObject = apply_filters('wpdatatables_filter_simplexml', $XMLObject, $id);
        $XMLArray = WDTTools::convertXMLtoArr($XMLObject);
        foreach ($XMLArray as &$xml_el) {
            if (is_array($xml_el) && array_key_exists('attributes', $xml_el)) {
                $xml_el = $xml_el['attributes'];
            }
        }
        return $XMLArray;
    }

    /**
     * Creates a reader depending on the file extension.
     *
     * Backward-compatible facade: the logic lives in
     * {@see WPDataTables\Infrastructure\Excel\SpreadsheetReaderAdapter}. Kept
     * static because WPDataTableCache and WDTSourceFile still call it.
     *
     * @param string $file
     *
     * @return \PhpOffice\PhpSpreadsheet\Reader\IReader
     * @throws WDTException
     */
    public static function createObjectReader($file)
    {
        return Plugin::container()->get(SpreadsheetReaderAdapter::class)->createReader($file);
    }

    /**
     * Helper method that renders the modal
     */
    public static function renderModal()
    {
        self::tableService()->renderModal();
    }

    /**
     * Frontend modal shell markup (edit/delete dialogs target #wdt-frontend-modal).
     *
     * @return string
     */
    public static function getModalHtml()
    {
        ob_start();
        self::renderModal();

        return (string) ob_get_clean();
    }

    /**
     * Generates table HTML
     * @return string
     */
    public function generateTable($connection)
    {
        return self::tableService()->generateTableHtml($this);
    }

    /**
     * Function that return table HTML content and
     * enqueue all necessary JS and CSS files
     * @return string
     */
    protected function renderWithJSAndStyles()
    {
        // The enqueue + table_main render live in Rendering\TableRenderer.
        return self::tableRenderer()->renderWithAssets($this);
    }

    /**
     * Function that enqueue all necessary JS and CSS files for wpDataTable
     */
    protected function enqueueJSAndStyles()
    {
        // The frontend asset enqueuing lives in Rendering\AssetManager.
        self::assetManager()->enqueueFrontend($this);
    }

    /**
     * * Helper method which prepares the column data from values stored in DB
     *
     * @param $tableData
     *
     * @return array
     */
    public function prepareColumnData($tableData)
    {
        return self::tableConfigService()->prepareColumnData($tableData);
    }

    //[<-- Full version -->]//

    /**
     * Helper method to detect the headers that are present in formula
     */
    public function detectHeadersInFormula($formula, $headers = null)
    {
        if (is_null($headers)) {
            $headers = $this->getColumnKeys();
        }

        return WDTTools::getColHeadersInFormula($formula, $headers);
    }

    /**
     * @param $formula String - formula that is passed from formula input
     * @param $headers array - where keys are original column headers and values are sanitized column headers
     * @param $row
     *
     * @return float
     */
    public static function solveFormula($formula, $headers, $row)
    {
        $vars = array();
        $formula = str_replace(array('$', '_', '&'), '', strtr($formula, $headers));

        foreach ($headers as $origHeader => $sanitizedHeader) {
            $vars[$sanitizedHeader] = (float)$row[$origHeader];
        }

        $parser = new Parser();

        $res = $parser->solve($formula, $vars);
        $err = error_get_last();
        if (is_array($err) && strpos($err['message'], 'Undefined') !== false) {
            $res = __('Unable to calculate', 'wpdatatables');
        }
        return $res;
    }

    /**
     * Tries to calculate formula value for first 5 table rows (or less if table has less than 5 rows)
     *
     * @param String $formula A string representation of formula to calculate
     *
     * @return String A result - first
     */
    public function calcFormulaPreview($formula)
    {
        $headers = array();
        $headersInFormula = $this->detectHeadersInFormula($formula);
        $headers = WDTTools::sanitizeHeaders($headersInFormula);

        $count = count($this->_dataRows) > 5 ? 5 : count($this->_dataRows);
        $result = __('Unable to calculate', 'wpdatatables');
        if ($count > 0) {
            $res_arr = array();
            try {
                for ($i = 0; $i < $count; $i++) {
                    $res_arr[] = self::solveFormula($formula, $headers, $this->_dataRows[$i]);
                }
                $result = __('Result for first 5 rows: ', 'wpdatatables') . implode(', ', $res_arr);
            } catch (Exception $e) {
                $result = __('Unable to calculate, error message: ', 'wpdatatables') . $e->getMessage();
            }
        }

        return $result;
    }

    public function checkFormulaPreview($formula)
    {
        $headers = array();
        $headersInFormula = $this->detectHeadersInFormula($formula);
        $headers = WDTTools::sanitizeHeaders($headersInFormula);

        $count = count($this->_dataRows) > 5 ? 1 : count($this->_dataRows);
        $result = __('Unable to calculate', 'wpdatatables');
        if ($count > 0) {
            $res_arr = array();
            try {
                for ($i = 0; $i < $count; $i++) {
                    $res_arr[] = self::solveFormula($formula, $headers, $this->_dataRows[$i]);
                }
                if ($res_arr[0] == 'Unable to calculate') {
                    $result = __('Unable to calculate', 'wpdatatables');
                } else {
                    $result = __('Formula is good', 'wpdatatables');
                }
            } catch (Exception $e) {
                $result = __('Unable to calculate, error message: ', 'wpdatatables') . $e->getMessage();
            }
        }
        return $result;
    }
    //[<--/ Full version -->]//

    /**
     * Helper method which populates the wpdatatables object with passed in parameters and data (stored in DB)
     *
     * @param $tableData
     * @param $columnData
     *
     * @throws WDTException
     * @throws Exception
     */
    public function fillFromData($tableData, $columnData)
    {
        self::tableService()->fillFromData($this, $tableData, $columnData);
    }

    /**
     * Helper method that prepares the rendering rules
     *
     * @param array $columnData
     */
    public function prepareRenderingRules($columnData)
    {
        self::tableHydrationService()->applyRenderingRules($this, $columnData);
    }

    /**
     * Returns JSON object for table description
     */
    public function getJsonDescription()
    {
        // The DataTables-description JSON builder lives in TableRenderer (JSON
        // mode). The internal Full-version strip markers travel with the body
        // into the renderer. WPExcelDataTable overrides this method with its own
        // builder.
        return self::tableRenderer()->buildJsonDescription($this);
    }

    /**
     * @param $columnKey
     * @param $foreignKeyRule
     * @param $dataRows
     *
     * @return mixed
     * @throws Exception
     * @throws WDTException
     */
    public function joinWithForeignWpDataTable($columnKey, $foreignKeyRule, $dataRows)
    {
        $joinedTable = self::loadWpDataTable($foreignKeyRule->tableId);
        $distinctValues = $joinedTable->getDistinctValuesForColumns($foreignKeyRule);
        foreach ($dataRows as &$dataRow) {
            $dataRow[$columnKey] = isset($distinctValues[$dataRow[$columnKey]]) ? $distinctValues[$dataRow[$columnKey]] : $dataRow[$columnKey];
        }

        return array(
            'dataRows' => $dataRows,
            'distinctValues' => $distinctValues
        );

    }

    /**
     * Function that returns related values (ID's and strings) for Foreign Key feature
     * by provided foreign key rule
     *
     * @param $foreignKeyRule stdClass that contains tableId, tableName, displayColumnId, displayColumnName,
     * storeColumnId and storeColumnName
     *
     * @return array
     */
    public function getDistinctValuesForColumns($foreignKeyRule)
    {
        global $wdtVar1, $wdtVar2, $wdtVar3, $wdtVar4, $wdtVar5, $wdtVar6, $wdtVar7, $wdtVar8, $wdtVar9, $wpdb;

        $distinctValues = array();
        $storeColumnName = $foreignKeyRule->storeColumnName;
        $displayColumnName = $foreignKeyRule->displayColumnName;
        $tableType = $this->getTableType();
        $columnTypeDisplayColumnName = $this->getWdtColumnTypes()[$displayColumnName];

        if ($tableType === 'mysql' || $tableType === 'manual') {
            $tableContent = $this->getTableContent();

            $tableContent = WDTTools::applyPlaceholders($tableContent);

            if ($this->getOnlyOwnRows()) {
                if (strpos($tableContent, 'WHERE') !== false) {
                    $tableContent .= ' AND ' . $this->getRuntimeTable()->getUserIdColumn() . '=' . get_current_user_id();
                } else {
                    $tableContent .= ' WHERE ' . $this->getRuntimeTable()->getUserIdColumn() . '=' . get_current_user_id();
                }
            }

            $vendor = Connection::getVendor($this->connection);

            $columnQuoteStart = Connection::getLeftColumnQuote($vendor);
            $columnQuoteEnd = Connection::getRightColumnQuote($vendor);

            $isMySql = $vendor === Connection::$MYSQL;
            $isMSSql = $vendor === Connection::$MSSQL;
            $isPostgreSql = $vendor === Connection::$POSTGRESQL;

            $groupBy = '';

            if ($isMySql) {
                $groupBy = "{$columnQuoteStart}{$storeColumnName}{$columnQuoteEnd}";
            }

            if ($isMSSql || $isPostgreSql) {
                $groupBy = "{$columnQuoteStart}{$storeColumnName}{$columnQuoteEnd}, {$columnQuoteStart}{$displayColumnName}{$columnQuoteEnd}";
            }

            $distValuesQuery = "SELECT({$columnQuoteStart}{$storeColumnName}{$columnQuoteEnd}) AS {$columnQuoteStart}{$storeColumnName}{$columnQuoteEnd}, ({$columnQuoteStart}{$displayColumnName}{$columnQuoteEnd}) AS {$columnQuoteStart}{$displayColumnName}{$columnQuoteEnd} FROM ($tableContent) tbl GROUP BY $groupBy ORDER BY {$columnQuoteStart}{$displayColumnName}{$columnQuoteEnd}";

            if (!(Connection::isSeparate($this->connection))) {
                global $wpdb;
                $mySqlResult = $wpdb->get_results($distValuesQuery);

                foreach ($mySqlResult as $dataRow) {
                    if (in_array($columnTypeDisplayColumnName, ['date',
                            'datetime',
                            'time']) && is_numeric($dataRow->$displayColumnName))
                        $dataRow[$displayColumnName] = WDTTools::wdtConvertUnixTimestampToString($columnTypeDisplayColumnName, $dataRow->$displayColumnName);
                    $distinctValues[$dataRow->$storeColumnName] = $dataRow->$displayColumnName;
                }
            } else {
                $sql = Connection::getInstance($this->connection);
                $mySqlResult = $sql->getAssoc($distValuesQuery);

                foreach ($mySqlResult ?: [] as $dataRow) {
                    if (in_array($columnTypeDisplayColumnName, ['date',
                            'datetime',
                            'time']) && is_numeric($dataRow[$displayColumnName]))
                        $dataRow[$displayColumnName] = WDTTools::wdtConvertUnixTimestampToString($columnTypeDisplayColumnName, $dataRow[$displayColumnName]);
                    $distinctValues[$dataRow[$storeColumnName]] = $dataRow[$displayColumnName];
                }
            }
        } else {
            foreach ($this->getDataRows() as $dataRow) {
                if (in_array($columnTypeDisplayColumnName, ['date',
                        'datetime',
                        'time']) && is_numeric($dataRow[$displayColumnName]))
                    $dataRow[$displayColumnName] = WDTTools::wdtConvertUnixTimestampToString($columnTypeDisplayColumnName, $dataRow[$displayColumnName]);
                $distinctValues[$dataRow[$storeColumnName]] = $dataRow[$displayColumnName];
            }
        }

        return $distinctValues;
    }


    /**
     * Delete table by ID
     *
     * @param $tableId
     *
     * @return bool
     * @throws Exception
     */
    public static function deleteTable($tableId)
    {
        if (!isset($_REQUEST['wdtNonce']) || empty($tableId) || !current_user_can('manage_options') || !wp_verify_nonce($_REQUEST['wdtNonce'], 'wdtDeleteTableNonce')) {
            return false;
        }

        // Deletion lives in the auth-decoupled TableService engine. The admin
        // browse-screen delete always drops a Manual table's backing storage,
        // so pass $dropStorage = true.
        $result = self::tableService()->deleteTable((int)$tableId, true);

        return $result['deleted'];
    }

    /**
     * Get all tables
     * @return array|null|object
     */
    public static function getAllTables()
    {
        global $wpdb;

        $query = "SELECT id, title, IF(table_type = 'mysql', 'SQL', table_type) AS table_type, connection, server_side FROM {$wpdb->prefix}wpdatatables ORDER BY id";

        $allTables = $wpdb->get_results($query, ARRAY_A);
        return $allTables;
    }

    /**
     * Get all tables except simple tables
     * @return array|null|object
     */
    public static function getAllTablesExceptSimple()
    {
        global $wpdb;

        $query = "SELECT id, title, connection, server_side FROM {$wpdb->prefix}wpdatatables WHERE NOT table_type = 'simple' ORDER BY id";

        $allTables = $wpdb->get_results($query, ARRAY_A);

        return $allTables;
    }

    /**
     * Helper method that load wpDataTable object by given table ID
     * and return array with $wpDataTable object and $tableData object
     *
     * @param $tableId
     * @param null $tableView
     * @param bool $disableLimit
     *
     * @return WPDataTable|WPExcelDataTable|bool
     * @throws Exception
     * @throws WDTException
     */
    public static function loadWpDataTable($tableId, $tableView = null, $disableLimit = false)
    {
        return self::tableService()->loadTable($tableId, $tableView, $disableLimit);
    }

    public static function wdtReoderColumnPositions($duplicateColumns, $positions, $tableId)
    {
        return self::tableConfigService()->reorderDuplicateColumnPositions($duplicateColumns, $positions, $tableId);
    }

}