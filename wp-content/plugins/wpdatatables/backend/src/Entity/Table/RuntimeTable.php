<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Entity\Table;

/**
 * Runtime handle for a live wpDataTable instance.
 *
 * Distinct from {@see Table}, which is the *persistence* entity (it wraps the
 * `wpdatatables` row). This is the *runtime* engine entity: the typed handle the
 * {@see \WPDataTables\Services\Table\TableService} engine operates on, and the
 * object the global {@see \WPDataTable} facade holds internally.
 *
 * It owns the table's runtime state as a single source of truth and wraps the
 * live `WPDataTable`, forwarding the engine-relevant accessors.
 *
 * @package WPDataTables\Entity\Table
 */
class RuntimeTable
{
    /** @var \WPDataTable */
    private $table;

    // ---------------------------------------------------------------------
    // This entity is the single source of truth for the runtime state; the
    // global `WPDataTable` getters/setters are thin delegators into it.
    //
    // Appearance / layout / pagination-display scalars.
    // ---------------------------------------------------------------------

    /** @var bool */
    private $fixedLayout = false;
    /** @var bool */
    private $wordWrap = false;
    /** @var bool */
    private $fixedColumns = false;
    /** @var int */
    private $fixedLeftColumnsNumber = 0;
    /** @var int */
    private $fixedRightColumnsNumber = 0;
    /** @var bool */
    private $fixedHeaders = false;
    /** @var int */
    private $fixedHeadersOffset = 0;
    /** @var int */
    private $indexColumn = 0;
    /** @var string */
    private $customRowDisplay = '';
    private $customStringEmptyFiltering = '';
    /** @var bool */
    private $cellPadding = 10;
    /** @var string */
    private $borderCollapse = 'collapse';
    /** @var int */
    private $borderSpacing = 0;
    /** @var bool */
    private $removeBorders = false;
    /** @var bool */
    private $stripeTable = false;
    /** @var bool */
    private $verticalScrollHeight = 600;
    /** @var bool */
    private $simpleHeader = false;
    /** @var bool */
    private $simpleResponsive = false;
    /** @var string */
    private $pdfPaperSize = 'A4';
    /** @var string */
    private $pdfPageOrientation = 'portrait';
    /** @var int */
    private $autoRefreshInterval = 0;
    /** @var bool */
    private $infoBlock = true;
    /** @var int */
    private $loader = 1;
    /** @var int */
    private $paginationTop = 0;
    /** @var string */
    private $paginationLayout = 'full_numbers';
    /** @var string */
    private $paginationLayoutMobile = 'simple';
    /** @var int */
    private $tableWcag = 0;
    /** @var int */
    private $advancedFilterOption = 0;
    /** @var string */
    private $tableSkin = '';
    /** @var int */
    private $tableBorderRemoval = 0;
    /** @var int */
    private $tableBorderRemovalHeader = 0;
    /** @var string */
    private $tableCustomCss = '';
    /** @var mixed */
    private $tableFontColorSettings;

    // ---------------------------------------------------------------------
    // Identity/key columns, display flags, sort/search defaults, content/title.
    //
    // Convention from here on: this entity is a PLAIN TYPED STORE (getters
    // return the field, setters store it). All casting, validation and transform
    // (e.g. setInterfaceLanguage's path-traversal guard, defaultSearchValue's
    // urlencode/urldecode, the (bool)/(int) coercions) stay in the `WPDataTable`
    // facade, which delegates only the storage here.
    // ---------------------------------------------------------------------

