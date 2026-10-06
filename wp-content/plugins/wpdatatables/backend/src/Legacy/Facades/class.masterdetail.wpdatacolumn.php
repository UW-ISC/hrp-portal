<?php

defined('ABSPATH') or die('Access denied.');

/**
 * WDTCoreMasterdetailColumn
 *
 * Core fallback alias. The implementation lives in
 * {@see WPDataTables\Entity\Column\MasterdetailColumn}.
 *
 * The legacy MasterdetailWDTColumn name is intentionally not used because PHP
 * class names are case-insensitive and the Master-Detail add-on owns
 * MasterDetailWDTColumn.
 */
class WDTCoreMasterdetailColumn extends \WPDataTables\Entity\Column\MasterdetailColumn
{
}
