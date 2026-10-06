<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Common\Helpers;

/**
 * Formula-column placeholder substitution for shortcode SQL.
 *
 * Legacy global {@see formulaFormat()} delegates here.
 *
 * @package WPDataTables\Common\Helpers
 */
class FormulaHelper
{
    /**
     * @param array  $headersInFormulaColumn Header keys referenced in the formula.
     * @param string $formulaColumn          Formula expression.
     * @param string $tableName              Table alias or name prefix.
     * @param string $leftSysIdentifier      Opening quote/identifier wrapper.
     * @param string $rightSysIdentifier     Closing quote/identifier wrapper.
     * @return string
     */
    public static function format(
        $headersInFormulaColumn,
        $formulaColumn,
        $tableName,
        $leftSysIdentifier,
        $rightSysIdentifier
    ): string {
        foreach ($headersInFormulaColumn as $headerColumnKey) {
            $formulaColumn = str_replace(
                $headerColumnKey,
                $tableName . '.' . $leftSysIdentifier . $headerColumnKey . $rightSysIdentifier,
                $formulaColumn
            );
        }

        return $formulaColumn;
    }
}
