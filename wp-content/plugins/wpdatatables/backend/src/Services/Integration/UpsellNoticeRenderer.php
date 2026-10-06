<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Integration;

/**
 * UpsellNoticeRenderer — renders the admin "upgrade / available in a higher
 * tier" notices for wpDataTables features whose integration is not present in
 * the current licence-tier build.
 *
 * Each method guards on `!defined('WDT_*_INTEGRATION')` (the constant a loaded
 * integration defines) and buffer-and-`include`s a `templates/admin/...` notice
 * partial (all under `templates/`, which the tier build never strips). The
 * methods are wired by {@see IntegrationService::registerAdminHooks()} as
 * `[$renderer, 'method']` callbacks.
 *
 * @package WPDataTables\Services\Integration
 */
class UpsellNoticeRenderer
{
    /**
     * addChartPickerStepNotice.
     *
     * @return void
     */
    public function addChartPickerStepNotice()
    {
        if (!defined('WDT_HC_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/chart_wizard/steps/charts_pick/highcharts.inc.php';
            $highChartsNotice = ob_get_contents();
            ob_end_clean();
            echo $highChartsNotice;
        }

        if (!defined('WDT_HS_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/chart_wizard/steps/charts_pick/highstock.inc.php';
            $highStockChartsNotice = ob_get_contents();
            ob_end_clean();
            echo $highStockChartsNotice;
        }

        if (!defined('WDT_AC_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/chart_wizard/steps/charts_pick/apexcharts.inc.php';
            $apexChartsNotice = ob_get_contents();
            ob_end_clean();
            echo $apexChartsNotice;
        }

    }

    /**
     * addNoticePlaceholdersOptions.
     *
     * @return void
     */
    public function addNoticePlaceholdersOptions()
    {
        if (!defined('WDT_PH_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/placeholders_notice_block.inc.php';
            $placeholdersNotice = ob_get_contents();
            ob_end_clean();
            echo $placeholdersNotice;
        }
    }

    /**
     * addChartsStableTagNotice.
     *
     * @return void
     */
    public function addChartsStableTagNotice()
    {
        if (!defined('WDT_HC_INTEGRATION') || !defined('WDT_AC_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/charts_stable_tag_notice_block.inc.php';
            $placeholdersNotice = ob_get_contents();
            ob_end_clean();
            echo $placeholdersNotice;
        }
    }

    /**
     * addHiddenColumnTypeNotice.
     *
     * @return void
     */
    public function addHiddenColumnTypeNotice()
    {
        if (!defined('WDT_HCOL_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/hidden_column_type_option_notice.inc.php';
            $hiddenColumnNotice = ob_get_contents();
            ob_end_clean();
            echo $hiddenColumnNotice;
        }
    }

    /**
     * addHiddenColumnConstructorNotice.
     *
     * @return void
     */
    public function addHiddenColumnConstructorNotice()
    {
        if (!defined('WDT_HCOL_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/hidden_column_constructor_notice_block.inc.php';
            $hiddenColumnConstructorNotice = ob_get_contents();
            ob_end_clean();
            echo $hiddenColumnConstructorNotice;
        }
    }

    /**
     * addHiddenColumnAddModalNotice.
     *
     * @return void
     */
    public function addHiddenColumnAddModalNotice()
    {
        if (!defined('WDT_HCOL_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/hidden_column_add_modal_notice_block.inc.php';
            $hiddenColumnAddModalNotice = ob_get_contents();
            ob_end_clean();
            echo $hiddenColumnAddModalNotice;
        }
    }

    /**
     * filterPossibleColumnTypes.
     *
     * @return mixed
     */
    public function filterPossibleColumnTypes($possibleColumnTypes)
    {
        if (!defined('WDT_HCOL_INTEGRATION')) {
            $newColVal =
                array('hidden' => __('Hidden (Dynamic) - Available from Standard Licence', 'wpdatatables'));

            return array_merge(
                array_slice($possibleColumnTypes, 0, 4),
                $newColVal, array_slice($possibleColumnTypes, 4)
            );
        }

        return $possibleColumnTypes;
    }

    /**
     * addFolderNotice.
     *
     * @return void
     */
    public function addFolderNotice()
    {
        if (!defined('WDT_FOLDERS_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/folders_notice_block.inc.php';
            $placeholdersNotice = ob_get_contents();
            ob_end_clean();
            echo $placeholdersNotice;
        }
    }

    /**
     * addUpdateManualNoticeBlock.
     *
     * @return void
     */
    public function addUpdateManualNoticeBlock()
    {
        if (!defined('WDT_UMFF_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/update_manual_notice_block.inc.php';
            $placeholdersNotice = ob_get_contents();
            ob_end_clean();
            echo $placeholdersNotice;
        }
    }

    /**
     * addSQLQueryNotice.
     *
     * @return void
     */
    public function addSQLQueryNotice($connection)
    {
        if (!defined('WDT_SQLQ_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/sql_query_notice_block.inc.php';
            $sqlQueryNotice = ob_get_contents();
            ob_end_clean();
            echo $sqlQueryNotice;
        }
    }

    /**
     * addNoticeEditingOptions.
     *
     * @return void
     */
    public function addNoticeEditingOptions()
    {
        if (!defined('WDT_EDIT_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/editing_notice_block.inc.php';
            $editingNotice = ob_get_contents();
            ob_end_clean();
            echo $editingNotice;
        }
    }

    /**
     * addNewFixedHeaderAndColumnsOptions.
     *
     * @return void
     */
    public function addNewFixedHeaderAndColumnsOptions()
    {
        if (!defined('WDT_FCH_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/fixed_headers_and_columns_notice_block.inc.php';
            $fchNotice = ob_get_contents();
            ob_end_clean();
            echo $fchNotice;
        }
    }

    /**
     * addNewTableTypesInConstructor.
     *
     * @return void
     */
    public function addNewTableTypesInConstructor()
    {
        if (!defined('WDT_SQLC_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/sql_integration_notice_block.inc.php';
            $sqlConstructorNotice = ob_get_contents();
            ob_end_clean();
            echo $sqlConstructorNotice;
        }

        if (!defined('WDT_WP_QUERY_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/wp_posts_integration_notice_block.inc.php';
            $wpPostsConstructorNotice = ob_get_contents();
            ob_end_clean();
            echo $wpPostsConstructorNotice;
        }
    }

    /**
     * addSeparateConnectionSettings.
     *
     * @return void
     */
    public function addSeparateConnectionSettings()
    {
        if (!defined('WDT_SDBC_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/settings/tabs/separate_connection.php';
            $addElements = ob_get_contents();
            ob_end_clean();
            echo $addElements;
        }
    }

    /**
     * addGoogleSheetAPISettings.
     *
     * @return void
     */
    public function addGoogleSheetAPISettings()
    {
        if (!defined('WDT_GSAPI_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/settings/tabs/google_sheet_settings.php';
            $addGSAPIElements = ob_get_contents();
            ob_end_clean();
            echo $addGSAPIElements;
        }
    }

    /**
     * addPublicRestApiSettingsTabNav.
     *
     * @return void
     */
    public function addPublicRestApiSettingsTabNav()
    {
        if (!defined('WDT_PUBLIC_API_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/settings/tabs/public_rest_api_tab_nav.php';
            $addPublicApiTabNav = ob_get_contents();
            ob_end_clean();
            echo $addPublicApiTabNav;
        }
    }

    /**
     * addPublicRestApiSettings.
     *
     * @return void
     */
    public function addPublicRestApiSettings()
    {
        if (!defined('WDT_PUBLIC_API_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/settings/tabs/public_rest_api.php';
            $addPublicApiElements = ob_get_contents();
            ob_end_clean();
            echo $addPublicApiElements;
        }
    }

    /**
     * addForeignKeySettings.
     *
     * @return void
     */
    public function addForeignKeySettings()
    {
        if (!defined('WDT_FKEY_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/foreign_key_settings_block_notice.inc.php';
            $addForeignKeysElements = ob_get_contents();
            ob_end_clean();
            echo $addForeignKeysElements;
        }
    }

    /**
     * addFormulaEditorModal.
     *
     * @return void
     */
    public function addFormulaEditorModal($connection)
    {
        if (!defined('WDT_FCOL_INTEGRATION')) {
            ob_start();
            include WDT_ROOT_PATH . 'templates/admin/table-settings/formula_editor_modal_notice.inc.php';
            $formulaEditorModal = ob_get_contents();
            ob_end_clean();
            echo $formulaEditorModal;
        }
    }

    /**
     * Upsell Webhooks tab when Pro integration is not present.
     *
     * @return void
     */
    public function addWebhooksNoticeTab()
    {
        if (!defined('WDT_WEBHOOKS_INTEGRATION')) {
            include WDT_ROOT_PATH . 'templates/admin/table-settings/webhooks_notice_tab.inc.php';
        }
    }

    /**
     * Upsell Webhooks panel when Pro integration is not present.
     *
     * @return void
     */
    public function addWebhooksNoticePanel()
    {
        if (!defined('WDT_WEBHOOKS_INTEGRATION')) {
            include WDT_ROOT_PATH . 'templates/admin/table-settings/webhooks_notice_block.inc.php';
        }
    }
}