    /** @var string */
    private $idColumnKey = '';
    /** @var string */
    private $userColumnKey = '';
    /** @var string */
    private $userEditColumnKey = '';
    /** @var string */
    private $datecreatedColumnKey = '';
    /** @var string */
    private $datecreatedEditColumnKey = '';
    /** @var bool */
    private $autoUpdateCache = false;
    /** @var bool */
    private $cacheSourceData = false;
    /** @var bool */
    private $clearFilters = false;
    /** @var bool */
    private $filteringForm = false;
    /** @var bool */
    private $globalSearch = true;
    /** @var bool */
    private $noData = false;
    /** @var bool */
    private $onlyOwnRows = false;
    /** @var bool */
    private $showAllRows = false;
    /** @var int */
    private $showCartInformation = 1;
    /** @var bool */
    private $showRowsPerPage = true;
    /** @var int */
    private $showTableToolsIncludeHTML = 0;
    /** @var int */
    private $showTableToolsIncludeTitle = 0;
    /** @var int */
    private $simpleTemplateId = 0;
    /** @var bool */
    private $enableDuplicateButton = false;
    /** @var string */
    private $responsiveAction = 'icon';
    /** @var string */
    private $fileLocation = 'wp_media_lib';
    /** @var int */
    private $displayLength = 10;
    /** @var string */
    private $style = '';
    /** @var mixed */
    private $defaultSortColumn;
    /** @var string */
    private $defaultSortDirection = 'ASC';
    /** @var string */
    private $defaultSearchValue = '';
    /** @var mixed */
    private $interfaceLanguage;
    /** @var string */
    private $tableDescription = '';
    /** @var string */
    private $tableContent = '';
    /** @var string */
    private $title = '';

    // ---------------------------------------------------------------------
    // Boolean feature flags exposed through enable/disable/getter method triads
    // (and a couple of branch-setters) plus pagination and the user-id column.
    // Same plain-store convention: the facade's enable*/disable*/set* methods
    // delegate to the plain setter here, every getter to the plain getter.
    // ---------------------------------------------------------------------

    /** @var bool */
    private $editable = false;
    /** @var bool */
    private $groupingEnabled = false;
    /** @var bool */
    private $hideBeforeLoad = false;
    /** @var bool */
    private $inlineEditing = false;
    /** @var string */
    private $paginationAlign = 'right';
    /** @var bool */
    private $popoverTools = false;
    /** @var bool */
    private $responsive = false;
    /** @var bool */
    private $scrollable = false;
    /** @var bool */
    private $serverProcessing = false;
    /** @var bool */
    private $showAdvancedFilter = false;
    /** @var bool */
    private $showFilter = true;
    /** @var bool */
    private $showTableDescription = false;
    /** @var bool */
    private $showTT = true;
    /** @var bool */
    private $verticalScroll = false;
    /** @var int */
    private $columnGroupIndex = 0;
    /** @var bool */
    private $tableSort = true;
    /** @var int */
    private $userIdColumn = 0;
    /** @var bool */
    private $pagination = true;

    // ---------------------------------------------------------------------
    // Core engine accessors: getId/getTableType/isServerSide/isAjaxReturn are
    // field-backed (single source of truth) and the facade delegates here.
    // (isServerSide() reads the `serverProcessing` field above.)
    // ---------------------------------------------------------------------

    /** @var string */
    private $tableType = '';
    /** @var bool */
    private $ajaxReturn = false;
    /** @var mixed */
    private $wpId = '';

    // -----------------------------------------------------------------
    // Array members. In-place mutation is contained in dedicated add* methods;
    // the facade's add*/set*/get* delegate here so the array is mutated on the
    // single owning instance.
    // -----------------------------------------------------------------

    /** @var array */
    private $sumColumns = array();
    /** @var array */
    private $avgColumns = array();
    /** @var array */
    private $minColumns = array();
    /** @var array */
    private $maxColumns = array();
    /** @var array */
    private $sumFooterColumns = array();
    /** @var array */
    private $avgFooterColumns = array();
    /** @var array */
    private $minFooterColumns = array();
    /** @var array */
    private $maxFooterColumns = array();
    /** @var array */
    private $columnsDecimalPlaces = array();
    /** @var array */
    private $columnsThousandsSeparator = array();
    /** @var array */
    private $conditionalFormattingColumns = array();
    /** @var array */
    private $transformValueColumns = array();
    /** @var array */
    private $editButtonsDisplayed = array('all');
    /** @var array */
    private $wdtColumnTypes = array();
    /** @var array */
    private $tableToolsConfig = array();

    public function __construct(\WPDataTable $table)
    {
        $this->table = $table;
    }

