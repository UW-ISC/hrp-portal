<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Rendering;

/**
 * Renders per-table custom CSS/JS blocks for the frontend.
 *
 * Legacy globals {@see wdtRenderScriptStyleBlock()} and
 * {@see wdtTableRenderScriptStyleBlock()} delegate here.
 *
 * @package WPDataTables\Rendering
 */
class StyleBlockRenderer
{
    /**
     * @param int $tableID wpDataTables table ID.
     * @return string
     */
    public static function renderScriptStyleBlock($tableID): string
    {
        $customJs = get_option('wdtCustomJs');
        $scriptBlockHtml = '';
        $styleBlockHtml = '';
        $wpDataTable = \WDTConfigController::loadTableFromDB($tableID, false);

        if ($customJs) {
            $scriptBlockHtml .= '<script type="text/javascript">' . stripslashes_deep(html_entity_decode($customJs)) . '</script>';
        }
        $returnHtml = $scriptBlockHtml;

        $wdtFontColorSettings = get_option('wdtFontColorSettings');
        if (!empty($wdtFontColorSettings)) {
            ob_start();
            include WDT_TEMPLATE_PATH . 'frontend/style_block.inc.php';
            $styleBlockHtml = ob_get_contents();
            ob_end_clean();
            $styleBlockHtml = apply_filters('wpdatatables_filter_style_block', $styleBlockHtml, $wpDataTable->id);
        }

        $returnHtml .= $styleBlockHtml;

        return $returnHtml;
    }

    /**
     * @param object $obj Runtime table object with getters used by the style template.
     * @return string
     */
    public static function renderTableScriptStyleBlock($obj): string
    {
        $styleBlockHtml = '';
        $returnData = "<style>\n";

        $tableCustomCss = $obj->getTableCustomCss();

        if ($tableCustomCss) {
            $returnData .= stripslashes_deep($tableCustomCss);
        }

        if ($obj->getTableBorderRemoval()) {
            $returnData .= ".wpDataTablesWrapper table.wpDataTable[data-wpdatatable_id='" . $obj->getWpId() . "'] > tbody > tr > td{ border: none !important; }\n";
            $returnData .= ".wpDataTablesWrapper table.wpDataTable[data-wpdatatable_id='" . $obj->getWpId() . "'] > thead { border: none !important; }\n";
            $returnData .= ".wpDataTablesWrapper table.wpDataTable[data-wpdatatable_id='" . $obj->getWpId() . "'] > tfoot > tr > td{ border: none !important; }\n";
            $returnData .= ".wpDataTablesWrapper table.wpDataTable[data-wpdatatable_id='" . $obj->getWpId() . "'] > tfoot { border: none !important; }\n";
        }
        if ($obj->getTableBorderRemovalHeader()) {
            $returnData .= ".wpDataTablesWrapper table.wpDataTable[data-wpdatatable_id='" . $obj->getWpId() . "'] > thead > tr > th{ border: none !important; }\n";
        }

        $returnData .= "</style>\n";

        $returnHtml = $returnData;
        $wdtTableFontColorSettings = $obj->getTableFontColorSettings();

        if (!empty($wdtTableFontColorSettings)) {
            ob_start();
            include WDT_TEMPLATE_PATH . 'frontend/style_table_block.inc.php';
            $styleBlockHtml = ob_get_contents();
            ob_end_clean();
            $styleBlockHtml = apply_filters('wpdatatables_filter_style_table_block', $styleBlockHtml, $obj->getWpId());
        }

        $returnHtml .= $styleBlockHtml;

        return $returnHtml;
    }
}
