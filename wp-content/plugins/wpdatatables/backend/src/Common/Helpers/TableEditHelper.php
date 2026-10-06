<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Common\Helpers;

/**
 * Frontend table-edit permission checks.
 *
 * Legacy global {@see wdtCurrentUserCanEdit()} delegates here.
 *
 * @package WPDataTables\Common\Helpers
 */
class TableEditHelper
{
    /**
     * @param string $tableEditorRoles Comma-separated role display names.
     * @param int    $id               Table ID.
     * @return bool
     */
    public static function currentUserCanEdit($tableEditorRoles, $id): bool
    {
        $wpRoles = new \WP_Roles();
        $userCanEdit = false;

        $tableEditorRoles = strtolower((string) $tableEditorRoles);
        $editorRoles = array();

        if (empty($tableEditorRoles)) {
            $userCanEdit = true;
        } else {
            $editorRoles = explode(',', $tableEditorRoles);

            $allRoles = $wpRoles->get_names();

            $currentUser = wp_get_current_user();
            if (!($currentUser instanceof \WP_User)) {
                return false;
            }

            foreach ($currentUser->roles as $userRole) {
                if (in_array(strtolower($allRoles[$userRole]), $editorRoles, true)) {
                    $userCanEdit = true;
                    break;
                }
            }
        }

        return apply_filters('wpdatatables_allow_edit_table', $userCanEdit, $editorRoles, $id);
    }
}