    /**
     * The wrapped live table instance (the engine code operates on this
     * concrete type).
     *
     * @return \WPDataTable
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * The persisted wpDataTables row id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->wpId;
    }

    public function getWpId()
    {
        return $this->wpId;
    }

    public function setWpId($wpId)
    {
        $this->wpId = $wpId;
    }

    public function getTableType()
    {
        return $this->tableType;
    }

    public function setTableType($tableType)
    {
        $this->tableType = $tableType;
    }

    public function isServerSide()
    {
        return (bool) $this->serverProcessing;
    }

    public function isAjaxReturn()
    {
        return $this->ajaxReturn;
    }

    public function setAjaxReturn($ajaxReturn)
    {
        $this->ajaxReturn = $ajaxReturn;
    }

    // ---------------------------------------------------------------------
    // Appearance / layout / pagination-display accessors. The global
    // WPDataTable accessors delegate here.
    // ---------------------------------------------------------------------

    public function isFixedLayout()
    {
        return $this->fixedLayout;
    }

    public function setFixedLayout($fixedLayout)
    {
        $this->fixedLayout = $fixedLayout;
    }

    public function isWordWrap()
    {
        return $this->wordWrap;
    }

    public function setWordWrap($wordWrap)
    {
        $this->wordWrap = $wordWrap;
    }

    public function isFixedColumns()
    {
        return $this->fixedColumns;
    }

    public function setFixedColumns($fixedcolumns)
    {
        $this->fixedColumns = $fixedcolumns;
    }

    public function getLeftFixedColumnsNumber()
    {
        return $this->fixedLeftColumnsNumber;
    }

    public function setLeftFixedColumnsNumber($fixedleftcolumns)
    {
        $this->fixedLeftColumnsNumber = $fixedleftcolumns;
    }

    public function getRightFixedColumnsNumber()
    {
        return $this->fixedRightColumnsNumber;
    }

    public function setRightFixedColumnsNumber($fixedrightcolumns)
    {
        $this->fixedRightColumnsNumber = $fixedrightcolumns;
    }

    public function isFixedHeaders()
    {
        return $this->fixedHeaders;
    }

    public function setFixedHeaders($fixedheader)
    {
        $this->fixedHeaders = $fixedheader;
    }

    public function getFixedHeadersOffset()
    {
        return $this->fixedHeadersOffset;
    }

    public function setFixedHeadersOffset($fixedheaderoffset)
    {
        $this->fixedHeadersOffset = $fixedheaderoffset;
    }

    public function getIndexColumn()
    {
        return $this->indexColumn;
    }

    public function setIndexColumn($indexcolumn)
    {
        $this->indexColumn = $indexcolumn;
    }

    public function getCusgtomDisplayLength()
    {
        return $this->customRowDisplay;
    }

    public function setCustomDisplayLength($customRowDisplay)
    {
        $this->customRowDisplay = $customRowDisplay;
    }

    public function getCustomStringEmptyFiltering()
    {
        return $this->customStringEmptyFiltering;
    }

    public function setCustomStringEmptyFiltering($customStringEmptyFiltering)
    {
        $this->customStringEmptyFiltering = $customStringEmptyFiltering;
    }

    public function getCellPadding()
    {
        return $this->cellPadding;
    }

    public function setCellPadding($cellPadding)
    {
        $this->cellPadding = (bool)$cellPadding;
    }

    public function getBorderCollapse()
    {
        return $this->borderCollapse;
    }

    public function setBorderCollapse($borderCollapse)
    {
        $this->borderCollapse = $borderCollapse;
    }

    public function getBorderSpacing()
    {
        return $this->borderSpacing;
    }

    public function setBorderSpacing($borderSpacing)
    {
        $this->borderSpacing = (int)$borderSpacing;
    }

    public function isRemoveBorders()
    {
        return $this->removeBorders;
    }

    public function setRemoveBorders($removeBorders)
    {
        $this->removeBorders = (bool)$removeBorders;
    }

    public function isStripeTable()
    {
        return $this->stripeTable;
    }

    public function setStripeTable($stripeTable)
    {
        $this->stripeTable = (bool)$stripeTable;
    }

    public function getVerticalScrollHeight()
    {
        return $this->verticalScrollHeight;
    }

    public function setVerticalScrollHeight($verticalScrollHeight)
    {
        $this->verticalScrollHeight = (bool)$verticalScrollHeight;
    }

    public function isSimpleHeader()
    {
        return $this->simpleHeader;
    }

    public function setSimpleHeader($simpleHeader)
    {
        $this->simpleHeader = (bool)$simpleHeader;
    }

    public function isSimpleResponsive()
    {
        return $this->simpleResponsive;
    }

    public function setSimpleResponsive($simpleResponsive)
    {
        $this->simpleResponsive = (bool)$simpleResponsive;
    }

    public function getPdfPaperSize()
    {
        return $this->pdfPaperSize;
    }

    public function setPdfPaperSize($pdfPaperSize)
    {
        $this->pdfPaperSize = $pdfPaperSize;
    }

    public function getPdfPageOrientation()
    {
        return $this->pdfPageOrientation;
    }

    public function setPdfPageOrientation($pdfPageOrientation)
    {
        $this->pdfPageOrientation = $pdfPageOrientation;
    }

    public function getRefreshInterval()
    {
        return (int)$this->autoRefreshInterval;
    }

    public function setAutoRefresh($refresh_interval)
    {
        $this->autoRefreshInterval = (int)$refresh_interval;
    }

    public function isInfoBlock()
    {
        return $this->infoBlock;
    }

    public function setInfoBlock($infoBlock)
    {
        $this->infoBlock = (bool)$infoBlock;
    }

    public function isLoaderVisible()
    {
        return $this->loader;
    }

    public function setLoader($loader)
    {
        $this->loader = $loader;
    }

    public function getPaginationOnTop()
    {
        return $this->paginationTop;
    }

    public function setPaginationOnTop($paginationOnTop)
    {
        $this->paginationTop = (int)$paginationOnTop;
    }

    public function getPaginationLayout()
    {
        return $this->paginationLayout;
    }

    public function setPaginationLayout($paginationLayout)
    {
        $this->paginationLayout = $paginationLayout;
    }

    public function getPaginationLayoutMobile()
    {
        return $this->paginationLayoutMobile;
    }

    public function setPaginationLayoutMobile($paginationLayout)
    {
        $this->paginationLayoutMobile = $paginationLayout;
    }

    public function isTableWCAG()
    {
        return $this->tableWcag;
    }

    public function setTableWCAG($tableWCAG)
    {
        $this->tableWcag = $tableWCAG;
    }

    public function isAdvancedFilterOption()
    {
        return $this->advancedFilterOption;
    }

    public function setAdvancedFilterOption($advancedFilterOption)
    {
        $this->advancedFilterOption = $advancedFilterOption;
    }

    public function getTableSkin()
    {
        return $this->tableSkin;
    }

    public function setTableSkin($tableSkin)
    {
        $this->tableSkin = $tableSkin;
    }

    public function getTableBorderRemoval()
    {
        return $this->tableBorderRemoval;
    }

    public function setTableBorderRemoval($tableBorderRemoval)
    {
        $this->tableBorderRemoval = $tableBorderRemoval;
    }

    public function getTableBorderRemovalHeader()
    {
        return $this->tableBorderRemovalHeader;
    }

    public function setTableBorderRemovalHeader($tableBorderRemovalHeader)
    {
        $this->tableBorderRemovalHeader = $tableBorderRemovalHeader;
    }

    public function getTableCustomCss()
    {
        return $this->tableCustomCss;
    }

    public function setTableCustomCss($tableCustomCss)
    {
        $this->tableCustomCss = $tableCustomCss;
    }

    public function getTableFontColorSettings()
    {
        return $this->tableFontColorSettings;
    }

    public function setTableFontColorSettings($tableFontColorSettings)
    {
        $this->tableFontColorSettings = $tableFontColorSettings;
    }

    // ---------------------------------------------------------------------
    // Identity/key-column, display-flag, sort/search and content accessors —
    // plain store; transform stays in the facade.
    // ---------------------------------------------------------------------

    public function getIdColumnKey()
    {
        return $this->idColumnKey;
    }

    public function setIdColumnKey($idColumnKey)
    {
        $this->idColumnKey = $idColumnKey;
    }

    public function getUserColumnKey()
    {
        return $this->userColumnKey;
    }

    public function setUserColumnKey($userColumnKey)
    {
        $this->userColumnKey = $userColumnKey;
    }

    public function getUserEditColumnKey()
    {
        return $this->userEditColumnKey;
    }

    public function setUserEditColumnKey($userEditColumnKey)
    {
        $this->userEditColumnKey = $userEditColumnKey;
    }

    public function getDatecreatedColumnKey()
    {
        return $this->datecreatedColumnKey;
    }

    public function setDatecreatedColumnKey($datecreatedColumnKey)
    {
        $this->datecreatedColumnKey = $datecreatedColumnKey;
    }

    public function getDatecreatedEditColumnKey()
    {
        return $this->datecreatedEditColumnKey;
    }

    public function setDatecreatedEditColumnKey($datecreatedEditColumnKey)
    {
        $this->datecreatedEditColumnKey = $datecreatedEditColumnKey;
    }

    public function getAutoUpdateCache()
    {
        return $this->autoUpdateCache;
    }

    public function setAutoUpdateCache($autoUpdateCache)
    {
        $this->autoUpdateCache = $autoUpdateCache;
    }

    public function getCacheSourceData()
    {
        return $this->cacheSourceData;
    }

    public function setCacheSourceData($cacheSourceData)
    {
        $this->cacheSourceData = $cacheSourceData;
    }

    public function isClearFilters()
    {
        return $this->clearFilters;
    }

    public function setClearFilters($clearFilters)
    {
        $this->clearFilters = $clearFilters;
    }

    public function getFilteringForm()
    {
        return $this->filteringForm;
    }

    public function setFilteringForm($filteringForm)
    {
        $this->filteringForm = $filteringForm;
    }

    public function isGlobalSearch()
    {
        return $this->globalSearch;
    }

    public function setGlobalSearch($globalSearch)
    {
        $this->globalSearch = $globalSearch;
    }

    public function getNoData()
    {
        return $this->noData;
    }

    public function setNoData($noData)
    {
        $this->noData = $noData;
    }

    public function getOnlyOwnRows()
    {
        return $this->onlyOwnRows;
    }

    public function setOnlyOwnRows($onlyOwnRows)
    {
        $this->onlyOwnRows = $onlyOwnRows;
    }

    public function isShowAllRows()
    {
        return $this->showAllRows;
    }

    public function setShowAllRows($showAllRows)
    {
        $this->showAllRows = $showAllRows;
    }

    public function getShowCartInformation()
    {
        return $this->showCartInformation;
    }

    public function setShowCartInformation($showCartInformation)
    {
        $this->showCartInformation = $showCartInformation;
    }

    public function isShowRowsPerPage()
    {
        return $this->showRowsPerPage;
    }

    public function setShowRowsPerPage($showRowsPerPage)
    {
        $this->showRowsPerPage = $showRowsPerPage;
    }

    public function getTableToolsIncludeHTML()
    {
        return $this->showTableToolsIncludeHTML;
    }

    public function setTableToolsIncludeHTML($showTableToolsIncludeHTML)
    {
        $this->showTableToolsIncludeHTML = $showTableToolsIncludeHTML;
    }

    public function getTableToolsIncludeTitle()
    {
        return $this->showTableToolsIncludeTitle;
    }

    public function setTableToolsIncludeTitle($showTableToolsIncludeTitle)
    {
        $this->showTableToolsIncludeTitle = $showTableToolsIncludeTitle;
    }

    public function getSimpleTemplateId()
    {
        return $this->simpleTemplateId;
    }

    public function setSimpleTemplateId($simpleTemplateId)
    {
        $this->simpleTemplateId = $simpleTemplateId;
    }

    public function isEnableDuplicateButton()
    {
        return $this->enableDuplicateButton;
    }

    public function setEnableDuplicateButton($enableDuplicateButton)
    {
        $this->enableDuplicateButton = $enableDuplicateButton;
    }

    public function getResponsiveAction()
    {
        return $this->responsiveAction;
    }

    public function setResponsiveAction($responsiveAction)
    {
        $this->responsiveAction = $responsiveAction;
    }

    public function getFileLocation()
    {
        return $this->fileLocation;
    }

    public function setFileLocation($fileLocation)
    {
        $this->fileLocation = $fileLocation;
    }

    public function getDisplayLength()
    {
        return $this->displayLength;
    }

    public function setDisplayLength($displayLength)
    {
        $this->displayLength = $displayLength;
    }

    public function getCSSStyle()
    {
        return $this->style;
    }

    public function setCSSStyle($style)
    {
        $this->style = $style;
    }

    public function getDefaultSortColumn()
    {
        return $this->defaultSortColumn;
    }

    public function setDefaultSortColumn($defaultSortColumn)
    {
        $this->defaultSortColumn = $defaultSortColumn;
    }

    public function getDefaultSortDirection()
    {
        return $this->defaultSortDirection;
    }

    public function setDefaultSortDirection($defaultSortDirection)
    {
        $this->defaultSortDirection = $defaultSortDirection;
    }

    public function getDefaultSearchValue()
    {
        return $this->defaultSearchValue;
    }

    public function setDefaultSearchValue($defaultSearchValue)
    {
        $this->defaultSearchValue = $defaultSearchValue;
    }

    public function getInterfaceLanguage()
    {
        return $this->interfaceLanguage;
    }

    public function setInterfaceLanguage($interfaceLanguage)
    {
        $this->interfaceLanguage = $interfaceLanguage;
    }

    public function getDescription()
    {
        return $this->tableDescription;
    }

    public function setDescription($tableDescription)
    {
        $this->tableDescription = $tableDescription;
    }

    public function getTableContent()
    {
        return $this->tableContent;
    }

    public function setTableContent($tableContent)
    {
        $this->tableContent = $tableContent;
    }

    public function getName()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    // ---------------------------------------------------------------------
    // Boolean feature-flag, pagination and user-id-column accessors — plain
    // store; the facade's flag triads collapse to one setter + one getter here.
    // ---------------------------------------------------------------------

    public function isEditable()
    {
        return $this->editable;
    }

    public function setEditable($editable)
    {
        $this->editable = $editable;
    }

    public function isGroupingEnabled()
    {
        return $this->groupingEnabled;
    }

    public function setGroupingEnabled($groupingEnabled)
    {
        $this->groupingEnabled = $groupingEnabled;
    }

    public function isHideBeforeLoad()
    {
        return $this->hideBeforeLoad;
    }

    public function setHideBeforeLoad($hideBeforeLoad)
    {
        $this->hideBeforeLoad = $hideBeforeLoad;
    }

    public function isInlineEditing()
    {
        return $this->inlineEditing;
    }

    public function setInlineEditing($inlineEditing)
    {
        $this->inlineEditing = $inlineEditing;
    }

    public function getPaginationAlign()
    {
        return $this->paginationAlign;
    }

    public function setPaginationAlign($paginationAlign)
    {
        $this->paginationAlign = $paginationAlign;
    }

    public function isPopoverTools()
    {
        return $this->popoverTools;
    }

    public function setPopoverTools($popoverTools)
    {
        $this->popoverTools = $popoverTools;
    }

    public function isResponsive()
    {
        return $this->responsive;
    }

    public function setResponsive($responsive)
    {
        $this->responsive = $responsive;
    }

    public function isScrollable()
    {
        return $this->scrollable;
    }

    public function setScrollable($scrollable)
    {
        $this->scrollable = $scrollable;
    }

    public function isServerProcessing()
    {
        return $this->serverProcessing;
    }

    public function setServerProcessing($serverProcessing)
    {
        $this->serverProcessing = $serverProcessing;
    }

    public function isShowAdvancedFilter()
    {
        return $this->showAdvancedFilter;
    }

    public function setShowAdvancedFilter($showAdvancedFilter)
    {
        $this->showAdvancedFilter = $showAdvancedFilter;
    }

    public function isShowFilter()
    {
        return $this->showFilter;
    }

    public function setShowFilter($showFilter)
    {
        $this->showFilter = $showFilter;
    }

    public function isShowTableDescription()
    {
        return $this->showTableDescription;
    }

    public function setShowTableDescription($showTableDescription)
    {
        $this->showTableDescription = $showTableDescription;
    }

    public function isShowTT()
    {
        return $this->showTT;
    }

    public function setShowTT($showTT)
    {
        $this->showTT = $showTT;
    }

    public function isVerticalScroll()
    {
        return $this->verticalScroll;
    }

    public function setVerticalScroll($verticalScroll)
    {
        $this->verticalScroll = $verticalScroll;
    }

    public function getColumnGroupIndex()
    {
        return $this->columnGroupIndex;
    }

    public function setColumnGroupIndex($columnGroupIndex)
    {
        $this->columnGroupIndex = $columnGroupIndex;
    }

    public function isTableSort()
    {
        return $this->tableSort;
    }

    public function setTableSort($tableSort)
    {
        $this->tableSort = $tableSort;
    }

    public function getUserIdColumn()
    {
        return $this->userIdColumn;
    }

    public function setUserIdColumn($userIdColumn)
    {
        $this->userIdColumn = $userIdColumn;
    }

    public function isPagination()
    {
        return $this->pagination;
    }

    public function setPagination($pagination)
    {
        $this->pagination = $pagination;
    }
    // -----------------------------------------------------------------
    // Array-member accessors; add* performs the in-place mutation on the
    // owning instance.
    // -----------------------------------------------------------------

    public function getSumColumns()
    {
        return $this->sumColumns;
    }

    public function setSumColumns($value)
    {
        $this->sumColumns = $value;
    }

    public function addSumColumn($value)
    {
        $this->sumColumns[] = $value;
    }

    public function getAvgColumns()
    {
        return $this->avgColumns;
    }

    public function setAvgColumns($value)
    {
        $this->avgColumns = $value;
    }

    public function addAvgColumn($value)
    {
        $this->avgColumns[] = $value;
    }

    public function getMinColumns()
    {
        return $this->minColumns;
    }

    public function setMinColumns($value)
    {
        $this->minColumns = $value;
    }

    public function addMinColumn($value)
    {
        $this->minColumns[] = $value;
    }

    public function getMaxColumns()
    {
        return $this->maxColumns;
    }

    public function setMaxColumns($value)
    {
        $this->maxColumns = $value;
    }

    public function addMaxColumn($value)
    {
        $this->maxColumns[] = $value;
    }

    public function getSumFooterColumns()
    {
        return $this->sumFooterColumns;
    }

    public function setSumFooterColumns($value)
    {
        $this->sumFooterColumns = $value;
    }

    public function addSumFooterColumn($value)
    {
        $this->sumFooterColumns[] = $value;
    }

    public function getAvgFooterColumns()
    {
        return $this->avgFooterColumns;
    }

    public function setAvgFooterColumns($value)
    {
        $this->avgFooterColumns = $value;
    }

    public function addAvgFooterColumn($value)
    {
        $this->avgFooterColumns[] = $value;
    }

    public function getMinFooterColumns()
    {
        return $this->minFooterColumns;
    }

    public function setMinFooterColumns($value)
    {
        $this->minFooterColumns = $value;
    }

    public function addMinFooterColumn($value)
    {
        $this->minFooterColumns[] = $value;
    }

    public function getMaxFooterColumns()
    {
        return $this->maxFooterColumns;
    }

    public function setMaxFooterColumns($value)
    {
        $this->maxFooterColumns = $value;
    }

    public function addMaxFooterColumn($value)
    {
        $this->maxFooterColumns[] = $value;
    }

    public function getColumnsDecimalPlaces()
    {
        return $this->columnsDecimalPlaces;
    }

    public function addColumnsDecimalPlaces($key, $value)
    {
        $this->columnsDecimalPlaces[$key] = $value;
    }

    public function getColumnsThousandsSeparator()
    {
        return $this->columnsThousandsSeparator;
    }

    public function addColumnsThousandsSeparator($key, $value)
    {
        $this->columnsThousandsSeparator[$key] = $value;
    }

    public function getConditionalFormattingColumns()
    {
        return $this->conditionalFormattingColumns;
    }

    public function addConditionalFormattingColumn($value)
    {
        $this->conditionalFormattingColumns[] = $value;
    }

    public function getTransformValueColumn()
    {
        return $this->transformValueColumns;
    }

    public function addTransformValueColumn($value)
    {
        $this->transformValueColumns[] = $value;
    }

    public function getEditButtonsDisplayed()
    {
        return $this->editButtonsDisplayed;
    }

    public function setEditButtonsDisplayed($value)
    {
        $this->editButtonsDisplayed = $value;
    }

    public function getWdtColumnTypes()
    {
        return $this->wdtColumnTypes;
    }

    public function setWdtColumnTypes($value)
    {
        $this->wdtColumnTypes = $value;
    }

    public function getTableToolsConfig()
    {
        return $this->tableToolsConfig;
    }

    public function setTableToolsConfig($value)
    {
        $this->tableToolsConfig = $value;
    }

}
