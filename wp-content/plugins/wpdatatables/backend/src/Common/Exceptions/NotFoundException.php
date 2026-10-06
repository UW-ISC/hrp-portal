<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Common\Exceptions;

use Exception;

/**
 * Class NotFoundException — maps to HTTP 404.
 *
 * @package WPDataTables\Common\Exceptions
 */
class NotFoundException extends WDTException
{
    public function __construct($message = 'not_found', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
