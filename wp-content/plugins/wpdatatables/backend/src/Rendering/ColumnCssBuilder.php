<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Rendering;

use WPDataTable;

/**
 * Per-column CSS rules for a rendered wpDataTable.
 *
 * The legacy {@see \WPDataTable::prepareRenderingRules()} loop used to append
 * these rules inline into `$_columnsCSS`; that body now lives here and the
 * facade delegates per column.
 *
 * @package WPDataTables\Rendering
 */
class ColumnCssBuilder
{
    /**
     * Resolve the sanitized CSS class suffix for a column header.
     *
     * @param object $column   column metadata row from the DB
     * @param int    $columnId fallback column index when the header is empty
     *
     * @return string e.g. `column-my-field`
     */
    public function resolveCssColumnHeader($column, $columnId)
    {
        if (sanitize_html_class(strtolower(str_replace(' ', '-', $column->orig_header))) === '') {
            return 'column-' . $columnId;
        }

        return 'column-' . sanitize_html_class(strtolower(str_replace(' ', '-', $column->orig_header)));
    }

    /**
     * Append per-column display CSS (text before/after, colour, alignment,
     * rotated headers) and apply the `wpdatatables_filter_columns_css` filter.
     *
     * @param WPDataTable $table
     * @param object      $column
     * @param string      $cssColumnHeader
     * @param string      $columnsCss    accumulated CSS so far
     * @param bool        $isSafari
     *
     * @return string updated CSS string
     */
    public function appendForColumn(WPDataTable $table, $column, $cssColumnHeader, $columnsCss, $isSafari)
    {
        if ($column->text_before != '') {
            $columnsCss .= "\n#{$table->getId()} > tbody > tr > td.{$cssColumnHeader}:not(:empty):before,
                                       \n#{$table->getId()} > tbody > tr.row-detail ul li.{$cssColumnHeader} span.columnValue:before
                                            { content: '{$column->text_before}' }";
        }
        if ($column->text_after != '') {
            $columnsCss .= "\n#{$table->getId()} > tbody > tr > td.{$cssColumnHeader}:not(:empty):after,
                                       \n#{$table->getId()} > tbody > tr.row-detail ul li.{$cssColumnHeader} span.columnValue:after
                                            { content: '{$column->text_after}' }";
        }

        if ($column->color != '') {
            $columnsCss .= "\n#{$table->getId()} > tbody > tr > td.{$cssColumnHeader}, "
                . "#{$table->getId()} > tbody > tr.row-detail ul li.{$cssColumnHeader}, "
                . "#{$table->getId()} > thead > tr > th.{$cssColumnHeader}, "
                . "#{$table->getId()} > .dtfh-floatingparent > th.{$cssColumnHeader}, "
                . "#{$table->getId()} > tfoot > tr > th.{$cssColumnHeader} { background-color: {$column->color} !important; }";
        }
        if ($column->column_align_fields != '') {
            $columnsCss .= "\n#{$table->getId()} > tbody > tr > td.{$cssColumnHeader} { text-align: {$column->column_align_fields} !important; }";
        }

        if ($column->column_align_header != '') {
            $columnsCss .= "\n#{$table->getId()} > thead > tr > th.{$cssColumnHeader} { text-align: {$column->column_align_header} !important; }";
            if ($table->isFixedHeaders()) {
                $columnsCss .= "\n#{$table->getId()} > div.dtfh-floatingparent.dtfh-floatingparenthead > table > thead > tr > th.{$cssColumnHeader} { text-align: {$column->column_align_header} !important; }";
            }
        }

        $currentSkin = $table->getTableSkin();
        $rotationSafariSpan = $isSafari ? 'span' : '';

        if ($column->column_rotate_header_name != '') {
            if ($isSafari) {
                $columnsCss .= "\n#{$table->getId()} >thead >tr >th.wdtheader.{$cssColumnHeader} {text-align:center; vertical-align: middle;}";
            }
            if ($column->column_rotate_header_name == '180') {
                $columnsCss .= "\n#{$table->getId()} >thead >tr >th.wdtheader.{$cssColumnHeader} {$rotationSafariSpan}{rotate: {$column->column_rotate_header_name}deg; writing-mode: vertical-rl; width: auto;}";
                $columnsCss .= "\n#{$table->getId()} >thead >tr >th.wdtheader.{$cssColumnHeader} div.tooltip.fade{rotate: 180deg; left:15px !important; top: 16px !important; writing-mode: horizontal-tb;}";
                $columnsCss .= "\n#{$table->getId()} >thead >tr >th.wdtheader.{$cssColumnHeader} div.tooltip.fade div.tooltip-arrow{display: none;}";
                $columnsCss .= "\n#{$table->getId()} .fixedHeader-floating >thead >tr >th.wdtheader.{$cssColumnHeader} {$rotationSafariSpan}{rotate: {$column->column_rotate_header_name}deg; writing-mode: vertical-rl; width: auto;}";
                $columnsCss .= "\n#{$table->getId()} .fixedHeader-floating >thead >tr >th.wdtheader.{$cssColumnHeader} div.tooltip.fade{rotate: 180deg; left:15px !important; top: 16px !important; writing-mode: horizontal-tb;}";
                $columnsCss .= "\n#{$table->getId()} .fixedHeader-floating >thead >tr >th.wdtheader.{$cssColumnHeader} div.tooltip.fade div.tooltip-arrow{display: none;}";

            } elseif ($column->column_rotate_header_name == '360') {
                $columnsCss .= "\n#{$table->getId()} >thead >tr >th.wdtheader.{$cssColumnHeader} {$rotationSafariSpan}{writing-mode: vertical-rl;}";
                $columnsCss .= "\n#{$table->getId()} >thead >tr >th.wdtheader.{$cssColumnHeader} div.tooltip.fade{writing-mode: horizontal-tb;}";
                $columnsCss .= "\n#{$table->getId()} >thead >tr >th.wdtheader.{$cssColumnHeader} div.tooltip.fade div.tooltip-arrow{display: none;}";
                $columnsCss .= "\n#{$table->getId()} .fixedHeader-floating >thead >tr >th.wdtheader.{$cssColumnHeader} {$rotationSafariSpan}{writing-mode: vertical-rl;}";
                $columnsCss .= "\n#{$table->getId()} .fixedHeader-floating >thead >tr >th.wdtheader.{$cssColumnHeader} div.tooltip.fade{writing-mode: horizontal-tb;}";
                $columnsCss .= "\n#{$table->getId()} .fixedHeader-floating >thead >tr >th.wdtheader.{$cssColumnHeader} div.tooltip.fade div.tooltip-arrow{display: none;}";
            }
            if (in_array($currentSkin, ['graphite', 'light'])) {
                $columnsCss .= "\n#{$table->getId()} >thead >tr >th.wdtheader.{$cssColumnHeader}.sorting_asc:after{position: relative !important; left: -10px !important; top: 4px !important;}";
                $columnsCss .= "\n#{$table->getId()} >thead >tr >th.wdtheader.{$cssColumnHeader}.sorting_desc:after{position: relative !important; left: -10px !important; top: 4px !important;}";
                $columnsCss .= "\n#{$table->getId()} >thead >tr >th.wdtheader.{$cssColumnHeader}.sorting:after{position: relative; left: -10px; top: 0px;}";
                $columnsCss .= "\n#{$table->getId()} .fixedHeader-floating >thead >tr >th.wdtheader.{$cssColumnHeader}.sorting_asc:after{position: relative !important; left: -10px !important; top: 4px !important;}";
                $columnsCss .= "\n#{$table->getId()} .fixedHeader-floating >thead >tr >th.wdtheader.{$cssColumnHeader}.sorting_desc:after{position: relative !important; left: -10px !important; top: 4px !important;}";
                $columnsCss .= "\n#{$table->getId()} .fixedHeader-floating >thead >tr >th.wdtheader.{$cssColumnHeader}.sorting:after{position: relative; left: -10px; top: 0px;}";
            }
            if (in_array($currentSkin, ['graphite', 'light']) && $column->column_rotate_header_name == '180') {
                $columnsCss .= "\n#{$table->getId()} >thead >tr >th.wdtheader.{$cssColumnHeader}{position: relative; bottom: 0.5px; z-index: 0; left:0.5px; padding: 7px 8px;}";
                $columnsCss .= "\n#{$table->getId()} .fixedHeader-floating >thead >tr >th.wdtheader.{$cssColumnHeader}{position: relative; bottom: 0.5px; z-index: 0; left:0.5px; padding: 7px 8px;}";
            }
            if (in_array($currentSkin, [
                    'purple',
                    'aqua',
                    'raspberry-cream',
                    'mojito',
                    'dark-mojito',
                ]) && $column->column_rotate_header_name == '180') {
                $columnsCss .= "\n#{$table->getId()} >thead >tr >th.wdtheader.{$cssColumnHeader}{position: relative; bottom: 1px; z-index:0;}";
                $columnsCss .= "\n#{$table->getId()} .fixedHeader-floating >thead >tr >th.wdtheader.{$cssColumnHeader}{position: relative; bottom: 1px; z-index:0;}";
            }
        }

        $columnsCss = apply_filters_deprecated(
            'wpdt_filter_columns_css',
            array($columnsCss, $column, $table->getId(), $cssColumnHeader),
            WDT_INITIAL_STARTER_VERSION,
            'wpdatatables_filter_columns_css'
        );

        return apply_filters('wpdatatables_filter_columns_css', $columnsCss, $column, $table->getId(), $cssColumnHeader);
    }
}
