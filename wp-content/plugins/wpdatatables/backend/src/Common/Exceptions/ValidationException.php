<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Common\Exceptions;

use Exception;

/**
 * Class ValidationException — maps to HTTP 422.
 *
 * @package WPDataTables\Common\Exceptions
 */
class ValidationException extends WDTException
{
    public function __construct($message = 'validation_error', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
