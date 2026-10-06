<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Common\Helpers;

/**
 * Strips dangerous SQL keywords from user-supplied query fragments.
 *
 * Legacy global {@see wdtSanitizeQuery()} delegates here.
 *
 * @package WPDataTables\Common\Helpers
 */
class QuerySanitizer
{
    /**
     * @param string $query Raw query or variable fragment.
     * @return string
     */
    public static function sanitize(string $query): string
    {
        $query = str_replace('DELETE', '', $query);
        $query = str_replace('DELETE ', '', $query);
        $query = str_replace(' DELETE ', '', $query);
        $query = str_replace(' delete ', '', $query);
        $query = str_replace('DROP', '', $query);
        $query = str_replace('DROP ', '', $query);
        $query = str_replace(' DROP ', '', $query);
        $query = str_replace(' drop ', '', $query);
        $query = str_replace('INSERT ', '', $query);
        $query = str_replace(' INSERT ', '', $query);
        $query = str_replace(' insert ', '', $query);
        $query = str_replace('UPDATE ', '', $query);
        $query = str_replace(' UPDATE ', '', $query);
        $query = str_replace(' update ', '', $query);
        $query = str_replace('TRUNCATE', '', $query);
        $query = str_replace('TRUNCATE ', '', $query);
        $query = str_replace(' TRUNCATE ', '', $query);
        $query = str_replace(' truncate ', '', $query);
        $query = str_replace('CREATE', '', $query);
        $query = str_replace('CREATE ', '', $query);
        $query = str_replace(' CREATE ', '', $query);
        $query = str_replace(' create ', '', $query);
        $query = str_replace('ALTER', '', $query);
        $query = str_replace('ALTER ', '', $query);
        $query = str_replace(' ALTER ', '', $query);
        $query = str_replace(' alter ', '', $query);
        $query = stripslashes($query);
        $query = rtrim($query, "; \t\n");

        $query = apply_filters_deprecated(
            'wpdt_sanitize_query',
            array($query),
            WDT_INITIAL_STARTER_VERSION,
            'wpdatatables_sanitize_query'
        );

        return apply_filters('wpdatatables_sanitize_query', $query);
    }
}
