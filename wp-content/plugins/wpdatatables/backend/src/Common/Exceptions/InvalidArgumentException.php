<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Common\Exceptions;

use Exception;

/**
 * Class InvalidArgumentException — maps to HTTP 400.
 *
 * @package WPDataTables\Common\Exceptions
 */
class InvalidArgumentException extends WDTException
{
    public function __construct($message = 'invalid_argument', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
