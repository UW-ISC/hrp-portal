<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Common\Exceptions;

use Exception;

/**
 * Class ForbiddenException — maps to HTTP 403.
 *
 * Unlike a hard `wp_die()`, this lets the abstract Controller convert the
 * exception into a structured 403 response so REST and admin-ajax stay
 * consistent.
 *
 * @package WPDataTables\Common\Exceptions
 */
class ForbiddenException extends WDTException
{
    public function __construct($message = 'forbidden', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
